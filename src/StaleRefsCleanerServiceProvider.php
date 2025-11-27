<?php

declare(strict_types=1);

namespace Dv0vD\LaravelRedisStaleRefsCleaner;

use Illuminate\Support\ServiceProvider;

class StaleRefsCleanerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PurgeRedisStaleRefs::class,
            ]);
        }
    }
}