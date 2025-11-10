<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deflatores;
use App\Models\DeflatoresEventosMotoristas;
use App\Models\Filial;
use App\Models\Motorista;
use App\Models\Pessoal;
use App\Models\Veiculo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeflatoresEventosMotoristasController extends Controller
{
    public function index(Request $request)
    {
        $query = DeflatoresEventosMotoristas::with('motorista');

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_deflatores_motoristas_eventos')) {
            $query->where('id_deflatores_motoristas_eventos', $request->input('id_deflatores_motoristas_eventos'));
        }
        if ($request->filled('filial_lancamento')) {
            $query->where('filial_lancamento', $request->input('filial_lancamento'));
        }
        if ($request->filled('id_motorista')) {
            $query->where('id_motorista', $request->input('id_motorista'));
        }
        if ($request->filled('data_evento')) {
            $query->where('data_evento', $request->input('data_evento'));
        }
        if ($request->filled('id_deflatores')) {
            $query->where('id_deflatores', $request->input('id_deflatores'));
        }
        if ($request->filled('data_evento')) {
            $query->where('data_evento', $request->input('id_deflatores'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        $deflator = Deflatores::select('id_deflatores as value', 'descricao_evento as label')
            ->orderBy('descricao_evento')
            ->get();

        $criteria_id_motorista = Pessoal::query()
            ->where('ativo', true) // exemplo de critério
            ->orderBy('id_pessoal', 'asc');

        // Monta os dados para o select (value = id, label = nome)
        $motorista = $criteria_id_motorista
            ->select('nome as value', 'nome as label')
            ->get();


        $listagem = $query->latest('id_deflatores_motoristas_eventos')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.deflatoreseventospormotoristas.index', compact('filial', 'deflator', 'motorista', 'listagem'));
    }

    public function create(Request $request)
    {
        $evento = null;

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        $deflator = Deflatores::select('id_deflatores as value', 'descricao_evento as label')
            ->orderBy('descricao_evento')
            ->get();

        // Se quiser aplicar filtros como no $criteria_id_motorista:
        $criteria_id_motorista = Pessoal::query()
            ->where('ativo', true) // exemplo de critério
            ->orderBy('id_pessoal', 'asc');

        // Monta os dados para o select (value = id, label = nome)
        $motorista = $criteria_id_motorista
            ->select('id_pessoal as value', 'nome as label')
            ->get();


        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        return view('admin.deflatoreseventospormotoristas.create', compact('evento', 'filial', 'deflator', 'motorista', 'placa'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'id_deflatores'  => 'required',
            'id_motorista'  => 'required',
            'data_evento'  => 'required',
            'observacao'  => 'required',
            'arquivo'  => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'user_lancamento'  => 'required',
            'filial_lancamento'  => 'required',
            'id_veiculo'  => 'required',
        ], [
            'id_deflatores.required' => 'O Tipo Deflator é obrigatório',
            'id_motorista.required' => 'O Motorista é obrigatório',
            'data_evento.required' => 'Data de Evento é obrigatório',
            'observacao.required' => 'A Observação é obrigatória',
            'user_lancamento.required' => 'O Usuario é obrigatório',
            'filial_lancamento.required' => 'A Filial é obrigatório',
            'id_veiculo.required' => 'O Veiculo é obrigatório',
        ]);

        try {
            DB::beginTransaction();

            $deflator = new DeflatoresEventosMotoristas();



            $deflator->id_deflatores = $validate['id_deflatores'];
            $deflator->id_motorista = $validate['id_motorista'];
            $deflator->data_evento = $validate['data_evento'];
            $deflator->observacao = $validate['observacao'];
            $deflator->user_lancamento = $validate['user_lancamento'];
            $deflator->filial_lancamento = $validate['filial_lancamento'];
            $deflator->id_veiculo = $validate['id_veiculo'];
            $deflator->data_inclusao = now();

            if ($request->hasFile('arquivo')) {
                $file = $request->file('arquivo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/arquivos', $filename); // armazena em storage/app/public/arquivos
                $deflator->arquivo = $filename;
            } else {
                $deflator->arquivo = null; // caso não tenha enviado arquivo
            }

            $deflator->save();
            DB::commit();
            return redirect()->route('admin.deflatoreseventospormotoristas.index')->with('success', 'Deflator Evento por Motorista Cadastrado com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao cadastrar Deflator Evento por Motorista: ' . $e->getMessage());
            return redirect()->route('admin.deflatoreseventospormotoristas.index')->with('error', 'Erro ao cadastrar o Deflator: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $evento = DeflatoresEventosMotoristas::findOrFail($id);

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        $deflator = Deflatores::select('id_deflatores as value', 'descricao_evento as label')
            ->orderBy('descricao_evento')
            ->get();

        // Se quiser aplicar filtros como no $criteria_id_motorista:
        $criteria_id_motorista = Pessoal::query()
            ->where('ativo', true) // exemplo de critério
            ->orderBy('id_pessoal', 'asc');

        // Monta os dados para o select (value = id, label = nome)
        $motorista = $criteria_id_motorista
            ->select('id_pessoal as value', 'nome as label')
            ->get();


        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        return view('admin.deflatoreseventospormotoristas.edit', compact('evento', 'filial', 'deflator', 'motorista', 'placa'));
    }

    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'id_deflatores'  => 'required',
            'id_motorista'  => 'required',
            'data_evento'  => 'required',
            'observacao'  => 'required',
            'arquivo'  => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'user_lancamento'  => 'required',
            'filial_lancamento'  => 'required',
            'id_veiculo'  => 'required',
        ], [
            'id_deflatores.required' => 'O Tipo Deflator é obrigatório',
            'id_motorista.required' => 'O Motorista é obrigatório',
            'data_evento.required' => 'Data de Evento é obrigatório',
            'observacao.required' => 'A Observação é obrigatória',
            'user_lancamento.required' => 'O Usuario é obrigatório',
            'filial_lancamento.required' => 'A Filial é obrigatório',
            'id_veiculo.required' => 'O Veiculo é obrigatório',
        ]);

        try {
            DB::beginTransaction();


            $deflator = DeflatoresEventosMotoristas::findOrFail($id);

            $deflator->id_deflatores = $validate['id_deflatores'];
            $deflator->id_motorista = $validate['id_motorista'];
            $deflator->data_evento = $validate['data_evento'];
            $deflator->observacao = $validate['observacao'];
            $deflator->user_lancamento = $validate['user_lancamento'];
            $deflator->filial_lancamento = $validate['filial_lancamento'];
            $deflator->id_veiculo = $validate['id_veiculo'];
            $deflator->data_alteracao = now();

            if ($request->hasFile('arquivo')) {
                $file = $request->file('arquivo');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/arquivos', $filename); // armazena em storage/app/public/arquivos
                $deflator->arquivo = $filename;
            } else {
                $deflator->arquivo = null; // caso não tenha enviado arquivo
            }

            $deflator->update();

            DB::commit();
            return redirect()->route('admin.deflatoreseventospormotoristas.index')->with('success', 'Deflator Evento por Motorista Atualizado com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao cadastrar Deflator Evento por Motorista: ' . $e->getMessage());
            return redirect()->route('admin.deflatoreseventospormotoristas.index')->with('error', 'Erro ao cadastrar o Deflator: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $evento = DeflatoresEventosMotoristas::findOrFail($id);

            $evento->delete();

            return redirect()->route('admin.deflatoreseventospormotoristas.index')->with('success', 'Deflator Evento por Motorista Excluída com Sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::INFO('Erro ao excluír Deflator Evento por Motorista: ' . $e->getMessage());
            return redirect()->route('admin.deflatoreseventospormotoristas.index')->with('error', 'Erro ao excluír o Deflator Evento por Motorista: ' . $e->getMessage());
        }
    }
}
