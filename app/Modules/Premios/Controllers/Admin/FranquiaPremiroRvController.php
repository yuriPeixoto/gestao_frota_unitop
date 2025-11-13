<?php

namespace App\Modules\Premios\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaVeiculo;
use App\Models\FranquiaPremioRv;
use App\Models\SubCategoriaVeiculo;
use App\Models\TipoEquipamento;
use App\Models\TipoOperacao;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FranquiaPremiroRvController extends Controller
{
    public function index(Request $request)
    {
        $query = FranquiaPremioRv::query();

        if ($request->filled('id_operacao')) {
            $query->where('id_operacao', $request->input('id_operacao'));
        }
        if ($request->filled('id_tipoequipamento')) {
            $query->where('id_tipoequipamento', $request->input('id_tipoequipamento'));
        }
        if ($request->filled('id_subcategoria')) {
            $query->where('id_subcategoria', $request->input('id_subcategoria'));
        }
        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->input('id_categoria'));
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

        $listagem = $query->latest('id_franquia_premio_rv')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.franquiapremiorv.index', compact('listagem', 'categoria', 'subcategoria', 'operador', 'equipamento'));
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

        $step = FranquiaPremioRv::whereIn('step', ['Step 1', 'Step 2', 'Step 3', 'Step 4',])
            ->distinct('step')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_franquia_premio_rv,
                    'label' => $item->step
                ];
            })
            ->toArray();

        return view('admin.franquiapremiorv.create', compact('categoria', 'subcategoria', 'operador', 'equipamento', 'step', 'franquia'));
    }

    public function store(Request $request)
    {
        if ($request->filled('valor')) {
            $valorFormatado = str_replace(
                ',',
                '.',
                preg_replace('/[^\d,]/', '', $request->input('valor'))
            );
            $request->merge(['valor' => $valorFormatado]);
        }

        $validate = $request->validate([
            'id_operacao' => 'required',
            'id_categoria' => 'required',
            'id_tipoequipamento' => 'required',
            'id_subcategoria' => 'required',
            'step' => 'required',
            'media' => 'required',
            'valor' => 'required',
            'id_filial' => 'required',
            'usuario_inclusao' => 'required',
            'pesobruto' => 'required',
            'ativo' => 'required|boolean',
        ], [
            'id_operacao.required'  =>  'O campo Operação é obrigatorio.',
            'id_categoria.required'  =>  'O campo Categoria é obrigatorio.',
            'id_tipoequipamento.required'  =>  'O campo Tipo Equipamento é obrigatorio.',
            'id_subcategoria.required'  =>  'O campo SubCategoria é obrigatorio.',
            'step.required'  =>  'O campo Step é obrigatorio.',
            'media.required'  =>  'O campo Media é obrigatorio.',
            'valor.required'  =>  'O campo Valor é obrigatorio.',
            'id_filial.required'  =>  'O campo Filial é obrigatorio.',
            'usuario_inclusao.required'  =>  'O campo Usuario é obrigatorio.',
            'pesobruto.required'  =>  'O campo Pesobruto é obrigatorio.',
            'ativo.required'  =>  'O campo Ativo é obrigatorio.',
        ]);

        try {
            DB::beginTransaction();

            $franquia = new FranquiaPremioRv();

            $franquia->id_operacao = $validate['id_operacao'];
            $franquia->id_categoria = $validate['id_categoria'];
            $franquia->id_tipoequipamento = $validate['id_tipoequipamento'];
            $franquia->id_subcategoria = $validate['id_subcategoria'];
            $franquia->step = $validate['step'];
            $franquia->media = $validate['media'];
            $franquia->media = $validate['media'];
            $franquia->valor = $validate['valor'];
            $franquia->id_filial = $validate['id_filial'];
            $franquia->usuario_inclusao = $validate['usuario_inclusao'];
            $franquia->pesobruto = $validate['pesobruto'];
            $franquia->ativo = $validate['ativo'];
            $franquia->data_inclusao    =   now();

            $franquia->save();

            DB::commit();

            return redirect()->route('admin.franquiapremiorv.index')->with('success', 'Franquia Cadastrada com Sucesso!');
        } catch (Exception $e) {
            Log::info('Erro ao cadastrar franquia: '    . $e->getMessage());
            return redirect()->route('admin.franquiapremiorv.index')->with('error', 'Erro a cadastrar franquia: '    . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $franquia = FranquiaPremioRv::findOrFail($id);

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

        $step = FranquiaPremioRv::whereIn('step', ['Step 1', 'Step 2', 'Step 3', 'Step 4',])
            ->distinct('step')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->id_franquia_premio_rv,
                    'label' => $item->step
                ];
            })
            ->toArray();

        return view('admin.franquiapremiorv.edit', compact('categoria', 'subcategoria', 'operador', 'equipamento', 'step', 'franquia'));
    }

    public function update(Request $request, $id)
    {
        if ($request->filled('valor')) {
            $valorFormatado = str_replace(
                ',',
                '.',
                preg_replace('/[^\d,]/', '', $request->input('valor'))
            );
            $request->merge(['valor' => $valorFormatado]);
        }

        $validate = $request->validate([
            'id_operacao' => 'required',
            'id_categoria' => 'required',
            'id_tipoequipamento' => 'required',
            'id_subcategoria' => 'required',
            'step' => 'required',
            'media' => 'required',
            'valor' => 'required',
            'id_filial' => 'required',
            'usuario_inclusao' => 'required',
            'pesobruto' => 'required',
            'ativo' => 'required|boolean',
        ], [
            'id_operacao.required'  =>  'O campo Operação é obrigatorio.',
            'id_categoria.required'  =>  'O campo Categoria é obrigatorio.',
            'id_tipoequipamento.required'  =>  'O campo Tipo Equipamento é obrigatorio.',
            'id_subcategoria.required'  =>  'O campo SubCategoria é obrigatorio.',
            'step.required'  =>  'O campo Step é obrigatorio.',
            'media.required'  =>  'O campo Media é obrigatorio.',
            'valor.required'  =>  'O campo Valor é obrigatorio.',
            'id_filial.required'  =>  'O campo Filial é obrigatorio.',
            'usuario_inclusao.required'  =>  'O campo Usuario é obrigatorio.',
            'pesobruto.required'  =>  'O campo Pesobruto é obrigatorio.',
            'ativo.required'  =>  'O campo Ativo é obrigatorio.',
        ]);

        try {
            DB::beginTransaction();

            $franquia = FranquiaPremioRv::findOrFail($id);

            $franquia->id_operacao = $validate['id_operacao'];
            $franquia->id_categoria = $validate['id_categoria'];
            $franquia->id_tipoequipamento = $validate['id_tipoequipamento'];
            $franquia->id_subcategoria = $validate['id_subcategoria'];
            $franquia->step = $validate['step'];
            $franquia->media = $validate['media'];
            $franquia->media = $validate['media'];
            $franquia->valor = $validate['valor'];
            $franquia->id_filial = $validate['id_filial'];
            $franquia->usuario_inclusao = $validate['usuario_inclusao'];
            $franquia->pesobruto = $validate['pesobruto'];
            $franquia->ativo = $validate['ativo'];

            $franquia->save();

            DB::commit();

            return redirect()->route('admin.franquiapremiorv.index')->with('success', 'Franquia Atualizada com Sucesso!');
        } catch (Exception $e) {
            Log::info('Erro ao atualizar franquia: '    . $e->getMessage());
            return redirect()->route('admin.franquiapremiorv.index')->with('error', 'Erro ao atualizar franquia: '    . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $franquia = FranquiaPremioRv::findOrFail($id);

            $franquia->delete();

            DB::commit();

            return redirect()->route('admin.franquiapremiorv.index')->with('success', 'Franquia Excluída com Sucesso!');
        } catch (Exception $e) {
            Log::info('Erro ao excluir franquia: '    . $e->getMessage());
            return redirect()->route('admin.franquiapremiorv.index')->with('error', 'Erro ao excluir franquia: '    . $e->getMessage());
        }
    }

    public function clonarFranquia($id)
    {
        try {
            DB::beginTransaction();

            $franquia = FranquiaPremioRv::findOrFail($id);

            // clona todos os campos
            $novaFranquia = $franquia->replicate();

            // altera apenas o necessário
            $novaFranquia->data_inclusao = now();
            $novaFranquia->clonado = 'S';

            $novaFranquia->save();

            DB::commit();

            return redirect()
                ->route('admin.franquiapremiorv.index')
                ->with('success', 'Franquia clonada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao clonar franquia: ' . $e->getMessage());
            return redirect()
                ->route('admin.franquiapremiorv.index')
                ->with('error', 'Erro ao clonar franquia: ' . $e->getMessage());
        }
    }

    public function desativarFranquia($id)
    {

        try {
            DB::beginTransaction();

            $franquia = FranquiaPremioRv::findOrFail($id);

            if ($franquia->ativo) {
                $franquia->ativo = false;
                $franquia->update();

                DB::commit();

                return redirect()
                    ->route('admin.franquiapremiorv.index')
                    ->with('success', 'Franquia desativada com sucesso!');
            } else {
                return redirect()
                    ->route('admin.franquiapremiorv.index')
                    ->with('error', 'Franquia já se encontra desativada!');
            }
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao desativar franquia: ' . $e->getMessage());
            return redirect()
                ->route('admin.franquiapremiorv.index')
                ->with('error', 'Erro ao desativar franquia: ' . $e->getMessage());
        }
    }
}
