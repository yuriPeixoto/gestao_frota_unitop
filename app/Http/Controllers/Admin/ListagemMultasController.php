<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmartecCnh;
use App\Models\SmartecMultasSneDetran;
use App\Models\SmartecNotificacoesSneDetran;
use App\Models\Veiculo;
use App\Models\VSmartecMultasSneDetran;
use App\Services\IntegradorSmartecService;
use Illuminate\Http\Request;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ListagemMultasController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;

    public function index(Request $request)
    {
        $query = VSmartecMultasSneDetran::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('motorista_nome')) {
            $query->where('motorista_nome', $request->motorista_nome);
        }

        if ($request->filled('orgao')) {
            $query->where('orgao', $request->orgao);
        }

        if ($request->filled('ait')) {
            $query->where('ait', $request->ait);
        }

        $query->orderBy('placa', 'desc');

        $listagemMultas = $query->paginate(10);

        $placa = $this->getPlaca();

        $renavam = $this->getRenavam();

        $nomeMotorista = $this->getNomeMotorista();

        $orgaoAutuador = $this->getOrgaoAutuador();

        $ait = $this->getAit();

        $veiculos = Veiculo::select('placa as value', 'placa as label')->orderBy('placa')->limit(30)->get();


        $condutor = $this->getCondutor();

        // Retornar a view
        return view('admin.listagemmultas.index', compact(
            'listagemMultas',
            'placa',
            'renavam',
            'nomeMotorista',
            'orgaoAutuador',
            'ait',
            'condutor'
        ), ['veiculos' => $veiculos]);
    }

    private function getPlaca()
    {

        return VSmartecMultasSneDetran::select(
            'placa as value',
            'placa as label'
        )
            ->distinct()
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }

    private function getRenavam()
    {

        return VSmartecMultasSneDetran::select(
            'renavam as value',
            'renavam as label'
        )
            ->distinct()
            ->orderBy('renavam', 'asc')
            ->get()
            ->toArray();
    }

    private function getNomeMotorista()
    {

        return VSmartecMultasSneDetran::select(
            'motorista_nome as value',
            'motorista_nome as label'
        )
            ->distinct()
            ->orderBy('motorista_nome', 'asc')
            ->get()
            ->toArray();
    }

    private function getOrgaoAutuador()
    {

        return VSmartecMultasSneDetran::select(
            'orgao as value',
            'orgao as label'
        )
            ->distinct()
            ->orderBy('orgao', 'asc')
            ->get()
            ->toArray();
    }

    private function getCondutor()
    {

        return SmartecCnh::select(
            'cnh as value',
            'nome as label'
        )
            ->distinct()
            ->orderBy('nome', 'asc')
            ->get()
            ->toArray();
    }

    private function getAit()
    {

        return VSmartecMultasSneDetran::select(
            'ait as value',
            'ait as label'
        )
            ->distinct()
            ->orderBy('ait', 'asc')
            ->get()
            ->toArray();
    }

    protected function buildExportQuery(Request $request)
    {
        $query = VSmartecMultasSneDetran::orderBy('placa', 'desc');

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('motorista_nome')) {
            $query->where('motorista_nome', $request->motorista_nome);
        }

        if ($request->filled('orgao')) {
            $query->where('orgao', $request->orgao);
        }

        if ($request->filled('ait')) {
            $query->where('ait', $request->ait);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'placa',
            'renavam',
            'motorista_nome',
            'orgao',
            'ait'
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.listagemant/.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('veiculos_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'prazo_indicacao' => 'Renavam',
            'motorista_nome' => 'Orgão',
            'data_infracao'  => 'Data Infração',
            'ait' => 'Orgão Adesão SNE',
            'ait_originaria' => 'Nome Motorista',
            'orgao_autuador' => 'Data Infração',
            'descricao' => 'Descrição',
            'valor_a_pagar' => 'Valor a Pagar',
            'boleto_vencimento' => 'Vencimento Boleto',
            'local' => 'Local',
            'gravidade' => 'Gravidade',
            'confirmacao_pagamento_manual' => 'Enviar P/ Financeiro',
        ];

        return $this->exportToCsv($request, $query, $columns, 'listagemmultas', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'prazo_indicacao' => 'Renavam',
            'motorista_nome' => 'Orgão',
            'data_infracao'  => 'Data Infração',
            'ait' => 'Orgão Adesão SNE',
            'ait_originaria' => 'Nome Motorista',
            'orgao_autuador' => 'Data Infração',
            'descricao' => 'Descrição',
            'valor_a_pagar' => 'Valor a Pagar',
            'boleto_vencimento' => 'Vencimento Boleto',
            'local' => 'Local',
            'gravidade' => 'Gravidade',
            'confirmacao_pagamento_manual' => 'Enviar P/ Financeiro',
        ];

        return $this->exportToExcel($request, $query, $columns, 'listagemmultas', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'placa' => 'Placa',
            'prazo_indicacao' => 'Renavam',
            'motorista_nome' => 'Orgão',
            'data_infracao'  => 'Data Infração',
            'ait' => 'Orgão Adesão SNE',
            'ait_originaria' => 'Nome Motorista',
            'orgao_autuador' => 'Data Infração',
            'descricao' => 'Descrição',
            'valor_a_pagar' => 'Valor a Pagar',
            'boleto_vencimento' => 'Vencimento Boleto',
            'local' => 'Local',
            'gravidade' => 'Gravidade',
            'confirmacao_pagamento_manual' => 'Enviar P/ Financeiro',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'listagemmultas',
            'listagemmulta',
            'listagemmultas ',
            $this->getValidExportFilters()
        );
    }


    public function baixarLote(Request $request)
    {
        if (!$request->filled('placa')) {
            return back()->withErrors(['placa' => 'Placa obrigatória']);
        }

        $placas = explode(',', $request->input('placa'));

        return $this->gerarZipDeArquivos([
            'tabela' => 'v_smartec_multas_sne_detran',
            'coluna_url' => 'boleto',
            'coluna_nome' => 'placa',
            'filtros' => [
                'placa' => $placas
            ],
            'prefixo' => 'boletos',
        ]);
    }

    public function indicarMotorista(Request $request, IntegradorSmartecService $smartecService)
    {
        try {
            $cnh = $request->input('condutor');
            $ait = $request->input('ait');
            $desconto = $request->input('desconto');

            DB::beginTransaction();

            // Verificar se já existe uma indicação para esta AIT
            $indicacaoExistente = SmartecMultasSneDetran::where('ait', $ait)
                ->whereNotNull('motorista_nome')
                ->first();

            if ($indicacaoExistente) {
                throw new \Exception('Já existe uma indicação de condutor para esta multa.');
            }

            // Buscar nome do motorista pela CNH
            $condutor = SmartecCnh::where('cnh', $cnh)->first();
            if (!$condutor) {
                throw new \Exception('Condutor não encontrado');
            }

            // Buscar código do órgão pela AIT
            $codigoOrgao = SmartecMultasSneDetran::where('ait', $ait)->value('codigo_orgao');
            if (!$codigoOrgao) {
                throw new \Exception('Órgão autuador não encontrado para esta AIT');
            }

            // Enviar dados à API da Smartec
            $retorno = $smartecService->indicarInfracao(
                nome: $condutor->nome,
                cnh: $cnh,
                tipo: 'INDICAR',
                ait: $ait,
                codigoOrgao: $codigoOrgao
            );

            // Verificar se a resposta é válida
            if (empty($retorno)) {
                // Se a resposta estiver vazia, mas a API retornou 200, consideramos sucesso
                Log::info('API retornou 200 sem body, assumindo sucesso');
            }
            // Se $retorno for um objeto, verifica se há erro
            elseif (is_object($retorno) && isset($retorno->IdErro)) {
                throw new \Exception('Erro na API: ' . ($retorno->Mensagem ?? 'Erro desconhecido'));
            }
            // Se $retorno for um array, verifica o primeiro item
            elseif (is_array($retorno) && isset($retorno[0]->IdErro)) {
                throw new \Exception('Erro na API: ' . ($retorno[0]->Mensagem ?? 'Erro desconhecido'));
            }

            // Atualiza o banco de dados se não houver erros
            SmartecMultasSneDetran::where('ait', $ait)
                ->update([
                    'motorista_nome' => $condutor->nome,
                    'desconto'       => $desconto
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Condutor indicado com sucesso!',
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Condutor indicado com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao indicar condutor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao indicar condutor: ' . $e->getMessage(),
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao indicar condutor.'
                ]
            ], 500);
        }
    }

    public function removerMotorista(Request $request, IntegradorSmartecService $smartecService)
    {
        try {
            $ait = $request->input('ait');

            DB::beginTransaction();

            // Buscar multa
            $multa = SmartecMultasSneDetran::where('ait', $ait)->first();

            if (!$multa) {
                throw new \Exception('Multa não encontrada.');
            }

            // Verificar se havia motorista indicado
            if ($multa->motorista_nome) {

                // Chamar API da Smartec para remover indicação
                $retorno = $smartecService->excluirIndicacao(
                    tipo: 'EXCLUIR INDICACAO',
                    ait: $ait
                );

                // Verificar erro na resposta
                if (!isset($retorno[0]->IdErro)) {
                    // Remover motorista localmente
                    $multa->motorista_nome = null;
                    $multa->save();

                    DB::commit();

                    return response()->json([
                        'success' => true,
                        'message' => 'Indicação removida com sucesso!',
                        'notification' => [
                            'title' => 'Sucesso!',
                            'type' => 'success',
                            'message' => 'Indicação removida com sucesso!'
                        ]
                    ]);
                } else {
                    throw new \Exception('Erro na API: ' . json_encode($retorno));
                }
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum condutor indicado nesta multa.',
                    'notification' => [
                        'title' => 'Aviso!',
                        'type' => 'info',
                        'message' => 'Nenhum condutor indicado nesta multa.'
                    ]
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao remover condutor: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover condutor: ' . $e->getMessage(),
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao remover condutor.'
                ]
            ], 500);
        }
    }

    public function gerarFici(Request $request, IntegradorSmartecService $smartecService)
    {
        try {
            $idMulta = $request->input('id_smartec_notificacoes_sne_detran'); // mesmo parâmetro

            DB::beginTransaction();

            // Buscar dados da multa
            $multa = SmartecMultasSneDetran::find($idMulta);

            if (!$multa) {
                throw new \Exception('Multa não encontrada.');
            }

            // Gera o FICI via API
            $caminhoPdf = $smartecService->gerarFici(
                tipo: 'GERAR FICI',
                ait: $multa->ait,
                orgao: $multa->codigo_orgao
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'FICI gerado com sucesso.',
                'caminho_pdf' => Storage::url($caminhoPdf), // Ex: "/storage/fici/2025/08/FICI.pdf"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gerar FICI: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar FICI: ' . $e->getMessage(),
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao gerar FICI.'
                ]
            ], 500);
        }
    }

    public function solicitarDescontoQuarenta(Request $request, IntegradorSmartecService $smartecService)
    {
        try {
            $idMulta = $request->input('id_smartec_multas_sne_detran');

            // Buscar multa
            $multa = SmartecMultasSneDetran::find($idMulta);
            if (!$multa) {
                throw new \Exception('Multa não encontrada.');
            }

            $ait = $multa->ait;
            $codigoOrgao = $multa->codigo_orgao;
            $reconhecerInfracao = true;
            $tipo = 'GERAR BOLETO';

            // Chamar API Smartec
            $resposta = $smartecService->solicitarDescontoQuarenta(
                ait: $ait,
                codigoOrgao: $codigoOrgao,
                reconhecerInfracao: $reconhecerInfracao,
                tipo: $tipo
            );

            if (!$resposta) {
                throw new \Exception('Erro ao solicitar desconto na API.');
            }

            // Atualizar dados no banco
            DB::beginTransaction();

            $multa->status_desconto_40 = $resposta->Status ?? 'PENDENTE';
            $multa->id_solicitante_desconto = auth()->user()->id();
            $multa->data_solicitacao_desconto = now();
            $multa->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Desconto solicitado com sucesso!',
                'notification' => [
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Solicitação de desconto enviada com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao solicitar desconto 40%: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao solicitar desconto: ' . $e->getMessage(),
                'notification' => [
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Erro ao solicitar desconto.'
                ]
            ], 500);
        }
    }
}
