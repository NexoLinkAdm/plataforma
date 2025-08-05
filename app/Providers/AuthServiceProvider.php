<?php

namespace App\Providers;

use App\Models\Service;
use App\Policies\ServicePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * O mapeamento entre Models e Policies da aplicação.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Service::class => ServicePolicy::class,
    ];

    /**
     * Registrar quaisquer serviços de autenticação/autorização.
     */
    public function boot(): void
    {
        $this->registerPolicies(); // ainda necessário em algumas versões
    }
}
