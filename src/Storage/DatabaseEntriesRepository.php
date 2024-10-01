<?php

namespace NextBuild\ActivityTracker\Storage;

use DateTimeInterface;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use NextBuild\ActivityTracker\Contracts\ClearableRepository;
use NextBuild\ActivityTracker\Contracts\EntriesRepository as Contract;
use NextBuild\ActivityTracker\Contracts\PrunableRepository;
use NextBuild\ActivityTracker\Contracts\TerminableRepository;
use NextBuild\ActivityTracker\EntryResult;
use NextBuild\ActivityTracker\EntryType;
use NextBuild\ActivityTracker\IncomingEntry;

class DatabaseEntriesRepository implements Contract, ClearableRepository, PrunableRepository, TerminableRepository
{
    /**
     * The database connection name that should be used.
     *
     * @var string
     */
    protected $connection;

    /**
     * The number of entries that will be inserted at once into the database.
     *
     * @var int
     */
    protected $chunkSize = 1000;

    /**
     * The tags currently being monitored.
     *
     * @var array|null
     */
    protected $monitoredTags;

    /**
     * Create a new database repository.
     *
     * @param  string  $connection
     * @param  int  $chunkSize
     * @return void
     */
    public function __construct(string $connection, int $chunkSize = null)
    {
        $this->connection = $connection;

        if ($chunkSize) {
            $this->chunkSize = $chunkSize;
        }
    }

    /**
     * Find the entry with the given ID.
     *
     */
    public function find($id)
    {
        $entry = VisitorIpModel::on($this->connection)
        ->whereUuid($id)->firstOrFail();

        return response()->json($entry, 200);
    }

    /**
     * Return all the entries of a given type.
     *
     * @param  string|null  $type
     * @param  \NextBuild\ActivityTracker\Storage\EntryQueryOptions  $options
     * @return \Illuminate\Support\Collection|\NextBuild\ActivityTracker\EntryResult[]
     */
    public function get($type, EntryQueryOptions $options)
    {
        return VisitorIpModel::on($this->connection)
            ->take($options->limit)
            ->orderByDesc('id')
            ->get()
            ->reject(function ($entry) {
                return ! is_array($entry->content);
            })->map(function ($entry) {
                return $entry;
            })->values();

    }

    /**
     * Store the given entry updates and return the failed updates.
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function update(Collection $updates)
    {
        $failedUpdates = [];

        foreach ($updates as $update) {
            $entry = $this->table('telescope_entries')
                            ->where('uuid', $update->uuid)
                            ->where('type', $update->type)
                            ->first();

            if (! $entry) {
                $failedUpdates[] = $update;

                continue;
            }

            $content = json_encode(array_merge(
                json_decode($entry->content ?? $entry['content'] ?? [], true) ?: [], $update->changes
            ));

            $this->table('telescope_entries')
                            ->where('uuid', $update->uuid)
                            ->where('type', $update->type)
                            ->update(['content' => $content]);

            // $this->updateTags($update);
        }

        return collect($failedUpdates);
    }

    /**
     * Determine if any of the given tags are currently being monitored.
     *
     * @param  array  $tags
     * @return bool
     */
    public function isMonitoring(array $tags)
    {
        return count(array_intersect($tags, $this->monitoredTags)) > 0;
    }

    /**
     * Get the list of tags currently being monitored.
     *
     * @return array
     */
    public function monitoring()
    {
        return $this->table('telescope_monitoring')->pluck('tag')->all();
    }

    /**
     * Begin monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function monitor(array $tags)
    {
        $tags = array_diff($tags, $this->monitoring());

        if (empty($tags)) {
            return;
        }

        $this->table('telescope_monitoring')
                    ->insert(collect($tags)
                    ->mapWithKeys(function ($tag) {
                        return ['tag' => $tag];
                    })->all());
    }

    /**
     * Stop monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function stopMonitoring(array $tags)
    {
        $this->table('telescope_monitoring')->whereIn('tag', $tags)->delete();
    }

    /**
     * Prune all of the entries older than the given date.
     *
     * @param  \DateTimeInterface  $before
     * @param  bool  $keepExceptions
     * @return int
     */
    public function prune(DateTimeInterface $before, $keepExceptions)
    {
        $query = $this->table('telescope_entries')
                ->where('created_at', '<', $before);

        if ($keepExceptions) {
            $query->where('type', '!=', 'exception');
        }

        $totalDeleted = 0;

        do {
            $deleted = $query->take($this->chunkSize)->delete();

            $totalDeleted += $deleted;
        } while ($deleted !== 0);

        return $totalDeleted;
    }

    /**
     * Clear all the entries.
     *
     * @return void
     */
    public function clear()
    {
        do {
            $deleted = $this->table('telescope_entries')->take($this->chunkSize)->delete();
        } while ($deleted !== 0);

        do {
            $deleted = $this->table('telescope_monitoring')->take($this->chunkSize)->delete();
        } while ($deleted !== 0);
    }

    /**
     * Perform any clean-up tasks needed after storing Activity Tracker entries.
     *
     * @return void
     */
    public function terminate()
    {
        $this->monitoredTags = null;
    }

    /**
     * Get a query builder instance for the given table.
     *
     * @param  string  $table
     * @return \Illuminate\Database\Query\Builder
     */
    protected function table($table)
    {
        return DB::connection($this->connection)->table($table);
    }
}
