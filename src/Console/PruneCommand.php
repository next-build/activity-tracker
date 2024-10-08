<?php

namespace NextBuild\ActivityTracker\Console;

use Illuminate\Console\Command;
use NextBuild\ActivityTracker\Contracts\PrunableRepository;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'activity-tracker:prune')]
class PruneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-tracker:prune {--hours=24 : The number of hours to retain Activity Tracker data} {--keep-exceptions : Retain exception data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune stale entries from the Activity Tracker database';

    /**
     * Execute the console command.
     *
     * @param  \NextBuild\ActivityTracker\Contracts\PrunableRepository  $repository
     * @return void
     */
    public function handle(PrunableRepository $repository)
    {
        $this->info($repository->prune(now()->subHours($this->option('hours')), $this->option('keep-exceptions')).' entries pruned.');
    }
}
