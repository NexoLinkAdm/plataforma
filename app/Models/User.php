<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mp_user_id',
        'mp_access_token',
        'mp_refresh_token',
        'mp_token_expires_at',
        'mp_connected_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mp_access_token',
        'mp_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mp_token_expires_at' => 'datetime',
            'mp_connected_at' => 'datetime',
            'mp_access_token' => 'encrypted',
            'mp_refresh_token' => 'encrypted',
        ];
    }

    /**
     * Verifica se a criadora conectou sua conta do Mercado Pago.
     */
    public function hasMercadoPagoConnected(): bool
    {
        return !is_null($this->mp_user_id) && !is_null($this->mp_access_token);
    }

    // =================================================================
    //  INÍCIO DA CORREÇÃO - ADICIONE ESTE MÉTODO
    // =================================================================

    /**
     * RELACIONAMENTO: Uma criadora (User) pode ter muitos serviços (Service).
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    // =================================================================
    //  FIM DA CORREÇÃO
    // =================================================================
}