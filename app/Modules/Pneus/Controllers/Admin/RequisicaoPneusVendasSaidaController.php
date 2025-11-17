<?php

namespace App\Modules\Pneus\Controllers\Admin;

set_time_limit(120);

use App\Http\Controllers\Controller;

use App\Models\VFilial;
use App\Models\User;
use App\Models\Pneu;
use App\Models\RequisicaoPneu;
use App\Models\RequisicaoPneuItens;
use App\Models\RequisicaoPneuModelos;
use App\Models\HistoricoPneu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\SanitizesMonetaryValues;
use JasperPHP\JasperPHP;
use App\Traits\JasperServerIntegration;
use App\Traits\ExportableTrait;




class RequisicaoPneusVendasSaidaController extends Controller
{
    use SanitizesMonetaryValues;
    use ExportableTrait;

    public function index(Request $request)
    {

        $query = RequisicaoPneu::where('is_cancelada', false)
            ->where('venda', true)
            ->with('usuarioEstoque', 'usuarioSolicitante', 'filial')
            ->orderBy('id_requisicao_pneu', 'desc');

        if ($request->filled('id_requisicao_pneu')) {
            $query->where("id_requisicao_pneu", $request->id_requisicao_pneu);
        }

        if ($request->filled(['data_inicial', 'data_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial,
                $request->data_final
            ]);
        }

        if ($request->filled('id_situacao')) {
            $query->where("situacao", $request->id_situacao);
        }

        if ($request->filled('id_filial')) {
            $query->where("id_filial", $request->id_filial);
        }

        if ($request->filled('id_usuario')) {
            $query->where("id_usuario_solicitante", $request->id_usuario);
        }

        $requisicaoPneusSaida = $query->paginate(20);

        $form = [
            'filial' => VFilial::select('id as value', 'name as label')->get(),
            'pessoa' => User::select('id as value', 'name as label')->get(),
            'situacao' => RequisicaoPneu::select('situacao as value', 'situacao as label')->distinct()->get()
        ];

        return view('admin.requisicaopneusvendassaida.index', compact('form', 'requisicaoPneusSaida'));
    }

    public function edit($id)
    {
        $requisicaoPneus = RequisicaoPneu::where('id_requisicao_pneu', $id)
            ->with(['usuarioEstoque', 'usuarioSolicitante', 'filial', 'terceiro'])
            ->first();

        $requisicaoPneusModelos = RequisicaoPneuModelos::with('modelo')->where('id_requisicao_pneu', $id)->get();

        return view('admin.requisicaopneusvendassaida.edit', compact('requisicaoPneus', 'requisicaoPneusModelos'));
    }

