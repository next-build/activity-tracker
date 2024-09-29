<?php

namespace NextBuild\ActivityTracker;

trait RegistersWatchers
{
    /**
     * The class names of the registered watchers.
     *
     * @var array
     */
    protected static $watchers = [];

    /**
     * Determine if a given watcher has been registered.
     *
     * @param  string  $class
     * @return bool
     */
    public static function hasWatcher($class)
    {
        return in_array($class, static::$watchers);
    }

    /**
     * Register the configured Activity Tracker watchers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected static function registerWatchers($app)
    {
        if (config('activity-tracker.enabled')) {
            $watcher = [
                'class' => Watchers\RequestWatcher::class,
                'size_limit' => config('activity-tracker.response_size_limit'),
                'ignore_http_methods' => config('activity-tracker.ignore_http_methods'),
                'ignore_status_codes' => config('activity-tracker.ignore_status_codes'),
            ];

            $watcher = $app->make(is_string($watcher['class']) ? $watcher['class'] : $watcher, [
                'options' => is_array($watcher) ? $watcher : [],
            ]);

            static::$watchers[] = get_class($watcher);

            $watcher->register($app);
        }
    }
}
