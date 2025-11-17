<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TipoDesenhoPneu;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Traits\ExportableTrait;

class TipoDesenhoPneuController extends Controller
{
    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $query = TipoDesenhoPneu::query();

            if ($request->filled('id_desenho_pneu')) {
                $query->where('id_desenho_pneu', $request->id_desenho_pneu);
            }

            if ($request->filled('descricao_desenho_pneu')) {
                $query->where('descricao_desenho_pneu', $request->descricao_desenho_pneu);
            }

            if ($request->filled('numero_sulcos')) {
                $query->where('numero_sulcos', $request->numero_sulcos);
            }

            if ($request->filled('data_inclusao')) {
                $query->whereDate('data_inclusao', $request->data_inclusao);
            }


            // Obter os dados com paginação diretamente do banco
            $tipodesenhopneus = $query->orderBy('id_desenho_pneu', 'desc')
                ->paginate(15)  // Define 15 registros por página
                ->through(function ($desenho) {
                    // Formatar cada item para exibição
                    return [
                        'id'              => $desenho->id_desenho_pneu,
                        'descricao'       => $desenho->descricao_desenho_pneu,
                        'Número de Sulcos' => $desenho->numero_sulcos,
                        'Quantidade de Lona' => $desenho->quantidade_lona_pneu,
                        'Dias Calibragem' => $desenho->dias_calibragem,
                        'Data Inclusão'   => format_date($desenho->data_inclusao),
                        'Data Alteração'  => $desenho->data_alteracao ? format_date($desenho->data_alteracao) : ''
                    ];
                });

            $descricao_desenho_pneu = TipoDesenhoPneu::select('descricao_desenho_pneu as label', 'descricao_desenho_pneu as value')
                ->orderBy('label')
                ->get()
                ->toArray();

            $numero_sulcos = TipoDesenhoPneu::select('numero_sulcos as label', 'numero_sulcos as value')
                ->distinct()
                ->orderBy('label')
                ->get()
                ->toArray();


            return view('admin.tipodesenhopneus.index', compact('tipodesenhopneus', 'descricao_desenho_pneu', 'numero_sulcos'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de desenho de pneus: ' . $e->getMessage());
            return redirect()->back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de tipos de desenho de pneus.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.tipodesenhopneus.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descricao_desenho_pneu' => 'required|string|max:500',
                'numero_sulcos' => 'nullable|numeric',
                'quantidade_lona_pneu' => 'nullable|numeric',
                'dias_calibragem' => 'nullable|numeric'
            ]);

            $tipodesenhopneu = new TipoDesenhoPneu();
            $tipodesenhopneu->data_inclusao = now();
            $tipodesenhopneu->descricao_desenho_pneu = $validated['descricao_desenho_pneu'];
            $tipodesenhopneu->numero_sulcos = $request->numero_sulcos;
            $tipodesenhopneu->quantidade_lona_pneu = $request->quantidade_lona_pneu;
            $tipodesenhopneu->dias_calibragem = $request->dias_calibragem;
            $tipodesenhopneu->save();