    public function update(Request $request, $id)
    {
        $requisicaoPneu = RequisicaoPneu::where('id_requisicao_pneu', $id)->first();

        if ($requisicaoPneu->situacao == 'FINALIZADA') {
            return redirect()
                ->back()
                ->withNotification([
                    'title'   => 'Atenção',
                    'type'    => 'warning',
                    'message' => 'Não é possível editar uma requisição de pneu finalizada!'
                ]);
        }

        $reqPneus = $request->validate([
            'justificativa_de_finalizacao' => 'nullable|string|max:200',
        ]);

        $reqPneusModelos = json_decode($request->input('requisicaoPneusModelos'), true) ?? [];
        $pneuSelecionados = json_decode($request->input('pneusSelecionados'), true) ?? [];

        if (empty($pneuSelecionados)) {
            $pneuSelecionados = RequisicaoPneu::with([
                'RequisicaoPneuModelos.requisicaoItens',
                'RequisicaoPneuModelos.modelo.controleVida'
            ])->where('id_requisicao_pneu', $id)->first()->toArray();

            $resultado = [];

            foreach ($pneuSelecionados['requisicao_pneu_modelos'] as $modelo) {
                foreach ($modelo['requisicao_itens'] as $item) {
                    $resultado[] = [
                        'id_pneu' => $item['id_pneu'] . ' - ' . $modelo['id_modelo_pneu'] . ' - ' .
                            trim($modelo['modelo']['descricao_modelo']) . ' - VIDA: ' .
                            ($modelo['modelo']['controle_vida']['descricao_vida_pneu'] ?? ''),
                        'modelo' => [
                            'id_modelo_pneu' => $modelo['modelo']['codigo'],
                            'descricao_modelo' => trim($modelo['modelo']['descricao_modelo']),
                            'id_fornecedor' => $modelo['modelo']['id_fornecedor']
                        ],
                        'vida' => $modelo['idVida'] ?? '',
                        'id_requisicao_pneu_modelos' => $modelo['id_requisicao_pneu_modelos'],
                        'valor_venda' => (float) $item['valor_venda'],
                        'selecionado' => true
                    ];
                }
            }
            $pneuSelecionados = $resultado;
        }

        try {
            DB::beginTransaction();

            foreach ($reqPneusModelos as $reqItem) {
                RequisicaoPneuItens::where('id_requisicao_pneu_modelos', $reqItem['id_requisicao_pneu_modelos'])->delete();
            }

            foreach ($pneuSelecionados as $item) {
                preg_match('/^\d+/', $item['id_pneu'], $matches);
                $id_pneu = intval($matches[0]);
                $reqPneuItens = new RequisicaoPneuItens();

                $reqPneuItens->data_inclusao              = now();
                $reqPneuItens->id_requisicao_pneu_modelos = $item['id_requisicao_pneu_modelos'];
                $reqPneuItens->id_pneu                    = $id_pneu;
                $reqPneuItens->valor_venda                = $item['valor_venda'];
                $reqPneuItens->id_user_edit               = Auth::user()->id;

                $reqPneuItens->save();

                if (!empty($requisicaoPneu->id_terceiro) && $requisicaoPneu->venda) {
                    $statusPneu = Pneu::where('id_pneu', $id_pneu)->first();
                    $statusPneu->data_alteracao = now();
                    $statusPneu->status_pneu    = 'TERCEIRO';

                    $statusPneu->save();

                    $histPneu = HistoricoPneu::where('id_pneu', $id_pneu)
                        ->orderBy('id_historico_pneu', 'desc')
                        ->first();

                    if (empty($histPneu) || $histPneu->status_movimentacao != 'Venda - Terceiro') {
                        $histpneu = new HistoricoPneu();
                        $histpneu->data_inclusao = now();
                        $histpneu->id_pneu       = $id_pneu;
                        $histpneu->id_modelo     = $item['modelo']['id'];
                        $histpneu->id_vida_pneu  = $item['idVida'];
                        $histpneu->status_movimentacao = 'Venda - Terceiro';

                        $histpneu->save();
                    }
                }
            }

            foreach ($reqPneusModelos as $item) {
                $modeloPneuReq = RequisicaoPneuModelos::where('id_requisicao_pneu_modelos', $item['id_requisicao_pneu_modelos'])->first();

                $modeloPneuReq->data_alteracao   = now();
                $modeloPneuReq->quantidade_baixa = $item['quantidade_baixa'] ?? null;
                $modeloPneuReq->data_baixa       = $item['data_baixa'] ?? null;
                $modeloPneuReq->id_filial        = GetterFilial();
                $modeloPneuReq->valor_total      = $item['valor_total'] ?? null;
                $modeloPneuReq->documento        = $item['documento'] ?? null;

                $modeloPneuReq->save();
            }

            $situcao = 'AGUARDANDO DOCUMENTO DE VENDA';
            $docAutorizacao = null;
            if ($request->hasFile('documento_autorizacao')) {
                try {
                    $arquivo = $request->file('documento_autorizacao');
                    $docAutorizacao = $arquivo->store('requisicao_pneus', 'public');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar documento autorização: ' . $e->getMessage());
                }
            }

            $requisicaoPneu->data_alteracao               = now();
            $requisicaoPneu->situacao                     = $requisicaoPneu->situacao ?? $situcao;
            $requisicaoPneu->justificativa_de_finalizacao = $reqPneus['justificativa_de_finalizacao'];
            $requisicaoPneu->documento_autorizacao        = $docAutorizacao ?? $requisicaoPneu->documento_autorizacao;

            $requisicaoPneu->save();


            DB::commit();

            return redirect()
                ->route('admin.requisicaopneusvendassaida.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Requisição de pneu atualizada com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao editar Requisição de pneu:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.requisicaopneusvendassaida.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar a Requisição de pneu."
                ]);
        }
    }

    public function imprimir($id)
    {
        $parametros = array('P_id_requisicao' => $id);
        $name       = 'vendaPneu_v1';
        $agora      = date('d-m-YH:i');
        $tipo       = '.pdf';
        $relatorio  = $name . $agora . $tipo;
        $barra      = '/';
        $partes     = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
        $host       = $partes['host'] . PHP_EOL;
        $pathrel    = (explode('.', $host));
        $dominio    = $pathrel[0];
        $input      = public_path('reports/' . $dominio . '/' . $name . '.jasper');

        if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
            $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
            $pastarelatorio = '/reports/homologacao/' . $name;
            $imprime = 'homologacao';

            Log::info('Usando servidor de homologação');
        } elseif ($dominio == 'lcarvalima') {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/carvalima/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;
            $imprime = $dominio;

            Log::info('Usando servidor de produção');
        } else {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/' . $dominio . '/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;
            $imprime = $dominio;

            Log::info('Usando servidor de produção');
        }


        $jsi = new jasperserverintegration(
            $jasperserver,
            $pastarelatorio,                        // Report Unit Path
            'pdf',                                  // Tipo da exportação do relatório
            'unitop',                               // Usuário com acesso ao relatório
            'unitop2022',                           // Senha do usuário
            $parametros                             // Conteudo do Array
        );

        $data = $jsi->execute();

        try {
            DB::beginTransaction();
            $requisicaoPneu = RequisicaoPneu::where('id_requisicao_pneu', $id)->first();

            $requisicaoPneu->data_alteracao = now();
            $requisicaoPneu->is_impresso    = true;

            $requisicaoPneu->save();

            DB::commit();

            return response($data, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
        } catch (\Throwable $e) {
            DB::rollBack();
            LOG::ERROR('Erro ao gerar relatório: ' . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao gerar relatório',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function onCancel($id)
    {
        try {
            DB::beginTransaction();
            // Atualiza a requisição de pneu para cancelada
            $requisicaoPneu = RequisicaoPneu::with('requisicaoPneuModelos.requisicaoItens')
                ->where('id_requisicao_pneu', $id)
                ->first();

            $pneus = $requisicaoPneu->requisicaoPneuModelos->flatMap(function ($modelo) {
                return $modelo->requisicaoItens;
            });

            // Atualiza o status dos pneus para "ESTOQUE"
            foreach ($pneus as $pneu) {
                Pneu::where('id_pneu', $pneu->id_pneu)
                    ->update(['status_pneu' => 'ESTOQUE']);

                HistoricoPneu::where('id_pneu', $pneu->id_pneu)
                    ->where('status_movimentacao', 'Venda - Terceiro')
                    ->update(['status_movimentacao' => 'Venda - Terceiro - Cancelada', 'data_alteracao' => now()]);
            }

            $requisicaoPneu->is_cancelada = true;
            $requisicaoPneu->save();

            DB::commit();
            return response()->json(['message' => 'Requisição pneus cancelada com sucesso.']);
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::ERROR('ERRO AO CANCELAR REQUISICAO PNEUS: ' . $e->getMessage());

            return response()->json(['error' => 'Erro ao cancelar o registro de Requisição Pneus.'], 500);
        }
    }

    public function getPneusByModelo($requisicaoId, $modeloId = null)
    {
        // Consulta base para requisição de pneus modelos
        $query = RequisicaoPneuModelos::with('modelo', 'modelo.controleVida')
            ->where('id_requisicao_pneu', $requisicaoId);

        // Se um ID de modelo específico for fornecido, filtre por ele
        if ($modeloId !== null) {
            $query->where('id_modelo_pneu', $modeloId);
        }

        $reqPneusModelos = $query->get();

        if ($reqPneusModelos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum modelo de pneu encontrado'
            ]);
        }

        $resultados = [];

        foreach ($reqPneusModelos as $modelo) {

            $qtd = RequisicaoPneuModelos::where('id_modelo_pneu', $modelo->id_modelo_pneu)->where('id_requisicao_pneu', $requisicaoId)->first()->quantidade;
            $pneus = RequisicaoPneuItens::with('pneu.controleVidaPneus')->where('id_requisicao_pneu_modelos', $modelo->id_requisicao_pneu_modelos)
                ->get()
                ->map(function ($item) {
                    $item->selecionado = true;
                    return $item;
                });

            if ($pneus->isEmpty() || $pneus->count() < $qtd) {
                // Vamos marcar como selecionado
                $pneuItem = RequisicaoPneuItens::where('id_requisicao_pneu_modelos', $modelo->id_requisicao_pneu_modelos)
                    ->get()
                    ->map(function ($item) {
                        $item->selecionado = true;
                        return $item;
                    });

                $estoquePneus = Pneu::where('id_modelo_pneu', $modelo->id_modelo_pneu)
                    ->where('status_pneu', 'ESTOQUE')
                    ->get();

                foreach ($estoquePneus as $pneu) {
                    if ($pneuItem->contains('id_pneu', $pneu->id_pneu)) {
                        $estoquePneus->forget($estoquePneus->search($pneu));
                    }
                }
                // Agora vamos mesclar o estoque para continuar com a seleção
                $pneus = $pneuItem->merge($estoquePneus);
            }

            // Para cada pneu encontrado, adicione ao array de resultados

            foreach ($pneus as $pneu) {
                $resultados[] = [
                    'id' => $pneu->id_pneu,
                    // 'codigo' => $modelo->id_requisicao_pneu_modelos,
                    'modelo' => [
                        'id' => $modelo->modelo->id_modelo_pneu,
                        'codigo' => $modelo->modelo->id_modelo_pneu,
                        'descricao' => $modelo->modelo->descricao_modelo
                    ],
                    'id_vida' => $pneu->pneu->controleVidaPneus->id_controle_vida_pneu,
                    'vida' => $modelo->modelo->controleVida->descricao_vida_pneu,
                    'id_requisicao_pneu_modelos' => $modelo->id_requisicao_pneu_modelos,
                    'valor_venda' => $pneu->valor_venda,
                    'selecionado' => $pneu->selecionado
                ];
            }
        }

        // Verificar se encontramos algum resultado após o processamento
        if (empty($resultados)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum pneu encontrado para os modelos'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pneus encontrados com sucesso',
            'data' => $resultados
        ]);
    }

    public function onFinalizarSaida($id)
    {
        $reqPneu = RequisicaoPneu::where('id_requisicao_pneu', $id)->first();
        if ($reqPneu->is_impresso == false) {
            return redirect()->back()->with('notification', [
                'type' => 'info',
                'title' => 'Atenção',
                'message' => 'Você não pode finalizar esse processo antes de imprimir o documentos de venda.',
                'duration' => 3000, // opcional (padrão: 5000ms)
            ]);
        }

        if (empty($reqPneu->documento_autorizacao)) {
            return redirect()->back()->with('notification', [
                'type' => 'info',
                'title' => 'Atenção',
                'message' => 'Você não pode finalizar esse processo antes de insirir o documento assinado.',
                'duration' => 3000, // opcional (padrão: 5000ms)
            ]);
        }

        try {
            DB::beginTransaction();
            if (!empty($reqPneu->documento_autorizacao)) {
                $finaliza = RequisicaoPneu::findOrFail($id);

                $finaliza->data_alteracao = now();
                $finaliza->situacao = 'FINALIZADA';

                $finaliza->save();

                db::commit();

                return redirect()->back()->with('notification', [
                    'type' => 'success',
                    'title' => 'Operação Concluída',
                    'message' => 'Requisição de saida finalizada com sucesso.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
            } else {
                return redirect()->back()->with('notification', [
                    'type' => 'info',
                    'title' => 'Atenção',
                    'message' => 'Ainda não foi inserido o Documento de Autorização, por isso não é possível finalizar a venda.',
                    'duration' => 3000, // opcional (padrão: 5000ms)
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::ERROR('Erro ao finalizar a requisição: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Não foi possivel finalizar a requisição',
                'message' => $e->getMessage(),
                'duration' => 10000, // opcional (padrão: 5000ms)
            ]);
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);
            LOG::DEBUG('Query: ' . $query->toSql());

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            log::debug($this->hasAnyFilter($request, $this->getValidExportFilters()));
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
                $pdf->loadView('admin.requisicaopneusvendassaida.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('requisicaoPneusVenda_' . date('Y-m-d_His') . '.pdf');
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

    public function buildExportQuery($request)
    {
        $query = RequisicaoPneu::where('is_cancelada', false)
            ->where('venda', true)
            ->with('usuarioEstoque', 'usuarioSolicitante', 'filial')
            ->orderBy('id_requisicao_pneu', 'desc');

        if ($request->filled('id_requisicao_pneu')) {
            $query->where("id_requisicao_pneu", $request->id_requisicao_pneu);
        }

        if ($request->filled(['data_inicial', 'data_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inicial,
                $request->data_final
            ]);
        }

        if ($request->filled('id_situacao')) {
            $query->where("situacao", $request->id_situacao);
        }

        if ($request->filled('id_filial')) {
            $query->where("id_filial", $request->id_filial);
        }

        if ($request->filled('id_usuario')) {
            $query->where("id_usuario_solicitante", $request->id_usuario);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_requisicao_pneu',
            'data_inicial',
            'data_final',
            'id_situacao',
            'id_filial',
            'id_usuario'
        ];
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_requisicao_pneu' => 'Código',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'usuarioSolicitante.name' => 'Usuário Alteração',
            'situacao' => 'Situação',
            'usuarioEstoque.name' => 'Usuário Estoque',
            'filial.name' => 'Filial',
            'documento_autorizacao' => 'Documento Autorização'
        ];

        return $this->exportToExcel($request, $query, $columns, 'requisicaoPneuVenda', $this->getValidExportFilters());
    }

    /**
     * Exportar para CSV
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_requisicao_pneu' => 'Código',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
            'usuarioSolicitante.name' => 'Usuário Alteração',
            'situacao' => 'Situação',
            'usuarioEstoque.name' => 'Usuário Estoque',
            'filial.name' => 'Filial',
            'documento_autorizacao' => 'Documento Autorização'
        ];

        return $this->exportToCsv($request, $query, $columns, 'requisicaoPneuVenda', $this->getValidExportFilters());
    }

    /**
     * Exportar para XML
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_requisicao_pneu',
            'Data Inclusao' => 'data_inclusao',
            'Data Alteracao' => 'data_alteracao',
            'Usuario Alteracao' => 'usuarioSolicitante.name',
            'Situacao' => 'situacao',
            'Usuario Alteracao' => 'usuarioSolicitante.name',
            'filial.name' => 'Filial',
            'Documento Autorizacao' => 'documento_autorizacao',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'requisicaoPneuVendas',
            'requisicaoPneuVenda',
            'requisicaoPneuVendas',
            $this->getValidExportFilters()
        );
    }
}
