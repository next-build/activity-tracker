<?php

namespace NextBuild\ActivityTracker\Http\Middleware;

use NextBuild\ActivityTracker\ActivityTracker;

class Authorize
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        return ActivityTracker::check($request) ? $next($request) : abort(403);
    }
}
