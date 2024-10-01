<?php

namespace NextBuild\ActivityTracker\Contracts;

use Illuminate\Support\Collection;
use NextBuild\ActivityTracker\Storage\EntryQueryOptions;

interface EntriesRepository
{
    /**
     * Return an entry with the given ID.
     *
     * @param  mixed  $id
     */
    public function find($id);

    /**
     * Return all the entries of a given type.
     *
     * @param  string|null  $type
     * @param  \NextBuild\ActivityTracker\Storage\EntryQueryOptions  $options
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
     * @return \Illuminate\Support\Collection|null
     */
    public function update(Collection $updates);

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
