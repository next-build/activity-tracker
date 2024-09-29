<?php

namespace NextBuild\ActivityTracker\Http\Controllers;

use NextBuild\ActivityTracker\EntryType;
use NextBuild\ActivityTracker\Watchers\RequestWatcher;

class RequestsController extends EntryController
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::REQUEST;
    }

    // function analytics()
    // {
    //     return 'DONE';
    // }

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    protected function watcher()
    {
        return RequestWatcher::class;
    }
}
