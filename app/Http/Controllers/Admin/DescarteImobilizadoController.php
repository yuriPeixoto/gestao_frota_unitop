<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\DescarteImobilizado;
use App\Models\Filial;
use App\Models\ProdutosImobilizados;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ExportableTrait;
use Illuminate\Support\Facades\Log;

class DescarteImobilizadoController extends Controller
{

    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = DescarteImobilizado::query();

        if ($request->filled('id_descarte_imobilizados')) {
            $query->where('id_descarte_imobilizados', $request->id_descarte_imobilizados);
        }

        if ($request->filled('id_produtos_imobilizados')) {
            $query->where('id_produtos_imobilizados', $request->id_produtos_imobilizados);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }


        $descarteImobilizados = $query->latest('id_descarte_imobilizados')
            ->paginate(40)
            ->appends($request->query());

        $id_descarte_imobilizados = DescarteImobilizado::select('id_descarte_imobilizados as value', 'id_descarte_imobilizados as label')
            ->orderBy('id_descarte_imobilizados', 'desc')
            ->get()
            ->toArray();

        $produtosImobilizados = ProdutosImobilizados::with('produto')
            ->where('status', '=', 'DESCARTE')
            ->orderBy('id_produtos_imobilizados')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_produtos_imobilizados,
                    'label' => $item->id_produtos_imobilizados . ' - ' . ($item->produto->descricao_produto ?? ''),
                ];
            })
            ->toArray();

        $filiais = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();


        return view('admin.descarteimobilizado.index', compact('descarteImobilizados', 'id_descarte_imobilizados', 'produtosImobilizados', 'filiais'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $produtosImobilizados = ProdutosImobilizados::with('produto')
            ->where('status', '!=', 'DESCARTE')
            ->orderBy('id_produtos_imobilizados')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_produtos_imobilizados,
                    'label' => $item->id_produtos_imobilizados . ' - ' . ($item->produto->descricao_produto ?? ''),
                ];
            })
            ->toArray();

        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')->get();


        return view('admin.descarteimobilizado.create', compact('produtosImobilizados', 'users', 'filial'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'id_produtos_imobilizados'   => 'required',
                'motivo_descarte'            => 'nullable',
                'id_usuario'                 => 'required',
                'id_filial'                  => 'required',
            ]);

            /* COLOCAR O STATUS DE DESCARTE NO PRODUTO IMOBILIZADO */
            $id_produtos_imobilizados = $validated['id_produtos_imobilizados'];
            $status = 'DESCARTE';
            $is_descarte = true;

            DB::beginTransaction();

            $produtosImobilizados = ProdutosImobilizados::find($id_produtos_imobilizados);
            $produtosImobilizados->update([
                'data_alteração'               => now(),
                'is_descarte'                  => $is_descarte,
                'status'                       => $status
            ]);

            DescarteImobilizado::create([
                'data_inclusao'               => now(),
                'id_produtos_imobilizados'    => $validated['id_produtos_imobilizados'],
                'motivo_descarte'             => $validated['motivo_descarte'],
                'id_usuario'                  => $validated['id_usuario'],
                'id_filial'                   => $validated['id_filial'],
            ]);

            DB::commit();

            return redirect()
                ->route('admin.descarteimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Descarte Imobilizado cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação do Descarte Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.descarteimobilizado.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Descarte Imobilizados."
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $descarteImobilizado = DescarteImobilizado::find($id);

        $produtosImobilizados = ProdutosImobilizados::where('id_produtos_imobilizados', $descarteImobilizado->id_produtos_imobilizados)
            ->where(function ($query) use ($descarteImobilizado) {
                $query->where('id_produtos_imobilizados', $descarteImobilizado->id_produtos_imobilizados)
                    ->orWhere('status', '!=', 'DESCARTE');
            })
            ->with('produto')
            ->orderBy('id_produtos_imobilizados')
            ->limit(30)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_produtos_imobilizados,
                    'label' => $item->id_produtos_imobilizados . ' - ' . ($item->produto->descricao_produto ?? ''),
                ];
            })
            ->toArray();

        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')->get();


        return view('admin.descarteimobilizado.edit', compact('descarteImobilizado', 'produtosImobilizados', 'users', 'filial'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $validated = $request->validate([
                'id_produtos_imobilizados'   => 'required',
                'motivo_descarte'            => 'nullable',
                'id_usuario'                 => 'required',
                'id_filial'                  => 'required',
            ]);


            DB::beginTransaction();

            /* VERIFICAR SE O PRODUTO IMOBILIZADO FOI ALTERADO E TIRAR O STATUS DE DESCARTE 
            E COLOCAR COMO ESTAVA ANTES */
            $id_produtos_imobilizados_antigo = DescarteImobilizado::find($id)->id_produtos_imobilizados;
            if ($id_produtos_imobilizados_antigo != $validated['id_produtos_imobilizados']) {
                $produtosImobilizados = ProdutosImobilizados::find($id_produtos_imobilizados_antigo);

                if (
                    !empty($produtosImobilizados->id_veiculo) ||
                    !empty($produtosImobilizados->id_responsavel_imobilizado) ||
                    !empty($produtosImobilizados->id_lider_setor) ||
                    !empty($produtosImobilizados->id_departamento)
                ) {
                    $status = 'APLICADO';
                } else {
                    $status = 'EM ESTOQUE';
                }

                $produtosImobilizados->update([
                    'data_alteracao'               => now(),
                    'status'                       => $status,
                    'is_descarte'                  => false
                ]);
            }

            /* VERIFICAR SE O PRODUTO IMOBILIZADO FOI ALTERADO E COLOCAR O STATUS DE DESCARTE 
            NO NOVO ITEM */
            if (!empty($validated['id_produtos_imobilizados'])) {
                /* COLOCAR O STATUS DE DESCARTE NO PRODUTO IMOBILIZADO */
                $id_produtos_imobilizados = $validated['id_produtos_imobilizados'];
                $status = 'DESCARTE';
                $is_descarte = true;

                $produtosImobilizados = ProdutosImobilizados::find($id_produtos_imobilizados);
                $produtosImobilizados->update([
                    'data_alteracao'               => now(),
                    'status'                       => $status,
                    'is_descarte'                  => $is_descarte
                ]);
            }


            $descarteImobilizado = DescarteImobilizado::find($id);
            $descarteImobilizado->update([
                'data_alteracao'              => now(),
                'id_produtos_imobilizados'    => $validated['id_produtos_imobilizados'],
                'motivo_descarte'             => $validated['motivo_descarte'],
                'id_usuario'                  => $validated['id_usuario'],
                'id_filial'                   => $validated['id_filial'],
            ]);


            DB::commit();

            return redirect()
                ->route('admin.descarteimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Descarte Imobilizado editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na edição oe Descarte Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.descarteimobilizado.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar o Descarte Imobilizados."
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            DB::beginTransaction();

            $id_produtos_imobilizados = DescarteImobilizado::find($id)->id_produtos_imobilizados;
            $produtosImobilizados = ProdutosImobilizados::find($id_produtos_imobilizados);

            if (
                !empty($produtosImobilizados->id_veiculo) ||
                !empty($produtosImobilizados->id_responsavel_imobilizado) ||
                !empty($produtosImobilizados->id_lider_setor) ||
                !empty($produtosImobilizados->id_departamento)
            ) {
                $status = 'APLICADO';
            } else {
                $status = 'EM ESTOQUE';
            }

            $produtosImobilizados->update([
                'data_alteracao'               => now(),
                'status'                       => $status,
                'is_descarte'                  => false
            ]);


            $descarteImobilizado = DescarteImobilizado::find($id);
            $descarteImobilizado->delete();
            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Descarte Imobilizado excluído!',
                    'type'    => 'success',
                    'message' => 'Descarte Imobilizado excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir Descarte Imobilizado: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir Descarte Imobilizado: ' . $e->getMessage()
                ]
            ], 500);
        }
    }


    protected function buildExportQuery(Request $request)
    {

        $query = DescarteImobilizado::query()
            ->with(['produtoImobilizado', 'produtoImobilizado.produto', 'user', 'filial']);

        if ($request->filled('id_descarte_imobilizados')) {
            $query->where('id_descarte_imobilizados', $request->id_descarte_imobilizados);
        }

        if ($request->filled('id_produtos_imobilizados')) {
            $query->where('id_produtos_imobilizados', $request->id_produtos_imobilizados);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }


        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_descarte_imobilizados',
            'id_produtos_imobilizados',
            'id_filial',
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
                $pdf->loadView('admin.descarteimobilizado.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('descarteimobilizado_' . date('Y-m-d_His') . '.pdf');
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
            'id_descarte_imobilizados' => 'Cód. Descarte Imobilizado',
            'id_produtos_imobilizados' => 'Cód. Produto Imobilizado',
            'produtoImobilizado.produto.descricao_produto' => 'Produto',
            'user.name' => 'Usuário',
            'filial.name' => 'Filial',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
        ];

        return $this->exportToCsv($request, $query, $columns, 'descarteimobilizado', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_descarte_imobilizados' => 'Cód. Descarte Imobilizado',
            'id_produtos_imobilizados' => 'Cód. Produto Imobilizado',
            'produtoImobilizado.produto.descricao_produto' => 'Produto',
            'user.name' => 'Usuário',
            'filial.name' => 'Filial',
            'data_inclusao' => 'Data Inclusão',
            'data_alteracao' => 'Data Alteração',
        ];

        return $this->exportToExcel($request, $query, $columns, 'descarteimobilizado', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'cod_descarte_imobilizado' =>  'id_descarte_imobilizados',
            'cod_produto_imobilizado' => 'id_produtos_imobilizados',
            'produto' => 'produtoImobilizado.produto.descricao_produto',
            'usuario' => 'user.name',
            'filial' => 'filial.name',
            'data_inclusao' => 'data_inclusao',
            'data_alteracao' => 'data_alteracao',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'descarteimobilizados',
            'descarteimobilizado',
            'descarteimobilizados',
            $this->getValidExportFilters()
        );
    }
}