            return redirect()->route('admin.tipodesenhopneus.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Novo tipo de desenho adicionado com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao criar tipo de desenho de pneu: ' . $e->getMessage());
            return back()->withInput()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Ocorreu um erro ao salvar o tipo de desenho: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $tipodesenhopneu = TipoDesenhoPneu::findOrFail($id);
            return view('admin.tipodesenhopneus.show', compact('tipodesenhopneu'));
        } catch (\Exception $e) {
            Log::error('Erro ao mostrar tipo de desenho de pneu: ' . $e->getMessage());
            return redirect()->route('admin.tipodesenhopneus.index')->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Tipo de desenho não encontrado.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $tipodesenhopneus = TipoDesenhoPneu::findOrFail($id);
            return view('admin.tipodesenhopneus.edit', compact('tipodesenhopneus'));
        } catch (\Exception $e) {
            Log::error('Erro ao editar tipo de desenho de pneu: ' . $e->getMessage());
            return redirect()->route('admin.tipodesenhopneus.index')->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Tipo de desenho não encontrado.'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $validated = $request->validate([
                'descricao_desenho_pneu' => 'required|string|max:500',
                'numero_sulcos' => 'nullable|numeric',
                'quantidade_lona_pneu' => 'nullable|numeric',
                'dias_calibragem' => 'nullable|numeric'
            ]);

            $tipodesenhopneu = TipoDesenhoPneu::findOrFail($id);

            $updated = $tipodesenhopneu->update([
                'descricao_desenho_pneu' => $validated['descricao_desenho_pneu'],
                'data_alteracao' => now(),
                'numero_sulcos' => $request->numero_sulcos,
                'quantidade_lona_pneu' => $request->quantidade_lona_pneu,
                'dias_calibragem' => $request->dias_calibragem
            ]);

            if (!$updated) {
                return redirect()->route('admin.tipodesenhopneus.index')->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível editar o tipo de desenho!'
                ]);
            }

            return redirect()->route('admin.tipodesenhopneus.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Tipo de desenho editado com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar tipo de desenho de pneu: ' . $e->getMessage());
            return back()->withInput()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Ocorreu um erro ao atualizar o tipo de desenho: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Verificar se o desenho está sendo usado antes de tentar excluir
            $emUso = DB::connection('pgsql')->table('manutencao_pneu_entrada_itens')
                ->where('id_desenho_pneu', $id)
                ->exists();

            if ($emUso) {
                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => 'Este tipo de desenho está sendo utilizado em manutenções de pneus e não pode ser excluído.'
                    ]
                ], 422);
            }

            $tipodesenhopneu = TipoDesenhoPneu::findOrFail($id);
            $descricao = $tipodesenhopneu->descricao_desenho_pneu;

            // Tenta excluir o registro
            $tipodesenhopneu->delete();

            // Retorna resposta de sucesso para a requisição Ajax
            return response()->json([
                'notification' => [
                    'title'   => 'Tipo excluído',
                    'type'    => 'success',
                    'message' => 'Tipo de Desenho excluído com sucesso'
                ]
            ]);
        } catch (QueryException $e) {
            // Verificar a mensagem de erro específica para chave estrangeira do PostgreSQL
            if (
                str_contains($e->getMessage(), 'violates foreign key constraint') ||
                str_contains($e->getMessage(), 'SQLSTATE[23503]')
            ) {

                // Extrair informações detalhadas do erro para um diagnóstico melhor
                $errorInfo = $e->errorInfo[2] ?? '';
                $table = '';

                // Tentar extrair o nome da tabela do erro
                if (preg_match('/table "(.*?)"/', $errorInfo, $matches)) {
                    $table = $matches[1];
                }

                $mensagem = 'Este desenho não pode ser excluído pois está sendo utilizado em outros registros';

                if (!empty($table)) {
                    $tabela = str_replace('_', ' ', $table);
                    $mensagem .= " da tabela {$tabela}";
                }

                $mensagem .= '.';

                return response()->json([
                    'notification' => [
                        'title'   => 'Não é possível excluir',
                        'type'    => 'error',
                        'message' => $mensagem
                    ]
                ], 422);
            }

            // Outros erros de consulta
            Log::error('Erro ao excluir tipo de desenho de pneu: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Erro de banco de dados ao excluir o tipo de desenho.'
                ]
            ], 500);
        } catch (\Exception $e) {
            // Log de erro para debugging
            Log::error('Erro ao excluir tipo de desenho de pneu: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            // Retorna resposta de erro para a requisição Ajax
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o tipo de desenho: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    protected function buildExportQuery(Request $request)
    {

        $query = TipoDesenhoPneu::query();

        if ($request->filled('id_desenho_pneu')) {
            $query->where('id_desenho_pneu', $request->id_desenho_pneu);
        }

        if ($request->filled('descricao_desenho_pneu')) {
            $query->where('descricao_desenho_pneu', $request->descricao_desenho_pneu);
        }

        if ($request->filled('numero_sulcos')) {
            $query->where('numero_sulcos', $request->numero_sulcos);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', $request->data_inclusao);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_desenho_pneu',
            'descricao_desenho_pneu',
            'numero_sulcos',
            'data_inclusao'
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
                $pdf->loadView('admin.tipodesenhopneus.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('tipoDesenhosPneus_' . date('Y-m-d_His') . '.pdf');
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
            'id_desenho_pneu' => 'Código',
            'descricao_desenho_pneu' => 'Descrição',
            'numero_sulcos' => 'Número de Sulcos',
            'quantidade_lona_pneu' => 'Quantidade de Lona',
            'dias_calibragem' => 'Dias Calibragem',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
        ];

        return $this->exportToCsv($request, $query, $columns, 'tipoDesenhosPneus', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_desenho_pneu' => 'Código',
            'descricao_desenho_pneu' => 'Descrição',
            'numero_sulcos' => 'Número de Sulcos',
            'quantidade_lona_pneu' => 'Quantidade de Lona',
            'dias_calibragem' => 'Dias Calibragem',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
        ];

        return $this->exportToExcel($request, $query, $columns, 'tipoDesenhosPneus', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'codigo' =>  'id_desenho_pneu',
            'descricao' => 'descricao_desenho_pneu',
            'numero_de_sulcos' => 'numero_sulcos',
            'quantidade_de_lona' => 'quantidade_lona_pneu',
            'dias_calibragem' => 'dias_calibragem',
            'data_inclusao' => 'data_inclusao',
            'data_alteracao' => 'data_alteracao',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'tipoDesenhosPneus',
            'tipoDesenhosPneu',
            'tipoDesenhosPneus',
            $this->getValidExportFilters()
        );
    }
}
