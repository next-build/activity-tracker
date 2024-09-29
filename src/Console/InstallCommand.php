<?php

namespace NextBuild\ActivityTracker\Console;

use Illuminate\Console\Command;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'activity-tracker:install')]
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-tracker:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install all of the Activity Tracker resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->comment('Publishing Activity Tracker Service Provider...');
        $this->callSilent('vendor:publish', ['--tag' => 'activity-tracker-provider']);

        $this->comment('Publishing Activity Tracker Assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'activity-tracker-assets']);

        $this->comment('Publishing Activity Tracker Configuration...');
        $this->callSilent('vendor:publish', ['--tag' => 'activity-tracker-config']);

        $this->comment('Publishing Activity Tracker Migrations...');
        $this->callSilent('vendor:publish', ['--tag' => 'activity-tracker-migrations']);

        $this->registerTelescopeServiceProvider();

        $this->info('Activity Tracker scaffolding installed successfully.');
    }

    /**
     * Register the Activity Tracker service provider in the application configuration file.
     *
     * @return void
     */
    protected function registerTelescopeServiceProvider()
    {
        if (method_exists(ServiceProvider::class, 'addProviderToBootstrapFile') &&
            ServiceProvider::addProviderToBootstrapFile(\App\Providers\ActivityTrackerServiceProvider::class)) { // @phpstan-ignore-line
            return;
        }

        $namespace = Str::replaceLast('\\', '', $this->laravel->getNamespace());

        $appConfig = file_get_contents(config_path('app.php'));

        if (Str::contains($appConfig, $namespace.'\\Providers\\ActivityTrackerServiceProvider::class')) {
            return;
        }

        $lineEndingCount = [
            "\r\n" => substr_count($appConfig, "\r\n"),
            "\r" => substr_count($appConfig, "\r"),
            "\n" => substr_count($appConfig, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        file_put_contents(config_path('app.php'), str_replace(
            "{$namespace}\\Providers\RouteServiceProvider::class,".$eol,
            "{$namespace}\\Providers\RouteServiceProvider::class,".$eol."        {$namespace}\Providers\ActivityTrackerServiceProvider::class,".$eol,
            $appConfig
        ));

        file_put_contents(app_path('Providers/ActivityTrackerServiceProvider.php'), str_replace(
            "namespace App\Providers;",
            "namespace {$namespace}\Providers;",
            file_get_contents(app_path('Providers/ActivityTrackerServiceProvider.php'))
        ));
    }
}
