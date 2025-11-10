<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbastecimentoIntegracao;
use App\Models\EntradaAfericaoAbastecimento;
use App\Models\Bomba;
use App\Models\Tanque;
use App\Models\User;
use App\Traits\ExportableTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AfericaoBombaController extends Controller
{
    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Aplicar filtro de entrada realizada antes da paginação
        $hasEntradaFilter = $request->filled('entrada_realizada');
        $entradaFilter = $request->entrada_realizada;

        // Primeiro, obtém os IDs das aferições que já tiveram entrada
        $entradasIds = null;
        if ($hasEntradaFilter) {
            $entradasIds = EntradaAfericaoAbastecimento::pluck('id_abastecimento_integracao')->toArray();
        }

        $query = AbastecimentoIntegracao::query()
            ->select('abastecimento_integracao.*')
            ->where('placa', 'like', '%AFERIR%')
            ->with('bomba');

        // Aplicar filtros
        if ($request->filled('id_abastecimento_integracao')) {
            $query->where('id_abastecimento_integracao', $request->id_abastecimento_integracao);
        }

        if ($request->filled('data_inicio_inicial')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio_inicial);
        }

        if ($request->filled('data_inicio_final')) {
            $query->whereDate('data_inicio', '<=', $request->data_inicio_final);
        }

        if ($request->filled('descricao_bomba')) {
            $query->where('descricao_bomba', $request->descricao_bomba);
        }

        // Aplicar filtro de entrada diretamente na query
        if ($hasEntradaFilter) {
            if ($entradaFilter == '1') {
                // Apenas as aferições que já têm entrada
                $query->whereIn('id_abastecimento_integracao', $entradasIds);
            } elseif ($entradaFilter == '0') {
                // Apenas as aferições que não têm entrada
                $query->whereNotIn('id_abastecimento_integracao', $entradasIds);
            }
        }

        // Ordenação
        $query->orderBy('id_abastecimento_integracao', 'desc');

        // Paginação
        $afericoes = $query->paginate();

        // Marcar as que já tiveram entrada para exibição na tabela
        $afericoesIds = $afericoes->pluck('id_abastecimento_integracao')->toArray();
        $entradasRealizadas = EntradaAfericaoAbastecimento::whereIn('id_abastecimento_integracao', $afericoesIds)
            ->pluck('id_abastecimento_integracao')
            ->toArray();

        // Dados referência para o formulário de busca
        $referenceDatas = $this->getReferenceDatas();

        $descricao_bomba = $this->getUniqueBombaDescricoes();

        // Se for uma solicitação HTMX, retorne apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.afericaobombas._table', compact('afericoes', 'entradasRealizadas'));
        }

        // Verifica se houve um novo cadastro para scroll automático
        $newEntryId = session('new_entry_id');
        $shouldScrollToBottom = session('scroll_to_bottom', false);

        return view('admin.afericaobombas.index', array_merge(
            compact('afericoes', 'entradasRealizadas', 'descricao_bomba', 'newEntryId', 'shouldScrollToBottom'),
        ));
    }

    /**
     * Retorna os dados de referência para os formulários
     */
    protected function getReferenceDatas()
    {
        return Cache::remember('afericao_reference_datas', now()->addHours(12), function () {
            return [
                'bombas' => Bomba::orderBy('descricao_bomba')
                    ->pluck('descricao_bomba')
                    ->unique()
                    ->values()
                    ->toArray(),

                'tanques' => Tanque::orderBy('tanque')
                    ->with('estoqueCombustivel')
                    ->whereHas('estoqueCombustivel', function ($query) {
                        $query->whereNull('data_encerramento');
                    })
                    ->get(['id_tanque as value', 'tanque as label'])
            ];
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $usuario = Auth::user();

        // Verificação de permissão
        if (!($usuario->id == 1 || $usuario->is_superuser || in_array($usuario->id, [25, 195]))) {
            return redirect()
                ->route('admin.afericaobombas.index')
                ->with('error', 'Usuário não permitido para cadastrar nova Aferição');
        }

        $tanques = Tanque::select('id_tanque as value', 'tanque as label')
            ->with('estoqueCombustivel')
            ->whereHas('estoqueCombustivel', function ($query) {
                $query->whereNull('data_encerramento');
            })
            ->orderBy('tanque')
            ->get();

        // Verificar se foi fornecido um ID de abastecimento (similar ao onShow do Adianti)
        $abastecimento = null;
        $tanqueSelecionado = null;

        if ($request->has('id_abastecimento_int')) {
            $abastecimentoId = $request->id_abastecimento_int;

            // Buscar o abastecimento
            $abastecimento = AbastecimentoIntegracao::find($abastecimentoId);

            if ($abastecimento) {
                // Buscar o tanque associado à bomba
                $bomba = Bomba::where('descricao_bomba', $abastecimento->descricao_bomba)->first();
                if ($bomba) {
                    $tanqueSelecionado = $bomba->id_tanque;
                }
            }
        }

        return view('admin.afericaobombas.create', compact('usuario', 'tanques', 'abastecimento', 'tanqueSelecionado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log para debug
            Log::info('Iniciando método store com dados:', $request->all());

            // Validação dos dados
            $validated = $request->validate([
                'id_tanque' => 'required|exists:tanque,id_tanque',
                'volume_entrada' => 'required|numeric|min:0.01',
            ]);

            Log::info('Validação passou com sucesso', $validated);

            // Verificar se tem um ID de abastecimento
            $abastecimentoId = $request->id_abastecimento_int;
            $abastecimento = null;

            if ($abastecimentoId) {
                Log::info('ID de abastecimento encontrado: ' . $abastecimentoId);

                // Verificar se já existe entrada para este abastecimento
                $entradaExistente = EntradaAfericaoAbastecimento::where('id_abastecimento_integracao', $abastecimentoId)->first();

                if ($entradaExistente) {
                    Log::warning('Entrada existente encontrada para o abastecimento: ' . $abastecimentoId);
                    return redirect()
                        ->route('admin.afericaobombas.index')
                        ->with('error', 'Já existe uma entrada para esta aferição.');
                }

                // Buscar o abastecimento
                $abastecimento = AbastecimentoIntegracao::find($abastecimentoId);

                if (!$abastecimento) {
                    Log::error('Abastecimento não encontrado para o ID: ' . $abastecimentoId);
                    return back()
                        ->withInput()
                        ->with('error', 'Abastecimento não encontrado.');
                }

                Log::info('Abastecimento encontrado. Volume: ' . $abastecimento->volume);

                // Validar volume de entrada
                if (floatval($validated['volume_entrada']) > floatval($abastecimento->volume)) {
                    Log::warning('Volume entrada maior que volume abastecimento');
                    return back()
                        ->withInput()
                        ->with('error', 'O volume de entrada não pode ser maior que o volume de abastecimento.');
                }

                // Gerar notificação se volume de entrada for menor que o volume de abastecimento
                if (floatval($validated['volume_entrada']) < floatval($abastecimento->volume)) {
                    Log::warning("Possível inconsistência detectada: Volume de entrada menor que o volume de abastecimento. ID: {$abastecimentoId}");

                    // No código legado, notificações são enviadas para os usuários 1 e 25
                    // Aqui iremos implementar um sistema de notificação similar
                    // Registrar a notificação em log por enquanto
                    Log::warning("NOTIFICAÇÃO: Uma possível inconsistência pode ter sido gerada ao ser feita uma entrada menor que o abastecimento por aferição. Cód. Abastecimento Integração Nº: {$abastecimentoId}");

                    // TODO: Implementar sistema de notificação para usuários específicos
                    // Exemplo:
                    // Notification::send(User::find([1, 25]), new InconsistenciaAfericaoNotification($abastecimentoId));
                }
            }

            Log::info('Iniciando transação para salvar entrada');
            DB::beginTransaction();

            try {
                // Criar entrada de aferição
                $afericao = new EntradaAfericaoAbastecimento();
                $afericao->data_inclusao = now();
                $afericao->id_usuario = Auth::id();
                $afericao->id_tanque = $validated['id_tanque'];

                if ($abastecimento) {
                    $afericao->id_abastecimento_integracao = $abastecimentoId;
                    $afericao->volume_abastecimento = $abastecimento->volume;
                } else {
                    $afericao->volume_abastecimento = $validated['volume_entrada']; // Se não tiver abastecimento, usa o mesmo valor
                }

                $afericao->volume_entrada = $validated['volume_entrada'];

                $afericao->save();

                Log::info('Entrada salva com sucesso. ID: ' . $afericao->id_entrada_afericao_abastecimento);

                // Registrar o ID do abastecimento para destacar na lista de forma mais visível
                if ($abastecimento) {
                    // Guarda o ID do abastecimento para marcar na lista
                    session()->flash('new_afericao_id', $abastecimentoId);
                    // Também guarda a descrição da bomba para facilitar identificação visual
                    session()->flash('new_afericao_bomba', $abastecimento->descricao_bomba);
                }

                DB::commit();
                Log::info('Transação concluída com sucesso');

                return redirect()
                    ->route('admin.afericaobombas.index')
                    ->with('success', 'Entrada por aferição cadastrada com sucesso!')
                    ->with('show_toast', true)
                    ->with('reload_table', true);
            } catch (\Exception $innerEx) {
                Log::error('Erro ao salvar entrada: ' . $innerEx->getMessage());
                DB::rollBack();
                throw $innerEx; // Re-throw para o catch externo
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gravar a entrada por aferição: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar entrada por aferição: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $afericao = AbastecimentoIntegracao::findOrFail($id);
        $usuario = Auth::user();

        // Verificar se já existe entrada para esta aferição
        $entradaExistente = EntradaAfericaoAbastecimento::where('id_abastecimento_integracao', $id)->first();

        if ($entradaExistente) {
            return redirect()
                ->route('admin.afericaobombas.index')
                ->with('error', 'Já existe uma entrada para esta aferição.');
        }

        // Pegar o tanque automaticamente com base na bomba
        $tanqueId = null;
        $bomba = Bomba::where('descricao_bomba', $afericao->descricao_bomba)->first();
        if ($bomba) {
            $tanqueId = $bomba->id_tanque;
        }

        $tanques = Tanque::select('id_tanque as value', 'tanque as label')
            ->with('estoqueCombustivel')
            ->whereHas('estoqueCombustivel', function ($query) {
                $query->whereNull('data_encerramento');
            })
            ->orderBy('tanque')
            ->get();

        // Selecionar o tanque automaticamente
        $tanqueSelecionado = $tanqueId;

        return view('admin.afericaobombas.edit', compact('afericao', 'usuario', 'tanques', 'tanqueSelecionado'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validação dos dados
            $validated = $request->validate([
                'id_tanque' => 'required|exists:tanque,id_tanque',
                'volume_entrada' => 'required|numeric|min:0.01',
            ]);

            // Verificar se já existe entrada
            $entradaExistente = EntradaAfericaoAbastecimento::where('id_abastecimento_integracao', $id)->first();

            if ($entradaExistente) {
                return redirect()
                    ->route('admin.afericaobombas.index')
                    ->with('error', 'Já existe uma entrada para esta aferição.');
            }

            // Verificar o abastecimento
            $abastecimento = AbastecimentoIntegracao::findOrFail($id);

            // Validar volume de entrada
            if (floatval($validated['volume_entrada']) > floatval($abastecimento->volume)) {
                return back()
                    ->withInput()
                    ->with('error', 'O volume de entrada não pode ser maior que o volume de abastecimento.');
            }

            // Gerar notificação se volume de entrada for menor que o volume de abastecimento
            if (floatval($validated['volume_entrada']) < floatval($abastecimento->volume)) {
                // Aqui seria implementada a lógica de notificação para usuários específicos
                Log::warning("Possível inconsistência detectada: Volume de entrada menor que o volume de abastecimento. ID: {$id}");
            }

            DB::beginTransaction();

            // Criar entrada de aferição
            $afericao = new EntradaAfericaoAbastecimento();
            $afericao->data_inclusao = now();
            $afericao->id_usuario = Auth::id();
            $afericao->id_tanque = $validated['id_tanque'];
            $afericao->id_abastecimento_integracao = $id;
            $afericao->volume_abastecimento = $abastecimento->volume;
            $afericao->volume_entrada = $validated['volume_entrada'];

            $afericao->save();

            DB::commit();

            // Armazenar o ID da nova entrada na sessão para poder destacá-la na lista
            session()->flash('new_entry_id', $afericao->id_entrada_afericao_abastecimento);
            session()->flash('scroll_to_bottom', true);

            return redirect()
                ->route('admin.afericaobombas.index')
                ->with('success', 'Entrada por aferição cadastrada com sucesso!')
                ->with('reload_table', true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao gravar a entrada por aferição: ' . $e->getMessage());

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar entrada por aferição: ' . $e->getMessage());
        }
    }

    /**
     * Métodos para exportação
     */
    protected function buildExportQuery(Request $request)
    {
        $query = AbastecimentoIntegracao::query()
            ->select('abastecimento_integracao.*')
            ->where('placa', 'like', '%AFERIR%')
            ->with(['bomba', 'entradaAfericao'])
            ->distinct();

        // Aplicar filtros
        if ($request->filled('id_abastecimento_integracao')) {
            $query->where('id_abastecimento_integracao', $request->id_abastecimento_integracao);
        }

        if ($request->filled('data_inicio_inicial')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio_inicial);
        }

        if ($request->filled('data_inicio_final')) {
            $query->whereDate('data_inicio', '<=', $request->data_inicio_final);
        }

        if ($request->filled('descricao_bomba')) {
            $query->where('descricao_bomba', 'like', '%' . $request->descricao_bomba . '%');
        }

        // Ordenação
        return $query->orderBy('id_abastecimento_integracao', 'desc');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_abastecimento_integracao',
            'data_inicio_inicial',
            'data_inicio_final',
            'descricao_bomba'
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Marcar as que já tiveram entrada
                $afericoesIds = $data->pluck('id_abastecimento_integracao')->toArray();
                $entradasRealizadas = EntradaAfericaoAbastecimento::whereIn('id_abastecimento_integracao', $afericoesIds)
                    ->pluck('id_abastecimento_integracao')
                    ->toArray();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.afericaobombas.pdf', compact('data', 'entradasRealizadas'));

                // Forçar download
                return $pdf->download('afericao_bombas_' . date('Y-m-d_His') . '.pdf');
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
            'id_abastecimento_integracao' => 'Código',
            'descricao_bomba' => 'Bomba',
            'placa' => 'Placa',
            'volume' => 'Volume',
            'data_inicio' => 'Data',
            'entrada_realizada' => 'Entrada Realizada'
        ];

        return $this->exportToCsv($request, $query, $columns, 'afericao_bombas', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_abastecimento_integracao' => 'Código',
            'descricao_bomba' => 'Bomba',
            'placa' => 'Placa',
            'volume' => 'Volume',
            'data_inicio' => 'Data',
            'entrada_realizada' => 'Entrada Realizada'
        ];

        return $this->exportToExcel($request, $query, $columns, 'afericao_bombas', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'id' => 'id_abastecimento_integracao',
            'bomba' => 'descricao_bomba',
            'placa' => 'placa',
            'volume' => 'volume',
            'data' => 'data_inicio',
            'entrada_realizada' => 'entrada_realizada'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'afericao_bombas',
            'afericao',
            'afericoes',
            $this->getValidExportFilters()
        );
    }

    private function getUniqueBombaDescricoes()
    {
        return Cache::remember('bombas_descricoes_unicas', now()->addHours(12), function () {
            return Bomba::select('descricao_bomba')
                ->distinct()
                ->whereNotNull('descricao_bomba')
                ->where('descricao_bomba', '!=', '')
                ->orderBy('descricao_bomba')
                ->get()
                ->map(function ($bomba) {
                    return [
                        'value' => $bomba->descricao_bomba,
                        'label' => $bomba->descricao_bomba
                    ];
                });
        });
    }
}
