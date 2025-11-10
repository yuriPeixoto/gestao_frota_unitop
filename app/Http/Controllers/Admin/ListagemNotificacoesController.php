<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\SmartecCnh;
use App\Models\SmartecMultasSneDetran;
use App\Models\SmartecNotificacoesSneDetran;
use App\Models\SmartecVeiculo;
use App\Models\Veiculo;
use App\Models\VSmartecNotificacoesSneDetran;
use App\Services\IntegradorSmartecService;
use Illuminate\Http\Request;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use DragonCode\Support\Facades\Filesystem\File;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ListagemNotificacoesController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = VSmartecNotificacoesSneDetran::query();

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

        $listagemnotificacoe = $query->paginate(10);


        $veiculos = $this->getPlaca();

        //$multa = VSmartecNotificacoesSneDetran::where('ait')->first();

        $renavam = $this->getRenavam();

        $nomeMotorista = $this->getNomeMotorista();

        $orgaoAutuador = $this->getOrgaoAutuador();

        $ait = $this->getAit();

        $condutor = $this->getCondutor();

        // Retornar a view
        return view('admin.listagemnotificacoes.index', compact(
            'listagemnotificacoe',
            'renavam',
            'nomeMotorista',
            'orgaoAutuador',
            'ait',
            'condutor'
        ), ['veiculos' => $veiculos]);
    }

    private function getPlaca()
    {

        return Cache::remember('veiculos_ativos_select', now()->addHour(), function () {
            return SmartecVeiculo::select('placa as value', 'placa as label')
                ->orderBy('placa')
                ->limit(50)
                ->get();
        });
    }

    private function getRenavam()
    {

        return VSmartecNotificacoesSneDetran::select(
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

        return VSmartecNotificacoesSneDetran::select(
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

        return VSmartecNotificacoesSneDetran::select(
            'orgao_autuador as value',
            'orgao_autuador as label'
        )
            ->distinct()
            ->orderBy('orgao_autuador', 'asc')
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

        return VSmartecNotificacoesSneDetran::select(
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
        $query = VSmartecNotificacoesSneDetran::orderBy('placa', 'desc');

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
                $pdf->loadView('admin.listagemnotificacoes/.pdf', compact('data'));

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

        return $this->exportToCsv($request, $query, $columns, 'listagemnotificacoes', $this->getValidExportFilters());
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

        return $this->exportToExcel($request, $query, $columns, 'listagemnotificacoes', $this->getValidExportFilters());
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
            'listagemnotificacoes',
            'listagemmulta',
            'listagemnotificacoes ',
            $this->getValidExportFilters()
        );
    }


    public function baixarLote(Request $request)
    {
        try {
            // Obtém ano atual
            $anoAtual = date('Y');

            // Tratamento das datas (opcional - você pode remover se não quiser filtrar por data)
            $dataInicial = $request->filled('data_inicio_boleto')
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('data_inicio_boleto'))->format('Y-m-d')
                : "$anoAtual-01-01";

            $dataFinal = $request->filled('data_fim_boleto')
                ? \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('data_fim_boleto'))->format('Y-m-d')
                : "$anoAtual-12-31";

            // Busca todos os boletos não pagos no período
            $boletos = DB::table('v_smartec_notificacoes_sne_detran')
                ->select('placa', 'boleto')
                ->whereNotNull('boleto')
                ->where('confirmacao_pagamento', false)
                ->whereBetween(DB::raw('boleto_vencimento::date'), [$dataInicial, $dataFinal])
                ->get();

            if ($boletos->isEmpty()) {
                return back()->with('error', 'Nenhum boleto disponível para download no período informado.');
            }

            // Cria um arquivo ZIP temporário
            $zipFileName = 'boletos_' . now()->format('Ymd_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Garante que o diretório existe
            if (!File::exists(storage_path('app/temp'))) {
                File::makeDirectory(storage_path('app/temp'), 0755, true);
            }

            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) !== TRUE) {
                throw new \Exception("Não foi possível criar o arquivo ZIP");
            }

            // Baixa e adiciona cada boleto ao ZIP
            foreach ($boletos as $boleto) {
                try {
                    $fileContent = file_get_contents($boleto->boleto);
                    if ($fileContent !== false) {
                        $fileName = preg_replace('/[^A-Za-z0-9]/', '_', $boleto->placa) . '.pdf';
                        $zip->addFromString($fileName, $fileContent);
                    }
                } catch (\Exception $e) {
                    // Loga o erro mas continua com os outros boletos
                    Log::error("Erro ao baixar boleto para placa {$boleto->placa}: " . $e->getMessage());
                }
            }

            $zip->close();

            // Retorna o arquivo para download
            return response()->download($zipPath, $zipFileName)
                ->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao gerar arquivo: ' . $e->getMessage());
        }
    }


    public function indicarMotorista(Request $request, IntegradorSmartecService $smartecService)
    {
        try {
            $placa = $request->input('placa');
            $renavam = $request->input('renavam');
            $cnh = $request->input('condutor');
            $ait = $request->input('ait');
            $desconto = $request->input('desconto');

            DB::beginTransaction();

            // Verificar se já existe uma indicação para esta AIT
            $indicacaoExistente = SmartecNotificacoesSneDetran::where('ait', $ait)
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
            $codigoOrgao = SmartecNotificacoesSneDetran::where('ait', $ait)->value('codigo_orgao');
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
                throw new Exception('Erro na API: ' . ($retorno->Mensagem ?? 'Erro desconhecido'));
            }
            // Se $retorno for um array, verifica o primeiro item
            elseif (is_array($retorno) && isset($retorno[0]->IdErro)) {
                throw new Exception('Erro na API: ' . ($retorno[0]->Mensagem ?? 'Erro desconhecido'));
            }

            // Atualiza o banco de dados se não houver erros
            SmartecNotificacoesSneDetran::where('ait', $ait)
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
        } catch (Exception $e) {
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
            $multa = SmartecNotificacoesSneDetran::find($idMulta);

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
            $idMulta = $request->input('id_smartec_notificacoes_sne_detran');

            // Buscar multa
            $multa = SmartecNotificacoesSneDetran::find($idMulta);
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

    /*
    public function consultarNot(Request $request, IntegradorSmartecService $smartec_service)
    {
        try {
            Log::info('entrou na Consulta de Notificação');
            Log::info('Request completo:', $request->all());

            $placa = $request->input('placa');
            Log::info('Placa recebida:', ['placa' => $placa]);

            $placa = $request->input('placa');

            $veiculo = Veiculo::where('placa', $placa)->first();

            if (!$placa) {

                return redirect()->back()->with('error', 'Placa não informada');
            }

            if (!$veiculo || !$veiculo->renavam) {
                return redirect()->back()->with('error', 'Veiculo ou renavam não encontrado!');
            }

            $renavam = $veiculo->renavam;


            $dataPesquisa = now()->subMonth()->startOfMonth()->format('01/m/Y');

            $response = $smartec_service->consultarInfracoes(

                $renavam,
                'NOTIFICACOES SNE DETRAN',
                $dataPesquisa,

            );

            foreach ($response as $value) {


                $dadosNot = [
                    'placa' => $value->PLACA,
                    'renavam' => $value->RENAVAM,
                    'identificador_smartec' => $value->IDENTIFICADOR_SMARTEC,
                    'ait' => $value->AIT,
                    'ait_sne' => $value->AIT_SNE,
                    'renainf' => $value->RENAINF,
                    'ait_originario' => $value->AIT_ORIGINARIA,
                    'renainf_originaria' => $value->RENAINF_ORIGINARIA,
                    'data_infracao' => $value->DATA_INFRACAO,
                    'hora' => $value->Hora,
                    'local' => $value->LOCAL,
                    'valor_a_pagar' => $value->VALOR_A_PAGAR,
                    'codigo_municipio' => $value->CODIGO_MUNICIPIO,
                    'municipio' => $value->MUNICIPIO,
                    'uf' => $value->UF,
                    'descricao' => $value->DESCRICAO,
                    'codigo_infracao' => $value->CodigoInfracao,
                    'desdobramento' => $value->DESDOBRAMENTO,
                    'pontuacao' => $value->PONTUACAO,
                    'codigo_orgao' => $value->CODIGO_ORGAO,
                    'orgao_autuador' => $value->ORGAO_AUTUADOR,
                    'prazo_indicacao' => $value->PRAZO_INDICACAO,
                    'data_pesquisa' => $value->DATA_PESQUISA,
                    'notificacao' => $value->NOTIFICACAO,
                    'boleto' => $value->Boleto,
                    'codigo_boleto' => $value->CODIGO_BOLETO,
                    'situacao_boleto' => $value->SITUACAO_BOLETO,
                    'descricao_boleto' => $value->DESCRICAO_BOLETO,
                    'boleto_valor' => $value->BOLETO_VALOR,
                    'linha_digitavel' => $value->LINHA_DIGITAVEL,
                    'boleto_vencimento' => $value->BOLETO_VENCIMENTO,
                    'confirmacao_pagamento' => $value->CONFIRMACAO_PAGAMENTO,
                    'motorista_nome' => $value->MOTORISTA_NOME,
                    'motorista_matricula' => $value->MOTORISTA_MATRICULA,

                    // novos campos:
                    'data_inclusao' => now(),  // ou use Carbon
                    'data_alteracao' => now(),
                ];

                Log::info('Entrou no foreach das notificações');
                Log::info('Notificação recebida:', (array) $value);
                // Atualiza ou cria com base na PLACA
                Log::info('Dados a serem salvos:', $dadosNot);
                try {
                    SmartecNotificacoesSneDetran::updateOrCreate(
                        ['placa' => $value->PLACA],
                        $dadosNot
                    );
                    Log::info('Registro salvo ou atualizado com sucesso para a placa: ' . $value->PLACA);
                } catch (\Exception $e) {
                    Log::error('Erro ao salvar notificação: ' . $e->getMessage());
                    Log::error('Trace do erro: ' . $e->getTraceAsString());
                }
            }
            return redirect()->back()->with('success', 'Consulta de notificações do veículo realizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro na consulta de notificação de veículo: ' . $e->getMessage());
        }
    }
    */
}
