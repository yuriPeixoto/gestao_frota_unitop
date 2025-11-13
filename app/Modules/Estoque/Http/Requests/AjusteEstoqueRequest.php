<?php

namespace App\Modules\Estoque\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AjusteEstoqueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'codigo_estoque' => ['required'],
            'data_acerto' => ['required'],
            'filial_id' => ['required'],
            'usuario_id' => ['required'],
            'tipo_acerto' => ['required'],
            'estoque' => ['required'],
            'produto_id' => ['required'],
            'quantidade' => ['required'],
            'preco_medio' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_estoque.required' => 'O campo codigo é obrigatório.',
            'data_acerto.required' => 'O campo data acerto é obrigatório.',
            'filial_id.email' => 'O campo filial é obrigatório',
            'usuario_id.unique' => 'O campo usuártio é obrigatório',
            'tipo_acerto.required' => 'O campo tipo acerto é obrigatório',
            'estoque.min' => 'O campo estoque é obrigatório',
            'produto_id.min' => 'O campo produto é obrigatório',
            'quantidade.min' => 'O campo quantidade é obrigatório',
            'preco_medio.min' => 'O campo preço médio é obrigatório',
        ];
    }
}
