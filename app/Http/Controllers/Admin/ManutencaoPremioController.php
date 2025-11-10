<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DistanciaMotoristaAuxiliar;
use App\Models\DistanciasSemMotoristaPremio;
use App\Models\Pessoal;
use App\Models\Veiculo;
use App\Models\VPremioDeflatores;
use App\Models\VPremioDistanciaSemLogin;
use App\Models\VPremioMotoristaKmValor;
use App\Models\VPremioMotoristaTotal;
use App\Models\VPremioPagamento;
use App\Models\VPremioUnionPlacasDistancia;
use App\Models\VPremioVeiculosSemLoginTotal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManutencaoPremioController extends Controller
{
    public function index(Request $request)
    {
        $totalMotoristas = VPremioMotoristaTotal::count('id_mot_unitop');
        $totalDistancia = VPremioMotoristaKmValor::sum('distancia');
        $totalValorPremio = VPremioMotoristaKmValor::sum('valor_premio');
        $veiculosSemLogin = VPremioVeiculosSemLoginTotal::count();
        $distanciaSemLogin = VPremioDistanciaSemLogin::count('distancia');
        $distanciaInconsistencia = VPremioUnionPlacasDistancia::count('km_rodado');
        $distanciaInconsistenciaMensal = VPremioUnionPlacasDistancia::count('km_rodado');

        $distanciasPorMes = VPremioMotoristaKmValor::select(
            DB::raw("TO_CHAR(data_inicial, 'Mon') as mes"),
            DB::raw("SUM(distancia) as total")
        )
            ->groupBy(DB::raw("TO_CHAR(data_inicial, 'Mon')"))
            ->orderBy(DB::raw("MIN(data_inicial)"))
            ->get();

        $query = VPremioMotoristaKmValor::query();
        $labels = $distanciasPorMes->pluck('mes');
        $valores = $distanciasPorMes->pluck('total');

        if ($request->filled('cod_premio')) {
            $query->where('cod_premio', $request->input('cod_premio'));
        }
        if ($request->filled('nome_motorista')) {
            $query->where('nome_motorista', $request->input('nome_motorista'));
        }

        $mediaKmPorLitro = VPremioUnionPlacasDistancia::avg('media') ?? 0;

        $user = VPremioMotoristaKmValor::select('nome_motorista as value', 'nome_motorista as label')
            ->orderBy('nome_motorista')
            ->limit(30)
            ->get();

        $listagem = $query->latest('cod_premio')
            ->paginate(10)
            ->appends($request->query());

        return view('admin.manutencaopremio.index', compact(
            'totalMotoristas',
            'totalDistancia',
            'totalValorPremio',
            'veiculosSemLogin',
            'mediaKmPorLitro',
            'distanciaSemLogin',
            'distanciaInconsistencia',
            'distanciaInconsistenciaMensal',
            'user',
            'listagem',
            'labels',
            'valores',
            'totalDistancia'
        ));
    }

    public function show(Request $request)
    {
        $query = VPremioDistanciaSemLogin::query();

        if ($request->filled('placa')) {
            // Mude para buscar por placa se quiser usar placa como identificador
            $query->where('placa', $request->input('placa'));
        }

        $placa = VPremioDistanciaSemLogin::select('placa as value', 'placa as label')
            ->distinct()
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $listagem = $query->latest('id_veiculo')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.manutencaopremio.show', compact('placa', 'listagem'));
    }

    public function edit($id)
    {
        $veiculo = VPremioDistanciaSemLogin::findOrFail($id);

        $criteria_id_motorista = Pessoal::query()
            ->where('ativo', true) // exemplo de critério
            ->orderBy('id_pessoal', 'asc');

        // Monta os dados para o select (value = id, label = nome)
        $motorista = $criteria_id_motorista
            ->select('id_pessoal as value', 'nome as label')
            ->limit(30)
            ->get();


        return view('admin.manutencaopremio.edit', compact('motorista', 'veiculo'));
    }

    public function update(Request $request, $id)
    {
        Log::info('Entrando no método');
        try {
            $request->validate([
                'idmotorista' => 'required',
                'distancia' => 'required|numeric|min:0',
            ], [
                'motorista.required' => 'Atenção: Motorista não informado.',
                'distancia.required' => 'Atenção: Distância não informada.',
            ]);

            // Busca o objeto da view
            $veiculo = VPremioDistanciaSemLogin::with('distanciaAuxiliar')->findOrFail($id);

            // Recupera dados do motorista
            $pessoal = Pessoal::findOrFail($request->idmotorista);
            $id_motorista_sascar = $pessoal->id_sascar;

            // Dados da distância vinculada
            $distanciaAtual = $veiculo->distanciaAuxiliar;
            $distanciaSuplementar = $distanciaAtual->distancia;
            $novaDistancia = $request->distancia;
            $saldo = $distanciaSuplementar - $novaDistancia;

            Log::info('parte 2');

            if ($novaDistancia > $distanciaSuplementar) {
                return back()->with('error', 'Atenção: não é permitido adicionar uma distância maior que a atual.');
            }

            Log::info('entrando no insert');

            DB::beginTransaction();

            // Atualiza o registro vinculado pela relação
            $distanciaAtual->update([
                'distancia' => $novaDistancia,
                'idmotorista' => $id_motorista_sascar,
            ]);

            // Cria o saldo se sobrar
            if ($saldo > 0) {
                DistanciaMotoristaAuxiliar::create([
                    'distancia' => $saldo,
                    'idveiculo' => $veiculo->id_veiculo,
                    'idmotorista' => 0,
                    'data_' => $veiculo->data_,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.manutencaopremio.show')
                ->with('success', 'Registro alterado com sucesso.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar distância: ' . $e->getMessage());
            return back()->with('error', 'Erro ao atualizar distância: ' . $e->getMessage());
        }
    }

    public function editarMotorista($id)
    {

        $veiculo = VPremioMotoristaKmValor::where('id_mot_unitop', $id)->firstOrFail();

        $premioPagamento = VPremioPagamento::where('nome', $veiculo->nome_motorista)->get();

        $premioUniao = VPremioUnionPlacasDistancia::where('id_motorista', $veiculo->id_mot_unitop)
            ->where('tipo', 'PREMIO RV')
            ->orderByDesc('id_motorista')
            ->get();

        $premioUniaoMensal = VPremioUnionPlacasDistancia::where('id_motorista', $veiculo->id_mot_unitop)
            ->where('tipo', 'PREMIO MENSAL')
            ->orderByDesc('id_motorista')
            ->get();

        $premioDeflatores = VPremioDeflatores::where('nome', $veiculo->nome_motorista)->get();

        // ======================
        // DASHBOARD — SOMAS
        // ======================
        $distanciaRv = VPremioUnionPlacasDistancia::where('id_motorista', $veiculo->id_mot_unitop)
            ->where('tipo', 'PREMIO RV')
            ->sum('km_rodado');

        $distanciaMensal = VPremioUnionPlacasDistancia::where('id_motorista', $veiculo->id_mot_unitop)
            ->where('tipo', 'PREMIO MENSAL')
            ->sum('km_rodado');

        $valorPremio = VPremioMotoristaKmValor::where('id_mot_unitop', $veiculo->id_mot_unitop)
            ->sum('valor_premio');

        // ======================
        // DADOS PARA OS GRÁFICOS
        // ======================

        // --- VALOR DO PRÊMIO (REAL OU INTERPOLADO) ---
        $valorPremioQuery = VPremioMotoristaKmValor::where('id_mot_unitop', $veiculo->id_mot_unitop)
            ->selectRaw("DATE_TRUNC('month', data_final) AS mes, SUM(valor_premio) AS total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total')
            ->toArray();

        $valorPremioData = count($valorPremioQuery) > 1
            ? $valorPremioQuery
            : collect(range(1, 6))->map(fn($i) => round(($valorPremioQuery[0] ?? $valorPremio) * ($i / 6)))->toArray();


        // --- DISTÂNCIA RV (REAL OU INTERPOLADA) ---
        $distanciaRvQuery = VPremioUnionPlacasDistancia::where('id_motorista', $veiculo->id_mot_unitop)
            ->where('tipo', 'PREMIO RV')
            ->selectRaw("DATE_TRUNC('month', data_final) AS mes, SUM(km_rodado) AS total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total')
            ->toArray();

        $distanciaRvData = count($distanciaRvQuery) > 1
            ? $distanciaRvQuery
            : collect(range(1, 6))->map(fn($i) => round(($distanciaRvQuery[0] ?? $distanciaRv) * ($i / 6)))->toArray();


        // --- DISTÂNCIA MENSAL (REAL OU INTERPOLADA) ---
        $distanciaMensalQuery = VPremioUnionPlacasDistancia::where('id_motorista', $veiculo->id_mot_unitop)
            ->where('tipo', 'PREMIO MENSAL')
            ->selectRaw("DATE_TRUNC('month', data_final) AS mes, SUM(km_rodado) AS total")
            ->groupBy('mes')
            ->orderBy('mes')
            ->pluck('total')
            ->toArray();

        $distanciaMensalData = count($distanciaMensalQuery) > 1
            ? $distanciaMensalQuery
            : collect(range(1, 6))->map(fn($i) => round(($distanciaMensalQuery[0] ?? $distanciaMensal) * ($i / 6)))->toArray();

        return view('admin.manutencaopremio.editar_motorista', compact(
            'premioPagamento',
            'veiculo',
            'premioUniao',
            'premioDeflatores',
            'premioUniaoMensal',
            'distanciaRv',
            'distanciaMensal',
            'valorPremio',
            'valorPremioData',
            'distanciaRvData',
            'distanciaMensalData'
        ));
    }


    public function update_motorista(Request $request, $id)
    {
        $request->validate([
            'idmotorista' => 'required|int',
            'km_sem_mot'   => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            Log::info('entrou aqui');
            $idmot   = $request->input('idmotorista');
            $km      = number_format($request->input('km_sem_mot'), 1, '.', '');

            // 🔹 Buscar último código de prêmio
            $cod_premio = DB::table('premio_superacao_vf')
                ->orderByDesc('id_premio_superacao')
                ->limit(1)
                ->value('id_premio_superacao');

            // 🔹 Buscar ID do motorista pelo id_sascar
            $motorista = DB::table('pessoal')
                ->where('id_pessoal', $idmot)
                ->orWhere('id_sascar', $idmot)
                ->first();


            if (!$motorista) {
                return redirect()->route('admin.manutencaopremio.editKm')->with('error', 'Motorista não encontrado.');
            }

            Log::info('Dados recebidos:', [
                'id_motorista' => $idmot,
                'km_sem_mot' => $km,
                'motorista' => $motorista,
            ]);

            $idmot = $motorista->id_pessoal;

            // 🔹 Buscar informações da distância
            $distancia = DB::table('distancias_sem_motorista_premio')
                ->where('id_distancia_sem', $id)
                ->first();

            if (!$distancia) {
                return redirect()->route('admin.manutencaopremio.index')->with('error', 'Registro de distância não encontrado.');
            }

            $kmbanco = $distancia->km_sem_mot;
            $dataini = $distancia->data_inicial;
            $datafin = $distancia->data_final;

            // 🔹 Calcular diferença de KM
            if ($kmbanco >= $km) {
                $kmresutl = $kmbanco - $km;
            } else {
                return redirect()
                    ->back()
                    ->with('error', 'Não será possível gravar esta quilometragem, pois é maior que a distância existente pela placa!');
            }

            // 🔹 Inserir na tabela premio_base_motorista
            DB::table('premio_base_motorista')->insertUsing(
                [
                    'data_inclusao',
                    'id_motorista',
                    'id_veiculo',
                    'subcategoria',
                    'km_logado',
                    'media',
                    'id_franquia',
                    'tipo_distancia',
                    'data_inicial',
                    'data_final'
                ],
                DB::table('distancias_sem_motorista_premio as ds')
                    ->selectRaw('CURRENT_TIMESTAMP, ?, ds.id_veiculo, ds.subcategoria, ?, ds.media, ds.id_franquia, ?, ?, ?', [
                        $idmot,
                        $km,
                        'DISTANCIA SEM LOGIN',
                        $dataini,
                        $datafin
                    ])
                    ->where('ds.id_distancia_sem', $id)
            );

            // 🔹 Chamar função SQL (fc_premio_mensal_calculo_base)
            DB::select("SELECT * FROM fc_premio_mensal_calculo_base(?, ?, ?)", [
                $dataini,
                $datafin,
                $cod_premio
            ]);

            // 🔹 Atualizar o registro principal
            DB::table('distancias_sem_motorista_premio')
                ->where('id_distancia_sem', $id)
                ->update([
                    'km_sem_mot'   => $kmresutl,
                    'id_motorista' => $idmot,

                ]);

            DB::commit();

            return redirect()
                ->route('admin.manutencaopremio.index')
                ->with('success', 'KM atribuído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atribuir KM: ' . $e->getMessage());
            return redirect()
                ->route('admin.manutencaopremio.index')
                ->with('error', 'Erro ao atribuir KM: ' . $e->getMessage());
        }
    }

    public function editKm($id)
    {
        $distancia = DistanciasSemMotoristaPremio::findOrFail($id);


        $criteria_id_motorista = Pessoal::query()
            ->where('ativo', true) // exemplo de critério
            ->orderBy('id_pessoal', 'asc');

        // Monta os dados para o select (value = id, label = nome)
        $motorista = $criteria_id_motorista
            ->select('id_pessoal as value', 'nome as label')
            ->get();

        return view('admin.manutencaopremio.form_km', compact('distancia', 'motorista'));
    }

    public function modalDistancia(Request $request)
    {
        $query = DistanciasSemMotoristaPremio::with('veiculo', 'motorista');

        // Filtro por placa
        if ($request->filled('placa')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('placa', 'ilike', '%' . $request->placa . '%');
            });
        }

        // Filtro por data inicial
        if ($request->filled('data_inicial')) {
            $query->whereDate('created_at', '>=', $request->data_inicial);
        }

        // Filtro por data final
        if ($request->filled('data_final')) {
            $query->whereDate('created_at', '<=', $request->data_final);
        }

        $listagem = $query->latest('id_distancia_sem')->limit(50)->get();

        // Se for requisição AJAX, retorna apenas o HTML da tabela
        if ($request->ajax()) {
            return view('admin.manutencaopremio._table_distancias', compact('listagem'))->render();
        }

        return view('admin.manutencaopremio._modal', compact('listagem'));
    }


    public function search(Request $request)
    {
        try {
            $term = strtolower($request->get('term'));

            $premio = Cache::remember('veiculoPremio_search_' . $term, now()->addMinutes(30), function () use ($term) {
                return VPremioDistanciaSemLogin::selectRaw('placa as value, placa as label')
                    ->whereRaw('LOWER(placa) LIKE ?', ["%{$term}%"])
                    ->groupBy('placa')
                    ->orderBy('placa')
                    ->limit(30)
                    ->get();
            });

            return response()->json($premio);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar veículos: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar veículos.'], 500);
        }
    }

    public function getById($id)
    {
        // Se $id agora é a placa, busque pela placa
        $premio = Cache::remember('premio_' . $id, now()->addHours(24), function () use ($id) {
            return VPremioDistanciaSemLogin::where('placa', $id)->firstOrFail();
        });

        return response()->json([
            'value' => $premio->placa,  // Retorna a placa como valor
            'label' => $premio->placa,  // Retorna a placa como label
        ]);
    }
}
