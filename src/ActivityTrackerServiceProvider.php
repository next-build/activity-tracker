<?php

namespace NextBuild\ActivityTracker;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use NextBuild\ActivityTracker\Contracts\ClearableRepository;
use NextBuild\ActivityTracker\Contracts\EntriesRepository;
use NextBuild\ActivityTracker\Contracts\PrunableRepository;
use NextBuild\ActivityTracker\Storage\DatabaseEntriesRepository;

class ActivityTrackerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerCommands();
        $this->registerPublishing();

        if (! config('activity-tracker.enabled')) {
            return;
        }

        Route::middlewareGroup('activity-tracker', config('activity-tracker.middleware', []));

        $this->registerRoutes();
        $this->registerResources();

        ActivityTracker::start($this->app);
        // ActivityTracker::listenForStorageOpportunities($this->app);
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    protected function registerRoutes()
    {
        Route::group([
            'domain' => config('activity-tracker.domain', null),
            'namespace' => 'NextBuild\ActivityTracker\Http\Controllers',
            'prefix' => config('activity-tracker.path'),
            'middleware' => 'activity-tracker',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        });
    }

    /**
     * Register the Activity Tracker resources.
     *
     * @return void
     */
    protected function registerResources()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'activity-tracker');
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $publishesMigrationsMethod = method_exists($this, 'publishesMigrations')
                ? 'publishesMigrations'
                : 'publishes';

            $this->{$publishesMigrationsMethod}([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'activity-tracker-migrations');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/activity-tracker'),
            ], ['activity-tracker-assets', 'laravel-assets']);

            $this->publishes([
                __DIR__.'/../config/activity-tracker.php' => config_path('activity-tracker.php'),
            ], 'activity-tracker-config');

            $this->publishes([
                __DIR__.'/../stubs/ActivityTrackerServiceProvider.stub' => app_path('Providers/ActivityTrackerServiceProvider.php'),
            ], 'activity-tracker-provider');
        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ClearCommand::class,
                Console\InstallCommand::class,
                Console\PauseCommand::class,
                Console\PruneCommand::class,
                Console\PublishCommand::class,
                Console\ResumeCommand::class,
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/activity-tracker.php', 'activity-tracker'
        );

        $this->registerStorageDriver();
    }

    /**
     * Register the package storage driver.
     *
     * @return void
     */
    protected function registerStorageDriver()
    {
        $driver = config('activity-tracker.driver');

        if (method_exists($this, $method = 'register'.ucfirst($driver).'Driver')) {
            $this->$method();
        }
    }

    /**
     * Register the package database storage driver.
     *
     * @return void
     */
    protected function registerDatabaseDriver()
    {
        $this->app->singleton(
            EntriesRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->singleton(
            ClearableRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->singleton(
            PrunableRepository::class, DatabaseEntriesRepository::class
        );

        $this->app->when(DatabaseEntriesRepository::class)
            ->needs('$connection')
            ->give(config('activity-tracker.storage.database.connection'));

        $this->app->when(DatabaseEntriesRepository::class)
            ->needs('$chunkSize')
            ->give(config('activity-tracker.storage.database.chunk'));
    }

    /**
     * Generate Folder Base Path
     */
    public static function basePath(string $path): string
    {
        return __DIR__.'/..'.$path;
    }
}
