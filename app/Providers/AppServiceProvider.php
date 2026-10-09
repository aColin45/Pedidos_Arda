<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator; // <--- 1. ¡ESTA LÍNEA ES OBLIGATORIA!

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        Paginator::useBootstrap(); // <--- 2. ESTA LÍNEA TAMBIÉN
    }
}