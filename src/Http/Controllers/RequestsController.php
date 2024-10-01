<?php

namespace NextBuild\ActivityTracker\Http\Controllers;

use NextBuild\ActivityTracker\EntryType;
use NextBuild\ActivityTracker\Watchers\RequestWatcher;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use NextBuild\ActivityTracker\Storage\VisitorIpModel;
use NextBuild\ActivityTracker\Storage\VisitorRequestModel;

class RequestsController extends Controller
{
    /**
     * List the entries of the given type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
    */
    public function index(Request $request): JsonResponse
    {
        $entries = VisitorIpModel::orderByDesc('id')->get()
        ->reject(function ($entry) {
            return ! is_array($entry->content);
        })->map(function ($entry) {
            return $entry;
        })->values();

        return response()->json([
            'entries' => $entries,
            'status' => $this->status(),
        ], 200);
    }

    /**
     * @param  mixed  $uuid
     */
    public function show($uuid): JsonResponse
    {
        $entry = VisitorIpModel::where('uuid', $uuid)->first();

        return response()->json([
            'entry' => $entry,
        ], 200);
    }

    /**
     * @param  mixed  $uuid
     */
    function requestIndex($uuid): JsonResponse
    {
        $entries = VisitorRequestModel::where('visitor_ip_uuid', $uuid)
        ->orderByDesc('created_at')
        ->paginate(10);

        return response()->json([
            'entries' => $entries,
        ], 200);
    }

    /**
     * @param  mixed  $visitor_uuid
     * @param  mixed  $request_uuid
     */
    function requestShow($visitor_uuid, $request_uuid): JsonResponse
    {
        $entry = VisitorRequestModel::where('uuid', $request_uuid)->first();

        return response()->json([
            'entry' => $entry,
        ], 200);
    }

    /**
     * Dump all records
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(): JsonResponse
    {
        VisitorIpModel::truncate();
        VisitorRequestModel::truncate();
        return response()->json([], 200);
    }

    /**
     * Determine the watcher recording status.
     *
     * @return string
     */
    protected function status(): string
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

    /**
     * The entry type for the controller.
     *
     * @return string
     */
    protected function entryType()
    {
        return EntryType::REQUEST;
    }

    /**
     * The watcher class for the controller.
     *
     * @return string
     */
    protected function watcher(): string
    {
        return RequestWatcher::class;
    }
}
