<?php

namespace NextBuild\ActivityTracker;

use Closure;
use Exception;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Testing\Fakes\EventFake;
use NextBuild\ActivityTracker\Contracts\EntriesRepository;
use NextBuild\ActivityTracker\Contracts\TerminableRepository;
use Throwable;

class ActivityTracker
{
    use AuthorizesRequests;
    // use ExtractsMailableTags;
    use ListensForStorageOpportunities;
    use RegistersWatchers;


    /**
     * The callbacks that filter the entries that should be recorded.
     *
     * @var array
     */
    public static $filterUsing = [];

    /**
     * The callbacks that filter the batches that should be recorded.
     *
     * @var array
     */
    public static $filterBatchUsing = [];

    /**
     * The callback executed after queuing a new entry.
     *
     * @var \Closure
     */
    public static $afterRecordingHook;

    /**
     * The callbacks executed after storing the entries.
     *
     * @var \Closure
     */
    public static $afterStoringHooks = [];

    /**
     * The callbacks that add tags to the record.
     *
     * @var \Closure[]
     */
    public static $tagUsing = [];

    /**
     * The list of queued entries to be stored.
     *
     * @var array
     */
    public static $entriesQueue = [];

    /**
     * The list of queued entry updates.
     *
     * @var array
     */
    public static $updatesQueue = [];

    /**
     * The list of hidden request headers.
     *
     * @var array
     */
    public static $hiddenRequestHeaders = [
        'authorization',
        'php-auth-pw',
    ];

    /**
     * The list of hidden request parameters.
     *
     * @var array
     */
    public static $hiddenRequestParameters = [
        'password',
        'password_confirmation',
    ];

    /**
     * The list of hidden response parameters.
     *
     * @var array
     */
    public static $hiddenResponseParameters = [];

    /**
     * Indicates if Activity Tracker should ignore events fired by Laravel.
     *
     * @var bool
     */
    public static $ignoreFrameworkEvents = true;

    /**
     * Indicates if Activity Tracker should use the dark theme.
     *
     * @var bool
     */
    public static $useDarkTheme = false;

    /**
     * Indicates if Activity Tracker should record entries.
     *
     * @var bool
     */
    public static $shouldRecord = false;

    /**
     * Register the Activity Tracker watchers and start recording if necessary.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public static function start($app)
    {
        if (! config('activity-tracker.enabled')) {
            return;
        }

        static::registerWatchers($app);

        if (! static::runningWithinOctane($app) &&
            (static::runningApprovedArtisanCommand($app) ||
            static::handlingApprovedRequest($app))
        ) {
            static::startRecording();
        }
    }

    /**
     * Determine if Activity Tracker is running within Octane.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return bool
     */
    protected static function runningWithinOctane($app)
    {
        return isset($_SERVER['LARAVEL_OCTANE']);
    }

    /**
     * Determine if the application is running an approved command.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return bool
     */
    protected static function runningApprovedArtisanCommand($app)
    {
        return $app->runningInConsole() && ! in_array(
            $_SERVER['argv'][1] ?? null,
            array_merge([
                // 'migrate',
                'migrate:rollback',
                'migrate:fresh',
                // 'migrate:refresh',
                'migrate:reset',
                'migrate:install',
                'package:discover',
                'queue:listen',
                'queue:work',
                'horizon',
                'horizon:work',
                'horizon:supervisor',
            ], config('activity-tracker.ignoreCommands', []), config('activity-tracker.ignore_commands', []))
        );
    }

    /**
     * Determine if the application is handling an approved request.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return bool
     */
    protected static function handlingApprovedRequest($app)
    {
        if ($app->runningInConsole()) {
            return false;
        }

        return static::requestIsToApprovedDomain($app['request']) &&
            static::requestIsToApprovedUri($app['request']);
    }

