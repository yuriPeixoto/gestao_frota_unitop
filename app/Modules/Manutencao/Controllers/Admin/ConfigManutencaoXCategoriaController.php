<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaPlanejamentoManutencao;
use App\Models\Manutencao;
use App\Models\PlanejamentoManutencao;
use App\Models\TipoCategoria;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConfigManutencaoXCategoriaController extends Controller
{
    public function index(Request $request)
    {
        $query = CategoriaPlanejamentoManutencao::query();

        if ($request->filled('id_categoria')) {
            $query->where('id_categoria', $request->id_categoria);
        }

        if ($request->filled('id_manutencao')) {
            $query->where('id_manutencao_categoria', $request->id_manutencao);
        }

        // dd($query);
        $categoria = $query->latest('id_manutencao_categoria')
            ->paginate(10);

        $referenceDatas = $this->getReferenceDatas();


        return view('admin.manutencaocategoria.index', array_merge(
            [
                'categoria'      => $categoria,
                'referenceDatas' => $referenceDatas,
            ]
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('config_mutencao_categoria', now()->addHours(12), function () {
            return [
                'categoria' => TipoCategoria::where('ativo', true)
                    ->orderBy('id_categoria')
                    ->get(['id_categoria as value', 'descricao_categoria as label']),

                'manutencao' => Manutencao::orderBy('id_manutencao')
                    ->get(['id_manutencao as value', 'descricao_manutencao as label']),
            ];
        });
    }

    public function edit($id)
    {
        $manutencaoConfig = CategoriaPlanejamentoManutencao::where('id_manutencao_categoria', $id)->first();

        $categorias = TipoCategoria::where('ativo', true)
            ->orderBy('id_categoria')
            ->get();

        $planejamentos = PlanejamentoManutencao::where('status_planejamento', true)
            ->with('manutencao')  // Carregue apenas a relação manutencao
            ->orderBy('id_planejamento_manutencao')
            ->get();

        return view('admin.manutencaocategoria.edit', compact(
            'categorias',
            'planejamentos',
            'manutencaoConfig',
        ));
    }

    public function create()
    {
        $categorias = TipoCategoria::where('ativo', true)
            ->orderBy('id_categoria')
            ->get();

        $planejamentos = PlanejamentoManutencao::where('status_planejamento', true)
            ->with('manutencao')  // Carregue apenas a relação manutencao
            ->orderBy('id_planejamento_manutencao')
            ->get();

        return view('admin.manutencaocategoria.create', compact(
            'categorias',
            'planejamentos',
        ));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        try {
            DB::beginTransaction();

            $categoriaPlanejamentoManutencao = new CategoriaPlanejamentoManutencao();
            $categoriaPlanejamentoManutencao->data_inclusao            = now();
            $categoriaPlanejamentoManutencao->id_categoria             = $request->id_categoria;
            $categoriaPlanejamentoManutencao->hora_gerar_os_automatica = $request->hora_gerar_os_automatica;
            $categoriaPlanejamentoManutencao->km_gerar_os_automatica   = $request->km_gerar_os_automatica;
            $categoriaPlanejamentoManutencao->horas_frequencia         = $request->horas_frequencia;
            $categoriaPlanejamentoManutencao->km_frequencia            = $request->km_frequencia;
            $categoriaPlanejamentoManutencao->dias_frequencia          = $request->dias_frequencia;
            $categoriaPlanejamentoManutencao->eventos_frequencia       = $request->eventos_frequencia;
            $categoriaPlanejamentoManutencao->litros_frequencia        = $request->litros_frequencia;
            $categoriaPlanejamentoManutencao->horas_tolerancia         = $request->horas_tolerancia;
            $categoriaPlanejamentoManutencao->km_tolerancia            = $request->km_tolerancia;
            $categoriaPlanejamentoManutencao->dia_tolerancia           = $request->dia_tolerancia;
            $categoriaPlanejamentoManutencao->eventos_tolerancia       = $request->eventos_tolerancia;
            $categoriaPlanejamentoManutencao->litros_tolerancia        = $request->litros_tolerancia;
            $categoriaPlanejamentoManutencao->hora_alerta              = $request->hora_alerta;
            $categoriaPlanejamentoManutencao->km_alerta                = $request->km_alerta;
            $categoriaPlanejamentoManutencao->dias_alerta              = $request->dias_alerta;
            $categoriaPlanejamentoManutencao->eventos_alerta           = $request->eventos_alerta;
            $categoriaPlanejamentoManutencao->litros_alerta            = $request->litros_alerta;
            $categoriaPlanejamentoManutencao->hora_adiantamento        = $request->hora_adiantamento;
            $categoriaPlanejamentoManutencao->km_adiantamento          = $request->km_adiantamento;
            $categoriaPlanejamentoManutencao->dias_adiantamento        = $request->dias_adiantamento;
            $categoriaPlanejamentoManutencao->eventos_adiantamento     = $request->eventos_adiantamento;
            $categoriaPlanejamentoManutencao->litros_adiantamento      = $request->litros_adiantamento;
            $categoriaPlanejamentoManutencao->horas_tempo_previsto     = $request->horas_tempo_previsto;
            $categoriaPlanejamentoManutencao->dias_previstos           = $request->dias_previstos;
            $categoriaPlanejamentoManutencao->id_planejamento          = $request->id_planejamento;

            $categoriaPlanejamentoManutencao->save();

            DB::commit();

            return redirect()
                ->route('admin.manutencaocategoria.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Planejamento por categoria cadastrado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro na criação do Planejamento por categoria:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.manutencaocategoria.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o Planejamento por categoria."
                ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $categoriaPlanejamentoManutencao = CategoriaPlanejamentoManutencao::findOrFail($id);

            $categoriaPlanejamentoManutencao->data_alteracao           = now();
            $categoriaPlanejamentoManutencao->id_categoria             = $request->id_categoria;
            $categoriaPlanejamentoManutencao->hora_gerar_os_automatica = $request->hora_gerar_os_automatica;
            $categoriaPlanejamentoManutencao->km_gerar_os_automatica   = $request->km_gerar_os_automatica;
            $categoriaPlanejamentoManutencao->horas_frequencia         = $request->horas_frequencia;
            $categoriaPlanejamentoManutencao->km_frequencia            = $request->km_frequencia;
            $categoriaPlanejamentoManutencao->dias_frequencia          = $request->dias_frequencia;
            $categoriaPlanejamentoManutencao->eventos_frequencia       = $request->eventos_frequencia;
            $categoriaPlanejamentoManutencao->litros_frequencia        = $request->litros_frequencia;
            $categoriaPlanejamentoManutencao->horas_tolerancia         = $request->horas_tolerancia;
            $categoriaPlanejamentoManutencao->km_tolerancia            = $request->km_tolerancia;
            $categoriaPlanejamentoManutencao->dia_tolerancia           = $request->dia_tolerancia;
            $categoriaPlanejamentoManutencao->eventos_tolerancia       = $request->eventos_tolerancia;
            $categoriaPlanejamentoManutencao->litros_tolerancia        = $request->litros_tolerancia;
            $categoriaPlanejamentoManutencao->hora_alerta              = $request->hora_alerta;
            $categoriaPlanejamentoManutencao->km_alerta                = $request->km_alerta;
            $categoriaPlanejamentoManutencao->dias_alerta              = $request->dias_alerta;
            $categoriaPlanejamentoManutencao->eventos_alerta           = $request->eventos_alerta;
            $categoriaPlanejamentoManutencao->litros_alerta            = $request->litros_alerta;
            $categoriaPlanejamentoManutencao->hora_adiantamento        = $request->hora_adiantamento;
            $categoriaPlanejamentoManutencao->km_adiantamento          = $request->km_adiantamento;
            $categoriaPlanejamentoManutencao->dias_adiantamento        = $request->dias_adiantamento;
            $categoriaPlanejamentoManutencao->eventos_adiantamento     = $request->eventos_adiantamento;
            $categoriaPlanejamentoManutencao->litros_adiantamento      = $request->litros_adiantamento;
            $categoriaPlanejamentoManutencao->horas_tempo_previsto     = $request->horas_tempo_previsto;
            $categoriaPlanejamentoManutencao->dias_previstos           = $request->dias_previstos;
            $categoriaPlanejamentoManutencao->id_planejamento          = $request->id_planejamento;

            $categoriaPlanejamentoManutencao->save();

            Log::info('Informações salvas:', $categoriaPlanejamentoManutencao->toArray());

            DB::commit();

            return redirect()
                ->route('admin.manutencaocategoria.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Planejamento por categoria editado com sucesso!'
                ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro na edição do Planejamento por categoria:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.manutencaocategoria.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível editar o Planejamento por categoria."
                ]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $categoriaManutencao = CategoriaPlanejamentoManutencao::where('id_manutencao_categoria', $id)->first();
            $categoriaManutencao->delete();

            $planejamentoManutencao = PlanejamentoManutencao::where(
                'id_planejamento_manutencao',
                $categoriaManutencao->id_planejamento
            )->first();
            $planejamentoManutencao->update([
                'status_planejamento'   => false,
                'data_alteracao'        => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
