<?php

namespace App\Providers;

use App\Models\Design;
use App\Policies\DesignPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Design::class => DesignPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
