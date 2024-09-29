<?php

namespace NextBuild\ActivityTracker;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class ActivityTrackerApplicationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->authorization();
    }

    /**
     * Configure the ActivityTracker authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        $this->gate();

        ActivityTracker::auth(function ($request) {
            return app()->environment('local') ||
                   Gate::check('viewActivityTracker', [$request->user()]);
        });
    }

    /**
     * Register the ActivityTracker gate.
     *
     * This gate determines who can access ActivityTracker in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewActivityTracker', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
