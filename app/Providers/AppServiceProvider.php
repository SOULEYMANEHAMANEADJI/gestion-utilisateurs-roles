<?php

namespace App\Providers;

use App\Models\User;
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
        Gate::define('manage-users', function ($user){
            return $user->hasAnyRole(['author', 'admin']);
        });
        Gate::define('edit-users', function ($user){
            return $user->hasAnyRole(['author', 'admin']);
        });
        Gate::define('delete-users', function ($user){
            return $user->isAdmin();
        });
    }
}
