<?php

declare(strict_types=1);

namespace RedExplosion\SkeletonLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use RedExplosion\SkeletonLaravel\SkeletonServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            SkeletonServiceProvider::class,
        ];
    }
}
