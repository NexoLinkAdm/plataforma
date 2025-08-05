<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;


class ServicePolicy
{
    /**
     * Determine whether the user can view the model.
     *
     * Usado implicitamente pelo Laravel ao resolver a rota.
     * Permite que qualquer usuário veja o serviço, mas a lógica de edição/exclusão
     * será mais restrita.
     *
     * @param  \App\Models\User|null  $user
     * @param  \App\Models\Service  $service
     * @return bool
     */
    public function view(?User $user, Service $service): bool
    {
        // Por enquanto, qualquer um pode TENTAR ver a página de edição,
        // mas a verificação 'update' irá barrá-lo se não for o dono.
        // Ou, para ser mais seguro, podemos fazer a verificação aqui também.
        // Vamos deixar aberto por enquanto e focar na edição.
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Service  $service
     * @return bool
     */
    public function update(User $user, Service $service): bool
    {
        return $user->id === $service->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Service  $service
     * @return bool
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->id === $service->user_id;
    }
}