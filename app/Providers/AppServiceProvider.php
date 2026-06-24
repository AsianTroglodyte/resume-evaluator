<?php

namespace App\Providers;

use App\Enums\GlobalRole;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        View::composer(['components.dashboard-layout', 'components.app-navbar'], function ($view): void {
            $view->with('actingUser', auth()->user());
        });

        // Gate::define('view-module', function (User $user, Module $module) {
        //     return $module->users()->contains($user);
        // });

        Gate::define('add-assignment', function (User $user, Module $module) {
            $userIsModuleInstructor = $module->instructors->contains($user);
            $userIsAdmin = $user->global_role === GlobalRole::Admin;

            return $userIsModuleInstructor || $userIsAdmin;
        });
    }
}
