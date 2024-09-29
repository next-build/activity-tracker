<?php

namespace NextBuild\ActivityTracker\Http\Controllers;

use Illuminate\Routing\Controller;
use NextBuild\ActivityTracker\Contracts\ClearableRepository;

class EntriesController extends Controller
{
    /**
     * Delete all of the entries from storage.
     *
     * @param  \NextBuild\ActivityTracker\Contracts\ClearableRepository  $storage
     * @return void
     */
    public function destroy(ClearableRepository $storage)
    {
        $storage->clear();
    }
}
