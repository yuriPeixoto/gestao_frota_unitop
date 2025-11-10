<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Models\Filial;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Modules\Abastecimentos\Models\ValorCombustivelTerceiro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ValorCombustivelTerceiroController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = ValorCombustivelTerceiro::with(['bomba', 'tipoCombustivel', 'usuario']);

        // Filtros de busca
        if ($request->has('data_inicio') && $request->data_inicio) {
            $query->where('data_inicio', '>=', $request->data_inicio);
        }

        if ($request->has('data_fim') && $request->data_fim) {
            $query->where('data_fim', '<=', $request->data_fim);
        }

        if ($request->has('boma_combustivel') && $request->boma_combustivel) {
            $query->where('boma_combustivel', $request->boma_combustivel);
        }

        if ($request->has('id_tipo_combustivel') && $request->id_tipo_combustivel) {
            $query->where('id_tipo_combustivel', $request->id_tipo_combustivel);
        }

        // Ordenação e paginação
        $valorCombustiveis = $query->orderBy('data_inclusao', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Carregar dados para os selects
        // MODIFICAÇÃO: Usar o método bombasInternasParaSelect em vez de getFormatadoParaSelect
        $bombas = Bomba::bombasInternasParaSelect();
        $tiposCombustivel = TipoCombustivel::orderBy('descricao')->get(['id_tipo_combustivel as value', 'descricao as label']);

        // Se estamos tratando de uma requisição HTMX, retornamos apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.valorcombustiveis._table', compact('valorCombustiveis'));
        }

        return view('admin.valorcombustiveis.index', compact('valorCombustiveis', 'bombas', 'tiposCombustivel'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Preparar dados para o formulário
        $formOptions = $this->getFormOptions();

        return view('admin.valorcombustiveis.create', compact('formOptions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'id_filial' => 'required|exists:filial,id',
            'boma_combustivel' => 'required|exists:bomba,id_bomba',
            'valor_acrescimo' => 'required|numeric|min:0',
            'valor_terceiro' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        // Adicionar campos extras
        $validated['data_inclusao'] = now();
        $validated['id_usuario'] = Auth::id();

        // Buscar dados da bomba para associar o tipo de combustível
        $bomba = Bomba::with('tanque')->find($request->boma_combustivel);
        if ($bomba && $bomba->tanque) {
            $validated['id_tipo_combustivel'] = $bomba->tanque->combustivel;
            $validated['valor_diesel'] = $request->valor_diesel;
        }

        // Criar o registro
        $valorCombustivel = ValorCombustivelTerceiro::create($validated);

        return redirect()
            ->route('admin.valorcombustiveis.index')
            ->with('success', 'Valor de combustível cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $valorCombustiveis = ValorCombustivelTerceiro::with(['bomba', 'tipoCombustivel', 'usuario'])->findOrFail($id);
        $formOptions = $this->getFormOptions();

        return view('admin.valorcombustiveis.show', compact('valorCombustiveis', 'formOptions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $valorCombustiveis = ValorCombustivelTerceiro::findOrFail($id);
        $formOptions = $this->getFormOptions();

        // Define modo de edição para a sessão
        session(['modo' => 'editar']);

        return view('admin.valorcombustiveis.edit', compact('valorCombustiveis', 'formOptions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validação dos dados
        $validated = $request->validate([
            'id_filial' => 'required|exists:filial,id',
            'boma_combustivel' => 'required|exists:bomba,id_bomba',
            'valor_acrescimo' => 'required|numeric|min:0',
            'valor_terceiro' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
        ]);

        // Adicionar campos extras
        $validated['data_alteracao'] = now();

        // Buscar o registro
        $valorCombustivel = ValorCombustivelTerceiro::findOrFail($id);

        // Atualizar o registro
        $valorCombustivel->update($validated);

        return redirect()
            ->route('admin.valorcombustiveis.index')
            ->with('success', 'Valor de combustível atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $valorCombustivel = ValorCombustivelTerceiro::findOrFail($id);
        $valorCombustivel->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.valorcombustiveis.index')
            ->with('success', 'Valor de combustível excluído com sucesso!');
    }

    /**
     * Obter valores da bomba selecionada
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getValorBomba(Request $request)
    {
        $idBomba = $request->idBomba;

        if (!$idBomba) {
            return response()->json(['error' => 'ID da bomba não informado']);
        }

        $bomba = Bomba::with('tanque.tipoCombustivel')->find($idBomba);

        if (!$bomba) {
            return response()->json(['error' => 'Bomba não encontrada']);
        }

        // Obter o último valor cadastrado para esta bomba
        $ultimoValor = ValorCombustivelTerceiro::where('boma_combustivel', $idBomba)
            ->orderBy('data_inclusao', 'desc')
            ->first();

        $valorInterno = $ultimoValor ? $ultimoValor->valor_diesel : 0;

        $tipoCombustivel = $bomba->tanque && $bomba->tanque->tipoCombustivel
            ? $bomba->tanque->tipoCombustivel->descricao
            : 'Não informado';

        return response()->json([
            'tipo_combustivel' => $tipoCombustivel,
            'vlrunitario_interno' => $valorInterno
        ]);
    }

    /**
     * Preparar opções para os selects do formulário
     *
     * @return array
     */
    protected function getFormOptions()
    {
        return [
            // MODIFICAÇÃO: Usar o método bombasInternasParaSelect em vez de getFormatadoParaSelect
            'bombas' => Bomba::bombasInternasParaSelect(),
            'filiais' => Filial::orderBy('name')
                ->get(['id as value', 'name as label'])
        ];
    }
}
