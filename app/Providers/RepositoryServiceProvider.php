<?php

declare(strict_types=1);

namespace App\Providers;

use App\Queries\BillQuery;
use App\Queries\ContainerQuery;
use App\Queries\CuttingTestQuery;
use App\Repositories\BillRepository;
use App\Repositories\ContainerRepository;
use App\Repositories\CuttingTestRepository;
use App\Services\BillService;
use App\Services\ContainerService;
use App\Services\CuttingTestService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register Repositories
        $this->app->singleton(BillRepository::class);
        $this->app->singleton(ContainerRepository::class);
        $this->app->singleton(CuttingTestRepository::class);

        // Register Queries
        $this->app->singleton(BillQuery::class);
        $this->app->singleton(ContainerQuery::class);
        $this->app->singleton(CuttingTestQuery::class);

        // Register Services
        $this->app->singleton(BillService::class);
        $this->app->singleton(ContainerService::class);
        $this->app->singleton(CuttingTestService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
