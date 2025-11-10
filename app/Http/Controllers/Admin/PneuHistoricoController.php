<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pneu;
use App\Traits\ExportableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PneuHistoricoController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $query = Pneu::with(['modeloPneu', 'departamentoPneu', 'filialPneu']);

        // Filtros de busca
        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->id_pneu);
        }

        if ($request->filled('cod_antigo')) {
            $query->where('cod_antigo', 'LIKE', '%' . $request->cod_antigo . '%');
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled(['data_inicial', 'data_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial,
                $request->data_final,
            ]);
        }

        $pneus = $query->orderBy('id_pneu', 'desc')->paginate(15);

        return view('admin.pneuhistorico.index', compact('pneus'));
    }

    public function show($id)
    {
        $pneu = Pneu::with(['modeloPneu', 'departamentoPneu', 'filialPneu'])->findOrFail($id);

        // Buscar todo o histórico de vida do pneu
        $historico = $this->getComprehensiveHistory($id);

        return view('admin.pneuhistorico.show', compact('pneu', 'historico'));
    }

    private function getComprehensiveHistory($pneuId)
    {
        $historico = [];

        // 1. ENTRADA NO SISTEMA (Nota Fiscal)
        $notaFiscal = DB::connection('pgsql')->table('nota_fiscal_pneu as nfp')
            ->leftJoin('fornecedor as f', 'nfp.id_fornecedor', '=', 'f.id_fornecedor')
            ->where('nfp.id_pneu', $pneuId)
            ->select([
                'nfp.data_nf as data_entrada',
                'nfp.numero_nf as num_nota_fiscal',
                'nfp.serie as serie_nota_fiscal',
                'nfp.valor_unitario',
                'f.nome_fornecedor as fornecedor',
                'nfp.data_inclusao',
            ])
            ->first();

        if ($notaFiscal) {
            $historico[] = [
                'tipo' => 'ENTRADA_SISTEMA',
                'data' => $notaFiscal->data_entrada,
                'descricao' => 'Entrada no Sistema via Nota Fiscal',
                'detalhes' => [
                    'documento' => "NF {$notaFiscal->num_nota_fiscal}/{$notaFiscal->serie_nota_fiscal}",
                    'fornecedor' => $notaFiscal->fornecedor,
                    'valor_unitario' => $notaFiscal->valor_unitario,
                    'data_inclusao' => $notaFiscal->data_inclusao,
                ],
                'icone' => 'document-plus',
                'cor' => 'green',
            ];
        }

        // 2. HISTÓRICO DE MOVIMENTAÇÕES (Aplicação/Remoção em Veículos)
        $movimentacoes = DB::connection('pgsql')->table('historicopneu as hp')
            ->leftJoin('veiculo as v', 'hp.id_veiculo', '=', 'v.id_veiculo')
            ->leftJoin('modelo_veiculo as mv', 'v.id_modelo_veiculo', '=', 'mv.id_modelo_veiculo')
            ->leftJoin('controle_vida_pneu as cvp', 'hp.id_vida_pneu', '=', 'cvp.id_controle_vida_pneu')
            ->where('hp.id_pneu', $pneuId)
            ->select([
                'hp.*',
                'v.placa as veiculo_placa',
                'mv.descricao_modelo_veiculo as veiculo_modelo',
                'cvp.descricao_vida_pneu as vida_pneu',
            ])
            ->orderBy('hp.data_inclusao')
            ->get();

        foreach ($movimentacoes as $mov) {
            $tipoMovimentacao = $this->getTipoMovimentacao($mov->status_movimentacao);

            $historico[] = [
                'tipo' => $mov->status_movimentacao,
                'data' => $mov->data_inclusao,
                'descricao' => $tipoMovimentacao['descricao'],
                'detalhes' => [
                    'veiculo' => $mov->veiculo_placa ? "{$mov->veiculo_placa} - {$mov->veiculo_modelo}" : null,
                    'eixo_aplicado' => $mov->eixo_aplicado,
                    'vida_pneu' => $mov->vida_pneu,
                    'km_inicial' => $mov->km_inicial,
                    'km_final' => $mov->km_final,
                    'km_rodados' => $mov->km_final && $mov->km_inicial ? ($mov->km_final - $mov->km_inicial) : null,
                    'hr_inicial' => $mov->hr_inicial,
                    'hr_final' => $mov->hr_final,
                    'data_retirada' => $mov->data_retirada,
                    'origem_operacao' => $mov->origem_operacao ?? null,
                    'observacoes' => $mov->observacoes_operacao ?? null,
                ],
                'icone' => $tipoMovimentacao['icone'],
                'cor' => $tipoMovimentacao['cor'],
            ];
        }

        // 3. MANUTENÇÕES (Envio para Manutenção)
        $manutencoes = DB::connection('pgsql')->table('manutencao_pneus_itens as mpi')
            ->join('manutencao_pneus as mp', 'mpi.id_manutencao_pneu', '=', 'mp.id_manutencao_pneu')
            ->leftJoin('fornecedor as f', 'mp.id_fornecedor', '=', 'f.id_fornecedor')
            ->where('mpi.id_pneu', $pneuId)
            ->select([
                'mp.data_inclusao',
                'mp.data_assumir as data_prevista_retorno',
                'f.nome_fornecedor as fornecedor',
                'mp.nf_envio as numero_os',
            ])
            ->get();

        foreach ($manutencoes as $manutencao) {
            $historico[] = [
                'tipo' => 'ENVIO_MANUTENCAO',
                'data' => $manutencao->data_inclusao,
                'descricao' => 'Envio para Manutenção',
                'detalhes' => [
                    'numero_os' => $manutencao->numero_os,
                    'fornecedor' => $manutencao->fornecedor,
                    'data_prevista_retorno' => $manutencao->data_prevista_retorno,
                ],
                'icone' => 'wrench-screwdriver',
                'cor' => 'orange',
            ];
        }

        // 4. RETORNO DE MANUTENÇÕES
        $retornosManutencao = DB::connection('pgsql')->table('manutencao_pneu_entrada_itens as mpei')
            ->join('manutencao_pneu_entrada as mpe', 'mpei.id_manutencao_pneu_entrada', '=', 'mpe.id_manutencao_entrada')
            ->where('mpei.id_pneu', $pneuId)
            ->select([
                'mpe.data_inclusao',
                'mpe.data_recebimento',
                'mpei.situacao_pneu_interno',
                'mpei.laudo_descarte',
                'mpei.is_conferido',
                'mpei.is_feito',
                'mpe.situacao_entrada',
            ])
            ->get();

        foreach ($retornosManutencao as $retorno) {
            $historico[] = [
                'tipo' => 'RETORNO_MANUTENCAO',
                'data' => $retorno->data_recebimento ?: $retorno->data_inclusao,
                'descricao' => 'Retorno da Manutenção',
                'detalhes' => [
                    'situacao_pneu' => $retorno->situacao_pneu_interno,
                    'situacao_entrada' => $retorno->situacao_entrada,
                    'data_recebimento' => $retorno->data_recebimento,
                    'laudo_descarte' => $retorno->laudo_descarte,
                    'conferido' => $retorno->is_conferido ? 'Sim' : 'Não',
                    'feito' => $retorno->is_feito ? 'Sim' : 'Não',
                ],
                'icone' => 'check-circle',
                'cor' => 'blue',
            ];
        }

        // 5. CALIBRAGENS
        $calibragens = DB::connection('pgsql')->table('calibragem_pneus_itens as cpi')
            ->join('calibragem_pneu as cp', 'cpi.id_calibragem', '=', 'cp.id_calibragem_pneu')
            ->leftJoin('veiculo as v', 'cp.id_veiculo', '=', 'v.id_veiculo')
            ->leftJoin('users as u', 'cp.id_user_calibragem', '=', 'u.id')
            ->where('cpi.id_numero_fogo', $pneuId)
            ->select([
                'cp.data_inclusao as data_calibragem',
                'cpi.libras',
                'cpi.calibrado',
                'cpi.sulco_pneu',
                'cpi.localizacao',
                'v.placa as veiculo_placa',
                'u.name as usuario',
            ])
            ->get();

        foreach ($calibragens as $calibragem) {
            $historico[] = [
                'tipo' => 'CALIBRAGEM',
                'data' => $calibragem->data_calibragem,
                'descricao' => 'Calibragem do Pneu',
                'detalhes' => [
                    'veiculo' => $calibragem->veiculo_placa,
                    'pressao_libras' => $calibragem->libras,
                    'calibrado' => $calibragem->calibrado ? 'Sim' : 'Não',
                    'sulco_pneu' => $calibragem->sulco_pneu,
                    'localizacao' => $calibragem->localizacao,
                    'usuario' => $calibragem->usuario,
                ],
                'icone' => 'adjustments-horizontal',
                'cor' => 'indigo',
            ];
        }

        // 6. TRANSFERÊNCIAS
        $transferencias = DB::connection('pgsql')->table('transferencia_pneu_itens as tpi')
            ->join('transferencia_pneus_modelos as tpm', 'tpi.id_transferencia_modelo', '=', 'tpm.id_transferencia_pneus_modelos')
            ->join('transferencia_pneus as tp', 'tpm.id_transferencia_pneu', '=', 'tp.id_transferencia_pneus')
            ->leftJoin('users as u', 'tp.id_usuario', '=', 'u.id')
            ->leftJoin('filiais as f', 'tp.id_filial', '=', 'f.id')
            ->where('tpi.id_pneu', $pneuId)
            ->select([
                'tp.data_inclusao',
                'tp.situacao',
                'tp.observacao_saida',
                'tpi.recebido',
                'f.name as filial',
                'u.name as usuario',
            ])
            ->get();

        foreach ($transferencias as $transferencia) {
            $historico[] = [
                'tipo' => 'TRANSFERENCIA',
                'data' => $transferencia->data_inclusao,
                'descricao' => 'Transferência de Pneu',
                'detalhes' => [
                    'filial' => $transferencia->filial,
                    'situacao' => $transferencia->situacao,
                    'recebido' => $transferencia->recebido ? 'Sim' : 'Não',
                    'usuario' => $transferencia->usuario,
                    'observacoes' => $transferencia->observacao_saida,
                ],
                'icone' => 'arrow-right-circle',
                'cor' => 'purple',
            ];
        }

        // 7. VENDAS
        $vendas = DB::connection('pgsql')->table('requisicao_pneu_itens as rpi')
            ->join('requisicao_pneu_modelos as rpm', 'rpi.id_requisicao_pneu_modelos', '=', 'rpm.id_requisicao_pneu_modelos')
            ->join('requisicao_pneu as rp', 'rpm.id_requisicao_pneu', '=', 'rp.id_requisicao_pneu')
            ->leftJoin('users as u', 'rp.id_usuario_solicitante', '=', 'u.id')
            ->where('rpi.id_pneu', $pneuId)
            ->where('rp.is_aprovado', true)
            ->select([
                'rp.data_inclusao',
                'rp.data_aprovacao',
                'rp.situacao',
                'rpi.valor_venda',
                'rp.observacao',
                'u.name as usuario',
            ])
            ->get();

        foreach ($vendas as $venda) {
            $historico[] = [
                'tipo' => 'VENDA',
                'data' => $venda->data_aprovacao ?: $venda->data_inclusao,
                'descricao' => 'Venda do Pneu',
                'detalhes' => [
                    'situacao' => $venda->situacao,
                    'valor_venda' => $venda->valor_venda,
                    'usuario' => $venda->usuario,
                    'observacoes' => $venda->observacao,
                ],
                'icone' => 'banknotes',
                'cor' => 'emerald',
            ];
        }

        // 8. DESCARTES
        $descartes = DB::connection('pgsql')->table('descartepneu as dp')
            ->leftJoin('tipodescarte as td', 'dp.id_tipo_descarte', '=', 'td.id_tipo_descarte')
            ->leftJoin('users as u', 'dp.id_user_alter', '=', 'u.id')
            ->where('dp.id_pneu', $pneuId)
            ->select([
                'dp.data_inclusao',
                'dp.data_alteracao',
                'td.descricao_tipo_descarte',
                'dp.observacao',
                'dp.valor_venda_pneu',
                'u.name as usuario',
            ])
            ->get();

        foreach ($descartes as $descarte) {
            $historico[] = [
                'tipo' => 'DESCARTE',
                'data' => $descarte->data_alteracao ?: $descarte->data_inclusao,
                'descricao' => 'Descarte do Pneu',
                'detalhes' => [
                    'tipo_descarte' => $descarte->descricao_tipo_descarte,
                    'valor_venda' => $descarte->valor_venda_pneu,
                    'usuario' => $descarte->usuario,
                    'observacoes' => $descarte->observacao,
                ],
                'icone' => 'trash',
                'cor' => 'red',
            ];
        }

        // 9. CONTAGENS DE ESTOQUE
        $contagens = DB::connection('pgsql')->table('contagem_pneu as cp')
            ->leftJoin('users as u', 'cp.id_usuario', '=', 'u.id')
            ->where('cp.id_modelo_pneu', function ($query) use ($pneuId) {
                $query->select('id_modelo_pneu')
                    ->from('pneu')
                    ->where('id_pneu', $pneuId);
            })
            ->select([
                'cp.data_inclusao',
                'cp.quantidade_sistema',
                'cp.contagem_usuario as quantidade_fisica',
                'cp.is_igual',
                'u.name as usuario',
            ])
            ->get();

        foreach ($contagens as $contagem) {
            $historico[] = [
                'tipo' => 'CONTAGEM',
                'data' => $contagem->data_inclusao,
                'descricao' => 'Contagem de Estoque',
                'detalhes' => [
                    'quantidade_sistema' => $contagem->quantidade_sistema,
                    'quantidade_fisica' => $contagem->quantidade_fisica,
                    'diferenca' => $contagem->quantidade_fisica - $contagem->quantidade_sistema,
                    'contagem_igual' => $contagem->is_igual ? 'Sim' : 'Não',
                    'usuario' => $contagem->usuario,
                ],
                'icone' => 'calculator',
                'cor' => 'gray',
            ];
        }

        // Ordenar histórico por data (mais recente primeiro)
        usort($historico, function ($a, $b) {
            return strtotime($b['data']) - strtotime($a['data']);
        });

        return $historico;
    }

    private function getTipoMovimentacao($status)
    {
        $tipos = [
            'APLICADO' => [
                'descricao' => 'Aplicação em Veículo',
                'icone' => 'truck',
                'cor' => 'blue',
            ],
            'ESTOQUE' => [
                'descricao' => 'Remoção para Estoque',
                'icone' => 'archive-box',
                'cor' => 'gray',
            ],
            'MANUTENCAO' => [
                'descricao' => 'Remoção para Manutenção',
                'icone' => 'wrench',
                'cor' => 'orange',
            ],
            'RODIZIO' => [
                'descricao' => 'Rodízio de Posição',
                'icone' => 'arrow-path',
                'cor' => 'indigo',
            ],
            'DESCARTE' => [
                'descricao' => 'Remoção para Descarte',
                'icone' => 'trash',
                'cor' => 'red',
            ],
            'VENDA' => [
                'descricao' => 'Remoção para Venda',
                'icone' => 'banknotes',
                'cor' => 'emerald',
            ],
        ];

        return $tipos[$status] ?? [
            'descricao' => 'Movimentação',
            'icone' => 'question-mark-circle',
            'cor' => 'gray',
        ];
    }

    /**
     * Exportar para PDF
     *
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request, $id)
    {
        $pneu = Pneu::with(['modeloPneu', 'departamentoPneu', 'filialPneu'])->findOrFail($id);
        $historico = $this->getComprehensiveHistory($id);

        // Definir configurações para o PDF
        $options = [
            'paper' => 'a4',
            'orientation' => 'portrait',
            'options' => [
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'chroot' => public_path(),
            ],
        ];

        try {
            // Configurar o PDF
            $pdf = app('dompdf.wrapper');
            $pdf->setPaper($options['paper'], $options['orientation']);
            $pdf->setOptions($options['options']);

            // Carregar a view com os dados
            $pdf->loadView('admin.pneuhistorico.pdf', compact('pneu', 'historico'));

            // Retornar o PDF para download
            return $pdf->download('historico_pneu_' . $pneu->id_pneu . '_' . date('Y-m-d_His') . '.pdf');
        } catch (\Exception $e) {
            // Registrar o erro para diagnóstico
            \Log::error('Erro ao gerar PDF do histórico do pneu: ' . $e->getMessage());

            // Redirecionar com mensagem de erro amigável
            return back()->with([
                'error' => 'Não foi possível gerar o PDF. Erro: ' . $e->getMessage(),
                'export_error' => true,
            ]);
        }
    }

    /**
     * Lista de filtros válidos para exportação (não usado, mas necessário para o trait)
     *
     * @return array
     */
    protected function getValidExportFilters()
    {
        return [];
    }
}
