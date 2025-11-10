<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deflatores;
use App\Models\Filial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeflatoresCarvalimaController extends Controller
{
    public function index(Request $request)
    {
        $query = Deflatores::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_deflatores')) {
            $query->where('id_deflatores', $request->input('id_deflatores'));
        }
        if ($request->filled('descricao_evento')) {
            $query->where('descricao_evento', $request->input('descricao_evento'));
        }
        if ($request->filled('valor')) {
            $query->where('valor', $request->input('valor'));
        }
        if ($request->filled('unidade_id')) {
            $query->where('unidade_id', $request->input('unidade_id'));
        }

        $filial = Filial::select('name as value', 'name as label')
            ->orderBy('name')
            ->get();

        $descricao = Deflatores::select('descricao_evento as value', 'descricao_evento as label')
            ->orderBy('descricao_evento')
            ->get();

        $listagem = $query->latest('id_deflatores')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.deflatorescarvalima.index', compact('filial', 'listagem', 'descricao'));
    }

    public function create()
    {
        $evento = null;

        $deflator = Deflatores::select('descricao_evento as value', 'descricao_evento as label')
            ->orderBy('descricao_evento')
            ->get();

        $filial = Filial::select('name as value', 'name as label')
            ->orderBy('name')
            ->get();
        return view('admin.deflatorescarvalima.create', compact('evento', 'deflator', 'filial'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'descricao_evento'   => 'required',
            'valor'   => 'required',
            'user_id'   => 'required',
            'unidade_id'   => 'required',
        ], [
            'descricao_evento.required' => 'A Descrição é obrigatória.',
            'valor.required' => 'A Data do Evento é obrigatória.',
            'user_id.required' => 'O Usuário é obrigatório.',
            'unidade_id.required' => 'A Filial é obrigatória.',
        ]);

        if ($request->filled('valor')) {
            $valorFormatado = str_replace(
                ',',
                '.',
                preg_replace('/[^\d,]/', '', $request->input('valor'))
            );
            $request->merge(['valor' => $valorFormatado]);
        }

        try {
            DB::beginTransaction();

            $evento = new Deflatores();

            $evento->descricao_evento = $validate['descricao_evento'];
            $evento->valor = $request->valor;
            $evento->user_id = $validate['user_id'];
            $evento->unidade_id = $validate['unidade_id'];
            $evento->data_inclusao = now();


            $evento->save();
            DB::commit();

            return redirect()->route('admin.deflatorescarvalima.index')->with('success', 'Deflator Cadastrado com Sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Erro ao cadastrar Deflator' . $e->getMessage());
            return redirect()->route('admin.deflatorescarvalima.index')->with('error', 'Erro ao Cadastrar Deflator: .'   . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $evento = Deflatores::findOrFail($id);

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        return view('admin.deflatorescarvalima.edit', compact('evento', 'filial'));
    }

    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'descricao_evento'   => 'required',
            'valor'   => 'required',
            'user_id'   => 'required',
            'unidade_id'   => 'required',
        ], [
            'descricao_evento.required' => 'A Descrição é obrigatória.',
            'valor.required' => 'A Data do Evento é obrigatória.',
            'user_id.required' => 'O Usuário é obrigatório.',
            'unidade_id.required' => 'A Filial é obrigatória.',
        ]);

        if ($request->filled('valor')) {
            $valorFormatado = str_replace(
                ',',
                '.',
                preg_replace('/[^\d,]/', '', $request->input('valor'))
            );
            $request->merge(['valor' => $valorFormatado]);
        }

        try {
            DB::beginTransaction();

            $evento = Deflatores::findOrFail($id);

            $evento->descricao_evento = $validate['descricao_evento'];
            $evento->valor = $request->valor;
            $evento->user_id = $validate['user_id'];
            $evento->unidade_id = $validate['unidade_id'];
            $evento->data_alteracao = now();

            $evento->save();
            DB::commit();

            return redirect()->route('admin.deflatorescarvalima.index')->with('success', 'Deflator Atualizado com Sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Erro ao cadastrar Deflator' . $e->getMessage());
            return redirect()->route('admin.deflatorescarvalima.index')->with('error', 'Erro ao Cadastrar Deflator: .'   . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $evento = Deflatores::findOrFail($id);

            $evento->delete();
            DB::commit();

            return redirect()->route('admin.deflatorescarvalima.index')->with('success', 'Deflator Excluído com Sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Erro ao excluír Deflator' . $e->getMessage());
            return redirect()->route('admin.deflatorescarvalima.index')->with('error', 'Erro ao Excluír Deflator: .'   . $e->getMessage());
        }
    }
}
