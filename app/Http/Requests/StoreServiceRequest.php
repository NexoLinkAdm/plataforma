<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreServiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Já estamos dentro de um grupo de middleware 'auth', então a autorização básica está feita.
        // A autorização de "policy" faremos no controller para diferenciar create de update.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // O slug único precisa ignorar o próprio ID ao editar
        $serviceId = $this->route('servico') ? $this->route('servico')->id : null;
        $titleRule = 'unique:services,title';
        if ($serviceId) {
            $titleRule .= ',' . $serviceId;
        }

        return [
            'title' => ['required', 'string', 'max:100', $titleRule],
            'description' => ['required', 'string', 'max:5000'],
            'price_in_cents' => ['required', 'integer', 'min:500'], // Mínimo de R$ 5,00
            'delivery_time_days' => ['required', 'integer', 'min:1'],
            'revisions_limit' => ['required', 'integer', 'min:0', 'max:100'],
        ];
    }

    /**
     * Prepara os dados para validação.
     * É aqui que a "mágica" acontece!
     */
    protected function prepareForValidation(): void
    {
        // Limpa e converte o preço para centavos
        if ($this->has('price_in_cents')) {
            $price = $this->input('price_in_cents');
            // Remove 'R$', espaços, e substitui a vírgula do decimal por ponto
            $cleanedPrice = str_replace(['R$', ' ', '.'], '', $price);
            $cleanedPrice = str_replace(',', '.', $cleanedPrice);

            $this->merge([
                // Converte para float, multiplica por 100 e depois para inteiro
                'price_in_cents' => (int) round(floatval($cleanedPrice) * 100),
            ]);
        }

        // Gera o slug a partir do título automaticamente
        if ($this->has('title')) {
            $this->merge([
                'slug' => Str::slug($this->input('title')),
            ]);
        }
    }
}