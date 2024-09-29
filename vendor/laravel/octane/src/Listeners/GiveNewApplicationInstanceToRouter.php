<?php

namespace Laravel\Octane\Listeners;

use Illuminate\Routing\RouteCollection;

class GiveNewApplicationInstanceToRouter
{
    /**
     * Handle the event.
     *
     * @param  mixed  $event
     */
    public function handle($event): void
    {
        if (! $event->sandbox->resolved('router')) {
            return;
        }

        $event->sandbox->make('router')->setContainer($event->sandbox);

        if ($event->sandbox->resolved('routes') && $event->sandbox->make('routes') instanceof RouteCollection) {
            foreach ($event->sandbox->make('routes') as $route) {
                $route->setContainer($event->sandbox);
            }
        }
    }
}
