<?php

namespace App\Providers;

use App\Models\Meal;
use App\Policies\MealPolicy;
use App\Models\KitchenProfile;
use Illuminate\Support\Facades\Gate;
use App\Policies\KitchenProfilePolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Schema;

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

        Gate::policy(KitchenProfile::class, KitchenProfilePolicy::class);
        Gate::policy(Meal::class, MealPolicy::class);
         Schema::defaultStringLength(191);
    }
}
