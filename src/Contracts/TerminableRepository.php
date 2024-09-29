<?php

namespace NextBuild\ActivityTracker\Contracts;

interface TerminableRepository
{
    /**
     * Perform any clean-up tasks needed after storing Activity Tracker entries.
     *
     * @return void
     */
    public function terminate();
}
