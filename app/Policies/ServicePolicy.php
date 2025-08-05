<?php

namespace App\Policies; // Namespace correto

use App\Models\Service;
use App\Models\User;

class ServicePolicy // Nome da classe correto
{
    /**
     * Verifica se o usuário pode visualizar a listagem de serviços (no painel, por exemplo).
     */
    public function viewAny(User $user): bool
    {
        // Permitir a todos os usuários autenticados
        return true;
    }

    /**
     * Verifica se o usuário pode visualizar um serviço específico.
     */
    public function view(User $user, Service $service): bool
    {
        // Se o serviço é público, pode personalizar aqui. Exemplo:
        return $user->id === $service->user_id;
        
    }

    /**
     * Verifica se o usuário pode criar um novo serviço.
     */
    public function create(User $user): bool
    {
        // Permitir a todos os usuários autenticados
        return true;
    }

    /**
     * Verifica se o usuário pode atualizar o serviço.
     */
    public function view(User $user, Service $service): bool
{
    return $user->id === $service->user_id;
}

    /**
     * Verifica se o usuário pode excluir o serviço.
     */
    public function delete(User $user, Service $service): bool
    {
        return $user->id === $service->user_id;
    }

    /**
     * Verifica se o usuário pode restaurar um serviço deletado (se usar soft deletes).
     */
    public function restore(User $user, Service $service): bool
    {
        return $user->id === $service->user_id;
    }
}