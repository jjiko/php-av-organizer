<?php

namespace Yapo;

use Illuminate\Support\ServiceProvider as AppServiceProvider;

use Illuminate\Support\Facades\Route;

class ServiceProvider extends AppServiceProvider
{
    protected $namespace = 'Yapo\Http\Controllers';

    public function register()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/yapo.php'));
    }
}
