<?php

namespace NextBuild\ActivityTracker\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use NextBuild\ActivityTracker\Contracts\EntriesRepository;
use NextBuild\ActivityTracker\Storage\EntryQueryOptions;

abstract class EntryController extends Controller
{
    /**
     * The entry type for the controller.
     *
     * @return string
     */
    abstract protected function entryType();

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    abstract protected function watcher();

    /**
     * List the entries of the given type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \NextBuild\ActivityTracker\Contracts\EntriesRepository  $storage
     * @return \Illuminate\Http\JsonResponse
     */

    //  EntriesRepository $storage
    public function index(Request $request)
    {
        $entries = \NextBuild\ActivityTracker\Storage\VisitorIpModel::
            // ->withTelescopeOptions($type, $options)
            // ->take($options->limit)
            orderByDesc('id')
            ->get()
            ->reject(function ($entry) {
                return ! is_array($entry->content);
            })->map(function ($entry) {
                return $entry;
            })->values();

            // $storage->get(
            //     $this->entryType(),
            //     EntryQueryOptions::fromRequest($request)
            // )

        return response()->json([
            'entries' => $entries,
            'status' => $this->status(),
        ]);
    }


    public function show($id)
    {
        // $entry = $storage->find($id)->generateAvatar();
        $entry = \NextBuild\ActivityTracker\Storage\VisitorIpModel::where('uuid', $id)->first();
        return response()->json([
            'entry' => $entry,
            // 'batch' => $storage->get(null, EntryQueryOptions::forBatchId($entry->batchId)->limit(-1)),
        ]);
    }

    function requestIndex($uuid)
    {
        $entries = \NextBuild\ActivityTracker\Storage\VisitorRequestModel::where('visitor_ip_uuid', $uuid)
        ->orderByDesc('created_at')
        ->paginate(10);

        return response()->json([
            'entries' => $entries,
        ]);
    }

    function requestShow($visitor_uuid, $request_uuid)
    {
        $entry = \NextBuild\ActivityTracker\Storage\VisitorRequestModel::where('uuid', $request_uuid)->first();
        return response()->json([
            'entry' => $entry,
        ]);
    }

    /**
     * Determine the watcher recording status.
     *
     * @return string
     */
    protected function status()
    {
        if (! config('activity-tracker.enabled', false)) {
            return 'disabled';
        }

        if (cache('activity-tracker:pause-recording', false)) {
            return 'paused';
        }

        $watcher = config('activity-tracker.watchers.'.$this->watcher());

        if (! $watcher || (isset($watcher['enabled']) && ! $watcher['enabled'])) {
            return 'off';
        }

        return 'enabled';
    }
}
