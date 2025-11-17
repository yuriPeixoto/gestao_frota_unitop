<?php

namespace App\Modules\Pneus\Services;

use App\Models\VeiculoXPneu;
use App\Models\PneusAplicados;
use App\Models\Pneu;
use App\Models\HistoricoPneu;
use App\Models\PneuDeposito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PneuAplicadoService
{
    /**
     * Processa a troca de pneus aplicados
     */
    public function processarTrocaPneus($idVeiculo, $pneuRemovidoId, $pneuAdicionadoId, $localizacao, $dadosOperacao = [])
    {
        try {
            DB::beginTransaction();

            // 1. Buscar o registro veiculo_x_pneu ativo
            $veiculoXPneu = $this->buscarVeiculoXPneuAtivo($idVeiculo);

            if (!$veiculoXPneu) {
                throw new \Exception("Nenhum registro ativo encontrado na tabela veiculo_x_pneu para o veículo {$idVeiculo}");
            }

            // 2. Desativar o pneu removido
            if ($pneuRemovidoId) {
                $this->desativarPneuAplicado($veiculoXPneu->id_veiculo_pneu, $pneuRemovidoId, $localizacao, $dadosOperacao);
            }

            // 3. Ativar o pneu adicionado
            if ($pneuAdicionadoId) {
                $this->ativarPneuAplicado($veiculoXPneu->id_veiculo_pneu, $pneuAdicionadoId, $localizacao, $dadosOperacao);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Troca de pneus processada com sucesso',
                'veiculo_x_pneu_id' => $veiculoXPneu->id_veiculo_pneu
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Erro ao processar troca de pneus", [
                'error' => $e->getMessage(),
                'veiculo_id' => $idVeiculo,
                'pneu_removido' => $pneuRemovidoId,
                'pneu_adicionado' => $pneuAdicionadoId,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Busca o registro ativo na tabela veiculo_x_pneu
     */
    private function buscarVeiculoXPneuAtivo($idVeiculo)
    {
        return VeiculoXPneu::where('id_veiculo', $idVeiculo)
            ->where('situacao', true)
            ->first();
    }

    /**
     * Desativa um pneu aplicado (marca como removido)
     */
    private function desativarPneuAplicado($idVeiculoPneu, $pneuId, $localizacao, $dadosOperacao)
    {
        // Buscar o registro ativo do pneu aplicado
        $pneuAplicado = PneusAplicados::where('id_veiculo_x_pneu', $idVeiculoPneu)
            ->where('id_pneu', $pneuId)
            ->where('localizacao', $localizacao)
            ->whereNull('deleted_at') // Ativo
            ->first();

        if (!$pneuAplicado) {
            Log::warning("Pneu aplicado não encontrado para desativação", [
                'veiculo_x_pneu_id' => $idVeiculoPneu,
                'pneu_id' => $pneuId,
                'localizacao' => $localizacao
            ]);
            return;
        }

        // ✅ BUSCAR O HISTÓRICO ATIVO DESTE PNEU PARA ATUALIZAR data_retirada
        // Primeiro buscar pelo veículo principal
        $veiculoXPneu = VeiculoXPneu::find($idVeiculoPneu);
        $veiculoId = $veiculoXPneu ? $veiculoXPneu->id_veiculo : null;

        // ✅ ESTRATÉGIA MÚLTIPLA DE BUSCA DO HISTÓRICO
        $historicoAtivo = null;

        // Busca 1: Por veículo principal + pneu + sem data_retirada
        if ($veiculoId) {
            $historicoAtivo = HistoricoPneu::where('id_pneu', $pneuId)
                ->where('id_veiculo', $veiculoId)
                ->whereNull('data_retirada')
                ->orderBy('data_inclusao', 'desc')
                ->first();
        }

        // Busca 2: Se não encontrou, buscar por pneu + localização + sem data_retirada
        if (!$historicoAtivo && $veiculoId) {
            $historicoAtivo = HistoricoPneu::where('id_pneu', $pneuId)
                ->where('id_veiculo', $veiculoId)
                ->where('eixo_aplicado', $localizacao)
                ->whereNull('data_retirada')
                ->orderBy('data_inclusao', 'desc')
                ->first();
        }

        // Busca 3: Se ainda não encontrou, buscar QUALQUER histórico ativo deste pneu
        if (!$historicoAtivo) {
            $historicoAtivo = HistoricoPneu::where('id_pneu', $pneuId)
                ->whereNull('data_retirada')
                ->where('status_movimentacao', '!=', 'DESCARTE')
                ->where('status_movimentacao', '!=', 'ESTOQUE')
                ->where('status_movimentacao', '!=', 'MANUTENCAO')
                ->orderBy('data_inclusao', 'desc')
                ->first();
        }

        // Busca 4: ÚLTIMO RECURSO - buscar o último registro deste pneu mesmo com data_retirada
        if (!$historicoAtivo) {
            $historicoAtivo = HistoricoPneu::where('id_pneu', $pneuId)
                ->orderBy('data_inclusao', 'desc')
                ->first();
        }

        $dataRetirada = Carbon::now();
        $kmFinal = isset($dadosOperacao['km_removido']) ? $dadosOperacao['km_removido'] : null;
        $statusMovimentacao = isset($dadosOperacao['destino']) ? $dadosOperacao['destino'] : 'ESTOQUE';
        $origemOperacao = isset($dadosOperacao['origem_operacao']) ? $dadosOperacao['origem_operacao'] : 'MANUAL';
        $destino = isset($dadosOperacao['destino']) ? $dadosOperacao['destino'] : 'ESTOQUE';

        // ✅ SE ENCONTROU HISTÓRICO, SEMPRE ATUALIZAR data_retirada
        if ($historicoAtivo) {
            $historicoAtivo->update([
                'data_retirada' => $dataRetirada,
                'data_alteracao' => $dataRetirada,
                'km_final' => $kmFinal,
                'status_movimentacao' => $statusMovimentacao,
                'origem_operacao' => $origemOperacao,
                'observacoes_operacao' => "REMOÇÃO CONFIRMADA - Destino: " . $destino . " | Localização: {$localizacao} | " . date('Y-m-d H:i:s')
            ]);
        } else {
            // ✅ SE NÃO ENCONTROU NENHUM HISTÓRICO, CRIAR UM NOVO COM data_retirada
            Log::warning("⚠️ NENHUM HISTÓRICO ENCONTRADO - CRIANDO REGISTRO COMPLETO DE REMOÇÃO", [
                'pneu_id' => $pneuId,
                'veiculo_id' => $veiculoId,
                'localizacao' => $localizacao
            ]);

            if ($veiculoId) {
                $novoHistorico = HistoricoPneu::create([
                    'data_inclusao' => $dataRetirada->copy()->subMinute(), // 1 minuto antes
                    'data_retirada' => $dataRetirada, // ✅ SEMPRE COM data_retirada
                    'data_alteracao' => $dataRetirada,
                    'id_veiculo' => $veiculoId,
                    'id_pneu' => $pneuId,
                    'eixo_aplicado' => $localizacao,
                    'km_inicial' => $kmFinal,
                    'km_final' => $kmFinal,
                    'status_movimentacao' => $statusMovimentacao,
                    'origem_operacao' => $origemOperacao,
                    'observacoes_operacao' => "REMOÇÃO REGISTRADA - Destino: " . $destino . " | Criado automaticamente | " . date('Y-m-d H:i:s')
                ]);
            }
        }

        // Atualizar campos de remoção no PneusAplicados
        $kmRemovido = isset($dadosOperacao['km_removido']) ? $dadosOperacao['km_removido'] : null;
        $sulcoRemovido = isset($dadosOperacao['sulco_removido']) ? $dadosOperacao['sulco_removido'] : null;
        $origemOperacao = isset($dadosOperacao['origem_operacao']) ? $dadosOperacao['origem_operacao'] : 'MANUAL';
        $destino = isset($dadosOperacao['destino']) ? $dadosOperacao['destino'] : null;

        $pneuAplicado->update([
            'km_removido' => $kmRemovido,
            'sulco_pneu_removido' => $sulcoRemovido,
            'data_alteracao' => Carbon::now(),
            'origem_operacao' => $origemOperacao,
            'destino' => $destino,
            'km_remocao' => $kmRemovido,
            'sulco_remocao' => $sulcoRemovido,
            'is_ativo' => false, // Garantir que está inativo quando removido
        ]);

        // Soft delete (desativar) - o trait não deve alterar is_ativo pois já foi definido
        $pneuAplicado->delete();

        // ✅ NOVO: Criar registro na tabela PneuDeposito quando o pneu for desaplicado
        $this->criarRegistroPneuDeposito($pneuId, $destino);

        // Atualizar status do pneu sempre para DEPOSITO
        $this->atualizarStatusPneu($pneuId, 'DEPOSITO');
    }

    /**
     * Ativa um pneu aplicado (marca como aplicado)
     */
    private function ativarPneuAplicado($idVeiculoPneu, $pneuId, $localizacao, $dadosOperacao)
    {
        // Verificar se já existe um registro para este pneu nesta localização
        $pneuExistente = PneusAplicados::where('id_veiculo_x_pneu', $idVeiculoPneu)
            ->where('id_pneu', $pneuId)
            ->where('localizacao', $localizacao)
            ->withTrashed() // Incluir soft deleted
            ->first();

        if ($pneuExistente) {
            // Restaurar registro existente
            $pneuExistente->restore();

            $kmAdicionado = isset($dadosOperacao['km_adicionado']) ? $dadosOperacao['km_adicionado'] : null;
            $sulcoAdicionado = isset($dadosOperacao['sulco_adicionado']) ? $dadosOperacao['sulco_adicionado'] : null;
            $origemOperacao = isset($dadosOperacao['origem_operacao']) ? $dadosOperacao['origem_operacao'] : 'MANUAL';

            $pneuExistente->update([
                'km_adicionado' => $kmAdicionado,
                'sulco_pneu_adicionado' => $sulcoAdicionado,
                'data_alteracao' => Carbon::now(),
                'origem_operacao' => $origemOperacao,
                'is_ativo' => true, // Garantir que está ativo quando aplicado
                'destino' => 'APLICACAO', // Definir destino como aplicação
                // Limpar campos de remoção
                'km_removido' => null,
                'sulco_pneu_removido' => null,
                'km_remocao' => null,
                'sulco_remocao' => null,
            ]);
        } else {
            // Criar novo registro
            $kmAdicionado = isset($dadosOperacao['km_adicionado']) ? $dadosOperacao['km_adicionado'] : null;
            $sulcoAdicionado = isset($dadosOperacao['sulco_adicionado']) ? $dadosOperacao['sulco_adicionado'] : null;
            $origemOperacao = isset($dadosOperacao['origem_operacao']) ? $dadosOperacao['origem_operacao'] : 'MANUAL';

            // ✅ CORREÇÃO: Truncar localização para máximo 10 caracteres
            $localizacaoTruncada = strlen($localizacao) > 10 ? substr($localizacao, 0, 10) : $localizacao;

            Log::info('🔧 Aplicando pneu - dados da localização', [
                'localizacao_original' => $localizacao,
                'localizacao_truncada' => $localizacaoTruncada,
                'tamanho_original' => strlen($localizacao),
                'tamanho_truncado' => strlen($localizacaoTruncada)
            ]);

            $novoPneuAplicado = PneusAplicados::create([
                'data_inclusao' => Carbon::now(),
                'data_alteracao' => Carbon::now(),
                'id_pneu' => $pneuId,
                'id_veiculo_x_pneu' => $idVeiculoPneu,
                'localizacao' => $localizacaoTruncada, // ✅ Usar versão truncada
                'km_adicionado' => $kmAdicionado,
                'sulco_pneu_adicionado' => $sulcoAdicionado,
                'origem_operacao' => $origemOperacao,
                'total_km' => 0, // Será calculado posteriormente
                'is_ativo' => true, // Garantir que está ativo quando aplicado
                'destino' => 'APLICACAO', // Definir destino como aplicação
            ]);
        }

        // ✅ CRIAR/ATUALIZAR HISTÓRICO PARA APLICAÇÃO
        $veiculoXPneu = VeiculoXPneu::find($idVeiculoPneu);
        if ($veiculoXPneu) {
            $kmInicial = isset($dadosOperacao['km_adicionado']) ? $dadosOperacao['km_adicionado'] : null;
            $origemOperacao = isset($dadosOperacao['origem_operacao']) ? $dadosOperacao['origem_operacao'] : 'MANUAL';

            HistoricoPneu::create([
                'data_inclusao' => Carbon::now(),
                'data_alteracao' => Carbon::now(),
                'id_veiculo' => $veiculoXPneu->id_veiculo,
                'id_pneu' => $pneuId,
                'eixo_aplicado' => $localizacaoTruncada, // ✅ Usar versão truncada
                'km_inicial' => $kmInicial,
                'status_movimentacao' => 'APLICADO',
                'origem_operacao' => $origemOperacao,
                'observacoes_operacao' => "Aplicação na localização {$localizacaoTruncada}" // ✅ Usar versão truncada
            ]);
        }

        // Atualizar status do pneu para APLICADO
        $this->atualizarStatusPneu($pneuId, 'APLICADO');
    }

    /**
     * Atualiza o status do pneu
     */
    private function atualizarStatusPneu($pneuId, $novoStatus)
    {
        $pneu = Pneu::find($pneuId);

        if ($pneu) {
            $pneu->update([
                'status_pneu' => $novoStatus,
                'data_alteracao' => Carbon::now()
            ]);
        }
    }

    /**
     * Processa múltiplas operações de pneus
     */
    public function processarOperacoesMultiplas($idVeiculo, $operacoes)
    {
        $resultados = [];
        $sucessos = 0;
        $erros = 0;

        foreach ($operacoes as $operacao) {
            $tipo = isset($operacao['tipo']) ? $operacao['tipo'] : 'troca';

            switch ($tipo) {
                case 'troca':
                    $pneuRemovidoId = isset($operacao['pneu_removido_id']) ? $operacao['pneu_removido_id'] : null;
                    $pneuAdicionadoId = isset($operacao['pneu_adicionado_id']) ? $operacao['pneu_adicionado_id'] : null;
                    $dados = isset($operacao['dados']) ? $operacao['dados'] : [];

                    $resultado = $this->processarTrocaPneus(
                        $idVeiculo,
                        $pneuRemovidoId,
                        $pneuAdicionadoId,
                        $operacao['localizacao'],
                        $dados
                    );
                    break;

                case 'remocao':
                    $dados = isset($operacao['dados']) ? $operacao['dados'] : [];

                    $resultado = $this->processarTrocaPneus(
                        $idVeiculo,
                        $operacao['pneu_removido_id'],
                        null,
                        $operacao['localizacao'],
                        $dados
                    );
                    break;

                case 'aplicacao':
                    $dados = isset($operacao['dados']) ? $operacao['dados'] : [];

                    $resultado = $this->processarTrocaPneus(
                        $idVeiculo,
                        null,
                        $operacao['pneu_adicionado_id'],
                        $operacao['localizacao'],
                        $dados
                    );
                    break;

                default:
                    $resultado = [
                        'success' => false,
                        'message' => "Tipo de operação desconhecido: {$tipo}"
                    ];
            }

            $resultados[] = array_merge($resultado, ['operacao' => $operacao]);

            if ($resultado['success']) {
                $sucessos++;
            } else {
                $erros++;
            }
        }

        return [
            'success' => $erros === 0,
            'total_operacoes' => count($operacoes),
            'sucessos' => $sucessos,
            'erros' => $erros,
            'resultados' => $resultados
        ];
    }

    /**
     * Busca pneus aplicados ativos para um veículo
     */
    public function buscarPneusAplicadosAtivos($idVeiculo)
    {
        $veiculoXPneu = $this->buscarVeiculoXPneuAtivo($idVeiculo);

        if (!$veiculoXPneu) {
            return collect();
        }

        return PneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
            ->whereNull('deleted_at')
            ->with(['pneu'])
            ->get();
    }

    /**
     * Valida se uma operação pode ser realizada
     */
    public function validarOperacao($idVeiculo, $pneuId, $localizacao, $tipo = 'aplicacao')
    {
        $validacao = [
            'valido' => true,
            'mensagens' => []
        ];

        // Verificar se o veículo tem registro ativo
        $veiculoXPneu = $this->buscarVeiculoXPneuAtivo($idVeiculo);
        if (!$veiculoXPneu) {
            $validacao['valido'] = false;
            $validacao['mensagens'][] = "Veículo não possui registro ativo na tabela veiculo_x_pneu";
            return $validacao;
        }

        // Verificar se o pneu existe
        $pneu = Pneu::find($pneuId);
        if (!$pneu) {
            $validacao['valido'] = false;
            $validacao['mensagens'][] = "Pneu não encontrado";
            return $validacao;
        }

        // Validações específicas por tipo de operação
        switch ($tipo) {
            case 'aplicacao':
                if ($pneu->status_pneu === 'APLICADO') {
                    $validacao['valido'] = false;
                    $validacao['mensagens'][] = "Pneu já está aplicado em outro veículo";
                }

                // Verificar se já existe pneu nesta localização
                $pneuNaLocalizacao = PneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                    ->where('localizacao', $localizacao)
                    ->whereNull('deleted_at')
                    ->first();

                if ($pneuNaLocalizacao) {
                    $validacao['valido'] = false;
                    $validacao['mensagens'][] = "Já existe um pneu aplicado na localização {$localizacao}";
                }
                break;

            case 'remocao':
                $pneuAplicado = PneusAplicados::where('id_veiculo_x_pneu', $veiculoXPneu->id_veiculo_pneu)
                    ->where('id_pneu', $pneuId)
                    ->where('localizacao', $localizacao)
                    ->whereNull('deleted_at')
                    ->first();

                if (!$pneuAplicado) {
                    $validacao['valido'] = false;
                    $validacao['mensagens'][] = "Pneu não está aplicado na localização especificada";
                }
                break;
        }

        return $validacao;
    }

    /**
     * ✅ MÉTODO ESPECÍFICO: Corrigir registros de remoção sem data_retirada
     */
    public function corrigirRegistrosRemocaoSemDataRetirada($idVeiculo = null)
    {
        try {

            // Buscar registros que são claramente de remoção mas não têm data_retirada
            $query = HistoricoPneu::whereNull('data_retirada')
                ->whereIn('status_movimentacao', ['DESCARTE', 'ESTOQUE', 'MANUTENCAO'])
                ->where('origem_operacao', 'AUTO_SAVE');

            if ($idVeiculo) {
                $query->where('id_veiculo', $idVeiculo);
            }

            $registrosParaCorrigir = $query->get();

            $corrigidos = 0;

            foreach ($registrosParaCorrigir as $registro) {
                // Para registros de remoção, sempre definir data_retirada
                $dataRetirada = $registro->data_alteracao ? Carbon::parse($registro->data_alteracao) : Carbon::parse($registro->data_inclusao);

                $observacoesAtuais = $registro->observacoes_operacao ? $registro->observacoes_operacao : '';

                $registro->update([
                    'data_retirada' => $dataRetirada,
                    'data_alteracao' => Carbon::now(),
                    'observacoes_operacao' => $observacoesAtuais . ' | CORREÇÃO: data_retirada definida automaticamente em ' . Carbon::now()->format('Y-m-d H:i:s')
                ]);

                $corrigidos++;
            }

            return [
                'success' => true,
                'registros_corrigidos' => $corrigidos,
                'registros_encontrados' => $registrosParaCorrigir->count()
            ];
        } catch (\Exception $e) {
            Log::error("❌ ERRO NA CORREÇÃO DE REGISTROS DE REMOÇÃO", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    public function corrigirHistoricosSemDataRetirada($idVeiculo = null)
    {
        try {
            $query = HistoricoPneu::whereNull('data_retirada')
                ->where('status_movimentacao', '!=', 'APLICADO');

            if ($idVeiculo) {
                $query->where('id_veiculo', $idVeiculo);
            }

            $historicosIncompletos = $query->get();

            $corrigidos = 0;

            foreach ($historicosIncompletos as $historico) {
                // Verificar se o pneu ainda está aplicado
                $pneuAindaAplicado = PneusAplicados::join('veiculo_x_pneu', 'pneus_aplicados.id_veiculo_x_pneu', '=', 'veiculo_x_pneu.id_veiculo_pneu')
                    ->where('veiculo_x_pneu.id_veiculo', $historico->id_veiculo)
                    ->where('pneus_aplicados.id_pneu', $historico->id_pneu)
                    ->whereNull('pneus_aplicados.deleted_at')
                    ->exists();

                if (!$pneuAindaAplicado) {
                    // Pneu não está mais aplicado, corrigir histórico
                    $dataRetirada = $historico->data_alteracao ? $historico->data_alteracao : Carbon::now();
                    $observacoesAtuais = $historico->observacoes_operacao ? $historico->observacoes_operacao : '';

                    $historico->update([
                        'data_retirada' => $dataRetirada,
                        'data_alteracao' => Carbon::now(),
                        'observacoes_operacao' => $observacoesAtuais . ' | Corrigido automaticamente'
                    ]);


                    $corrigidos++;
                }
            }


            return [
                'success' => true,
                'historicos_corrigidos' => $corrigidos
            ];
        } catch (\Exception $e) {
            Log::error("❌ ERRO NA CORREÇÃO DE HISTÓRICOS", [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ NOVO MÉTODO: Criar registro na tabela PneuDeposito quando pneu for desaplicado
     */
    private function criarRegistroPneuDeposito($pneuId, $destino)
    {
        try {
            // Mapear destino para destinacao_solicitada
            $destinacaoSolicitada = $this->mapearDestinoParaDestinacao($destino);

            PneuDeposito::create([
                'data_inclusao' => Carbon::now(),
                'id_pneu' => $pneuId,
                'descricao_destino' => 'DEPOSITO',
                'destinacao_solicitada' => $destinacaoSolicitada
            ]);

            Log::info("✅ Registro criado na tabela PneuDeposito", [
                'pneu_id' => $pneuId,
                'destino' => $destino,
                'destinacao_solicitada' => $destinacaoSolicitada
            ]);
        } catch (\Exception $e) {
            Log::error("❌ Erro ao criar registro PneuDeposito", [
                'pneu_id' => $pneuId,
                'destino' => $destino,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ MÉTODO AUXILIAR: Mapear destino para destinacao_solicitada
     */
    private function mapearDestinoParaDestinacao($destino)
    {
        $mapeamento = [
            'MANUTENCAO' => 'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO',
            'MANUTENÇÃO' => 'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO',
            'EM MANUTENÇÃO' => 'AGUARDANDO DESTINAÇÃO: MANUTENÇÃO',
            'ESTOQUE' => 'ENVIAR PARA O ESTOQUE',
            'DESCARTE' => 'DESCARTE'
        ];

        return $mapeamento[$destino] ?? 'ENVIAR PARA O ESTOQUE';
    }
}
