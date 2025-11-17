<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DescartePneu;
use App\Models\Pneu;
use App\Models\TipoDescarte;
use App\Services\DescarteService;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DescartePneuController extends Controller
{
    use SanitizesMonetaryValues;

    protected DescarteService $descarteService;

    public function __construct(DescarteService $descarteService)
    {
        $this->descarteService = $descarteService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);

        $query = DescartePneu::query()
            ->select([
                'id_descarte_pneu',
                'data_inclusao',
                'id_pneu',
                'id_tipo_descarte',
                'nome_arquivo',
                'status_processo',
                'origem',
                'finalizado_em',
                'valor_venda_pneu',
            ])
            ->with(['tipoDescarte', 'pneu'])
            ->orderBy('id_descarte_pneu', 'desc')
            ->distinct();

        // ✅ FILTROS EXISTENTES
        if ($request->filled('id_descarte_pneu')) {
            $query->where('id_descarte_pneu', $request->id_descarte_pneu);
        }

        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->id_pneu);
        }

        if ($request->filled('id_tipo_descarte')) {
            $query->where('id_tipo_descarte', $request->id_tipo_descarte);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        // ✅ NOVOS FILTROS
        if ($request->filled('status_processo')) {
            $query->where('status_processo', $request->status_processo);
        }

        if ($request->filled('origem')) {
            $query->where('origem', $request->origem);
        }

        $descartePneus = $query->paginate($pageSize)->appends($request->query());

        // ✅ TOTALIZADORES CORRETOS - COUNTS DIRETOS NO BANCO
        $totalAguardando = DescartePneu::where('status_processo', 'aguardando_inicio')->count();
        $totalEmAndamento = DescartePneu::where('status_processo', 'em_andamento')->count();
        $totalFinalizados = DescartePneu::where('status_processo', 'finalizado')->count();
        $totalGeral = DescartePneu::count();

        // ✅ DADOS PARA FILTROS
        $pneu = Pneu::select('id_pneu as label', 'id_pneu as value')
            ->where('status_pneu', 'DESCARTE')
            ->orderBy('label')
            ->limit(20)
            ->get()
            ->toArray();

        $tipodescarte = TipoDescarte::select('descricao_tipo_descarte as label', 'id_tipo_descarte as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        $destartePneus = DescartePneu::select('id_descarte_pneu as label', 'id_descarte_pneu as value')
            ->orderBy('id_descarte_pneu', 'desc')
            ->limit(50)
            ->get()
            ->toArray();

        // ✅ OPÇÕES PARA NOVOS FILTROS
        $statusOptions = [
            ['label' => 'Aguardando Início', 'value' => 'aguardando_inicio'],
            ['label' => 'Em Andamento', 'value' => 'em_andamento'],
            ['label' => 'Finalizado', 'value' => 'finalizado'],
        ];

        $origemOptions = [
            ['label' => 'Manual', 'value' => 'manual'],
            ['label' => 'Manutenção', 'value' => 'manutencao'],
            ['label' => 'Não Informado', 'value' => 'nao_informado'],
        ];

        // ✅ PNEUS AGUARDANDO DESCARTE (para laudo múltiplo)
        $pneusAguardando = $this->descarteService->buscarPneusAguardandoDescarte(100);

        return view('admin.descartepneus.index', compact(
            'descartePneus',
            'tipodescarte',
            'pneu',
            'destartePneus',
            'statusOptions',
            'origemOptions',
            'pneusAguardando',
            'totalAguardando',
            'totalEmAndamento',
            'totalFinalizados',
            'totalGeral'
        ));
    }

    /**
     * Show the form for creating a new resource.
     * ✅ RESTRITO A SUPERUSER
     */
    public function create()
    {
        // ✅ VERIFICAR SE É SUPERUSER
        if (! Auth::user()->isSuperuser()) {
            return redirect()->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Acesso Negado!',
                    'type' => 'error',
                    'message' => 'Apenas superusuários podem criar descartes manuais. Pneus devem vir de manutenção.',
                ]);
        }

        $pneu = Pneu::select('id_pneu as label', 'id_pneu as value')
            ->where('status_pneu', '!=', 'APLICADO')
            ->where('status_pneu', '!=', 'DESCARTE')
            ->orderBy('label', 'desc')
            ->get()->toArray();

        $tipodescarte = TipoDescarte::select('descricao_tipo_descarte as label', 'id_tipo_descarte as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        return view('admin.descartepneus.create', compact('pneu', 'tipodescarte'));
    }

    /**
     * Store a newly created resource in storage.
     * ✅ USANDO DESCARTESERVICE
     */
    public function store(Request $request)
    {
        $dados = $request->validate([
            'id_pneu' => 'required|integer',
            'id_tipo_descarte' => 'required|integer',
            'valor_venda_pneu' => 'required',
            'observacao' => 'required|string|max:700',
            'nome_arquivo' => 'nullable|file|max:2048',
        ]);

        try {
            // ✅ USAR DESCARTESERVICE
            $arquivo = $request->hasFile('nome_arquivo') ? $request->file('nome_arquivo') : null;

            $descarte = $this->descarteService->criarDescarteManual($dados, $arquivo);

            return redirect()
                ->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Baixa de pneu cadastrada com sucesso!',
                ]);

        } catch (\Exception $e) {
            Log::error('Erro na criação da Baixa de Pneu:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ]);
        }
    }

    /**
     * ✅ NOVO: Anexar laudo a múltiplos pneus
     */
    public function anexarLaudoMultiplo(Request $request): JsonResponse
    {
        $request->validate([
            'pneus_selecionados' => 'required|array|min:1',
            'pneus_selecionados.*' => 'integer|exists:pneu,id_pneu',
            'arquivo_laudo' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
        ]);

        try {
            $resultado = $this->descarteService->anexarLaudoMultiplo(
                $request->pneus_selecionados,
                $request->file('arquivo_laudo')
            );

            $message = count($resultado['processados']).' pneus processados com sucesso.';
            if (! empty($resultado['erros'])) {
                $message .= ' Erros: '.implode(', ', $resultado['erros']);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processados' => $resultado['processados'],
                'erros' => $resultado['erros'],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar laudo múltiplo:', [
                'message' => $e->getMessage(),
                'pneus' => $request->pneus_selecionados ?? [],
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * ✅ NOVO: Finalizar processo de descarte
     */
    public function finalizar(Request $request, $id): JsonResponse
    {
        try {
            $dadosFinalizacao = $request->validate([
                'valor_venda_pneu' => 'nullable|numeric',
                'observacao' => 'nullable|string|max:700',
            ]);

            $descarte = $this->descarteService->finalizarDescarte($id, $dadosFinalizacao);

            return response()->json([
                'success' => true,
                'message' => 'Processo de baixa finalizado com sucesso!',
                'descarte' => $descarte,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao finalizar descarte:', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * ✅ VERIFICAR SE PODE SER EDITADO
     */
    public function edit($id)
    {
        $descartePneus = DescartePneu::findOrFail($id);

        // ✅ VERIFICAR SE PODE SER EDITADO
        if (! $this->descarteService->podeSerEditado($descartePneus)) {
            return redirect()->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Acesso Negado!',
                    'type' => 'error',
                    'message' => 'Este processo já foi finalizado e só pode ser editado por superusuários.',
                ]);
        }

        $pneu = Pneu::select('id_pneu as label', 'id_pneu as value')
            ->where('status_pneu', '!=', 'APLICADO')
            ->where('status_pneu', '!=', 'DESCARTE')
            ->where('status_pneu', '!=', 'EM MANUTENÇÃO')
            ->orderBy('label')
            ->get()->toArray();

        $tipodescarte = TipoDescarte::select('descricao_tipo_descarte as label', 'id_tipo_descarte as value')
            ->orderBy('label')
            ->get()
            ->toArray();

        return view('admin.descartepneus.edit', compact('pneu', 'tipodescarte', 'descartePneus'));
    }

    /**
     * Update the specified resource in storage.
     * ✅ VERIFICAR PERMISSÕES E USAR VALIDAÇÕES
     */
    public function update(Request $request, $id)
    {
        $descarte = DescartePneu::findOrFail($id);

        // ✅ VERIFICAR SE PODE SER EDITADO
        if (! $this->descarteService->podeSerEditado($descarte)) {
            return redirect()->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Acesso Negado!',
                    'type' => 'error',
                    'message' => 'Este processo já foi finalizado e só pode ser editado por superusuários.',
                ]);
        }

        $request->merge([
            'nome_arquivo' => $request->input('selected_nome_arquivo', $request->file('nome_arquivo')),
        ]);

        $dados = $request->validate([
            'id_tipo_descarte' => 'required|integer',
            'valor_venda_pneu' => 'required|string',
            'observacao' => 'required|string|max:700',
            'nome_arquivo' => 'nullable|file|max:2048',
        ]);

        DB::beginTransaction();

        try {
            // ✅ UPLOAD DE ARQUIVO SE FORNECIDO
            if ($request->hasFile('nome_arquivo') && $request->file('nome_arquivo')->isValid()) {
                $fotoPath = $request->file('nome_arquivo')->store('laudos', 'public');
            } else {
                $fotoPath = $request->input('selected_nome_arquivo');
            }

            $dados['data_alteracao'] = now();
            $dados['nome_arquivo'] = $fotoPath;

            // ✅ ATUALIZAR STATUS SE AGORA TEM LAUDO
            if ($fotoPath && $descarte->status_processo === 'aguardando_inicio') {
                $dados['status_processo'] = 'em_andamento';
            }

            $descarte->update($dados);

            DB::commit();

            return redirect()
                ->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Sucesso!',
                    'type' => 'success',
                    'message' => 'Baixa de pneu alterada com sucesso!',
                ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na alteração da Baixa de Pneu:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.descartepneus.index')
                ->withNotification([
                    'title' => 'Erro!',
                    'type' => 'error',
                    'message' => 'Não foi possível alterar a Baixa de Pneu.',
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * ✅ USAR DESCARTESERVICE
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->descarteService->excluirDescarte($id);

            return response()->json([
                'notification' => [
                    'title' => 'Baixa de pneu excluída!',
                    'type' => 'success',
                    'message' => 'Baixa de pneu excluída com sucesso',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao excluir Baixa de Pneu: '.$e->getMessage());

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * ✅ NOVO: Obter arquivo de laudo (compatibilidade híbrida)
     */
    public function obterLaudo($id)
    {
        try {
            $descarte = DescartePneu::findOrFail($id);
            $arquivo = $this->descarteService->obterArquivoLaudo($descarte);

            if (! $arquivo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laudo não encontrado',
                ], 404);
            }

            if ($arquivo['tipo'] === 'storage') {
                return redirect($arquivo['url']);
            }

            // Para base64 (sistema legado) - implementar lógica de conversão se necessário
            return response()->json([
                'success' => true,
                'tipo' => $arquivo['tipo'],
                'arquivo' => $arquivo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter laudo: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * ✅ NOVO: Listar pneus aguardando descarte (para interface de laudo múltiplo)
     */
    public function pneusAguardando(): JsonResponse
    {
        try {
            $pneus = $this->descarteService->buscarPneusAguardandoDescarte();

            return response()->json([
                'success' => true,
                'pneus' => $pneus->map(function ($descarte) {
                    return [
                        'id_pneu' => $descarte->id_pneu,
                        'id_descarte' => $descarte->id_descarte_pneu,
                        'tipo_descarte' => $descarte->tipoDescarte->descricao_tipo_descarte ?? '',
                        'data_inclusao' => $descarte->data_inclusao->format('d/m/Y'),
                        'valor_venda' => $descarte->valor_venda_pneu,
                    ];
                }),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar pneus: '.$e->getMessage(),
            ], 500);
        }
    }
}
