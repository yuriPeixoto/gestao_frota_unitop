<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\Pessoal;
use App\Models\Produto;
use App\Models\Veiculo;
use App\Modules\Imobilizados\Models\ProdutosImobilizados;
use App\Modules\Imobilizados\Models\TipoImobilizado;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\ExportableTrait;

class ProdutosImobilizadosController extends Controller
{

    use ExportableTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = ProdutosImobilizados::query();

        if ($request->filled('id_produtos_imobilizados')) {
            $query->where('id_produtos_imobilizados', $request->id_produtos_imobilizados);
        }

        if ($request->filled('cod_patrimonio')) {
            $query->where('cod_patrimonio', $request->cod_patrimonio);
        }

        if ($request->filled('id_tipo_imobilizados')) {
            $query->where('id_tipo_imobilizados', $request->id_tipo_imobilizados);
        }

        if ($request->filled('id_produto')) {
            $query->where('id_produto', $request->id_produto);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }


        $produtosImobilizados = $query->latest('id_produtos_imobilizados')
            ->paginate(15)
            ->appends($request->query());

        $id_produtos_imobilizados = ProdutosImobilizados::select('id_produtos_imobilizados as value', 'id_produtos_imobilizados as label')
            ->orderBy('id_produtos_imobilizados', 'desc')
            ->get()
            ->toArray();

        $cod_patrimonio = ProdutosImobilizados::select('cod_patrimonio as value', 'cod_patrimonio as label')
            ->orderBy('cod_patrimonio', 'desc')
            ->get()
            ->toArray();


        $tipoImobilizados = TipoImobilizado::select('id_tipo_imobilizados as value', 'descricao_tipo_imobilizados as label')
            ->orderBy('descricao_tipo_imobilizados', 'desc')
            ->get()
            ->toArray();


        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_imobilizado', '=', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $status = ProdutosImobilizados::select('status as value', 'status as label')
            ->distinct()
            ->orderBy('status', 'desc')
            ->get()
            ->toArray();

        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name', 'desc')
            ->get()
            ->toArray();


