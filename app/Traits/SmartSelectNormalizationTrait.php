<?php

namespace App\Traits;

use App\Models\Departamento;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\Motorista;
use App\Modules\Veiculos\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Trait para normalizar parâmetros do smart-select
 *
 * Este trait resolve o problema onde o smart-select envia o texto da busca
 * (ex: placa "SPW2H66") ao invés do ID quando o usuário está filtrando.
 *
 * Uso:
 * - Automático: chame $this->normalizeSmartSelectParams($request) no início do método
 * - Manual: chame métodos específicos como $this->normalizeVeiculoId($value)
 */
trait SmartSelectNormalizationTrait
{
    /**
     * Normaliza todos os parâmetros comuns do smart-select em uma requisição
     *
     * @param Request $request
     * @return void
     */
    protected function normalizeSmartSelectParams(Request $request): void
    {
        // Lista de campos que precisam ser normalizados
        $normalizations = [
            'id_veiculo' => 'normalizeVeiculoId',
            'id_fornecedor' => 'normalizeFornecedorId',
            'id_motorista' => 'normalizeMotoristaId',
            'id_departamento' => 'normalizeDepartamentoId',
            'id_filial' => 'normalizeFilialId',
        ];

        foreach ($normalizations as $param => $method) {
            if ($request->filled($param)) {
                $originalValue = $request->input($param);
                $normalizedValue = $this->$method($originalValue);

                // Se o valor foi alterado, atualizar o request
                if ($normalizedValue !== $originalValue && $normalizedValue !== null) {
                    $request->merge([$param => $normalizedValue]);

                    Log::debug("SmartSelect: Normalizado '{$param}' de '{$originalValue}' para '{$normalizedValue}'");
                }
            }
        }
    }

    /**
     * Normaliza ID de veículo - aceita ID numérico ou placa
     *
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeVeiculoId($value): ?int
    {
        // Se já é numérico, retorna como está
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Se é string, tenta buscar pela placa
        if (is_string($value) && !empty($value)) {
            try {
                $veiculo = Veiculo::where('placa', 'ilike', trim($value))->first();

                if ($veiculo) {
                    return $veiculo->id_veiculo;
                }

                Log::warning("SmartSelect: Veículo não encontrado para placa '{$value}'");
            } catch (\Exception $e) {
                Log::error("SmartSelect: Erro ao buscar veículo por placa '{$value}': " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normaliza ID de fornecedor - aceita ID numérico, nome ou CNPJ
     *
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeFornecedorId($value): ?int
    {
        // Se já é numérico, retorna como está
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Se é string, tenta buscar por nome ou CNPJ
        if (is_string($value) && !empty($value)) {
            try {
                $fornecedor = Fornecedor::where(function ($query) use ($value) {
                    $query->where('nome_fornecedor', 'ilike', trim($value))
                          ->orWhere('apelido_fornecedor', 'ilike', trim($value))
                          ->orWhere('cnpj_fornecedor', 'ilike', str_replace(['.', '/', '-'], '', trim($value)));
                })->first();

                if ($fornecedor) {
                    return $fornecedor->id_fornecedor;
                }

                Log::warning("SmartSelect: Fornecedor não encontrado para '{$value}'");
            } catch (\Exception $e) {
                Log::error("SmartSelect: Erro ao buscar fornecedor '{$value}': " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normaliza ID de motorista - aceita ID numérico ou nome
     *
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeMotoristaId($value): ?int
    {
        // Se já é numérico, retorna como está
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Se é string, tenta buscar pelo nome
        if (is_string($value) && !empty($value)) {
            try {
                $motorista = Motorista::where('nome', 'ilike', trim($value))
                    ->where('ativo', '1')
                    ->first();

                if ($motorista) {
                    return $motorista->idobtermotorista;
                }

                Log::warning("SmartSelect: Motorista não encontrado para '{$value}'");
            } catch (\Exception $e) {
                Log::error("SmartSelect: Erro ao buscar motorista '{$value}': " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normaliza ID de departamento - aceita ID numérico ou descrição
     *
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeDepartamentoId($value): ?int
    {
        // Se já é numérico, retorna como está
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Se é string, tenta buscar pela descrição
        if (is_string($value) && !empty($value)) {
            try {
                $departamento = Departamento::where('descricao_departamento', 'ilike', trim($value))
                    ->where('ativo', true)
                    ->first();

                if ($departamento) {
                    return $departamento->id_departamento;
                }

                Log::warning("SmartSelect: Departamento não encontrado para '{$value}'");
            } catch (\Exception $e) {
                Log::error("SmartSelect: Erro ao buscar departamento '{$value}': " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normaliza ID de filial - aceita ID numérico ou nome
     *
     * @param mixed $value
     * @return int|null
     */
    protected function normalizeFilialId($value): ?int
    {
        // Se já é numérico, retorna como está
        if (is_numeric($value)) {
            return (int) $value;
        }

        // Se é string, tenta buscar pelo nome
        if (is_string($value) && !empty($value)) {
            try {
                $filial = VFilial::where('name', 'ilike', trim($value))->first();

                if ($filial) {
                    return $filial->id;
                }

                Log::warning("SmartSelect: Filial não encontrada para '{$value}'");
            } catch (\Exception $e) {
                Log::error("SmartSelect: Erro ao buscar filial '{$value}': " . $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Normaliza múltiplos IDs (para campos com seleção múltipla)
     *
     * @param array $values
     * @param string $method Nome do método de normalização a ser usado
     * @return array
     */
    protected function normalizeMultipleIds(array $values, string $method): array
    {
        $normalized = [];

        foreach ($values as $value) {
            $normalizedValue = $this->$method($value);
            if ($normalizedValue !== null) {
                $normalized[] = $normalizedValue;
            }
        }

        return $normalized;
    }
}