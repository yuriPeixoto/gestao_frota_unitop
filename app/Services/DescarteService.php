<?php

namespace App\Services;

use App\Models\DescartePneu;
use App\Models\HistoricoPneu;
use App\Models\Pneu;
use App\Models\TipoDescarte;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DescarteService
{
    /**
     * Criar descarte individual (cadastro manual - apenas superuser)
     * @throws Exception
     */
    public function criarDescarteManual(array $dados, ?UploadedFile $arquivo = null): DescartePneu
    {
        // Validar se usuário é superuser para cadastro manual
        if (! Auth::user()->isSuperuser()) {
            throw new Exception('Apenas superusuários podem criar descartes manuais.');
        }

        return $this->criarDescarte($dados, $arquivo, 'manual');
    }

    /**
     * Criar descarte vindo de manutenção
     */
    public function criarDescarteManutencao(array $dados, int $idManutencaoOrigem, ?UploadedFile $arquivo = null): DescartePneu
    {
        $dados['id_manutencao_origem'] = $idManutencaoOrigem;

        return $this->criarDescarte($dados, $arquivo, 'manutencao');
    }

    /**
     * Anexar laudo a múltiplos pneus
     */
    public function anexarLaudoMultiplo(array $idsPneus, UploadedFile $arquivo): array
    {
        DB::beginTransaction();

        try {
            // Upload do arquivo uma vez
            $caminhoArquivo = $arquivo->store('laudos', 'public');

            $processados = [];
            $erros = [];

            foreach ($idsPneus as $idPneu) {
                try {
                    $descarte = DescartePneu::where('id_pneu', $idPneu)
                        ->where('status_processo', 'aguardando_inicio')
                        ->first();

                    if (! $descarte) {
                        $erros[] = "Pneu {$idPneu}: Não encontrado ou não está aguardando descarte";

                        continue;
                    }

                    // Verificar se pode ser editado
                    if (! $this->podeSerEditado($descarte)) {
                        $erros[] = "Pneu {$idPneu}: Processo já finalizado, não pode ser editado";

                        continue;
                    }

                    // Anexar laudo e atualizar status
                    $descarte->update([
                        'nome_arquivo' => $caminhoArquivo,
                        'status_processo' => 'em_andamento',
                    ]);

                    $processados[] = $idPneu;
                } catch (Exception $e) {
                    $erros[] = "Pneu {$idPneu}: " . $e->getMessage();
                }
            }

            if (empty($processados)) {
                throw new Exception('Nenhum pneu foi processado: ' . implode(', ', $erros));
            }

            DB::commit();

            return [
                'processados' => $processados,
                'erros' => $erros,
                'arquivo' => $caminhoArquivo,
            ];
        } catch (Exception $e) {
            DB::rollBack();

            // Remover arquivo se upload foi feito mas transação falhou
            if (isset($caminhoArquivo) && Storage::disk('public')->exists($caminhoArquivo)) {
                Storage::disk('public')->delete($caminhoArquivo);
            }

            throw $e;
        }
    }

    /**
     * Finalizar processo de descarte
     */
    public function finalizarDescarte(int $idDescarte, array $dadosFinalizacao = []): DescartePneu
    {
        DB::beginTransaction();

        try {
            $descarte = DescartePneu::findOrFail($idDescarte);

            // Verificar se pode ser finalizado
            if (! $this->podeFinalizar($descarte)) {
                throw new Exception('Descarte não pode ser finalizado. Verifique se possui laudo anexado.');
            }

            // Atualizar dados finais se fornecidos
            if (! empty($dadosFinalizacao)) {
                $descarte->fill($dadosFinalizacao);
            }

            // Finalizar processo
            $descarte->update([
                'status_processo' => 'finalizado',
                'finalizado_em' => now(),
                'finalizado_por' => Auth::id(),
            ]);

            // Atualizar status do pneu para DESCARTE
            $pneu = Pneu::findOrFail($descarte->id_pneu);
            $pneu->update(['status_pneu' => 'DESCARTE']);

            // Registrar no histórico
            $this->registrarHistorico($descarte, $pneu);

            // Enviar notificação WhatsApp
            $this->enviarNotificacaoWhatsApp($descarte, $pneu);

            DB::commit();

            return $descarte->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Buscar pneus aguardando início de descarte
     */
    public function buscarPneusAguardandoDescarte(?int $limit = null)
    {
        $query = DescartePneu::with(['pneu', 'tipoDescarte'])
            ->where('status_processo', 'aguardando_inicio')
            ->orderBy('data_inclusao', 'asc');

        return $limit ? $query->limit($limit)->get() : $query->get();
    }

    /**
     * Verificar se descarte pode ser editado
     */
    public function podeSerEditado(DescartePneu $descarte): bool
    {
        // Processos finalizados só podem ser editados por superuser
        if ($descarte->status_processo === 'finalizado') {
            return Auth::user()->isSuperuser();
        }

        return true;
    }

    /**
     * Verificar se descarte pode ser finalizado
     */
    public function podeFinalizar(DescartePneu $descarte): bool
    {
        // Deve ter laudo anexado (sistema híbrido)
        $temLaudo = ! empty($descarte->nome_arquivo) || ! empty($descarte->id_foto);

        // Não pode estar já finalizado
        $naoFinalizado = $descarte->status_processo !== 'finalizado';

        return $temLaudo && $naoFinalizado;
    }

    /**
     * Obter arquivo de laudo (compatibilidade híbrida)
     */
    public function obterArquivoLaudo(DescartePneu $descarte): ?array
    {
        // Prioridade: Storage Laravel > Base64 legado
        if (! empty($descarte->nome_arquivo) && Storage::disk('public')->exists($descarte->nome_arquivo)) {
            return [
                'tipo' => 'storage',
                'url' => Storage::disk('public')->url($descarte->nome_arquivo),
                'caminho' => $descarte->nome_arquivo,
            ];
        }

        // Fallback para sistema legado (base64)
        if (! empty($descarte->id_foto)) {
            return [
                'tipo' => 'base64',
                'conteudo' => $descarte->id_foto,
                'nome' => $descarte->nome_arquivo ?? 'laudo_' . $descarte->id_descarte_pneu,
            ];
        }

        return null;
    }

    /**
     * Excluir descarte (apenas se não finalizado ou superuser)
     */
    public function excluirDescarte(int $idDescarte): bool
    {
        DB::beginTransaction();

        try {
            $descarte = DescartePneu::findOrFail($idDescarte);

            // Verificar permissão para exclusão
            if ($descarte->status_processo === 'finalizado' && ! Auth::user()->isSuperuser()) {
                throw new Exception('Apenas superusuários podem excluir processos finalizados.');
            }

            // Remover arquivo se existir no storage
            if (! empty($descarte->nome_arquivo) && Storage::disk('public')->exists($descarte->nome_arquivo)) {
                Storage::disk('public')->delete($descarte->nome_arquivo);
            }

            // Reverter status do pneu se necessário
            if ($descarte->status_processo === 'finalizado') {
                $pneu = Pneu::find($descarte->id_pneu);
                if ($pneu && $pneu->status_pneu === 'DESCARTE') {
                    $pneu->update(['status_pneu' => 'ESTOQUE']); // ou status anterior
                }
            }

            $descarte->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Criar descarte (método privado base)
     */
    private function criarDescarte(array $dados, ?UploadedFile $arquivo, string $origem): DescartePneu
    {
        DB::beginTransaction();

        try {
            // Validar se pneu já tem descarte
            if (DescartePneu::where('id_pneu', $dados['id_pneu'])->exists()) {
                throw new Exception('Pneu já possui registro de descarte.');
            }

            // Upload do arquivo se fornecido
            $caminhoArquivo = null;
            if ($arquivo) {
                $caminhoArquivo = $arquivo->store('laudos', 'public');
            }

            // Criar descarte
            $descarte = DescartePneu::create([
                'data_inclusao' => now(),
                'id_pneu' => $dados['id_pneu'],
                'id_tipo_descarte' => $dados['id_tipo_descarte'],
                'valor_venda_pneu' => $dados['valor_venda_pneu'] ?? 0,
                'observacao' => $dados['observacao'] ?? '',
                'nome_arquivo' => $caminhoArquivo,
                'origem' => $origem,
                'status_processo' => $caminhoArquivo ? 'em_andamento' : 'aguardando_inicio',
                'id_manutencao_origem' => $dados['id_manutencao_origem'] ?? null,
            ]);

            DB::commit();

            return $descarte;
        } catch (Exception $e) {
            DB::rollBack();

            // Remover arquivo se upload foi feito mas transação falhou
            if (isset($caminhoArquivo) && Storage::disk('public')->exists($caminhoArquivo)) {
                Storage::disk('public')->delete($caminhoArquivo);
            }

            throw $e;
        }
    }

    /**
     * Registrar movimentação no histórico
     */
    private function registrarHistorico(DescartePneu $descarte, Pneu $pneu): void
    {
        $tipoDescarte = TipoDescarte::find($descarte->id_tipo_descarte);

        HistoricoPneu::create([
            'data_inclusao' => now(),
            'data_retirada' => now(),
            'id_pneu' => $pneu->id_pneu,
            'status_movimentacao' => $tipoDescarte->descricao_tipo_descarte ?? 'DESCARTE',
            'origem_operacao' => 'MANUAL',
            'observacoes_operacao' => "Descarte finalizado: {$descarte->observacao}",
        ]);
    }

    /**
     * Enviar notificação WhatsApp
     */
    private function enviarNotificacaoWhatsApp(DescartePneu $descarte, Pneu $pneu): void
    {
        try {
            $tipoDescarte = TipoDescarte::find($descarte->id_tipo_descarte);
            $usuario = Auth::user();

            $mensagem = "*🔴 BAIXA DE PNEU REALIZADA*\n\n";
            $mensagem .= "📋 *Detalhes:*\n";
            $mensagem .= "• Nº Fogo: *{$pneu->id_pneu}*\n";
            $mensagem .= "• Tipo: *{$tipoDescarte->descricao_tipo_descarte}*\n";
            $mensagem .= '• Valor: *R$ ' . number_format($descarte->valor_venda_pneu, 2, ',', '.') . "*\n";
            $mensagem .= "• Responsável: *{$usuario->name}*\n";
            $mensagem .= '• Data: *' . now()->format('d/m/Y H:i') . "*\n\n";

            if ($descarte->observacao) {
                $mensagem .= "📝 *Observação:* {$descarte->observacao}\n\n";
            }

            $mensagem .= 'Sistema Gestão de Frota';

            // Lista de contatos para notificação (configurar conforme necessário)
            $contatos = [
                ['nome' => 'Controladoria', 'telefone' => ''], // Adicionar números reais
                ['nome' => 'Gerente da Área', 'telefone' => ''], // Adicionar números reais
            ];

            foreach ($contatos as $contato) {
                if (! empty($contato['telefone'])) {
                    IntegracaoWhatssappCarvalimaService::enviarMensagem(
                        $mensagem,
                        $contato['nome'],
                        $contato['telefone']
                    );
                }
            }
        } catch (Exception $e) {
            // Log do erro mas não interrompe o processo
            Log::warning('Erro ao enviar notificação WhatsApp para descarte', [
                'descarte_id' => $descarte->id_descarte_pneu,
                'erro' => $e->getMessage(),
            ]);
        }
    }
}
