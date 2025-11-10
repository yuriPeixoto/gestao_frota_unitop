<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Modules\Abastecimentos\Models\Tanque;
use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TanqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize = $request->input('pageSize', 10);
        $query = Tanque::with(['tipoCombustivel', 'filial', 'fornecedor']); // Incluindo todos os relacionamentos

        if ($request->filled('id_tanque')) {
            $query->where('id_tanque', $request->id_tanque);
        }

        if ($request->filled('descricao_ats')) {
            $query->where('descricao_ats', $request->descricao_ats);
        }

        if ($request->filled('tanque')) {
            $query->where('tanque', $request->tanque);
        }

        if ($request->filled('capacidade')) {
            $query->where('capacidade', $request->capacidade);
        }

        if ($request->filled('estoque_minimo')) {
            $query->where('estoque_minimo', $request->estoque_minimo);
        }

        if ($request->filled('estoque_maximo')) {
            $query->where('estoque_maximo', $request->estoque_maximo);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('combustivel')) {
            $query->where('combustivel', $request->combustivel);
        }

        // Filtro por data de inclusão (apenas data, sem considerar a hora)
        if ($request->filled('data_inclusao')) {
            $dataInclusao = Carbon::parse($request->data_inclusao)->format('Y-m-d');
            $query->whereRaw("DATE(data_inclusao) = ?", [$dataInclusao]);
        }

        // Filtro por data de alteração (apenas data, sem considerar a hora)
        if ($request->filled('data_alteracao')) {
            $dataAlteracao = Carbon::parse($request->data_alteracao)->format('Y-m-d');
            $query->whereRaw("DATE(data_alteracao) = ?", [$dataAlteracao]);
        }

        $tanque = $query->latest('id_tanque') // Ordenação correta dentro da query filtrada
            ->paginate($pageSize)
            ->appends($request->query());


        $filial = Filial::orderBy('name')
            ->get(['id as value', 'name as label']);

        $combustivel = Tanque::join('tipocombustivel', 'tipocombustivel.id_tipo_combustivel', '=', 'tanque.combustivel')
            ->distinct()
            ->orderBy('tipocombustivel.descricao')
            ->get(['tanque.combustivel as value', 'tipocombustivel.descricao as label']);

        $descricao_ats = Tanque::orderBy('descricao_ats')
            ->whereNotNull('descricao_ats')  // Garante que apenas valores não nulos sejam usados
            ->where('descricao_ats', '!=', '')  // Exclui strings vazias
            ->get(['id_tanque as value', 'descricao_ats as label']);

        $descricao_tanque = Tanque::orderBy('tanque')
            ->whereNotNull('tanque')  // Garante que apenas valores não nulos sejam usados
            ->where('tanque', '!=', '')  // Exclui strings vazias
            ->get(['id_tanque as value', 'tanque as label']);

        return view('admin.tanques.index', compact('tanque', 'filial', 'combustivel', 'descricao_ats', 'descricao_tanque'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $formOptions = [
            'filiais' => Filial::select('name as label', 'id as value')
                ->orderBy('label')
                ->get()
                ->toArray(),
            'tipocombustivel' => TipoCombustivel::select('descricao as label', 'id_tipo_combustivel as value')
                ->orderBy('label')
                ->get()
                ->toArray()
        ];

        // Obter apenas fornecedores frequentes/populares (limite de 10)
        $fornecedoresFrequentes = Fornecedor::select('nome_fornecedor as label', 'id_fornecedor as value')
            ->orderBy('nome_fornecedor')
            ->limit(10)
            ->get()
            ->toArray();

        return view('admin.tanques.create', compact('formOptions', 'fornecedoresFrequentes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'descricao_ats' => 'nullable',
            'tanque' => 'required',
            'capacidade' => 'required',
            'estoque_minimo' => 'required',
            'estoque_maximo' => 'required',
            'id_filial' => 'required',
            'id_fornecedor' => 'nullable',
            'combustivel' => 'required',
        ]);

        $data_inclusao = date('Y-m-d H:i:s');
        $tanques = new Tanque();
        $tanques->descricao_ats = $request->descricao_ats;
        $tanques->tanque = $request->tanque;
        $tanques->capacidade = $request->capacidade;
        $tanques->estoque_minimo = $request->estoque_minimo;
        $tanques->estoque_maximo = $request->estoque_maximo;
        $tanques->id_filial = $request->id_filial;
        $tanques->id_fornecedor = $request->id_fornecedor;
        $tanques->combustivel = $request->combustivel;  // Salvar o combustível selecionado
        $tanques->data_inclusao = $data_inclusao;
        $tanques->is_ativo = true; // Define como ativo por padrão
        $tanques->save();

        return redirect()->route('admin.tanques.index')->withNotification([
            'title'   => 'Sucesso!',
            'type'    => 'success',
            'message' => 'Novo tanque adicionado com sucesso!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Tanque $tanques)
    {
        //
        $tanques = Tanque::findOrFail($tanques);
        return view('admin.tanques.show', compact('tanque'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tanque $tanque)
    {
        //
        $formOptions = [
            'filiais' => Filial::select('name as label', 'id as value')
                ->orderBy('label')
                ->get()
                ->toArray(),
            'tipocombustivel' => TipoCombustivel::select('descricao as label', 'id_tipo_combustivel as value')
                ->orderBy('label')
                ->get()
                ->toArray()
        ];

        // Obter o fornecedor atual se existir
        $fornecedoresFrequentes = [];
        if ($tanque->id_fornecedor) {
            $fornecedor = Fornecedor::find($tanque->id_fornecedor);
            if ($fornecedor) {
                $fornecedoresFrequentes[] = [
                    'value' => $fornecedor->id_fornecedor,
                    'label' => $fornecedor->nome_fornecedor
                ];
            }
        }

        // Adicionar mais alguns fornecedores frequentes se necessário
        if (count($fornecedoresFrequentes) < 10) {
            $outrosFornecedores = Fornecedor::select('nome_fornecedor as label', 'id_fornecedor as value')
                ->when($tanque->id_fornecedor, function ($query) use ($tanque) {
                    return $query->where('id_fornecedor', '!=', $tanque->id_fornecedor);
                })
                ->orderBy('nome_fornecedor')
                ->limit(10 - count($fornecedoresFrequentes))
                ->get()
                ->toArray();

            $fornecedoresFrequentes = array_merge($fornecedoresFrequentes, $outrosFornecedores);
        }

        return view('admin.tanques.edit', compact('tanque', 'formOptions', 'fornecedoresFrequentes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tanque $tanque)
    {
        //

        $validated = $request->validate([
            'descricao_ats' => 'nullable|string|max:500',
            'tanque' => 'required|string|max:500',
            'capacidade' => 'required|integer',
            'estoque_minimo' => 'required|integer|min:0',
            'estoque_maximo' => 'required|integer|lte:capacidade',
            'id_filial' => 'required|integer|exists:filial,id',  // Validação atualizada
            'id_fornecedor' => 'nullable|integer|exists:fornecedor,id_fornecedor',  // Fornecedor é opcional, correção do campo
            'combustivel' => 'required|exists:tipocombustivel,id_tipo_combustivel',  // Combustível é obrigatório
        ]);

        try {
            $updated = $tanque->update([
                'descricao_ats' => $validated['descricao_ats'],
                'tanque' => $validated['tanque'],
                'capacidade' => $validated['capacidade'],
                'estoque_minimo' => $validated['estoque_minimo'],
                'estoque_maximo' => $validated['estoque_maximo'],
                'id_filial' => $validated['id_filial'],  // Usa o valor validado
                'id_fornecedor' => $validated['id_fornecedor'], // Usa o valor validado
                'combustivel' => $validated['combustivel'],  // Usa o valor validado
                'data_alteracao' => now()
            ]);
            if (!$updated) {
                return redirect()->route('admin.tanques.index')->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Nao foi possivel editar o tanque!'
                ]);
            }

            return redirect()->route('admin.tanques.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Tanque editado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $tanque = Tanque::findOrFail($id);
            $tanque->delete(); // Agora usa soft delete e toggle automaticamente

            $status = $tanque->is_ativo ? 'ativado' : 'desativado';

            return response()->json([
                'notification' => [
                    'title'   => 'Tanque desativado',
                    'type'    => 'success',
                    'message' => "Tanque {$status} com sucesso!"
                ],
                'is_ativo' => $tanque->is_ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }

    /**
     * Toggle the active status of the specified resource.
     */
    public function toggleActive(int $id)
    {
        try {
            $tanque = Tanque::findOrFail($id);
            $tanque->toggleActive();

            $status = $tanque->is_ativo ? 'ativado' : 'desativado';

            return response()->json([
                'notification' => [
                    'title'   => 'Status alterado',
                    'type'    => 'success',
                    'message' => "Tanque {$status} com sucesso!"
                ],
                'is_ativo' => $tanque->is_ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ], 500);
        }
    }
}
