<?php

namespace App\Providers;

use App\Models\Brand;
use App\Models\BrandAsset;
use App\Policies\BrandAssetPolicy;
use App\Policies\BrandPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(BrandAsset::class, BrandAssetPolicy::class);
    }
}
