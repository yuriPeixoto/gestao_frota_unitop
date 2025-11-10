<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JornadaFeriado;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeriadoPremioSuperacaoController extends Controller
{
    public function index(Request $request)
    {
        $query = JornadaFeriado::query();

        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }
        if ($request->filled('descricao')) {
            $query->where('descricao', $request->input('descricao'));
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->input('tipo'));
        }

        $tipo = JornadaFeriado::select('tipo as value', 'tipo as label')
            ->distinct()
            ->orderBy('tipo')
            ->get();

        $descricao = JornadaFeriado::select('descricao as value', 'descricao as label')
            ->distinct()
            ->orderBy('descricao')
            ->limit(20)
            ->get();

        $listagem = $query->latest('id')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.jornadaferiado.index', compact('tipo', 'descricao', 'listagem'));
    }

    public function create()
    {
        $feriado = null;

        return view('admin.jornadaferiado.create', compact('feriado'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'tipo'  => 'required',
            'descricao' => 'required'
        ], [
            'tipo.required' => 'O Tipo é obrigatório.',
            'descricao.required' => 'A Descrição é obrigatória'
        ]);

        try {

            $feriado = new JornadaFeriado();

            $feriado->tipo = $validate['tipo'];
            $feriado->descricao = $validate['descricao'];
            $feriado->data_inclusao = now();

            $feriado->save();

            DB::commit();

            return redirect()->route('admin.jornadaferiado.index')->with('success', 'Feriado Cadastrado com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao cadastrar Feriado: ' . $e->getMessage());
            return redirect()->route('admin.jornadaferiado.index')->with('error', 'Erro ao cadastrar o feriado: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $feriado = JornadaFeriado::findOrFail($id);

        return view('admin.jornadaferiado.edit', compact('feriado'));
    }

    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'tipo'  => 'required',
            'descricao' => 'required'
        ], [
            'tipo.required' => 'O Tipo é obrigatório.',
            'descricao.required' => 'A Descrição é obrigatória'
        ]);

        try {

            $feriado = JornadaFeriado::findOrFail($id);

            $feriado->tipo = $validate['tipo'];
            $feriado->descricao = $validate['descricao'];
            $feriado->data_inclusao = now();

            $feriado->update();

            DB::commit();

            return redirect()->route('admin.jornadaferiado.index')->with('success', 'Feriado Atualizado com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao atualizar Feriado: ' . $e->getMessage());
            return redirect()->route('admin.jornadaferiado.index')->with('error', 'Erro ao atualizar o feriado: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {

            DB::beginTransaction();

            $feriado = JornadaFeriado::findOrFail($id);

            $feriado->delete();

            DB::commit();
            return redirect()->route('admin.jornadaferiado.index')->with('success', 'Feriado Excluído com Sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Erro ao excluir Feriado : '  . $e->getMessage());
            return redirect()->route('admin.jornadaferiado.index')->with('error', 'Erro ao excluir feriado: ' . $e->getMessage());
        }
    }
}
