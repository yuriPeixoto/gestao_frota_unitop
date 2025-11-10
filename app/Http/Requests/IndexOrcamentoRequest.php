<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexOrcamentoRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('viewAny', \App\Models\Orcamento::class);
    }

    public function rules()
    {
        return [
            'id_pedido'      => ['nullable', 'exists:pedido_compras,id_pedido_compras'],
            'id_fornecedor'  => ['nullable', 'exists:fornecedores,id_fornecedor'],
            'data_inicial'   => ['nullable', 'date'],
            'data_final'     => ['nullable', 'date', 'after_or_equal:data_inicial'],
            'selecionado'    => ['nullable', 'in:0,1'],
        ];
    }

    /** Preparar os dados antes da validação */
    protected function prepareForValidation()
    {
        // converte 'selecionado' para booleano
        if ($this->has('selecionado')) {
            $this->merge([
                'selecionado' => (bool) $this->selecionado,
            ]);
        }
    }
}
