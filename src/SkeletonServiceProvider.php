<?php

declare(strict_types=1);

namespace RedExplosion\SkeletonLaravel;

use Illuminate\Support\ServiceProvider;

final class SkeletonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            path: __DIR__ . '/../config/skeleton.php',
            key: 'skeleton',
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes(
                paths: [
                    __DIR__ . '/../config/skeleton.php',
                ],
                groups: 'skeleton',
            );
        }
    }
}
