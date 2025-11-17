<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InconsistenciaAts;
use App\Models\InconsistenciaTruckPag;
use App\Models\Veiculo;
use App\Models\Departamento;
use App\Models\VFilial;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Modules\Compras\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class InconsistenciasController extends Controller
{
    /**
     * Exibe a página principal com as abas de inconsistências
     */
    public function index()
    {
        // Obter dados de referência para os filtros de busca
        $referenceDatas = $this->getReferenceDatas();

        return view('admin.inconsistencias.index', $referenceDatas);
    }

    /**
     * Busca inconsistências ATS conforme filtros
     */
    public function searchAts(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $validator = validator($request->all(), [
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'As datas de início e fim são obrigatórias.'
                ], 422);
            }
            return redirect()->back()->with('error', 'As datas de início e fim são obrigatórias.');
        }

        Log::info('Parâmetros de busca:', $request->all());

        try {
            $query = InconsistenciaAts::query();

            $veiculos = Veiculo::where('situacao_veiculo', true)->orderBy('placa')->get();

            $bombas = Bomba::orderBy('descricao_bomba')
                ->get(['descricao_bomba as value', 'descricao_bomba as label']);

            $filiais = InconsistenciaAts::select('id_filial as value', 'nomefilial as label')
                ->orderBy('nomefilial')
                ->get();
            //VFilial::orderBy('name')->get();
            $departamentos = Departamento::orderBy('descricao_departamento')->get();

            $tiposCombustivel = collect([
                ['value' => 'DIESELS10', 'label' => 'DIESEL S10'],
                ['value' => 'ARLA32', 'label' => 'ARLA 32'],
                ['value' => 'DIESELS500', 'label' => 'DIESEL S500'],
            ]);

            // Aplicar filtros
            if ($request->filled('data_inicio')) {
                $dataInicio = Carbon::parse($request->data_inicio)->startOfDay();
                $query->where('data_inclusao', '>=', $dataInicio);
            }

            if ($request->filled('data_fim')) {
                $dataFim = Carbon::parse($request->data_fim)->endOfDay();
                $query->where('data_inclusao', '<=', $dataFim);
            }

            // Outros filtros...
            if ($request->filled('id_veiculo')) {
                $query->where('id_veiculo', $request->id_veiculo);
            }

            if ($request->filled('id_filial')) {
                $query->where('id_filial', $request->id_filial);
            }

            // Filtro para não tratados
            $query->where(function ($q) {
                $q->where('tratado', false)
                    ->orWhereNull('tratado');
            });


            Log::info('SQL gerado:', [$query->toSql()]);
            Log::info('Bindings:', $query->getBindings());

            $inconsistencias = $query
                ->orderByDesc('data_inclusao')
                ->get();


            Log::info('Total de registros encontrados: ' . $inconsistencias->count());


            if ($request->ajax()) {
                return view('admin.inconsistencias._table_ats', compact('inconsistencias'))->render();
            }

            return view('admin.inconsistencias._tab_ats', compact('inconsistencias', 'veiculos', 'bombas', 'filiais', 'departamentos', 'tiposCombustivel'));
        } catch (Exception $e) {
            Log::error('Erro na busca de ATS: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao realizar a busca.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Erro ao realizar a busca.');
        }
    }

    /**
     * Busca inconsistências TruckPag conforme filtros
     */
    public function searchTruckPag(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $validator = validator($request->all(), [
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'As datas de início e fim são obrigatórias.'
                ], 422);
            }

            return redirect()->back()->with('error', 'As datas de início e fim são obrigatórias.');
        }

        $query = InconsistenciaTruckPag::query();

        // Aplicar filtros à query
        if ($request->filled('data_inicio')) {
            $dataInicio = Carbon::createFromFormat('Y-m-d', $request->data_inicio)->startOfDay();
            $query->whereDate('data_inclusao', '>=', $dataInicio);
        }

        if ($request->filled('data_fim')) {
            $dataFim = Carbon::createFromFormat('Y-m-d', $request->data_fim)->endOfDay();
            $query->whereDate('data_inclusao', '<=', $dataFim);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('descricao_bomba')) {
            $query->where('descricao_bomba', $request->descricao_bomba);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('tipo_servico')) {
            $query->where('tipo_servico', $request->tipo_servico);
        }

        Log::info('SQL gerado:', [$query->toSql()]);
        Log::info('Bindings:', $query->getBindings());

        // Executar a consulta com paginação
        $inconsistencias = $query->orderByDesc('data_inclusao')
            ->paginate(20)
            ->appends($request->query());

        if ($request->ajax()) {
            return view('admin.inconsistencias._table_truckpag', compact('inconsistencias'))->render();
        }

        return view('admin.inconsistencias._tab_truckpag', compact('inconsistencias'));
    }

    /**
     * Remover inconsistência ATS
     */
    public function removerAts(Request $request, $id)
    {
        try {
            $inconsistencia = InconsistenciaAts::findOrFail($id);

            // Atualizar o registro como tratado
            DB::connection('pgsql')
                ->table('abastecimento_integracao')
                ->where('id_abastecimento_integracao', $id)
                ->update(['tratado' => true]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inconsistência removida com sucesso!'
                ]);
            }

            return redirect()
                ->route('admin.inconsistencias.index')
                ->with('success', 'Inconsistência removida com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao remover inconsistência ATS: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao remover inconsistência: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao remover inconsistência: ' . $e->getMessage());
        }
    }

    /**
     * Remover inconsistência TruckPag
     */
    public function removerTruckPag(Request $request, $id)
    {
        try {
            $inconsistencia = InconsistenciaTruckPag::findOrFail($id);

            // Atualizar o registro como tratado
            DB::connection('pgsql')
                ->table('abastecimento_truck_pag')
                ->where('transacao', $id)
                ->update(['tratado' => true]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Inconsistência removida com sucesso!'
                ]);
            }

            return redirect()
                ->route('admin.inconsistencias.index')
                ->with('success', 'Inconsistência removida com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao remover inconsistência TruckPag: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao remover inconsistência: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao remover inconsistência: ' . $e->getMessage());
        }
    }

    /**
     * Reprocessar abastecimento ATS
     */
    public function reprocessarAts(Request $request, $id)
    {
        try {
            $inconsistencia = InconsistenciaAts::findOrFail($id);

            // Verificar se a inconsistência é por falta de estoque
            if ($inconsistencia->mensagem != 'Sem estoque de combustível, inserir Nota Fiscal') {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este tipo de inconsistência não pode ser reprocessada automaticamente'
                    ], 422);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Este tipo de inconsistência não pode ser reprocessada automaticamente');
            }

            // Obter informações do tanque
            $bombaInfo = Bomba::where('descricao_bomba', $inconsistencia->descricao_bomba)->first();

            if (!$bombaInfo) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bomba não encontrada'
                    ], 404);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Bomba não encontrada');
            }

            // Verificar estoque disponível
            $estoqueInfo = DB::connection('pgsql')
                ->table('estoque_combustivel')
                ->where('id_tanque', $bombaInfo->id_tanque)
                ->whereNull('data_encerramento')
                ->first();

            if (!$estoqueInfo || $estoqueInfo->quantidade_em_estoque < $inconsistencia->volume) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Estoque insuficiente para reprocessamento'
                    ], 422);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Estoque insuficiente para reprocessamento');
            }

            // Obter o ID do abastecimento ATS
            $abastecimentoAtsId = DB::connection('pgsql')
                ->table('abastecimento_integracao')
                ->where('id_abastecimento_integracao', $id)
                ->value('id_abastecimento_ats');

            if (!$abastecimentoAtsId) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'ID do abastecimento ATS não encontrado'
                    ], 404);
                }

                return redirect()
                    ->back()
                    ->with('error', 'ID do abastecimento ATS não encontrado');
            }

            // Verificar se o abastecimento já foi processado
            $abastecimentoJaProcessado = DB::connection('pgsql')
                ->table('historico_abastecimento_baixar_estoque')
                ->where('id_ats', $abastecimentoAtsId)
                ->where('descricao_bomba', $inconsistencia->descricao_bomba)
                ->exists();

            if ($abastecimentoJaProcessado) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Este abastecimento já foi processado anteriormente'
                    ], 422);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Este abastecimento já foi processado anteriormente');
            }

            // Inserir no histórico de abastecimento
            DB::connection('pgsql')
                ->table('historico_abastecimento_baixar_estoque')
                ->insert([
                    'data_inclusao' => now(),
                    'descricao_bomba' => $inconsistencia->descricao_bomba,
                    'placa' => $inconsistencia->placa,
                    'descricao_veiculo' => $inconsistencia->veiculo ? $inconsistencia->veiculo->descricao_veiculo : null,
                    'volume' => $inconsistencia->volume,
                    'id_ats' => $abastecimentoAtsId,
                    'id_tanque' => $bombaInfo->id_tanque,
                    'id_veiculo_unitop' => $inconsistencia->id_veiculo,
                    'data_abastecimento' => $inconsistencia->data_inclusao
                ]);

            // Chamar função para processar inconsistência
            $resultado = DB::connection('pgsql')->selectOne(
                "SELECT fc_processar_incosistencias_ats_idintegracao(?, ?, ?) as resultado",
                [
                    $inconsistencia->data_inclusao->format('Y-m-d H:i:s'),
                    $inconsistencia->data_inclusao->format('Y-m-d H:i:s'),
                    $id
                ]
            );

            if ($resultado && $resultado->resultado == 1) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Abastecimento reprocessado com sucesso!'
                    ]);
                }

                return redirect()
                    ->route('admin.inconsistencias.index')
                    ->with('success', 'Abastecimento reprocessado com sucesso!');
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Não foi possível reprocessar o abastecimento'
                    ], 500);
                }

                return redirect()
                    ->back()
                    ->with('error', 'Não foi possível reprocessar o abastecimento');
            }
        } catch (Exception $e) {
            Log::error('Erro ao reprocessar abastecimento: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao reprocessar abastecimento: ' . $e->getMessage()
                ], 500);
            }

            return redirect()
                ->back()
                ->with('error', 'Erro ao reprocessar abastecimento: ' . $e->getMessage());
        }
    }

    /**
     * Editar inconsistência ATS
     */
    public function editAts($id)
    {
        $inconsistencia = InconsistenciaAts::where('id_abastecimento_integracao', $id)->first();
        $veiculos = Veiculo::where('situacao_veiculo', true)->orderBy('placa')->get();
        $filiais = InconsistenciaAts::select('id_filial as value', 'nomefilial as label')
            ->orderBy('nomefilial')
            ->get();
        $departamentos = Departamento::orderBy('descricao_departamento')->get();

        // Verificar se a inconsistência é por motivo de placa não encontrada
        $isPlacaNaoEncontrada = (
            strpos(strtolower($inconsistencia->mensagem), 'placa não encontrada') !== false ||
            strpos(strtolower($inconsistencia->mensagem), 'veículo não encontrado') !== false ||
            strpos(strtolower($inconsistencia->mensagem), 'veiculo não encontrado') !== false
        );

        return view('admin.inconsistencias.edit_ats', compact(
            'inconsistencia',
            'veiculos',
            'filiais',
            'departamentos',
            'isPlacaNaoEncontrada'
        ));
    }

    /**
     * Editar inconsistência TruckPag
     */
    public function editTruckPag($id)
    {
        $inconsistencia = InconsistenciaTruckPag::where('id_abastecimento_integracao', $id)->first();
        $veiculos = Veiculo::where('situacao_veiculo', true)->orderBy('placa')->get();
        $filiais = InconsistenciaAts::select('id_filial as value', 'nomefilial as label')
            ->orderBy('nomefilial')
            ->get();
        $departamentos = Departamento::orderBy('descricao_departamento')->get();

        // Verificar se a inconsistência é por motivo de placa não encontrada
        $isPlacaNaoEncontrada = (
            strpos(strtolower($inconsistencia->mensagem), 'placa não encontrada') !== false ||
            strpos(strtolower($inconsistencia->mensagem), 'veículo não encontrado') !== false ||
            strpos(strtolower($inconsistencia->mensagem), 'veiculo não encontrado') !== false
        );

        return view('admin.inconsistencias.edit_truckpag', compact(
            'inconsistencia',
            'veiculos',
            'filiais',
            'departamentos',
            'isPlacaNaoEncontrada'
        ));
    }

    /**
     * Atualizar inconsistência ATS
     */
    public function updateAts(Request $request, $id)
    {
        try {
            $inconsistencia = InconsistenciaAts::where('id_abastecimento_integracao', $id)->first();

            $validated = $request->validate([
                'km_abastecimento' => 'required|numeric|min:0',
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'id_departamento' => 'required|exists:departamento,id_departamento',
                'id_filial' => 'required',
            ]);

            // Buscar KM anterior para validações
            $kmAnterior = $request->km_anterior;

            // Se não encontrar km_anterior, verificar se é o primeiro abastecimento
            if (is_null($kmAnterior)) {
                $kmAnterior = 0;
            }

            // Validar regras de negócio
            if ($validated['km_abastecimento'] <= $kmAnterior) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Atenção: KM Informado é Menor ou Igual o último KM Anterior.');
            }

            if (($validated['km_abastecimento'] - $kmAnterior) > 2800) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Atenção: KM Informado não pode ter mais de 2800 KM à mais do que o KM Anterior.');
            }

            if ($validated['km_abastecimento'] == 1 && $kmAnterior == 0) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Atenção: O KM atual não pode ser igual a 1 e o KM Anterior não pode ser igual a 0!');
            }

            // Verificar se a placa existe
            $placaExiste = DB::connection('pgsql')
                ->table('veiculo')
                ->where('id_veiculo', $validated['id_veiculo'])
                ->exists();

            if (!$placaExiste) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'A placa deste abastecimento não existe na base de dados.');
            }

            // Atualizar no banco
            DB::connection('pgsql')
                ->table('abastecimento_integracao')
                ->where('id_abastecimento_integracao', $id)
                ->update([
                    'km_abastecimento' => $validated['km_abastecimento'],
                    'km_anterior' => $kmAnterior,
                    'id_veiculo_unitop' => $validated['id_veiculo'],
                    'id_filial' => $validated['id_filial'],
                    'id_departamento' => $validated['id_departamento'],
                    'tratado' => true,
                    'id_user_tratado' => Auth::user()->id,
                    'data_tratado' => now()
                ]);

            // Reprocessar o abastecimento
            $retorno = DB::connection('pgsql')
                ->statement("SELECT fc_processar_abastecimento_ats_tratado(?)", [$id]);

            Log::debug('Retorno do reprocessamento ATS: ' . $retorno);

            return redirect()
                ->route('admin.inconsistencias.index')
                ->with('success', 'Inconsistência atualizada e tratada com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao atualizar inconsistência ATS: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar inconsistência TruckPag
     */
    public function updateTruckPag(Request $request, $id)
    {
        try {
            $inconsistencia = InconsistenciaTruckPag::findOrFail($id);

            $validated = $request->validate([
                'km_abastecimento' => 'required|integer|min:0',
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'id_departamento' => 'required|exists:departamento,id_departamento',
                'id_filial' => 'required|exists:system_unit,id',
            ]);

            // Buscar KM anterior para validações
            $kmAnterior = DB::connection('pgsql')
                ->table('v_abastecimento_completo_22_11_2022')
                ->where('placa', function ($query) use ($validated) {
                    $query->select('placa')
                        ->from('veiculo')
                        ->where('id_veiculo', $validated['id_veiculo'])
                        ->limit(1);
                })
                ->where('tipocombustivel', 'NOT IN', ['ALRLA 32', 'Arla 32', 'ARLA', 'Arla'])
                ->where('km_abastecimento', '<', $validated['km_abastecimento'])
                ->max('km_abastecimento');

            // Se não encontrar km_anterior, verificar se é o primeiro abastecimento
            if (is_null($kmAnterior)) {
                $kmAnterior = 0;
            }

            // Validar regras de negócio
            if ($validated['km_abastecimento'] <= $kmAnterior) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Atenção: KM Informado é Menor ou Igual o último KM Anterior.');
            }

            if (($validated['km_abastecimento'] - $kmAnterior) > 2800) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Atenção: KM Informado não pode ter mais de 2800 KM à mais do que o KM Anterior.');
            }

            if ($validated['km_abastecimento'] == 1 && $kmAnterior == 0) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Atenção: O KM atual não pode ser igual a 1 e o KM Anterior não pode ser igual a 0!');
            }

            // Verificar se a placa existe
            $placaExiste = DB::connection('pgsql')
                ->table('veiculo')
                ->where('id_veiculo', $validated['id_veiculo'])
                ->exists();

            if (!$placaExiste) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'A placa deste abastecimento não existe na base de dados.');
            }

            // Atualizar no banco
            DB::connection('pgsql')
                ->table('abastecimento_truck_pag')
                ->where('transacao', $id)
                ->update([
                    'hodometro' => $validated['km_abastecimento'],
                    'km_anterior' => $kmAnterior,
                    'id_veiculo_unitop' => $validated['id_veiculo'],
                    'id_departamento' => $validated['id_departamento'],
                    'id_filial' => $validated['id_filial'],
                    'tratado' => true
                ]);

            // Reprocessar o abastecimento
            DB::connection('pgsql')
                ->statement("SELECT fc_processar_abastecimento_truckpag_tratado(?)", [$id]);

            return redirect()
                ->route('admin.inconsistencias.index')
                ->with('success', 'Inconsistência atualizada e tratada com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao atualizar inconsistência TruckPag: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Erro ao atualizar: ' . $e->getMessage());
        }
    }

    /**
     * Método para buscar KM automaticamente a partir da placa e data
     */
    public function getKmInfo(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'id_veiculo' => 'required|exists:veiculo,id_veiculo',
                'data_abastecimento' => 'required|date_format:Y-m-d H:i:s',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Buscar placa do veículo
            $placa = DB::connection('pgsql')
                ->table('veiculo')
                ->where('id_veiculo', $request->id_veiculo)
                ->value('placa');

            if (!$placa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Placa não encontrada'
                ], 404);
            }

            // Buscar ID do dispositivo de telemetria (baseado no código legado)
            $idSascar = DB::connection('pgsql')
                ->table('veiculo')
                ->where('placa', $placa)
                ->value('id_sascar');

            // Buscar KM anterior nos abastecimentos
            $kmAnterior = DB::connection('pgsql')
                ->table('v_abastecimento_completo_22_11_2022')
                ->where('placa', $placa)
                ->where('tipocombustivel', 'NOT IN', ['ALRLA 32', 'Arla 32', 'ARLA', 'Arla'])
                ->where('data_inicio', '<', $request->data_abastecimento)
                ->where('data_inicio', '>', DB::connection('pgsql')->raw("'2024-01-01'"))
                ->max('km_abastecimento');

            $kmAbastecimento = null;

            // Se tiver ID do dispositivo de telemetria, buscar KM atual
            if ($idSascar) {
                // Buscar hodômetro do período do abastecimento
                $dataAbastecimento = Carbon::parse($request->data_abastecimento);

                $kmTelemetria = DB::connection('pgsql')
                    ->table('pacoteposicaorangjson')
                    ->where('idveiculo', $idSascar)
                    ->whereBetween('datapacote', [
                        $dataAbastecimento,
                        $dataAbastecimento->copy()->addMinutes(10)
                    ])
                    ->max('odometroexato');

                if ($kmTelemetria) {
                    $kmAbastecimento = $kmTelemetria;
                }
            }

            // Buscar id_seeflex
            $idSeeflex = DB::connection('pgsql')
                ->table('seeflex_veiculo')
                ->where('placa', 'like', "%{$placa}%")
                ->value('id');

            // Se tiver ID do Seeflex e não encontrou km por telemetria, buscar pelo horimetro
            if ($idSeeflex && !$kmAbastecimento) {
                $horimetro = DB::connection('pgsql')
                    ->table('seefle_movimento_veiculo as t')
                    ->join('seeflex_veiculo as vi', 'vi.id', '=', 't.veiculo_id')
                    ->where('vi.id', $idSeeflex)
                    ->whereBetween(
                        DB::connection('pgsql')
                            ->raw("(t.data_movimento::TEXT ||' '||t.hora::TEXT)::TIMESTAMP"),
                        [$request->data_abastecimento, DB::connection('pgsql')->raw("'{$request->data_abastecimento}'::TIMESTAMP +'01:00:00'::INTERVAL")]
                    )
                    ->whereNotNull('t.horimetro')
                    ->where('t.tipo', 'MOVIMENTO')
                    ->orderByDesc(DB::connection('pgsql')->raw('(t.horimetro::integer/60)'))
                    ->value(DB::connection('pgsql')->raw('(t.horimetro::integer/60) as horimetro'));

                if ($horimetro) {
                    $kmAbastecimento = $horimetro;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'km_anterior' => $kmAnterior ?? 0,
                    'hodometro' => $kmAbastecimento,
                    'placa' => $placa
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao buscar informações de KM: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar informações de KM: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter dados de referência para filtros
     */
    protected function getReferenceDatas()
    {
        return Cache::remember('inconsistencias_reference_data', now()->addHours(12), function () {
            $veiculos = Veiculo::where('situacao_veiculo', true)
                ->orderBy('placa')
                ->get(['id_veiculo as value', 'placa as label']);

            $bombas = Bomba::orderBy('descricao_bomba')
                ->get(['descricao_bomba as value', 'descricao_bomba as label']);

            $departamentos = Departamento::orderBy('descricao_departamento')
                ->get(['id_departamento as value', 'descricao_departamento as label']);

            $filiais = InconsistenciaAts::select('id_filial as value', 'nomefilial as label')
                ->orderBy('nomefilial')
                ->get();

            $tiposCombustivel = collect([
                ['value' => 'DIESELS10', 'label' => 'DIESEL S10'],
                ['value' => 'ARLA32', 'label' => 'ARLA 32'],
                ['value' => 'DIESELS500', 'label' => 'DIESEL S500'],
            ]);

            $tiposServico = collect([
                ['value' => 'COMBUSTIVEL', 'label' => 'Combustível'],
                ['value' => 'ARLA', 'label' => 'Arla']
            ]);

            return [
                'veiculos' => $veiculos,
                'bombas' => $bombas,
                'departamentos' => $departamentos,
                'filiais' => $filiais,
                'tiposCombustivel' => $tiposCombustivel,
                'tiposServico' => $tiposServico
            ];
        });
    }

    /**
     * Buscar informações adicionais do veículo selecionado
     */
    public function getVeiculoInfo($id)
    {
        try {
            $veiculo = Veiculo::findOrFail($id);

            // Buscar informações de departamento e filial relacionadas ao veículo
            $departamento = $veiculo->id_departamento;
            $filial = $veiculo->id_filial;

            return response()->json([
                'success' => true,
                'data' => [
                    'id_departamento' => $departamento,
                    'id_filial' => $filial
                ]
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao buscar informações do veículo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar informações do veículo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function searchVeiculos(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $veiculos = Veiculo::where('situacao_veiculo', true)
            ->where('placa', 'ilike', "%{$term}%")
            ->orderBy('placa')
            ->limit(15)
            ->get(['id_veiculo as value', 'placa as label']);

        return response()->json($veiculos);
    }

    public function searchFornecedores(Request $request)
    {
        $term = $request->input('term', '');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $fornecedores = Fornecedor::where(function ($query) use ($term) {
            $query->where('nome_fornecedor', 'ilike', "%{$term}%")
                ->orWhere('apelido_fornecedor', 'ilike', "%{$term}%")
                ->orWhere('cnpj_fornecedor', 'ilike', "%{$term}%");
        })
            ->orderBy('nome_fornecedor')
            ->limit(15)
            ->get(['id_fornecedor as value', 'nome_fornecedor as label', 'cnpj_fornecedor']);

        return response()->json($fornecedores);
    }
}
