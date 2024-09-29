<?php

namespace NextBuild\ActivityTracker\Contracts;

use Illuminate\Support\Collection;
use NextBuild\ActivityTracker\EntryResult;
use NextBuild\ActivityTracker\Storage\EntryQueryOptions;

interface EntriesRepository
{
    /**
     * Return an entry with the given ID.
     *
     * @param  mixed  $id
     * @return \NextBuild\ActivityTracker\EntryResult
     */
    public function find($id);

    /**
     * Return all the entries of a given type.
     *
     * @param  string|null  $type
     * @param  \NextBuild\ActivityTracker\Storage\EntryQueryOptions  $options
     * @return \Illuminate\Support\Collection|\NextBuild\ActivityTracker\EntryResult[]
     */
    public function get($type, EntryQueryOptions $options);

    /**
     * Store the given entries.
     *
     * @param  \Illuminate\Support\Collection|\NextBuild\ActivityTracker\IncomingEntry[]  $entries
     * @return void
     */
    public function store(Collection $entries);

    /**
     * Store the given entry updates and return the failed updates.
     *
     * @param  \Illuminate\Support\Collection|\NextBuild\ActivityTracker\EntryUpdate[]  $updates
     * @return \Illuminate\Support\Collection|null
     */
    public function update(Collection $updates);

    /**
     * Load the monitored tags from storage.
     *
     * @return void
     */
    public function loadMonitoredTags();

    /**
     * Determine if any of the given tags are currently being monitored.
     *
     * @param  array  $tags
     * @return bool
     */
    public function isMonitoring(array $tags);

    /**
     * Get the list of tags currently being monitored.
     *
     * @return array
     */
    public function monitoring();

    /**
     * Begin monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function monitor(array $tags);

    /**
     * Stop monitoring the given list of tags.
     *
     * @param  array  $tags
     * @return void
     */
    public function stopMonitoring(array $tags);
}
