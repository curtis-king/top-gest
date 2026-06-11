<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
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
        Blade::if('role', function ($role) {
            if (! auth()->check()) {
                return false;
            }

            $user = auth()->user();

            if (is_array($role)) {
                return $user->hasRole($role);
            }

            if (str_contains($role, '|') || str_contains($role, ',')) {
                $roles = preg_split('/[|,]/', $role);
                $roles = array_map('trim', $roles);
                return $user->hasRole($roles);
            }

            return $user->hasRole($role);
        });
    }
}