    /**
     * Determine if the request is to an approved domain.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected static function requestIsToApprovedDomain($request): bool
    {
        return is_null(config('activity-tracker.domain')) ||
            config('activity-tracker.domain') !== $request->getHost();
    }

    /**
     * Determine if the request is to an approved URI.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected static function requestIsToApprovedUri($request)
    {
        if (! empty($only = config('activity-tracker.only_paths', []))) {
            return $request->is($only);
        }

        return ! $request->is(
            collect([
                'activity-tracker-api*',
                'vendor/activity-tracker*',
                (config('horizon.path') ?? 'horizon').'*',
                'vendor/horizon*',
            ])
            ->merge(config('activity-tracker.ignore_paths', []))
            ->unless(is_null(config('activity-tracker.path')), function ($paths) {
                return $paths->prepend(config('activity-tracker.path').'*');
            })
            ->all()
        );
    }

    /**
     * Start recording entries.
     *
     * @return void
     */
    public static function startRecording()
    {
        $recordingPaused = false;

        try {
            $recordingPaused = cache('activity-tracker:pause-recording');
        } catch (Exception) {
            //
        }

        static::$shouldRecord = ! $recordingPaused;
    }

    /**
     * Stop recording entries.
     *
     * @return void
     */
    public static function stopRecording()
    {
        static::$shouldRecord = false;
    }

    /**
     * Execute the given callback without recording Activity Tracker entries.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public static function withoutRecording($callback)
    {
        $shouldRecord = static::$shouldRecord;

        static::$shouldRecord = false;

        try {
            return call_user_func($callback);
        } finally {
            static::$shouldRecord = $shouldRecord;
        }
    }

    /**
     * Determine if Activity Tracker is recording.
     *
     * @return bool
     */
    public static function isRecording()
    {
        return static::$shouldRecord && ! app('events') instanceof EventFake;
    }

    /**
     * Record the given entry.
     *
     * @param  string  $type
     * @param  \NextBuild\ActivityTracker\IncomingEntry  $entry
     * @return void
     */
    protected static function record(string $type, IncomingEntry $entry)
    {
        if (! static::isRecording()) {
            return;
        }

        try {
            if (Auth::hasResolvedGuards() && Auth::hasUser()) {
                $entry->user(Auth::user());
            }
        } catch (Throwable $e) {
            // Do nothing.
        }

        $entry->ip = $entry->content['ip_address'] ?? null;

        $entry->type($type)->tags(Arr::collapse(array_map(function ($tagCallback) use ($entry) {
            return $tagCallback($entry);
        }, static::$tagUsing)));

        static::withoutRecording(function () use ($entry) {
            if (collect(static::$filterUsing)->every->__invoke($entry)) {
                static::$entriesQueue[] = $entry;
            }

            if (static::$afterRecordingHook) {
                call_user_func(static::$afterRecordingHook, new static, $entry);
            }
        });
    }

    /**
     * Record the given entry.
     *
     * @param  \NextBuild\ActivityTracker\IncomingEntry  $entry
     * @return void
     */
    public static function recordRequest(IncomingEntry $entry)
    {
        static::record(EntryType::REQUEST, $entry);
    }

    /**
     * Flush all entries in the queue.
     *
     * @return static
     */
    public static function flushEntries()
    {
        static::$entriesQueue = [];

        return new static;
    }

    /**
     * Record the given exception.
     *
     * @param  \Throwable|\Exception  $e
     * @param  array  $tags
     * @return void
     */
    public static function catch($e, $tags = [])
    {
        event(new MessageLogged('error', $e->getMessage(), [
            'exception' => $e,
            'activity-tracker' => $tags,
        ]));
    }

    /**
     * Set the callback that filters the entries that should be recorded.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function filter(Closure $callback)
    {
        static::$filterUsing[] = $callback;

        return new static;
    }

    /**
     * Set the callback that filters the batches that should be recorded.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function filterBatch(Closure $callback)
    {
        static::$filterBatchUsing[] = $callback;

        return new static;
    }

    /**
     * Set the callback that will be executed after an entry is recorded in the queue.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function afterRecording(Closure $callback)
    {
        static::$afterRecordingHook = $callback;

        return new static;
    }

    /**
     * Add a callback that will be executed after an entry is stored.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function afterStoring(Closure $callback)
    {
        static::$afterStoringHooks[] = $callback;

        return new static;
    }

    /**
     * Add a callback that adds tags to the record.
     *
     * @param  \Closure  $callback
     * @return static
     */
    public static function tag(Closure $callback)
    {
        static::$tagUsing[] = $callback;

        return new static;
    }