        return view(
            'admin.produtosimobilizados.index',
            compact(
                'produtosImobilizados',
                'id_produtos_imobilizados',
                'cod_patrimonio',
                'tipoImobilizados',
                'produto',
                'status',
                'veiculosFrequentes',
                'filial'
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoImobilizados = TipoImobilizado::select('id_tipo_imobilizados as value', 'descricao_tipo_imobilizados as label')
            ->orderBy('descricao_tipo_imobilizados', 'desc')
            ->get()
            ->toArray();

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(20)
            ->get()
            ->toArray();



        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $pessoal = Pessoal::select('id_pessoal as value', 'nome as label')
            ->limit(20)
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')->get();


        return view(
            'admin.produtosimobilizados.create',
            compact('tipoImobilizados', 'produto', 'veiculosFrequentes', 'departamento', 'filial', 'users', 'pessoal')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'cod_patrimonio'             => 'required',
                'id_tipo_imobilizados'       => 'nullable',
                'id_produto'                 => 'required',
                'numero_nf'                  => 'nullable',
                'id_responsavel_imobilizado' => 'nullable',
                'id_lider_setor'             => 'nullable',
                'valor'                      => 'nullable',
                'id_departamento'            => 'nullable',
                'id_veiculo'                 => 'nullable',
                'id_filial'                  => 'required',
                'id_usuario'                 => 'required',
            ]);

            if (
                !empty($request->id_veiculo) ||
                !empty($request->id_responsavel_imobilizado) ||
                !empty($request->id_lider_setor) ||
                !empty($request->id_departamento)
            ) {
                $status = 'APLICADO';
            } else {
                $status = 'EM ESTOQUE';
            }

            DB::beginTransaction();

            // MOVER O PRODUTO PARA IMOBILIZADO
            $id_produto = $validated['id_produto'];
            $produto = Produto::find($id_produto);
            $produto->update([
                'id_imobilizado' => true
            ]);

            ProdutosImobilizados::create([
                'data_inclusao'               => now(),
                'cod_patrimonio'              => $validated['cod_patrimonio'],
                'id_tipo_imobilizados'        => $validated['id_tipo_imobilizados'] ?? null,
                'id_produto'                  => $validated['id_produto'] ?? null,
                'numero_nf'                   => $validated['numero_nf'] ?? null,
                'id_responsavel_imobilizado'  => $validated['id_responsavel_imobilizado'] ?? null,
                'id_lider_setor'              => $validated['id_lider_setor'] ?? null,
                'valor'                       => $this->sanitizeMoney($validated['id_lider_setor']) ?? null,
                'id_departamento'             => $validated['id_departamento'] ?? null,
                'id_veiculo'                  => $validated['id_veiculo'] ?? null,
                'id_filial'                   => $validated['id_filial'] ?? null,
                'id_usuario'                  => $validated['id_usuario'] ?? null,
                'status'                      => $status ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.produtosimobilizados.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Produto Imobilizado cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação de Produto Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.produtosimobilizados.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar a Produtos Imobilizados."
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
        $produtoImobilizado = ProdutosImobilizados::find($id);

        $tipoImobilizados = TipoImobilizado::select('id_tipo_imobilizados as value', 'descricao_tipo_imobilizados as label')
            ->orderBy('descricao_tipo_imobilizados', 'desc')
            ->get()
            ->toArray();

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $veiculosFrequentes = Veiculo::where('situacao_veiculo', true)
            ->orderBy('placa')
            ->limit(20)
            ->get(['id_veiculo as value', 'placa as label']);

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(20)
            ->get()
            ->toArray();

        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $pessoal = Pessoal::select('id_pessoal as value', 'nome as label')
            ->limit(20)
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')->get();


        return view(
            'admin.produtosimobilizados.edit',
            compact('produtoImobilizado', 'tipoImobilizados', 'produto', 'veiculosFrequentes', 'departamento', 'filial', 'users', 'pessoal')
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {

            $validated = $request->validate([
                'cod_patrimonio'             => 'required',
                'id_tipo_imobilizados'       => 'nullable',
                'id_produto'                 => 'required',
                'numero_nf'                  => 'nullable',
                'id_responsavel_imobilizado' => 'nullable',
                'id_lider_setor'             => 'nullable',
                'valor'                      => 'nullable',
                'id_departamento'            => 'nullable',
                'id_veiculo'                 => 'nullable',
                'id_filial'                  => 'required',
                'id_usuario'                 => 'required',
            ]);

            if (
                !empty($request->id_veiculo) ||
                !empty($request->id_responsavel_imobilizado) ||
                !empty($request->id_lider_setor) ||
                !empty($request->id_departamento)
            ) {
                $status = 'APLICADO';
            } else {
                $status = 'EM ESTOQUE';
            }

            DB::beginTransaction();

            $produtosImobilizados = ProdutosImobilizados::find($id);
            $produtosImobilizados->update([
                'data_alteração'               => now(),
                'cod_patrimonio'              => $validated['cod_patrimonio'],
                'id_tipo_imobilizados'        => $validated['id_tipo_imobilizados'] ?? null,
                'id_produto'                  => $validated['id_produto'] ?? null,
                'numero_nf'                   => $validated['numero_nf'] ?? null,
                'id_responsavel_imobilizado'  => $validated['id_responsavel_imobilizado'] ?? null,
                'id_lider_setor'              => $validated['id_lider_setor'] ?? null,
                'valor'                       => $this->sanitizeMoney($validated['id_lider_setor']) ?? null,
                'id_departamento'             => $validated['id_departamento'] ?? null,
                'id_veiculo'                  => $validated['id_veiculo'] ?? null,
                'id_filial'                   => $validated['id_filial'] ?? null,
                'id_usuario'                  => $validated['id_usuario'] ?? null,
                'status'                      => $status ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.produtosimobilizados.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Produto Imobilizado editado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na edição de Produto Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.produtosimobilizados.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar a Produto Imobilizado."
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

            $produtosImobilizados = ProdutosImobilizados::find($id);
            $produtosImobilizados->delete();
            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Produto Imobilizado excluído!',
                    'type'    => 'success',
                    'message' => 'Produto Imobilizado excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir Produto Imobilizado: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir Produto Imobilizado: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));


        // Cache para melhorar performance
        $produtosImobilizados = ProdutosImobilizados::with('produto')
            ->get()
            ->filter(function ($item) use ($term) {
                return str_contains(strtolower($item->produto->descricao_produto ?? ''), strtolower($term));
            })
            ->map(function ($item) {
                return [
                    'value' => $item->id_produtos_imobilizados,
                    'label' => $item->id_produtos_imobilizados . ' - ' . ($item->produto->descricao_produto ?? ''),
                ];
            })
            ->take(30)
            ->values(); // reindexa o array

        return response()->json($produtosImobilizados);
    }

    /**
     * Retorna um fornecedor específico pelo ID
     * Usado para carregar o item selecionado inicialmente e para interatividade entre campos
     */
    public function getById($id)
    {
        // Cache para melhorar performance
        $produtosImobilizados = ProdutosImobilizados::findOrFail($id);

        return response()->json([
            'value' => $produtosImobilizados->id_produtos_imobilizados,
            'label' => $produtosImobilizados->nome_fornecedor,
        ]);
    }


    protected function buildExportQuery(Request $request)
    {

        $query = ProdutosImobilizados::query()
            ->with('veiculo', 'produto', 'tipoImobilizado', 'filial', 'user', 'departamento');

        if ($request->filled('id_produtos_imobilizados')) {
            $query->where('id_produtos_imobilizados', $request->id_produtos_imobilizados);
        }

        if ($request->filled('cod_patrimonio')) {
            $query->where('cod_patrimonio', $request->cod_patrimonio);
        }

        if ($request->filled('id_tipo_imobilizados')) {
            $query->where('id_tipo_imobilizados', $request->id_tipo_imobilizados);
        }

        if ($request->filled('id_produto')) {
            $query->where('id_produto', $request->id_produto);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_produtos_imobilizados',
            'cod_patrimonio',
            'id_tipo_imobilizados',
            'id_produto',
            'status',
            'id_veiculo',
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
                $pdf->loadView('admin.produtosimobilizados.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('produtosimobilizados_' . date('Y-m-d_His') . '.pdf');
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
            'id_produtos_imobilizados' => 'Cód. Imobilizados',
            'cod_patrimonio' => 'Cód. Patrimonio',
            'veiculo.placa' => 'Placa',
            'produto.descricao_produto' => 'Produto',
            'tipoImobilizado.descricao_tipo_imobilizados' => 'Cód. Tipo Imobilizado',
            'departamento.descricao_departamento' => 'Departamento',
            'filial.name' => 'Filial',
            'status' => 'Status',
            'user.name' => 'Usuário',
            'data_inclusao' => 'Data Inclusão'
        ];

        return $this->exportToCsv($request, $query, $columns, 'produtosimobilizados', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_produtos_imobilizados' => 'Cód. Imobilizados',
            'cod_patrimonio' => 'Cód. Patrimonio',
            'veiculo.placa' => 'Placa',
            'produto.descricao_produto' => 'Produto',
            'tipoImobilizado.descricao_tipo_imobilizados' => 'Cód. Tipo Imobilizado',
            'departamento.descricao_departamento' => 'Departamento',
            'filial.name' => 'Filial',
            'status' => 'Status',
            'user.name' => 'Usuário',
            'data_inclusao' => 'Data Inclusão'
        ];

        return $this->exportToExcel($request, $query, $columns, 'produtosimobilizados', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'cod_imobilizados' =>  'id_produtos_imobilizados',
            'cod_patrimonio' => 'cod_patrimonio',
            'placa' => 'veiculo.placa',
            'produto' => 'produto.descricao_produto',
            'cod_tipo_imobilizado' => 'tipoImobilizado.descricao_tipo_imobilizados',
            'departamento' => 'id_departamento',
            'filial' => 'filial.name',
            'status' => 'status',
            'usuario' => 'user.name',
            'data_inclusao' => 'data_inclusao',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'produtosimobilizadoss',
            'produtosimobilizados',
            'produtosimobilizadoss',
            $this->getValidExportFilters()
        );
    }

    private function sanitizeMoney($value)
    {
        if (is_null($value)) {
            return 0.0;
        }

        // Remove qualquer coisa que não seja número ou vírgula
        $value = preg_replace('/[^\d,]/', '', $value);

        // Substitui vírgula por ponto para conversão float
        $value = str_replace(',', '', $value);

        // Converte para float
        return (float) $value;
    }
}
