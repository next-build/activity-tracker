<?php

namespace NextBuild\ActivityTracker\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'activity-tracker:pause')]
class PauseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-tracker:pause';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pause all Activity Tracker watchers';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function handle(CacheRepository $cache)
    {
        if (! $cache->get('activity-tracker:pause-recording')) {
            $cache->put('activity-tracker:pause-recording', true, now()->addDays(30));
        }

        $this->info('Activity Tracker watchers paused successfully.');
    }
}
