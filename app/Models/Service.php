<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Importar o helper Str


class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'description',
        'price_in_cents',
        'delivery_time_days',
        'revisions_limit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_in_cents' => 'integer',
    ];

    /**
     * Gera o slug automaticamente antes de criar um novo serviço.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    /**
     * Um serviço pertence a um usuário (criadora).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}