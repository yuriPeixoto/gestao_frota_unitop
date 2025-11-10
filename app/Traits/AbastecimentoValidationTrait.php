<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

trait AbastecimentoValidationTrait
{
    /**
     * Valida os dados do abastecimento
     *
     * @param Request $request
     * @return array
     * @throws ValidationException
     */
    protected function validateAbastecimento(Request $request)
    {
        Log::info('Validando dados de abastecimento');

        $rules = [
            'id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
            'id_filial' => 'required|exists:filiais,id',
            'numero_nota_fiscal' => 'nullable|string',
            'chave_nf' => 'nullable|string|max:44',

            'id_veiculo' => 'required|exists:veiculo,id_veiculo',
            'id_motorista' => 'nullable|exists:obtermotoristas,idobtermotorista',
            'id_departamento' => 'required|exists:departamento,id_departamento',
            'items' => ['required', 'json', function ($attribute, $value, $fail) use ($request) {
                $items = json_decode($value, true);
                Log::info('Validando JSON de itens com ' . (is_array($items) ? count($items) : 0) . ' itens');

                if (!is_array($items) || empty($items)) {
                    Log::warning('Validação falhou: JSON de items não é um array ou está vazio');
                    $fail('É necessário adicionar pelo menos um abastecimento.');
                    return;
                }

                foreach ($items as $index => $item) {
                    // Verificar campos obrigatórios
                    $camposObrigatorios = [
                        'data_abastecimento' => 'Data de Abastecimento',
                        'id_combustivel' => 'Tipo de Combustível',
                        'litros' => 'Litros',
                        'km_veiculo' => 'KM do Veículo',
                        'valor_unitario' => 'Valor Unitário',
                        'valor_total' => 'Valor Total'
                    ];

                    foreach ($camposObrigatorios as $campo => $descricao) {
                        if (!isset($item[$campo]) || $item[$campo] === '') {
                            $itemNumber = $index + 1;
                            $fail("O campo {$descricao} do item #{$itemNumber} é obrigatório.");
                            return;
                        }
                    }

                    // Validar valores numéricos
                    if (isset($item['litros']) && (float)$item['litros'] <= 0) {
                        $itemNumber = $index + 1;
                        $fail("A quantidade de litros do item #{$itemNumber} deve ser maior que zero.");
                        return;
                    }

                    if (isset($item['valor_unitario']) && (float)$item['valor_unitario'] <= 0) {
                        $itemNumber = $index + 1;
                        $fail("O valor unitário do item #{$itemNumber} deve ser maior que zero.");
                        return;
                    }

                    // Validar KM do veículo
                    if (isset($item['km_veiculo']) && (float)$item['km_veiculo'] <= 0) {
                        $itemNumber = $index + 1;
                        $fail("O KM do veículo do item #{$itemNumber} deve ser maior que zero.");
                        return;
                    }

                    // Validar data de abastecimento (não pode ser futura)
                    if (isset($item['data_abastecimento'])) {
                        $dataAbastecimento = new \DateTime($item['data_abastecimento']);
                        $hoje = new \DateTime();

                        if ($dataAbastecimento > $hoje) {
                            $itemNumber = $index + 1;
                            $fail("A data de abastecimento do item #{$itemNumber} não pode ser futura.");
                            return;
                        }
                    }
                }
            }]
        ];

        return $request->validate($rules);
    }

    /**
     * Verifica se já existe uma NF com o mesmo número para o mesmo fornecedor
     *
     * @param string $numeroNF
     * @param int $idFornecedor
     * @param int|null $idExcluir
     * @throws ValidationException
     */
    protected function checkDuplicateNF($numeroNF, $idFornecedor, $idExcluir = null)
    {
        Log::info("Verificando NF duplicada: {$numeroNF} / Fornecedor: {$idFornecedor}" . ($idExcluir ? " / Excluindo ID: {$idExcluir}" : ""));

        $query = DB::connection('pgsql')->table('abastecimento')
            ->where('numero_nota_fiscal', $numeroNF);

        // Se estiver atualizando, excluir o próprio registro da verificação
        if ($idExcluir) {
            $query->where('id_abastecimento', '!=', $idExcluir);
        }

        $exists = $query->exists();

        if ($exists) {
            Log::warning('Encontrada NF duplicada: ' . $numeroNF . ' para fornecedor ID: ' . $idFornecedor);
            throw ValidationException::withMessages([
                'numero_nota_fiscal' => ['Esta nota fiscal já foi cadastrada.']
            ]);
        }

        Log::info('Verificação de NF duplicada concluída. NF: ' . $numeroNF . ' não encontrada duplicada.');
    }

