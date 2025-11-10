<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoIntegracao;
use Illuminate\Http\Request;
use App\Models\AbastecimentoTruckPag;
use App\Models\Veiculo;
use App\Models\Bomba;
use App\Models\HistoricoAbastecimentoBaixarEstoque;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;



class AbastecimentoTruckPagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AbastecimentoTruckPag::query();

        if ($request->filled('data_inicio_transacao')) {
            $query->where('datatransacao', '>=', $request->data_inicio_transacao);
        }

        if ($request->filled('data_final_transacao')) {
            $query->where('datatransacao', '<=', $request->data_final_transacao);
        }

        $integracoes = $query->latest('transacao')
            ->where('servico', '=', 'ABASTECIMENTO')
            ->paginate(40)
            ->appends($request->query());

        if ($request->header('HX-Request')) {
            return view('admin.abastecimentostruckpag._table', compact('integracoes'));
        }

        return view('admin.abastecimentostruckpag.index', compact('integracoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $veiculos = Veiculo::select('placa as label', 'id_veiculo as value')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->orderBy('placa')->get()->toArray();

        $bombas = Bomba::select('descricao_bomba as label', 'id_bomba as value')->orderBy('descricao_bomba')->get()->toArray();


        return view('admin.abastecimentostruckpag.create', compact('veiculos', 'bombas'));
    }

    public function onProcessarTruckPag(Request $request)
    {
        try {
            $data         = implode('-', array_reverse(explode('/', $request->data_inicio_transacao)));
            $datamesCINCO = '01-10-2023';

            $timestamp1 = strtotime($data);
            $timestamp2 = strtotime($datamesCINCO);

            if ($timestamp1 >= $timestamp2) {
                $data_inicio = implode('', array_reverse(explode('/', $request->data_inicio_transacao)));
                $data_final = implode('', array_reverse(explode('/', $request->data_final_transacao)));

                $this->gettruckpag($data_inicio, $data_final);
            } else {
                return response()->json(['error' => 'Atenção: Não será possível fazer o reprocessamento da data informada, solicite ao Suporte Unitop'], 200);
            }

            return redirect()->route('admin.abastecimentostruckpag.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Processamento realizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function gettruckpag($dataini, $datafin)
    {
        try {
            set_time_limit(120); // Permite execução de até 120 segundos

            $token = AbastecimentoTruckPagController::gerarToken();
            $km_anterior = 0;
            $tratado = null;
            $idveiculo = null;
            $placa_tk = null;
            $placa = null;
            $transacao = null;
            $id_veiculo_unitop = null;

            $url = "https://api.truckpag.com.br/Transacoes?dtini=$dataini&dtfim=$datafin&todas=S";

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas se necessário, remover em produção
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Tempo limite em segundos
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // Tempo limite para conexão
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer $token",
                "Accept: application/json"
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($response === false) {
                LOG::error('Erro no cURL: ' . $error);
            }

            $data = json_decode($response, true);

            if ($data === null) {
                LOG::error('Erro ao decodificar JSON: ' . json_last_error_msg());
                return null;
            }

            //---------------------------------------------------------------------------------------------------------------------------------
            //apagando antes de inserir
            AbastecimentoTruckPag::whereBetween('datatransacao', [$dataini, $datafin])
                ->where(function ($query) {
                    $query->whereNull('tratado')
                        ->orWhere('tratado', false);
                })
                ->where('servico', 'ABASTECIMENTO')
                ->delete();
            //---------------------------------------------------------------------------------------------------------------------------------

            foreach ($data['Transacoes'] as $abastecimento) {
                $id = $abastecimento['Transacao'];
                $idretransacao = null;

                if ($abastecimento['Servico'] == 'ABASTECIMENTO') { //LISTAR APEANAS ABASTECIMENTO
                    $objects = AbastecimentoTruckPag::where('transacao', $id)
                        ->select('transacao', 'tratado')
                        ->first();

                    $transacao = $objects->transacao;
                    if ($objects->tratado == null || empty($objects->tratado)) {
                        $tratado = false;
                    } else {
                        $tratado = $objects->tratado;
                    }

                    //buscar a Placa para TK
                    $objects = Veiculo::where('placa', $placa)
                        ->select('possui_placa_tk')
                        ->first();

                    $placa_tk = $objects->possui_placa_tk ?? null;
                    if (empty($placa_tk) || $placa_tk == null) {
                        $placa_tk = null;
                    }

                    //---------------------------------------------------------------------------------------------------------------------------------
                    $placa = $placa_tk == null ? $abastecimento['Placa'] : $placa_tk;


                    $objects = DB::connection('pgsql')->select("SELECT * FROM get_ultimo_km_abastecimento(?, ?)", [$placa, $abastecimento['Hodometro']]);


                    $km_anterior = $objects[0]->get_ultimo_km_abastecimento;

                    //---------------------------------------------------------------------------------------------------------------------------------

                    //---------------------------------------------------------------------------------------------------------------------------------
                    //Incluir ID unitop

                    $objects = Veiculo::from('veiculo as p')
                        ->whereRaw("p.placa @@ ?", [$placa])
                        ->select('id_veiculo')
                        ->first();

                    $id_veiculo_unitop =  $objects->id_veiculo;
                    //---------------------------------------------------------------------------------------------------------------------------------
                }

                if ($tratado != true && $transacao == null && $abastecimento['Servico'] == 'ABASTECIMENTO') {

                    $objeto = new AbastecimentoTruckPag();
                    $objeto->transacao           = $abastecimento['Transacao'];
                    $objeto->datatransacao       = $abastecimento['DataTransacao'];
                    $objeto->hodometro           = $abastecimento['Hodometro'];
                    $objeto->valor               = $abastecimento['Valor'];
                    $objeto->litragem            = $abastecimento['Litragem'];
                    $objeto->codcombustivel      = $abastecimento['CodCombustivel'];
                    $objeto->nomecombustivel     = $abastecimento['NomeCombustivel'];
                    $objeto->servico             = $abastecimento['Servico'];
                    $objeto->tipoabastecimento   = $abastecimento['TipoAbastecimento'];
                    $objeto->codigotanque        = $abastecimento['CodigoTanque'];
                    $objeto->nometanque          = $abastecimento['NomeTanque'];
                    $objeto->codigobomba         = $abastecimento['CodigoBomba'];
                    $objeto->nomebomba           = $abastecimento['NomeBomba'];
                    $objeto->razaosocialposto    = $abastecimento['RazaoSocialPosto'];
                    $objeto->nomefantasiaposto   = $abastecimento['NomeFantasiaPosto'];
                    $objeto->cnpjposto           = $abastecimento['CNPJPosto'];
                    $objeto->cidadeposto         = $abastecimento['CidadePosto'];
                    $objeto->ufposto             = $abastecimento['UFPosto'];
                    $objeto->cartaomascarado     = $abastecimento['CartaoMascarado'];
                    $objeto->motorista           = $abastecimento['Motorista'];
                    $objeto->matriculamotorista  = $abastecimento['MatriculaMotorista'];
                    $objeto->cpfmotorista        = $abastecimento['CPFMotorista'];
                    $objeto->placa               = $placa;
                    $objeto->modeloveiculo       = $abastecimento['ModeloVeiculo'];
                    $objeto->anoveiculo          = $abastecimento['AnoVeiculo'];
                    $objeto->matriculaveiculo    = $abastecimento['MatriculaVeiculo'];
                    $objeto->marcaveiculo        = $abastecimento['MarcaVeiculo'];
                    $objeto->corveiculo          = $abastecimento['CorVeiculo'];
                    $objeto->transacaoestornada  = $abastecimento['TransacaoEstornada'];
                    $objeto->cnpjcliente         = $abastecimento['CNPJCliente'];
                    $objeto->km_anterior         = $km_anterior;
                    $objeto->id_veiculo_unitop   = $id_veiculo_unitop;

                    if (empty($abastecimento['TransacaoEstornada']) || $abastecimento['TransacaoEstornada'] == null || $abastecimento['TransacaoEstornada'] == 0) {
                        $objeto->ativo = true;
                    } else {
                        $objeto->ativo = false;
                    }

                    $objeto->save();
                }
                $km_anterior = 0;
                $tratado = false;
                $idveiculo = 0;
                $placa = null;
                $placa_tk = null;
                $transacao = null;
                $id_veiculo_unitop = null;
            }

            DB::connection('pgsql')->select("SELECT * FROM fc_processar_incosistencias_truckpag(?, ?)", [$dataini, $datafin]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function onProcessarATS(Request $request)
    {
        try {
            $data         = implode('-', array_reverse(explode('/', $request->data_inicio)));
            $datamesCINCO = implode('-', array_reverse(explode('/', $request->data_fim)));

            $usuario = auth()->user()->name;


            $timestamp1 = strtotime($data);
            $timestamp2 = strtotime($datamesCINCO);
            $valor      = auth()->user()->id;

            // Data inicial fornecida
            $dataInicial = implode('-', array_reverse(explode('/', $request->data_inicio)));
            // Obtém o primeiro dia do mês atual
            $primeiroDiaMesAtual = date('Y-m-01');

            // Verifica se a data inicial fornecida é maior ou igual ao primeiro dia do mês atual
            if ((strtotime($dataInicial) >= strtotime($primeiroDiaMesAtual))) {
                // A data inicial é válida
                // Faça aqui o que desejar caso a validação seja bem-sucedida

                date_default_timezone_set('America/Cuiaba');

                $data  = implode('-', array_reverse(explode('/', $request->data_inicio)));
                $dataI = $data . ' 00:00:00';

                $data2 = implode('-', array_reverse(explode('/', $request->data_fim)));
                $dataf = $data2 . ' 23:59:59';

                $user_reprocessamento = auth()->user()->id;
                $placa = Veiculo::select('placa')->where('id_veiculo', '=', $request->id_veiculo)->first();
                $bomba = Bomba::select('descricao_bomba')->where('id_bomba', '=', $request->id_bomba)->first();

                $dados = AbastecimentoTruckPagController::getTeste($dataI, $dataf, $placa->placa, $bomba->descricao_bomba, $user_reprocessamento);

                response()->json(['info', "Atenção: Reprocessamento finalizado"]);

                // Lista de números de telefone específicos que receberão a notificação
                $telefones = [

                    '65999721294' => 'Qualidade', // Número 4
                    '65996620322' => 'Rafael',  // Número 5
                    //'65998153363' => 'Mario'
                    '65999721294' => 'Marcos Vinicius',
                    '65992322756' => 'Marcos',
                ];

                // Envia a notificação para cada número de telefone
                foreach ($telefones as $telefone => $nome) {
                    if (!empty($telefone)) {
                        // Texto da mensagem para o WhatsApp
                        $texto = "*Atenção:* $nome. \n"
                            . "O Reprocessamento dos abastecimentos do mês atual foi realizado.\n"
                            . "pelo usuário: {$usuario}.\n";
                        // Envia a mensagem via WhatsApp
                        // AbastecimentoTruckPagController::enviarMensagem($texto, "$nome", "$telefone");
                    }
                }
            } else {
                return redirect()->route('admin.abastecimentostruckpag.index')->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'Erro',
                    'message' => 'Atenção: Não será possível fazer o reprocessamento do mês anterior, solicite ao Suporte Unitop'
                ]);
            }
            return redirect()->route('admin.abastecimentostruckpag.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'success',
                'message' => 'Processamento realizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            response()->json(['error', $e->getMessage()]);
            return redirect()->route('admin.abastecimentostruckpag.index')->withNotification([
                'title'   => 'Sucesso!',
                'type'    => 'Erro',
                'message' => 'Atenção: Não será possível fazer o reprocessamento do mês anterior, solicite ao Suporte Unitop'
            ]);
        }
    }

    public static function getTeste($datainicio, $datafinal, $placa_, $bomba, $user)
    {
        date_default_timezone_set('America/Cuiaba');
        $dataI = date($datainicio);
        $dataF = date($datafinal);

        $wherePlaca = !empty($placa_) ? "placa @@ '$placa_' " : '1 = 1 ';
        $whereBomba = !empty($bomba) ? "descricao_bomba @@ '$bomba' " : '1 = 1 ';

        $dataInicialPesquisa = strtotime($dataI);
        $dataFinalPesquisa   = strtotime($dataF);

        $registro = 0;
        $odometro = 0;
        $idveiculo = 0;
        $km_anterior = 0;
        $id_ats_historico = null;
        $id_veiculo_unitop = null;
        $semEstoque = 'null';
        $id_campo_grande = null;
        $created_at = null;

        $url = "https://api.layrz.com/outbound/atsrest/CarvalimaTransportesLTDA/history?date_start=$dataInicialPesquisa&date_end=$dataFinalPesquisa";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: LayrzToken iWU6KCCmSopm9zb6sNj5aRKlK3faAVEjGahoCl4DUcZ7Xek9gloxycvihGNt6n2RHU3WW9tj1dSGHHn3hKaodRJ1qz8QMtjAzdm6'));
        $data = json_decode(curl_exec($ch));


        try {
            $abaste = DB::connection('pgsql')->table('abastecimento_integracao')
                ->whereBetween(DB::raw('data_inicio::DATE'), [$dataI, $dataF])
                ->where('tratado', '!=', true)
                ->whereRaw($wherePlaca)
                ->whereRaw($whereBomba)
                ->delete();

            foreach ($data->data->exits as $abastecimento) {
                $dataAI = date('Y-m-d H:i:s', strtotime($abastecimento->start_at));
                $dataAF = date('Y-m-d H:i:s', strtotime($abastecimento->end_at));
                $created_at = date('Y-m-d', strtotime($abastecimento->created_at));
                $dataInicial = date('Y-m-d', strtotime($abastecimento->start_at));
                $dataFim = date('Y-m-d', strtotime($abastecimento->end_at));
                $volume = $abastecimento->supplied_volume;
                $placa = $abastecimento->vehicle_name;
                $id_abastecimento_ats = $abastecimento->identifier;
                $bomba_ = $abastecimento->from;
                $tratado = false;
                $registro = 0;
                $semEstoque = 'true';
                $id_ats_1 = false;

                if (!empty($placa_) && (str_replace(" ", "", $placa_) !=  str_replace(" ", "", $placa))) {
                    continue;
                }

                if (!empty($bomba) && (str_replace(" ", "", $bomba) !=  str_replace(" ", "", $bomba_))) {
                    continue;
                }

                $historico_abastecimento = DB::connection('pgsql')->table('historico_abastecimento_baixar_estoque as h')
                    ->join('tanque as t', 't.id_tanque', '=', 'h.id_tanque')
                    ->select('h.id_ats')
                    ->where('h.id_ats', $id_abastecimento_ats)
                    ->where('h.descricao_bomba', $abastecimento->from)
                    ->where('t.id_filial', 2)
                    ->where('h.data_abastecimento', '>=', '2024-01-04')
                    ->first();

                if ($historico_abastecimento) {
                    foreach ($historico_abastecimento as $registro) {
                        $id_ats_historico = $registro->id_ats;
                    }
                }

                //alterado dia 27/11 novo id carvalima
                $historico_abastecimento = DB::connection('pgsql')->table('historico_abastecimento_baixar_estoque as h')
                    ->join('tanque as t', 't.id_tanque', '=', 'h.id_tanque')
                    ->select('h.id_ats')
                    ->where('h.id_ats', $id_abastecimento_ats)
                    ->where('h.descricao_bomba', $abastecimento->from)
                    ->where('t.id_filial', 1) // id_filial = 01
                    ->where('h.data_abastecimento', '>=', '2024-01-04')
                    ->first();

                if ($historico_abastecimento) {
                    foreach ($historico_abastecimento as $registro) {
                        $id_ats_historico = $registro->id_ats;
                    }
                }

                if ($abastecimento->from != 'Bomba 2 Carvalima Transporte Campo Grande CGR' && $abastecimento->from != 'Bomba 1 Carvalima Transporte Campo Grande CGR' && $abastecimento->from != 'Bomba 3 - Arla32 Carvalima Transporte Campo Grande CGR') {
                    $historico_abastecimento = DB::connection('pgsql')->table('historico_abastecimento_baixar_estoque as h')
                        ->join('tanque as t', 't.id_tanque', '=', 'h.id_tanque')
                        ->select('h.id_ats')
                        ->where('h.id_ats', $id_abastecimento_ats)
                        ->whereNotIn('t.id_filial', [1, 2]) // id_filial NOT IN (01, 02)
                        ->where('h.descricao_bomba', $abastecimento->from)
                        ->first();

                    if ($historico_abastecimento) {
                        foreach ($historico_abastecimento as $registro) {
                            $id_ats_historico = $registro->id_ats;
                        }
                    }
                }

                if (empty($id_ats_historico) ||  $id_ats_historico == null || $id_ats_historico == 0) {
                    $id_tanque = null;
                    $saldo_tanque = null;

                    $tanqueabastecimento = DB::connection('pgsql')->table('bomba as b')
                        ->select('b.id_tanque')
                        ->where('b.descricao_bomba', '@@', $abastecimento->from)
                        ->first();

                    if ($tanqueabastecimento) {
                        foreach ($tanqueabastecimento as $values) {
                            $id_tanque = $values->id_tanque;
                        }
                    }

                    if (!empty($id_tanque) || $id_tanque != null) {

                        $tanqueabastecimento = DB::connection('pgsql')->table('estoque_combustivel as e')
                            ->select('e.quantidade_em_estoque')
                            ->where('e.id_tanque', $id_tanque)
                            ->whereNull('e.data_encerramento')
                            ->first();

                        if ($tanqueabastecimento) {
                            foreach ($tanqueabastecimento as $values) {
                                $saldo_tanque = $values->quantidade_em_estoque;
                            }
                        }
                    }
                    if ($saldo_tanque > 0 && ($saldo_tanque - $volume /*$abastecimento->supplied_volume*/) > 0) {

                        $semEstoque = 'false';

                        $data_historico = [
                            'descricao_bomba' => $abastecimento->from,
                            'placa' => $abastecimento->vehicle_name,
                            'descricao_veiculo' => $abastecimento->vehicle_plate,
                            'volume' => $abastecimento->supplied_volume,
                            'data_inclusao' => date('Y-m-d H:i:s', strtotime("now")),
                            'id_ats' => $abastecimento->identifier,
                            'id_tanque' => $id_tanque,
                            'data_abastecimento' => $dataAI

                        ];

                        $objeto = new HistoricoAbastecimentoBaixarEstoque();

                        $objeto->data_inclusao      = $data_historico['data_inclusao'];
                        $objeto->descricao_bomba    = $data_historico['descricao_bomba'];
                        $objeto->placa              = $data_historico['placa'];
                        $objeto->descricao_veiculo  = $data_historico['descricao_veiculo'];
                        $objeto->volume             = $data_historico['volume'];
                        $objeto->id_ats             = $data_historico['id_ats'];
                        $objeto->id_tanque          = $data_historico['id_tanque'];
                        $objeto->data_abastecimento = $data_historico['data_abastecimento'];

                        $objeto->save();
                    } else {
                        //incluso dia 23/08 gravar estoque na abastecimento_integracao
                        $semEstoque = 'true';
                    }
                } else {

                    $semEstoque = 'false';
                }



                //////////////////////////////////////////////fim da alteracao///////////////////////////////////////////////////////////////////////////////////////



                //////////////////////////////////////////////alterar para incluir horimetro/////////////////////////////////////////////////////////////////////////
                //Marcos Jr 23/01 - corrigido
                $id_veiculo_km_m = null;
                $data_abastecimento_km_m = null;

                $odometer = DB::connection('pgsql')->table('ajustekmabastecimento as ab')
                    ->select('ab.id_veiculo', 'ab.data_abastecimento')
                    ->whereDate('ab.data_abastecimento', now()->toDateString()) // Compara apenas a parte da data
                    ->whereNull('ab.id_abastecimento_ats') // Verifica se a coluna é NULL
                    ->get();

                if ($odometer) {
                    foreach ($odometer as $odometers) {
                        $id_veiculo_km_m = $odometers->id_veiculo;
                        $data_abastecimento_km_m = $odometers->data_abastecimento;

                        $odometer = DB::connection('pgsql')->select("SELECT * FROM fc_inserir_km_ajuste_manual(?, ?)", [
                            $id_veiculo_km_m,
                            $data_abastecimento_km_m,
                        ]);
                    }
                }

                //////////////////////////////////////////////fim da alteracao///////////////////////////////////////////////////////////////////////////////////////


                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $id_veiculo_unitop = null;
                $veiculoTerceiro   = null;

                //ALTERACAO DIA 16/02 - PREENCHER ID VEICULO UNITOP
                $odometer = DB::connection('pgsql')->table('veiculo as p')
                    ->select('p.id_veiculo', 'p.is_terceiro')
                    ->whereRaw("p.placa @@ ?", [$placa])
                    ->first();

                if ($odometer) {
                    $qtdRegistrosPlaca = count($odometer);

                    if ($qtdRegistrosPlaca > 1) {
                        $odometer = DB::connection('pgsql')->table('veiculo as p')
                            ->select('p.id_veiculo', 'p.is_terceiro')
                            ->whereRaw("p.placa @@ ?", [$placa])
                            ->orderByDesc('p.data_inclusao')
                            ->first();
                    }

                    foreach ($odometer as $odometers) {
                        $id_veiculo_unitop = $odometers->id_veiculo;
                        $veiculoTerceiro   = $odometers->is_terceiro ? 'true' : null;
                    }
                }

                $array = ["Bomba Plataforma Carvalima Transportes SP"];

                if (in_array($abastecimento->from, $array)) {

                    $odometer = DB::connection('pgsql')->table('pacoteposicaorangjson as p')
                        ->join('obterveiculos as v', 'v.idveiculo', '=', 'p.idveiculo')
                        ->select('p.odometroexato', 'p.idveiculo')
                        ->where('v.placa', 'LIKE', "%$placa%")
                        ->whereBetween('p.datapacote', [
                            DB::raw("'$dataAI'::TIMESTAMP"),
                            DB::raw("'$dataAF'::TIMESTAMP + INTERVAL '00:10:00'")
                        ])
                        ->orderByDesc('p.datapacote')
                        ->first();

                    if ($odometer) {
                        foreach ($odometer as $odometers) {
                            $odometro  = $odometers->odometroexato;
                            $idveiculo = $odometers->idveiculo;
                        }
                    } else {
                        $odometro = 0;
                        $idveiculo = 0;
                    }
                } else { //alteração para recuperar com o hoário de BRASILIA
                    $odometer = DB::connection('pgsql')->table('pacoteposicaorangjson as p')
                        ->join('obterveiculos as v', 'v.idveiculo', '=', 'p.idveiculo')
                        ->select('p.odometroexato', 'p.idveiculo')
                        ->where('v.placa', 'LIKE', "%$placa%")
                        ->whereBetween('p.datapacote', [
                            DB::raw("'$dataAI'::TIMESTAMP + INTERVAL '01:00:00'"),
                            DB::raw("'$dataAF'::TIMESTAMP + INTERVAL '01:10:00'")
                        ])
                        ->orderByDesc('p.datapacote')
                        ->first();

                    if ($odometer) {
                        foreach ($odometer as $odometers) {
                            $odometro  = $odometers->odometroexato;
                            $idveiculo = $odometers->idveiculo;
                        }
                    } else {
                        $odometro = 0;
                        $idveiculo = 0;
                    }
                }

                ///////////////buscar km seeflex//////////////////////////////////////////////////////////////////////////
                //alterado marcos 18/06/2023
                $id_veiculo_seeflex = null;

                $veiculoseeflex = DB::connection('pgsql')->table('seeflex_veiculo')
                    ->select('id as idveiculo')
                    ->whereRaw("placa @@ ?", [$placa])
                    ->first();

                if ($veiculoseeflex) {
                    foreach ($veiculoseeflex as $values) {
                        $id_veiculo_seeflex = $values->idveiculo;
                    }
                }

                if (!empty($id_veiculo_seeflex) || $id_veiculo_seeflex != null) {

                    $odometer = DB::connection('pgsql')->table('seefle_movimento_veiculo as t')
                        ->select(DB::raw('(t.horimetro::integer / 60) as horimetro'))
                        ->where('t.veiculo_id', $id_veiculo_seeflex)
                        ->whereRaw("(t.data_movimento::TEXT || ' ' || t.hora::TEXT)::TIMESTAMP BETWEEN ? AND ?::TIMESTAMP + INTERVAL '01:00:00'", [$dataAI, $dataAI])
                        ->whereNotNull('t.horimetro')
                        ->where('t.tipo', 'MOVIMENTO')
                        ->orderBy('t.hora')
                        ->first();

                    if ($odometer) {
                        foreach ($odometer as $odometers) {
                            $odometro = $odometers->horimetro;
                        }
                    }
                }

                if (!empty($odometro) && $odometro != null && $odometro != 0) {
                    $kmanterior = DB::connection('pgsql')->table('v_abastecimento_completo_22_11_2022 as t')
                        ->select(DB::raw('MAX(t.km_abastecimento) as ultimokm'))
                        ->where('t.placa', $placa)
                        ->where('t.km_abastecimento', '<', $odometro)
                        ->where('t.id_tipo_combustivel', '<>', 20)
                        ->first();

                    if ($kmanterior) {
                        foreach ($kmanterior as $kms) {
                            $km_anterior = $kms->ultimokm;
                        }
                    }
                } else {
                    $kmanterior = DB::connection('pgsql')->table('v_abastecimento_completo_22_11_2022 as t')
                        ->select(DB::raw('MAX(t.km_abastecimento) as ultimokm'))
                        ->where('t.placa', $placa)
                        ->where('t.id_tipo_combustivel', '<>', 20)
                        ->first();

                    if ($kmanterior) {
                        foreach ($kmanterior as $kms) {
                            $km_anterior = $kms->ultimokm;
                        }
                    }
                }

                if ($id_abastecimento_ats != null || $id_abastecimento_ats != 0) {

                    $abastecimento_ab = DB::connection('pgsql')->table('abastecimento_integracao as it')
                        ->select('it.tratado')
                        ->where('it.id_abastecimento_ats', $id_abastecimento_ats)
                        ->where('it.tratado', true)
                        ->whereDate('it.data_inicio', '>=', '2023-01-01')
                        ->where('it.descricao_bomba', 'LIKE', $abastecimento->from)
                        ->first();

                    if ($abastecimento_ab) {
                        foreach ($abastecimento_ab as $ids) {
                            $tratado = $ids->tratado;
                            if ($tratado != null || !empty($tratado)) {
                                $tratado = true;
                            }
                        }
                    }
                }

                $abastecimento_ab = DB::connection('pgsql')->table('abastecimento_integracao as it')
                    ->select('it.id_abastecimento_ats')
                    ->where('it.id_abastecimento_ats', $id_abastecimento_ats)
                    ->whereDate('it.data_inicio', DB::raw('CURRENT_DATE'))
                    ->where('it.descricao_bomba', 'LIKE', $abastecimento->from)
                    ->first();

                if ($abastecimento_ab) {
                    foreach ($abastecimento_ab as $ids) {
                        $id_ats_1 = $ids->id_abastecimento_ats;
                        if ($id_ats_1 != null || !empty($id_ats)) {
                            $id_ats_1 = true;
                            //echo('DUPLICADO');
                        }
                    }
                }

                if ($tratado != true && $id_ats_1 != true) {
                    $data = [
                        'descricao_bomba' => $abastecimento->from,
                        'placa' => $abastecimento->vehicle_name,
                        'descricao_veiculo' => $abastecimento->vehicle_plate,
                        'volume' => $abastecimento->supplied_volume,
                        'fluxometre' => $abastecimento->fluxometer,
                        'data_inicio' => $dataAI,
                        'data_fim' => $dataAF,
                        'fluxo_inicial' => $abastecimento->initial_fluxometer,
                        'fluxo_final' => $abastecimento->final_fluxometer,
                        'km_abastecimento' => $odometro,
                        'id_veiculo' => $idveiculo,
                        'data_inclusao' => date('Y-m-d H:i:s', strtotime("now")),
                        'tipo_combustivel' => $abastecimento->$abastecimento->fuel_subtype,
                        'valor_litro' => $abastecimento->unit_fuel_price,
                        'id_abastecimento_ats' => $abastecimento->identifier,
                        'km_anterior' => $km_anterior,
                        'id_veiculo_unitop' => $id_veiculo_unitop,
                        'is_tanque_zerado' => $semEstoque,
                        'tratado' => $veiculoTerceiro, // Carlos Eduardo 04/12/2023 - Inseri o abastecimento como Tratado = True, caso o veículo seja de terceiros, evitando a exclusão do registro da Listagem de Abastecimento da R.V de Terceiro.
                        'created_at' => $created_at

                    ];

                    $objeto = new AbastecimentoIntegracao();

                    $objeto->data_inclusao        = $data['data_inclusao'];
                    $objeto->data_alteracao       = $data['data_alteracao'];
                    $objeto->descricao_bomba      = $data['descricao_bomba'];
                    $objeto->placa                = $data['placa'];
                    $objeto->descricao_veiculo    = $data['descricao_veiculo'];
                    $objeto->volume               = $data['volume'];
                    $objeto->fluxometre           = $data['fluxometre'];
                    $objeto->data_inicio          = $data['data_inicio'];
                    $objeto->data_fim             = $data['data_fim'];
                    $objeto->fluxo_inicial        = $data['fluxo_inicial'];
                    $objeto->fluxo_final          = $data['fluxo_final'];
                    $objeto->km_abastecimento     = $data['km_abastecimento'];
                    $objeto->id_veiculo           = $data['id_veiculo'];
                    $objeto->tratado              = $data['tratado'];
                    $objeto->tipo_combustivel     = $data['tipo_combustivel'];
                    $objeto->valor_litro          = $data['valor_litro'];
                    $objeto->id_abastecimento_ats = $data['id_abastecimento_ats'];
                    $objeto->km_anterior          = $data['km_anterior'];
                    $objeto->ativo                = $data['ativo'];
                    $objeto->justificativa        = $data['justificativa'];
                    $objeto->vlrmedio             = $data['vlrmedio'];
                    $objeto->id_veiculo_unitop    = $data['id_veiculo_unitop'];
                    $objeto->vlrunitario_interno  = $data['vlrunitario_interno'];
                    $objeto->is_tanque_zerado     = $data['is_tanque_zerado'];
                    $objeto->created_at           = $data['created_at'];
                    $objeto->id_user_tratado      = $data['id_user_tratado'];
                    $objeto->data_tratado         = $data['data_tratado'];

                    $objeto->save();
                }
                $id_ATS = null;
                $tratado = false;
                $semEstoque = 'true';
            }

            $retorno = DB::connection('pgsql')->select("SELECT * FROM fc_processar_incosistencias_ats_sistema_interno(?, ?)", [$dataI, $dataF]);
        } catch (\Exception $e) {
            LOG::INFO($e->getMessage());
        }
    }

    public static function gerarToken()
    {
        $username = 'carvalima.unitop';
        $password = '073d7f102ff3955d919a879a61b94b6f';
        $headers = array(
            "Content-Type: application/json",
            'Authorization: Basic ' . base64_encode($username . ':' . $password),
            "Accept: application/json"
        );

        $url = "https://api.truckpag.com.br/auth";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([]));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $response = curl_exec($ch);

        if ($response === false) {
            LOG::error('Erro no cURL: ' . curl_error($ch));
        } else {
            $data = json_decode($response, true);
            LOG::info('Resposta da API: ', (array) $data);
        }

        curl_close($ch);

        return $data['token'] ?? null;
    }

    public static function enviarMensagem($mensagem, $nome, $numero)
    {
        $tokem          = "ad8d672c-b196-4f2e-bf24-aaa8523ef258";
        $accountId      = 2;
        $whatsappId     = 95;
        $messageTimeout = 600;
        $queued         = true;
        $from           = "UNITOP";
        $url            = "https://api.sacflow.io/api/send-text";

        $body = json_encode(array(
            "accountId" => $accountId,
            "whatsappId" => $whatsappId,
            "message" => $mensagem,
            "messageTimeout" => $messageTimeout,
            "from" => $from,
            "contact" => array(
                "name" => $nome,
                "phone" => $numero
            ),
            "queued" => true
        ));

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer $tokem",
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erro na requisição: ' . curl_error($ch);
        }

        curl_close($ch);

        return $response;
    }
}
