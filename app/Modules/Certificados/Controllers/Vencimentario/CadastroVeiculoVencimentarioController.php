<?php

namespace App\Modules\Certificados\Controllers\Vencimentario;

use App\Http\Controllers\Controller;
use App\Services\IntegradorSmartecService;
use App\Models\SmartecCrlv;
use App\Models\SmartecVeiculo;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CadastroVeiculoVencimentarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = SmartecVeiculo::query();

            if ($request->filled('placa')) {
                $query->where('placa', $request->placa);
            }

            $smartecVeiculo = $query->latest('id_smartec_veiculo')
                ->paginate(15)
                ->appends($request->query());

            $veiculos = $this->getVeiculosFrequentes();

            $smartecCrlv = $this->getSmartecCrlv();


            return view('admin.cadastroveiculovencimentario.index', compact('smartecVeiculo', 'veiculos', 'smartecCrlv'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de Solicitação: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de tipos de Solicitação.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $id)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function show($id, IntegradorSmartecService $smartecService)
    {
        try {
            $veiculo = SmartecVeiculo::findOrFail($id);
            $renavam = $veiculo->renavam;
            $data_e = now()->format('d/m/Y');

            $response = $smartecService->consultarVeiculo(
                '',
                '',
                '',
                '',
                '',
                $data_e,
                $renavam,
                'CRLV'
            );

            $data = $response;

            if (empty($data) || !isset($data[0]->Renavam)) {
                return response()->json([
                    'error' => true,
                    'message' => 'CRLV não encontrado.'
                ], 404);
            }

            $crlv = SmartecCrlv::where('renavam', $renavam)->first();

            foreach ($data as $value) {
                if (!$crlv) {
                    $crlv = new SmartecCrlv();
                }

                $crlv->renavam = $value->Renavam ?? null;
                $crlv->licenciamento = $value->Licenciamento ?? null;
                $crlv->uf = $value->Uf ?? null;
                $crlv->municipio = $value->Municipio ?? null;
                $crlv->url = $value->Url ?? null;
                $crlv->save();
            }

            return response()->json([
                'veiculo' => $veiculo,
                'crlv' => [
                    [
                        'renavam' => $crlv->renavam,
                        'licenciamento' => $crlv->licenciamento,
                        'uf' => $crlv->uf,
                        'municipio' => $crlv->municipio,
                        'url' => $crlv->url,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'error' => true,
                'message' => 'Erro ao buscar CRLV: ' . $e->getMessage(),
            ], 500);
        }
    }





    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function onAction(Request $request, IntegradorSmartecService $smartecService)
    {
        Log::info('Entrou no método onAction');
        try {
            $action = $request->input('action');
            Log::debug('Valor de action no request:', ['action' => $action]);

            if ($action !== 'cadastrar') {
                Log::info('Action diferente de cadastrar, retornando.');
                return back();
            }

            // Receber as placas selecionadas ou não no filtro.
            $veiculos = $request->input('placa', null);

            Log::debug('Valor recebido para veiculos (placa):', ['veiculos_raw' => $veiculos]);

            if ($veiculos && !is_array($veiculos)) {
                $veiculos = [$veiculos]; // transforma string em array de 1 elemento
                Log::debug('Veiculos convertido para array:', ['veiculos' => $veiculos]);
            }

            $tratar_todas = $request->input('selecionar_todos', null);
            Log::debug('Valor de tratar_todas (selecionar_todos):', ['tratar_todas' => $tratar_todas]);

            $data_e = now()->format('d/m/Y');
            Log::debug('Data atual formatada:', ['data_e' => $data_e]);

            if ($tratar_todas == null || $tratar_todas == 2) {
                Log::info('Tratar apenas veículos selecionados.');

                if (!empty($veiculos)) {
                    foreach ($veiculos as $placa) {
                        Log::info("Verificando veículo para integração", ['placa' => $placa]);

                        // Verificar se o veículo já foi integrado
                        $veiculo_smart = SmartecVeiculo::where('placa', $placa)->first();

                        if (!$veiculo_smart) {
                            Log::info("Veículo não encontrado na tabela SmartecVeiculo", ['placa' => $placa]);
                        } else {
                            Log::info("Veículo encontrado na tabela SmartecVeiculo", [
                                'placa' => $placa,
                                'integrado' => $veiculo_smart->integrado
                            ]);
                        }

                        if (!$veiculo_smart || $veiculo_smart->integrado == false) {
                            $veiculo_info = DB::table('veiculo as v')
                                ->join('municipio as mm', 'mm.id_municipio', '=', 'v.id_municipio')
                                ->select('v.renavam', 'v.placa', 'mm.uf')
                                ->where('v.placa', $placa)
                                ->where('v.is_terceiro', '!=', true)
                                ->where('v.situacao_veiculo', '!=', false)
                                ->first();

                            if ($veiculo_info) {
                                Log::info('Informações do veículo encontradas:', (array)$veiculo_info);
                            } else {
                                Log::warning("Nenhuma informação encontrada para o veículo", ['placa' => $placa]);
                            }
                        } else {
                            Log::info("Veículo já integrado, ignorando", ['placa' => $placa]);
                        }
                    }

                    return back()->with('success', "Placas Cadastradas, o período de processamento leva até 24hr.");
                } else {
                    Log::warning('Nenhuma placa selecionada para processar.');
                }
            } else {
                Log::info('Tratar todos os veículos.');

                $veiculos_info = DB::table('veiculo as v')
                    ->join('municipio as mm', 'mm.id_municipio', '=', 'v.id_municipio')
                    ->select('v.renavam', 'v.placa', 'mm.uf')
                    ->where('v.is_terceiro', '<>', true)
                    ->where('v.situacao_veiculo', '<>', false)
                    ->get();

                Log::info('Quantidade de veículos encontrados para integração:', ['count' => $veiculos_info->count()]);

                foreach ($veiculos_info as $veiculo_info) {
                    Log::info('Iniciando integração com placa', [
                        'placa' => $veiculo_info->placa,
                        'uf' => $veiculo_info->uf,
                        'renavam' => $veiculo_info->renavam,
                    ]);

                    $data = $smartecService->consultarVeiculo(
                        $veiculo_info->placa,
                        $veiculo_info->uf,
                        "UNITOP TESTE",
                        "",
                        "33070814000151",
                        $data_e,
                        $veiculo_info->renavam,
                        "CADASTRAR",
                    );
                    Log::debug('Resposta da integração CADASTRAR', ['response' => $data]);

                    $data = $smartecService->consultarVeiculo(
                        $veiculo_info->placa,
                        $veiculo_info->uf,
                        "UNITOP TESTE",
                        "",
                        "33070814000151",
                        $data_e,
                        $veiculo_info->renavam,
                        "CADASTRARCNPJ",
                    );
                    Log::debug('Resposta da integração CADASTRARCNPJ', ['response' => $data]);
                }

                return back()->with('success', "Placas Cadastradas, o período de processamento leva até 24hr.");
            }
        } catch (\Exception $e) {
            Log::error('Erro na integração Smartec:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function consultarVeiculo(Request $request, IntegradorSmartecService $smartecService, $renavam)
    {
        Log::info('Entrou no método consultarVeiculo');
        try {
            $dataAtual = now()->format('d/m/Y');

            // Chamada da API Smartec via classe de integração
            $response = $smartecService->consultarVeiculo(
                '',
                '',
                '',
                '',
                '',
                $dataAtual,
                $renavam,
                'CONSULTAR',
            );

            foreach ($response as $value) {
                if (isset($value->PLACA)) {
                    // Prepara os dados do veículo
                    $dadosVeiculo = [
                        'placa'                  => $value->PLACA,
                        'renavam'                => $value->RENAVAM,
                        'chassi'                 => $value->CHASSI,
                        'municipio'              => $value->MUNICIPIO,
                        'uf'                     => $value->UF,
                        'tipo'                   => $value->TIPO,
                        'combustivel'            => $value->COMBUSTIVEL,
                        'cor'                    => $value->COR,
                        'marca'                  => $value->MARCA,
                        'ano_fabricacao'         => $value->ANO_FABRICACAO,
                        'cpf_cnpj'               => $value->Cpf_Cnpj,
                        'proprietario'           => $value->PROPRIETARIO,
                        'licenciamento_vigente'  => $value->LICENCIADO_VIGENTE,
                        'exercicio_licenciamento' => $value->EXERCICIO_LICENCIAMENTO,
                        'restricoes'             => $value->RESTRICOES,
                        'ativo'                  => $value->Ativo,
                        'obs'                    => $value->Obs,
                        'data_alteracao'         => now(),
                        'integrado'              => true,
                    ];

                    // Atualiza ou cria com base na PLACA
                    SmartecVeiculo::updateOrCreate(
                        $dadosVeiculo               // dados para atualizar ou criar
                    );
                }
            }
            return redirect()->back()->with('success', 'Consulta e atualização do veículo realizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro na consulta do veículo: ' . $e->getMessage());
        }
    }

    public function getVeiculosFrequentes()
    {
        return Cache::remember('veiculos_ativos_select', now()->addHour(), function () {
            return Veiculo::select('placa as value', 'placa as label') // <- aqui o ajuste
                ->orderBy('placa')
                ->limit(50)
                ->get();
        });
    }

    public function getSmartecCrlv()
    {
        return SmartecCrlv::orderBy('id_smartec_crlv')
            ->get();
    }
}
