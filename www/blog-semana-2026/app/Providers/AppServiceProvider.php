<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    // Paginación con el estilo de Bootstrap.
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
