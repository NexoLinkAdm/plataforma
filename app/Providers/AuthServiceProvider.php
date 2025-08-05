<?php

namespace App\Providers;
use App\Models\Service;
use App\Policies\ServicePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
    Service::class => ServicePolicy::class,
];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}