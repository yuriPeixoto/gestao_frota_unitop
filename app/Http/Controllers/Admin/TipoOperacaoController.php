<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TipoOperacao;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TipoOperacaoController extends Controller
{
    public function index(Request $request)
    {
        $query = TipoOperacao::query();

        if ($request->filled('id_tipo_operacao')) {
            $query->where('id_tipo_operacao', $request->input('id_tipo_operacao'));
        }
        if ($request->filled('descricao_tipo_operacao')) {
            $query->where('descricao_tipo_operacao', $request->input('descricao_tipo_operacao'));
        }

        $tipo = TipoOperacao::select('descricao_tipo_operacao as value', 'descricao_tipo_operacao as label')
            ->orderBy('descricao_tipo_operacao')
            ->limit(30)
            ->get();

        $listagem = $query->latest('id_tipo_operacao')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.tipooperacao.index', compact('tipo', 'listagem'));
    }

    public function create()
    {
        $operacao = null;

        return view('admin.tipooperacao.create', compact('operacao'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'descricao_tipo_operacao'  => 'required',
            'km_operacao'              => 'required',

        ], [
            'descricao_tipo_operacao.required' => 'O campo Descrição é obrigatorio',
            'km_operacao.required' => 'O campo KM é obrigatorio',
        ]);

        try {
            DB::beginTransaction();

            $tipo = new TipoOperacao();

            $tipo->data_inclusao = now();
            $tipo->descricao_tipo_operacao  = $validate['descricao_tipo_operacao'];
            $tipo->km_operacao = $validate['km_operacao'];

            $tipo->save();

            DB::commit();
            return redirect()->route('admin.tipooperacao.index')->with('success', 'Tipo Operação Cadastrado com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao cadastrar Tipo Operação: ' . $e->getMessage());
            return redirect()->route('admin.tipooperacao.index')->with('error', 'Erro ao cadastrar o tipo operação: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $operacao = TipoOperacao::findOrFail($id);
        return view('admin.tipooperacao.edit', compact('operacao'));
    }

    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'descricao_tipo_operacao'  => 'required',
            'km_operacao'              => 'required',

        ], [
            'descricao_tipo_operacao.required' => 'O campo Descrição é obrigatorio',
            'km_operacao.required' => 'O campo KM é obrigatorio',
        ]);

        try {
            DB::beginTransaction();

            $tipo = TipoOperacao::findOrFail($id);

            $tipo->data_alteracao = now();
            $tipo->descricao_tipo_operacao  = $validate['descricao_tipo_operacao'];
            $tipo->km_operacao = $validate['km_operacao'];

            $tipo->update();

            DB::commit();
            return redirect()->route('admin.tipooperacao.index')->with('success', 'Tipo Operação Atualizada com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao cadastrar Tipo Operação: ' . $e->getMessage());
            return redirect()->route('admin.tipooperacao.index')->with('error', 'Erro ao cadastrar o tipo operação: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $tipo = TipoOperacao::findOrFail($id);

            $tipo->delete();

            DB::commit();

            return redirect()->route('admin.tipooperacao.index')->with('success', 'Tipo Operação Excluída com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao excluír Tipo Operação: ' . $e->getMessage());
            return redirect()->route('admin.tipooperacao.index')->with('error', 'Erro ao excluír o tipo operação: ' . $e->getMessage());
        }
    }
}
