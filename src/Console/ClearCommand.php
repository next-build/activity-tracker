<?php

namespace NextBuild\ActivityTracker\Console;

use Illuminate\Console\Command;
use NextBuild\ActivityTracker\Contracts\ClearableRepository;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'activity-tracker:clear')]
class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-tracker:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all ActivityTracker data from storage';

    /**
     * Execute the console command.
     *
     * @param  \NextBuild\ActivityTracker\Contracts\ClearableRepository  $storage
     * @return void
     */
    public function handle(ClearableRepository $storage)
    {
        $storage->clear();

        $this->info('Activity Tracker entries cleared!');
    }
}
