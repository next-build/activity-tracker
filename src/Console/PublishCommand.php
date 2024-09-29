<?php

namespace NextBuild\ActivityTracker\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use NextBuild\ActivityTracker\ActivityTrackerServiceProvider;
use Spatie\Watcher\Watch;

class PublishCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-tracker:publish {--force : Overwrite any existing files} {--watch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all of the Activity Tracker resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            '--tag' => 'activity-tracker-config',
            '--force' => $this->option('force'),
        ]);

        $this->call('vendor:publish', [
            '--tag' => 'activity-tracker-assets',
            '--force' => true,
        ]);

        if ($this->option('watch')) {
            if (! class_exists(Watch::class)) {
                $this->error('Please install the spatie/file-system-watcher package to use the --watch option.');
                $this->info('Learn more at https://github.com/spatie/file-system-watcher');

                return;
            }

            $this->info('Watching for file changes... (Press CTRL+C to stop)');

            Watch::path(ActivityTrackerServiceProvider::basePath('/public'))
                ->onAnyChange(function (string $type, string $path) {
                    if (Str::endsWith($path, 'manifest.json')) {
                        $this->call('vendor:publish', [
                            '--tag' => 'activity-tracker-assets',
                            '--force' => true,
                        ]);
                    }
                })
                ->start();
        }
    }
}