    /**
     * Store the queued entries and flush the queue.
     *
     * @param  \NextBuild\ActivityTracker\Contracts\EntriesRepository  $storage
     * @return void
     */
    public static function store(EntriesRepository $storage)
    {
        if (empty(static::$entriesQueue) && empty(static::$updatesQueue)) {
            return;
        }

        static::withoutRecording(function () use ($storage) {
            if (! collect(static::$filterBatchUsing)->every->__invoke(collect(static::$entriesQueue))) {
                static::flushEntries();
            }

            try {
                $batchId = Str::orderedUuid()->toString();

                $storage->store(static::collectEntries($batchId));

                $updateResult = $storage->update(static::collectUpdates($batchId)) ?: Collection::make();

                if ($storage instanceof TerminableRepository) {
                    $storage->terminate();
                }

                collect(static::$afterStoringHooks)->every->__invoke(static::$entriesQueue, $batchId);
            } catch (Throwable $e) {
                app(ExceptionHandler::class)->report($e);
            }
        });

        static::$entriesQueue = [];
        static::$updatesQueue = [];
    }

    /**
     * Collect the entries for storage.
     *
     * @param  string  $batchId
     * @return \Illuminate\Support\Collection
     */
    protected static function collectEntries($batchId)
    {
        return collect(static::$entriesQueue)
            ->each(function ($entry) use ($batchId) {
                $entry->batchId($batchId);

                if ($entry->isDump()) {
                    $entry->assignEntryPointFromBatch(static::$entriesQueue);
                }
            });
    }

    /**
     * Collect the updated entries for storage.
     *
     * @param  string  $batchId
     * @return \Illuminate\Support\Collection
     */
    protected static function collectUpdates($batchId)
    {
        return collect(static::$updatesQueue)
            ->each(function ($entry) use ($batchId) {
                $entry->change(['updated_batch_id' => $batchId]);
            });
    }

    /**
     * Hide the given request header.
     *
     * @param  array  $headers
     * @return static
     */
    public static function hideRequestHeaders(array $headers)
    {
        static::$hiddenRequestHeaders = array_values(array_unique(array_merge(
            static::$hiddenRequestHeaders,
            $headers
        )));

        return new static;
    }

    /**
     * Hide the given request parameters.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function hideRequestParameters(array $attributes)
    {
        static::$hiddenRequestParameters = array_merge(
            static::$hiddenRequestParameters,
            $attributes
        );

        return new static;
    }

    /**
     * Hide the given response parameters.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function hideResponseParameters(array $attributes)
    {
        static::$hiddenResponseParameters = array_values(array_unique(array_merge(
            static::$hiddenResponseParameters,
            $attributes
        )));

        return new static;
    }

    /**
     * Specifies that Activity Tracker should record events fired by Laravel.
     *
     * @return static
     */
    public static function recordFrameworkEvents()
    {
        static::$ignoreFrameworkEvents = false;

        return new static;
    }

    /**
     * Specifies that Activity Tracker should use the dark theme.
     *
     * @return static
     */
    public static function night()
    {
        static::$useDarkTheme = true;

        return new static;
    }

    /**
     * Register the Activity Tracker user avatar callback.
     *
     * @param  \Closure  $callback
     * @return static
     */
    // public static function avatar(Closure $callback)
    // {
    //     Avatar::register($callback);

    //     return new static;
    // }

    /**
     * Get the default JavaScript variables for Activity Tracker.
     *
     * @return array
     */
    public static function scriptVariables()
    {
        return [
            'path' => config('activity-tracker.path'),
            'timezone' => config('app.timezone'),
            'recording' => ! cache('activity-tracker:pause-recording'),
        ];
    }
}