    /**
     * Valida se um valor de KM é válido baseado nas regras de negócio
     *
     * @param float $kmAtual
     * @param float $kmAnterior
     * @param int $idDepartamento
     * @return array ['isValido' => bool, 'mensagem' => string|null]
     */
    protected function validarKmVeiculo($kmAtual, $kmAnterior, $idDepartamento)
    {
        $resultado = [
            'isValido' => true,
            'mensagem' => null
        ];

        // Verificar se KM atual é menor que KM anterior
        if ($kmAnterior && $kmAtual < $kmAnterior) {
            $resultado['mensagem'] = "Atenção: O KM informado ({$kmAtual}) é menor que o KM anterior ({$kmAnterior}).";
            // Não consideramos isso um erro fatal, apenas um aviso
        }

        // Validar diferença máxima de KM (mais de 2800 km - exceto departamento 90)
        if ($kmAnterior && $idDepartamento && $idDepartamento != 90) {
            $diferenca = $kmAtual - $kmAnterior;
            if ($diferenca > 2800) {
                if ($resultado['mensagem']) {
                    $resultado['mensagem'] .= " Além disso, ";
                } else {
                    $resultado['mensagem'] = "Atenção: ";
                }

                $resultado['mensagem'] .= "A diferença de KM ({$diferenca}) é maior que a autonomia do veículo (2800 km).";
                // Não consideramos isso um erro fatal, apenas um aviso
            }
        }

        return $resultado;
    }

    /**
     * Validar capacidade do tanque versus litros abastecidos
     *
     * @param float $litros
     * @param float $capacidadeTanque
     * @return array ['isValido' => bool, 'mensagem' => string|null]
     */
    protected function validarCapacidadeTanque($litros, $capacidadeTanque)
    {
        $resultado = [
            'isValido' => true,
            'mensagem' => null
        ];

        if ($capacidadeTanque && $litros > $capacidadeTanque) {
            $resultado['mensagem'] = "Atenção: Volume informado ({$litros} litros) maior que a capacidade do tanque ({$capacidadeTanque} litros).";
            // Não consideramos isso um erro fatal, apenas um aviso
        }

        return $resultado;
    }

    /**
     * Verifica se um veículo é carreta (não permitido para abastecimento)
     *
     * @param int $idVeiculo
     * @return bool
     */
    protected function isVeiculoCarreta($idVeiculo)
    {
        return DB::connection('pgsql')->table('veiculo')
            ->where('id_veiculo', $idVeiculo)
            ->whereNotIn('id_tipo_equipamento', [1, 2, 3, 52, 53, 54, 40, 44, 71, 49])
            ->whereRaw("TRIM(placa) NOT LIKE '%TK'")
            ->exists();
    }

    /**
     * Verifica se um veículo tem inconsistências de abastecimento
     *
     * @param int $idVeiculo
     * @return string|null Mensagem de inconsistência ou null se não houver
     */
    protected function verificarInconsistenciasAbastecimento($idVeiculo)
    {
        try {
            $inconsistencias = DB::connection('pgsql')->select("SELECT * FROM fc_abastecimento_inconsistencia_mensal(?)", [$idVeiculo]);

            if (!empty($inconsistencias)) {
                $mensagens = array_column($inconsistencias, 'fc_abastecimento_inconsistencia_mensal');
                return implode(', ', $mensagens);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar inconsistências de abastecimento: ' . $e->getMessage());
            return null;
        }
    }
}
