<?php

namespace NextBuild\ActivityTracker\Http\Controllers;

use Illuminate\Routing\Controller;
use NextBuild\ActivityTracker\ActivityTracker;

class HomeController extends Controller
{
    /**
     * Display the Activity Tracker view.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('activity-tracker::layout', [
            'scriptVariables' => ActivityTracker::scriptVariables(),
        ]);
    }
}
