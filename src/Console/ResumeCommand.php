<?php

namespace NextBuild\ActivityTracker\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'activity-tracker:resume')]
class ResumeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-tracker:resume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unpause all Activity Tracker watchers';

    /**
     * Execute the console command.
     *
     * @param  \Illuminate\Contracts\Cache\Repository  $cache
     * @return void
     */
    public function handle(CacheRepository $cache)
    {
        if ($cache->get('activity-tracker:pause-recording')) {
            $cache->forget('activity-tracker:pause-recording');
        }

        $this->info('Activity Tracker watchers resumed successfully.');
    }
}
