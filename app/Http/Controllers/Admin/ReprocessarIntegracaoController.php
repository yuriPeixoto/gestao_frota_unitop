<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoTruckPag;
use App\Models\Veiculo;
use App\Models\Bomba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReprocessarIntegracaoController extends Controller
{
    /**
     * Exibe a página principal com as abas de reprocessamento
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        // Obter dados de referência para os filtros
        $veiculos = Cache::remember('reprocessar_veiculos', now()->addHours(12), function () {
            return Veiculo::where('situacao_veiculo', true)
                ->orderBy('placa')
                ->get(['id_veiculo as value', 'placa as label']);
        });

        $bombas = Cache::remember('reprocessar_bombas', now()->addHours(12), function () {
            return Bomba::orderBy('descricao_bomba')
                ->get(['descricao_bomba as value', 'descricao_bomba as label']);
        });

        return view('admin.reprocessar.index', compact('veiculos', 'bombas'));
    }

    /**
     * Processar integração ATS
     */
    public function processarAts(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'data_inicial' => 'required|date',
                'data_final' => 'required|date',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Temporariamente desabilitado, como no código legado
            return redirect()
                ->back()
                ->with('info', 'Função desabilitada temporariamente!');
        } catch (Exception $e) {
            Log::error('Erro ao processar integração ATS: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Erro ao processar integração: ' . $e->getMessage());
        }
    }

    /**
     * Processar integração TruckPag
     */
    public function processarTruckPag(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'data_inicial' => 'required|date',
                'data_final' => 'required|date',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Conversão de datas
            $dataInicio = implode('', array_reverse(explode('/', $request->data_inicial)));
            $dataFinal = implode('', array_reverse(explode('/', $request->data_final)));

            // Verificação de data mínima (como no código legado)
            $dataMinima = '01-10-2023';
            $dataInicialFormatada = implode('-', array_reverse(explode('/', $request->data_inicial)));

            if (strtotime($dataInicialFormatada) < strtotime($dataMinima)) {
                return redirect()
                    ->back()
                    ->with('info', 'Atenção: Não será possível fazer o reprocessamento da data informada, solicite ao Suporte Unitop');
            }

            // Chamar o serviço de integração TruckPag (adaptado do código legado)
            DB::connection('pgsql')->statement("SELECT fc_processar_integracao_truckpag(?, ?)", [$dataInicio, $dataFinal]);

            return redirect()
                ->back()
                ->with('success', 'Integração TruckPag processada com sucesso!');
        } catch (Exception $e) {
            Log::error('Erro ao processar integração TruckPag: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('error', 'Erro ao processar integração: ' . $e->getMessage());
        }
    }
}
