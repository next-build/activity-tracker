<?php

namespace NextBuild\ActivityTracker\Http\Controllers;

use Illuminate\Routing\Controller;
use NextBuild\ActivityTracker\ActivityTracker;

// Import Models
use App\Storage\VisitorIpModel;
use App\Storage\VisitorRequestModel;

class DumpController extends Controller
{
    /**
     * Dump all records
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        VisitorIpModel::truncate();
        VisitorRequestModel::truncate();
        return response()->json([], 200);
    }
}
