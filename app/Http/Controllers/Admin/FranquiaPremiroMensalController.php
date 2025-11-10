<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FranquiaPremioMensal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SubCategoriaVeiculo;
use App\Models\TipoEquipamento;
use App\Models\TipoOperacao;
use App\Models\CategoriaVeiculo;

class FranquiaPremiroMensalController extends Controller
{
    public function index(Request $request)
    {
        $query = FranquiaPremioMensal::query();

        if ($request->filled('id_operacao')) {
            $query->where('id_operacao', $request->input('id_operacao'));
        }
        if ($request->filled('id_subcategoria')) {
            $query->where('id_subcategoria', $request->input('id_subcategoria'));
        }
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->input('ativo'));
        }

        $operador = TipoOperacao::select('id_tipo_operacao as value', 'descricao_tipo_operacao as label')
            ->orderBy('descricao_tipo_operacao')
            ->get();

        $equipamento = TipoEquipamento::select('id_tipo_equipamento as value', 'descricao_tipo as label')
            ->distinct()
            ->orderBy('descricao_tipo')
            ->get();

        $categoria = CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->orderBy('descricao_categoria')
            ->get();

        $subcategoria = SubCategoriaVeiculo::select('id_subcategoria as value', 'descricao_subcategoria as label')
            ->orderBy('descricao_subcategoria')
            ->get();

        $ativo = [
            ['value' => 1, 'label' => 'Sim'],
            ['value' => 0, 'label' => 'Não'],
        ];


        $listagem = $query->latest('id_franquia_premio_mensal')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.franquiapremiosmensal.index', compact('listagem', 'categoria', 'subcategoria', 'operador', 'equipamento', 'ativo'));
    }

    public function create()
    {
        $franquia = null;

        $operador = TipoOperacao::select('id_tipo_operacao as value', 'descricao_tipo_operacao as label')
            ->orderBy('descricao_tipo_operacao')
            ->get();

        $equipamento = TipoEquipamento::select('id_tipo_equipamento as value', 'descricao_tipo as label')
            ->distinct()
            ->orderBy('descricao_tipo')
            ->get();

        $categoria = CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->orderBy('descricao_categoria')
            ->get();

        $subcategoria = SubCategoriaVeiculo::select('id_subcategoria as value', 'descricao_subcategoria as label')
            ->orderBy('descricao_subcategoria')
            ->get();

        $step = FranquiaPremioMensal::whereIn('step', ['Step 1', 'Step 2', 'Step 3', 'Step 4',])
            ->distinct('step')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_franquia_premio_mensal,
                    'label' => $item->step
                ];
            })
            ->toArray();

        return view('admin.franquiapremiosmensal.create', compact('categoria', 'subcategoria', 'operador', 'equipamento', 'franquia', 'step'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'id_operacao' => 'required',
            'id_tipoequipamento' => 'required',
            'id_subcategoria' => 'required',
            'step' => 'required',
            'media' => 'required',
            'id_filial' => 'required',
            'usuario_inclusao' => 'required',
            'ativo' => 'required|boolean',
        ], [
            'id_operacao.required'  =>  'O campo Operação é obrigatório.',
            'id_tipoequipamento.required'  =>  'O campo Tipo Equipamento é obrigatório.',
            'id_subcategoria.required'  =>  'O campo SubCategoria é obrigatório.',
            'step.required'  =>  'O campo Step é obrigatório.',
            'media.required'  =>  'O campo Média é obrigatório.',
            'id_filial.required'  =>  'O campo Filial é obrigatório.',
            'usuario_inclusao.required'  =>  'O campo Usuário é obrigatório.',
            'ativo.required'  =>  'O campo Ativo é obrigatório.',
        ]);

        $camposMonetarios = [
            'media',
            '_0_1000',
            '_1000',
            '_2000',
            '_3000',
            '_4000',
            '_5000',
            '_6000',
            '_7000',
            '_8000',
            '_9000',
            '_10000',
            '_11000',
            '_12000',
            '_13000',
            '_14000',
            '_15000',
            '_16000',
            '_17000',
            '_18000',
            '_19000',
            '_20000'
        ];

        try {
            DB::beginTransaction();

            // 🔹 Limpa os valores monetários
            foreach ($camposMonetarios as $campo) {
                if ($request->filled($campo)) {
                    $valor = $request->input($campo);
                    $valor = preg_replace('/[^\d,]/', '', $valor); // remove R$, espaços, pontos
                    $valor = str_replace(',', '.', $valor); // troca vírgula por ponto
                    $request->merge([$campo => $valor]);
                }
            }

            // 🔹 Cria o modelo
            $franquia = new FranquiaPremioMensal();
            $franquia->id_operacao = $validate['id_operacao'];
            $franquia->id_tipoequipamento = $validate['id_tipoequipamento'];
            $franquia->id_subcategoria = $validate['id_subcategoria'];
            $franquia->step = $validate['step'];
            $franquia->id_filial = $validate['id_filial'];
            $franquia->usuario_inclusao = $validate['usuario_inclusao'];
            $franquia->ativo = $validate['ativo'];
            $franquia->data_inclusao = now();

            // 🔹 Atribui dinamicamente todos os campos monetários limpos
            foreach ($camposMonetarios as $campo) {
                if ($request->has($campo)) {
                    $franquia->{$campo} = $request->input($campo);
                }
            }

            $franquia->save();

            DB::commit();

            return redirect()
                ->route('admin.franquiapremiosmensal.index')
                ->with('success', 'Franquia Cadastrada com Sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::info('Erro ao cadastrar franquia: ' . $e->getMessage());
            return redirect()
                ->route('admin.franquiapremiosmensal.index')
                ->with('error', 'Erro ao cadastrar franquia: ' . $e->getMessage());
        }
    }



    public function edit($id)
    {
        $franquia = FranquiaPremioMensal::findOrFail($id);

        $operador = TipoOperacao::select('id_tipo_operacao as value', 'descricao_tipo_operacao as label')
            ->distinct()
            ->orderBy('descricao_tipo_operacao')
            ->get();

        $equipamento = TipoEquipamento::select('id_tipo_equipamento as value', 'descricao_tipo as label')
            ->distinct()
            ->orderBy('descricao_tipo')
            ->get();

        $categoria = CategoriaVeiculo::select('id_categoria as value', 'descricao_categoria as label')
            ->distinct()
            ->orderBy('descricao_categoria')
            ->get();

        $subcategoria = SubCategoriaVeiculo::select('id_subcategoria as value', 'descricao_subcategoria as label')
            ->distinct()
            ->orderBy('descricao_subcategoria')
            ->get();

        $ativo = [
            ['value' => 1, 'label' => 'Sim'],
            ['value' => 0, 'label' => 'Não'],
        ];

        $step = FranquiaPremioMensal::whereIn('step', ['Step 1', 'Step 2', 'Step 3', 'Step 4',])
            ->distinct('step')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_franquia_premio_mensal,
                    'label' => $item->step
                ];
            })
            ->toArray();

        return view('admin.franquiapremiosmensal.edit', compact('categoria', 'subcategoria', 'operador', 'equipamento', 'step', 'franquia'));
    }

    public function update(Request $request, $id)
    {
        $validate = $request->validate([
            'id_operacao' => 'required',
            'id_tipoequipamento' => 'required',
            'id_subcategoria' => 'required',
            'step' => 'required',
            'media' => 'required',
            'id_filial' => 'required',
            'usuario_inclusao' => 'required',
            'ativo' => 'required|boolean',
        ], [
            'id_operacao.required'  =>  'O campo Operação é obrigatorio.',
            'id_tipoequipamento.required'  =>  'O campo Tipo Equipamento é obrigatorio.',
            'id_subcategoria.required'  =>  'O campo SubCategoria é obrigatorio.',
            'step.required'  =>  'O campo Step é obrigatorio.',
            'media.required'  =>  'O campo Media é obrigatorio.',
            'id_filial.required'  =>  'O campo Filial é obrigatorio.',
            'usuario_inclusao.required'  =>  'O campo Usuario é obrigatorio.',
            'ativo.required'  =>  'O campo Ativo é obrigatorio.',
        ]);

        $camposMonetarios = [
            'media',
            '_0_1000',
            '_1000',
            '_2000',
            '_3000',
            '_4000',
            '_5000',
            '_6000',
            '_7000',
            '_8000',
            '_9000',
            '_10000',
            '_11000',
            '_12000',
            '_13000',
            '_14000',
            '_15000',
            '_16000',
            '_17000',
            '_18000',
            '_19000',
            '_20000'
        ];

        try {
            DB::beginTransaction();

            foreach ($camposMonetarios as $campo) {
                if ($request->filled($campo)) {
                    $valor = $request->input($campo);
                    $valor = preg_replace('/[^\d,]/', '', $valor); // remove R$, espaços, pontos
                    $valor = str_replace(',', '.', $valor); // troca vírgula por ponto
                    $request->merge([$campo => $valor]);
                }
            }


            $franquia = FranquiaPremioMensal::findOrFail($id);

            $franquia->id_operacao = $validate['id_operacao'];
            $franquia->id_tipoequipamento = $validate['id_tipoequipamento'];
            $franquia->id_subcategoria = $validate['id_subcategoria'];
            $franquia->step = $validate['step'];
            $franquia->media = $validate['media'];
            $franquia->id_filial = $validate['id_filial'];
            $franquia->usuario_inclusao = $validate['usuario_inclusao'];
            $franquia->ativo = $validate['ativo'];

            foreach ($camposMonetarios as $campo) {
                if ($request->has($campo)) {
                    $franquia->{$campo} = $request->input($campo);
                }
            }

            $franquia->save();

            DB::commit();

            return redirect()->route('admin.franquiapremiosmensal.index')->with('success', 'Franquia Atualizada com Sucesso!');
        } catch (Exception $e) {
            Log::info('Erro ao atualizar franquia: '    . $e->getMessage());
            return redirect()->route('admin.franquiapremiosmensal.index')->with('error', 'Erro ao atualizar franquia: '    . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $franquia = FranquiaPremioMensal::findOrFail($id);

            $franquia->delete();

            DB::commit();

            return redirect()->route('admin.franquiapremiosmensal.index')->with('success', 'Franquia Excluída com Sucesso!');
        } catch (Exception $e) {
            Log::info('Erro ao excluir franquia: '    . $e->getMessage());
            return redirect()->route('admin.franquiapremiosmensal.index')->with('error', 'Erro ao excluir franquia: '    . $e->getMessage());
        }
    }

    public function clonarFranquia($id)
    {
        try {
            DB::beginTransaction();

            $franquia = FranquiaPremioMensal::findOrFail($id);

            // clona todos os campos
            $novaFranquia = $franquia->replicate();

            // altera apenas o necessário
            $novaFranquia->data_inclusao = now();
            $novaFranquia->clonado = 'S';

            $novaFranquia->save();

            DB::commit();

            return redirect()
                ->route('admin.franquiapremiosmensal.index')
                ->with('success', 'Franquia clonada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar franquia: ' . $e->getMessage());
            return redirect()
                ->route('admin.franquiapremiosmensal.index')
                ->with('error', 'Erro ao clonar franquia: ' . $e->getMessage());
        }
    }

    public function desativarFranquia($id)
    {

        try {
            DB::beginTransaction();

            $franquia = FranquiaPremioMensal::findOrFail($id);

            if ($franquia->ativo) {
                $franquia->ativo = false;
                $franquia->update();

                DB::commit();

                return redirect()
                    ->route('admin.franquiapremiosmensal.index')
                    ->with('success', 'Franquia desativada com sucesso!');
            } else {
                return redirect()
                    ->route('admin.franquiapremiosmensal.index')
                    ->with('error', 'Franquia já se encontra desativada!');
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desativar franquia: ' . $e->getMessage());
            return redirect()
                ->route('admin.franquiapremiosmensal.index')
                ->with('error', 'Erro ao desativar franquia: ' . $e->getMessage());
        }
    }
}
