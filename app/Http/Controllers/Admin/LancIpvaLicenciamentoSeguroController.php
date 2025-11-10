<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenciamentoVeiculo;
use App\Models\Estado;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LancIpvaLicenciamentoSeguroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSize   = $request->input('pageSize', 10);
        $searchTerm = $request->input('search');

        $query = LicenciamentoVeiculo::query()
            ->select('licenciamentoveiculo.*')
            ->with('veiculo')
            ->where('data_inclusao', '>=', Carbon::now()->subYear()) // Subtrai 1 ano da data atual
            ->distinct();

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $searchTermLower = strtolower($searchTerm);

                $query->whereRaw('LOWER(CAST(ano_licenciamento AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(data_emissao_crlv AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(crlv AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(data_vencimento AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWherehas('veiculo', function ($query) use ($searchTermLower) {
                        $query->whereRaw('LOWER(CAST(placa AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%']);
                    });
            });
        };

        $query->orderBy('id_licenciamento', 'desc');

        $licenciamentoVeiculos = $query->paginate($pageSize)->withQueryString();

        $totalRegistros = $licenciamentoVeiculos->total();

        return view('admin.lancipvalicenciamentoseguros.index', compact('licenciamentoVeiculos', 'totalRegistros', 'searchTerm'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Range para alimentar o combo de ano de validade
        $anoAtual = date("Y");
        $anoInicial = $anoAtual - 29;
        $anos = [];

        for ($ano = $anoAtual; $ano >= $anoInicial; $ano--) {
            $anos[] = [
                'label' => (string)$ano,
                'value' => $ano
            ];
        }

        // Alimentando o combo estados
        $ufData = Estado::select('uf as label', 'uf as value')->orderBy('uf', 'asc')->get()->toArray();

        return view('admin.lancipvalicenciamentoseguros.create', compact('anos', 'ufData'));
    }

    public function edit(Request $request, $id)
    {
        $licenciamentoveiculos = LicenciamentoVeiculo::findOrFail($id);
        $anoAtual = date("Y");
        $anoInicial = $anoAtual - 29;
        $anos = [];

        for ($ano = $anoAtual; $ano >= $anoInicial; $ano--) {
            $anos[] = [
                'label' => (string)$ano,
                'value' => $ano
            ];
        }

        // Alimentando o combo estados
        $ufData = Estado::select('uf as label', 'uf as value')->orderBy('uf', 'asc')->get()->toArray();

        return view('admin.lancipvalicenciamentoseguros.edit', compact('anos', 'ufData', 'id', 'licenciamentoveiculos'));
    }
    /**
     * Lançamento de Licenciamento de Veículos
     */
    public function lancarLicenciamento(Request $request)
    {
        //dd($request->all());
        try {
            // Validação dos dados de entrada
            $validator = Validator::make($request->all(), [
                'ano_validade_licenciamento' => 'required|integer',
                'data_vencimento' => 'required|date',
                'uf' => 'required|string|max:2',
                'final_placa' => 'required|string|max:10',
                'valor_taxa' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Erro de validação',
                    'type' => 'error',
                    'message' => 'Por favor, preencha todos os campos corretamente.'
                ], 422);
            }

            $ano_validade      = intval($request->ano_validade_licenciamento);
            $data_vencimento   = date('Y-m-d', strtotime($request->data_vencimento));
            $uf                = $request->uf;
            $final_placa       = $request->final_placa;
            $valor_taxa        = is_numeric($request->valor_taxa) ? floatval($request->valor_taxa) : SanitizeToDouble($request->valor_taxa);

            // Executar função de banco
            $sql = "SELECT * FROM fc_lancamento_licenciamentos(?, ?, ?, ?, ?)";
            $resultado = DB::connection('pgsql')->select($sql, [
                $ano_validade,
                $uf,
                $final_placa,
                $data_vencimento,
                $valor_taxa
            ]);

            // Tratamento do retorno
            if (empty($resultado)) {
                return response()->json([
                    'title' => 'Erro no processamento',
                    'type' => 'error',
                    'message' => 'Não foi possível processar o lançamento.'
                ], 500);
            }

            $retorno = $resultado[0]->fc_lancamento_licenciamentos ?? 0;

            if ($retorno == 1) {
                return response()->json([
                    'title' => 'Sucesso',
                    'type' => 'success',
                    'message' => 'Os Licenciamentos foram lançados com sucesso.'
                ], 200);
            } else {
                return response()->json([
                    'title' => 'Aviso',
                    'type' => 'warning',
                    'message' => 'Não há veículos que atendam aos critérios selecionados ou eles já possuem licenciamento para o ano informado.'
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao fazer o lançamento de licenciamento: ' . $e->getMessage());
            return response()->json([
                'title' => 'Erro',
                'type' => 'error',
                'message' => 'Não foi possível fazer o lançamento dos Licenciamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lançamento de IPVA
     */
    public function lancarIpva(Request $request)
    {
        try {
            // Validação dos dados de entrada
            $validator = Validator::make($request->all(), [
                'ano_validade_ipva' => 'required|integer',
                'data_limite_ipva' => 'required|date',
                'uf_ipva' => 'required|string|max:2',
                'final_placa' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Erro de validação',
                    'type' => 'error',
                    'message' => 'Por favor, preencha todos os campos corretamente.'
                ], 422);
            }

            $ano_validade      = intval($request->ano_validade_ipva);
            $data_vencimento   = date('Y-m-d', strtotime($request->data_limite_ipva));
            $uf                = $request->uf_ipva;
            $final_placa       = $request->final_placa;

            // Executar função de banco
            $sql = "SELECT * FROM fc_lancamento_ipva(?, ?, ?, ?)";
            $resultado = DB::connection('pgsql')->select($sql, [
                $ano_validade,
                $uf,
                $final_placa,
                $data_vencimento
            ]);

            // Tratamento do retorno
            if (empty($resultado)) {
                return response()->json([
                    'title' => 'Erro no processamento',
                    'type' => 'error',
                    'message' => 'Não foi possível processar o lançamento de IPVA.'
                ], 500);
            }

            $retorno = $resultado[0]->fc_lancamento_ipva ?? 0;

            if ($retorno == 1) {
                return response()->json([
                    'title' => 'Sucesso',
                    'type' => 'success',
                    'message' => 'Os IPVAs foram lançados com sucesso.'
                ], 200);
            } else {
                return response()->json([
                    'title' => 'Aviso',
                    'type' => 'warning',
                    'message' => 'Não há veículos que atendam aos critérios selecionados ou eles já possuem IPVA para o ano informado.'
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao fazer o lançamento de IPVA: ' . $e->getMessage());
            return response()->json([
                'title' => 'Erro',
                'type' => 'error',
                'message' => 'Não foi possível fazer o lançamento dos IPVAs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lançamento de Seguro Obrigatório
     */
    public function lancarSeguro(Request $request)
    {
        try {
            // Validação dos dados de entrada
            $validator = Validator::make($request->all(), [
                'ano_validade_seguro' => 'required|integer',
                'data_limite_seguro' => 'required|date',
                'uf_seguro' => 'required|string|max:2',
                'dezena_placa_seguro' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Erro de validação',
                    'type' => 'error',
                    'message' => 'Por favor, preencha todos os campos corretamente.'
                ], 422);
            }

            $ano_validade      = intval($request->ano_validade_seguro);
            $data_vencimento   = date('Y-m-d', strtotime($request->data_limite_seguro));
            $uf                = $request->uf_seguro;
            $final_placa       = $request->dezena_placa_seguro;

            // Executar função de banco
            $sql = "SELECT * FROM fc_lancamento_seguro_obrigatorio(?, ?, ?, ?)";
            $resultado = DB::connection('pgsql')->select($sql, [
                $ano_validade,
                $uf,
                $final_placa,
                $data_vencimento
            ]);

            // Tratamento do retorno
            if (empty($resultado)) {
                return response()->json([
                    'title' => 'Erro no processamento',
                    'type' => 'error',
                    'message' => 'Não foi possível processar o lançamento de Seguro Obrigatório.'
                ], 500);
            }

            $retorno = $resultado[0]->fc_lancamento_seguro_obrigatorio ?? 0;

            if ($retorno == 1) {
                return response()->json([
                    'title' => 'Sucesso',
                    'type' => 'success',
                    'message' => 'Os Seguros Obrigatórios foram lançados com sucesso.'
                ], 200);
            } else {
                return response()->json([
                    'title' => 'Aviso',
                    'type' => 'warning',
                    'message' => 'Não há veículos que atendam aos critérios selecionados ou eles já possuem Seguro Obrigatório para o ano informado.'
                ], 200);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao fazer o lançamento de Seguro Obrigatório: ' . $e->getMessage());
            return response()->json([
                'title' => 'Erro',
                'type' => 'error',
                'message' => 'Não foi possível fazer o lançamento dos Seguros Obrigatórios: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateLicenciamento(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_licenciamento' => 'required|integer',
                'ano_validade_licenciamento' => 'required|integer',
                'data_vencimento' => 'required|date',
                'uf' => 'required|string|max:2',
                'final_placa' => 'required|string|max:10',
                'valor_taxa' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Erro de validação',
                    'type' => 'error',
                    'message' => 'Por favor, preencha todos os campos corretamente.'
                ], 422);
            }

            $id_licenciamento  = intval($request->id_licenciamento);
            $ano_validade      = intval($request->ano_validade_licenciamento);
            $data_vencimento   = date('Y-m-d', strtotime($request->data_vencimento));
            $uf                = $request->uf;
            $final_placa       = $request->final_placa;
            $valor_taxa        = floatval($request->valor_taxa);

            $sql = "SELECT * FROM fc_update_licenciamento(?, ?, ?, ?, ?, ?)";
            $resultado = DB::connection('pgsql')->select($sql, [
                $id_licenciamento,
                $ano_validade,
                $uf,
                $final_placa,
                $data_vencimento,
                $valor_taxa
            ]);

            if (empty($resultado)) {
                return response()->json([
                    'title' => 'Erro no processamento',
                    'type' => 'error',
                    'message' => 'Não foi possível atualizar o licenciamento.'
                ], 500);
            }

            $retorno = $resultado[0]->fc_update_licenciamento ?? 0;

            return $retorno == 1
                ? response()->json(['title' => 'Sucesso', 'type' => 'success', 'message' => 'Licenciamento atualizado com sucesso.'], 200)
                : response()->json(['title' => 'Aviso', 'type' => 'warning', 'message' => 'Não foi possível atualizar. Verifique os critérios.'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar licenciamento: ' . $e->getMessage());
            return response()->json(['title' => 'Erro', 'type' => 'error', 'message' => 'Erro ao atualizar licenciamento: ' . $e->getMessage()], 500);
        }
    }

    public function updateIpva(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_ipva' => 'required|integer',
                'ano_validade_ipva' => 'required|integer',
                'data_limite_ipva' => 'required|date',
                'uf_ipva' => 'required|string|max:2',
                'final_placa' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Erro de validação',
                    'type' => 'error',
                    'message' => 'Por favor, preencha todos os campos corretamente.'
                ], 422);
            }

            $id_ipva        = intval($request->id_ipva);
            $ano_validade    = intval($request->ano_validade_ipva);
            $data_vencimento = date('Y-m-d', strtotime($request->data_limite_ipva));
            $uf              = $request->uf_ipva;
            $final_placa     = $request->final_placa;

            $sql = "SELECT * FROM fc_update_ipva(?, ?, ?, ?, ?)";
            $resultado = DB::connection('pgsql')->select($sql, [
                $id_ipva,
                $ano_validade,
                $uf,
                $final_placa,
                $data_vencimento
            ]);

            if (empty($resultado)) {
                return response()->json([
                    'title' => 'Erro no processamento',
                    'type' => 'error',
                    'message' => 'Não foi possível atualizar o IPVA.'
                ], 500);
            }

            $retorno = $resultado[0]->fc_update_ipva ?? 0;

            return $retorno == 1
                ? response()->json(['title' => 'Sucesso', 'type' => 'success', 'message' => 'IPVA atualizado com sucesso.'], 200)
                : response()->json(['title' => 'Aviso', 'type' => 'warning', 'message' => 'Não foi possível atualizar. Verifique os critérios.'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar IPVA: ' . $e->getMessage());
            return response()->json(['title' => 'Erro', 'type' => 'error', 'message' => 'Erro ao atualizar IPVA: ' . $e->getMessage()], 500);
        }
    }


    public function updateSeguro(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id_seguro' => 'required|integer',
                'ano_validade_seguro' => 'required|integer',
                'data_limite_seguro' => 'required|date',
                'uf_seguro' => 'required|string|max:2',
                'dezena_placa_seguro' => 'required|string|max:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'title' => 'Erro de validação',
                    'type' => 'error',
                    'message' => 'Por favor, preencha todos os campos corretamente.'
                ], 422);
            }

            $id_seguro      = intval($request->id_seguro);
            $ano_validade   = intval($request->ano_validade_seguro);
            $data_vencimento = date('Y-m-d', strtotime($request->data_limite_seguro));
            $uf             = $request->uf_seguro;
            $final_placa    = $request->dezena_placa_seguro;

            $sql = "SELECT * FROM fc_update_seguro_obrigatorio(?, ?, ?, ?, ?)";
            $resultado = DB::connection('pgsql')->select($sql, [
                $id_seguro,
                $ano_validade,
                $uf,
                $final_placa,
                $data_vencimento
            ]);

            if (empty($resultado)) {
                return response()->json([
                    'title' => 'Erro no processamento',
                    'type' => 'error',
                    'message' => 'Não foi possível atualizar o Seguro Obrigatório.'
                ], 500);
            }

            $retorno = $resultado[0]->fc_update_seguro_obrigatorio ?? 0;

            return $retorno == 1
                ? response()->json(['title' => 'Sucesso', 'type' => 'success', 'message' => 'Seguro Obrigatório atualizado com sucesso.'], 200)
                : response()->json(['title' => 'Aviso', 'type' => 'warning', 'message' => 'Não foi possível atualizar. Verifique os critérios.'], 200);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar Seguro Obrigatório: ' . $e->getMessage());
            return response()->json(['title' => 'Erro', 'type' => 'error', 'message' => 'Erro ao atualizar Seguro Obrigatório: ' . $e->getMessage()], 500);
        }
    }



    /**
     * Destroy the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $licenciamento = LicenciamentoVeiculo::findOrFail($id);

            // Obter informações para mensagem antes de excluir
            $placa = $licenciamento->veiculo->placa ?? 'desconhecido';
            $ano = $licenciamento->ano_licenciamento;

            $licenciamento->delete();

            return response()->json([
                'success' => true,
                'notification' => [
                    'title' => 'Sucesso',
                    'message' => "Licenciamento do veículo {$placa} para o ano {$ano} excluído com sucesso.",
                    'type' => 'success'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir licenciamento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'notification' => [
                    'title' => 'Erro',
                    'message' => 'Não foi possível excluir o licenciamento. ' . $e->getMessage(),
                    'type' => 'error'
                ]
            ], 500);
        }
    }
}
