<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Models\Departamento;
use App\Models\Fornecedor;
use App\Models\Motorista;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Models\Veiculo;
use App\Models\VFilial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AbastecimentoLoteController extends Controller
{
    /**
     * Processar lote de abastecimentos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processarLote(Request $request)
    {
        try {
            Log::info('Iniciando processamento de lote de abastecimentos');

            // Validar os dados recebidos
            $validated = $request->validate([
                'abastecimentos' => 'required|array|min:1',
                'abastecimentos.*.id_fornecedor' => 'required|exists:fornecedor,id_fornecedor',
                'abastecimentos.*.id_filial' => 'required|exists:filiais,id',
                'abastecimentos.*.numero_nota_fiscal' => 'required|string',
                'abastecimentos.*.id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'abastecimentos.*.id_departamento' => 'required|exists:departamento,id_departamento',
                'abastecimentos.*.items' => 'required|array|min:1',
            ]);

            // Iniciar transação no banco
            DB::beginTransaction();

            $sucessos = 0;
            $falhas = 0;
            $mensagensErro = [];

            // Processar cada abastecimento
            foreach ($validated['abastecimentos'] as $index => $abastecimento) {
                try {
                    Log::info("Processando abastecimento #{$index} - NF: {$abastecimento['numero_nota_fiscal']}");

                    // Verificar se a NF já existe para este fornecedor
                    $nfExistente = DB::connection('pgsql')->table('abastecimento')
                        ->where('numero_nota_fiscal', $abastecimento['numero_nota_fiscal'])
                        ->where('id_fornecedor', $abastecimento['id_fornecedor'])
                        ->exists();

                    if ($nfExistente) {
                        Log::warning("NF duplicada encontrada: {$abastecimento['numero_nota_fiscal']} - Fornecedor: {$abastecimento['id_fornecedor']}");
                        throw new ValidationException(null, [
                            'numero_nota_fiscal' => ["A nota fiscal {$abastecimento['numero_nota_fiscal']} já foi cadastrada para este fornecedor."]
                        ]);
                    }

                    // Buscar id_pessoal do motorista (se existir)
                    $id_pessoal = null;
                    if (!empty($abastecimento['id_motorista'])) {
                        $motorista = Motorista::find($abastecimento['id_motorista']);
                        if ($motorista && $motorista->pessoal) {
                            $id_pessoal = $motorista->pessoal->id_pessoal;
                        }
                    }

                    // Preparar dados do abastecimento
                    $abastecimentoData = [
                        'data_inclusao'      => now(),
                        'id_fornecedor'      => $abastecimento['id_fornecedor'],
                        'id_filial'          => $abastecimento['id_filial'],
                        'numero_nota_fiscal' => $abastecimento['numero_nota_fiscal'],
                        'chave_nf'           => $abastecimento['chave_nf'] ?? null,
                        'id_veiculo'         => $abastecimento['id_veiculo'],
                        'id_pessoal'         => $id_pessoal,
                        'id_departamento'    => $abastecimento['id_departamento']
                    ];

                    // Inserir abastecimento
                    $abastecimentoId = DB::connection('pgsql')->table('abastecimento')->insertGetId($abastecimentoData, 'id_abastecimento');
                    Log::info("Abastecimento #{$index} inserido com ID: {$abastecimentoId}");

                    // Processar itens do abastecimento
                    foreach ($abastecimento['items'] as $itemIndex => $item) {
                        $itemData = [
                            'id_abastecimento'   => $abastecimentoId,
                            'data_inclusao'      => now(),
                            'data_abastecimento' => $item['data_abastecimento'],
                            'id_combustivel'     => (int)$item['id_combustivel'],
                            'id_bomba'           => $item['id_bomba'] ?? null,
                            'litros'             => (float)$item['litros'],
                            'km_veiculo'         => (float)$item['km_veiculo'],
                            'valor_unitario'     => (float)$item['valor_unitario'],
                            'valor_total'        => (float)$item['valor_total']
                        ];

                        DB::connection('pgsql')->table('abastecimento_itens')->insert($itemData);
                    }

                    $sucessos++;
                } catch (ValidationException $e) {
                    $falhas++;
                    $mensagensErro[] = "Abastecimento #{$index} (NF: {$abastecimento['numero_nota_fiscal']}): " . implode(', ', array_map(function ($messages) {
                        return implode(', ', $messages);
                    }, $e->errors()));

                    Log::error("Erro de validação no abastecimento #{$index}: " . json_encode($e->errors()));
                } catch (Exception $e) {
                    $falhas++;
                    $mensagensErro[] = "Abastecimento #{$index} (NF: {$abastecimento['numero_nota_fiscal']}): {$e->getMessage()}";

                    Log::error("Erro no abastecimento #{$index}: " . $e->getMessage());
                }
            }

            // Se todos falharam, reverter a transação
            if ($sucessos === 0 && $falhas > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum abastecimento foi processado com sucesso.',
                    'errors' => $mensagensErro
                ], 422);
            }

            // Commit da transação
            DB::commit();

            // Construir mensagem de resposta
            $mensagem = "{$sucessos} abastecimento(s) processado(s) com sucesso.";
            if ($falhas > 0) {
                $mensagem .= " {$falhas} falha(s) ocorreram.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensagem,
                'errors' => $mensagensErro,
                'sucessos' => $sucessos,
                'falhas' => $falhas
            ]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação no processamento do lote.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar lote de abastecimentos: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar lote de abastecimentos: ' . $e->getMessage()
            ], 500);
        }
    }
}
