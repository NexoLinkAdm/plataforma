<?php

namespace App\Providers;

// ADICIONE ESTAS DUAS LINHAS NO TOPO, JUNTO COM OS OUTROS 'use'
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
        // ESTA É A LINHA QUE RESOLVE O BUG.
        // ELA DIZ: "PARA O MODELO 'Service', USE A POLÍTICA 'ServicePolicy'".
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