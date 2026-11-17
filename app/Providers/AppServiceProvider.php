<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Client;

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
    View::composer('*', function ($view) {

        $isSuperAdmin = false;

        if (session()->has('ClientUser')) {

            $client = Client::find(session('ClientUser'));

            if ($client) {
                $isSuperAdmin = ($client->id == $client->parent_id);
            }
        }

        $view->with('isSuperAdmin', $isSuperAdmin);
    });
}
}
