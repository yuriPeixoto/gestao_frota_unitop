<?php

namespace App\Http\Controllers;

use App\Services\IntegradorSmartecService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SmartecController extends Controller
{
    public function __construct(
        private IntegradorSmartecService $smartecService
    ) {}

    /**
     * Consulta informações de um veículo
     */
    public function consultarVeiculo(Request $request): JsonResponse
    {
        $request->validate([
            'placa' => 'required|string',
            'uf' => 'required|string|size:2',
            'frota' => 'required|string',
            'prefixo' => 'required|string',
            'cnpj_cpf' => 'required|string',
            'data_base' => 'required|date',
            'renavam' => 'required|string',
            'tipo' => 'required|string'
        ]);

        try {
            $resultado = $this->smartecService->consultarVeiculo(
                placa: $request->placa,
                uf: $request->uf,
                frota: $request->frota,
                prefixo: $request->prefixo,
                cnpjCpf: $request->cnpj_cpf,
                dataBase: $request->data_base,
                renavam: $request->renavam,
                tipo: $request->tipo
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao consultar veículo: ' . $e->getMessage(), [
                'placa' => $request->placa,
                'renavam' => $request->renavam
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar informações do veículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Indica um condutor para uma infração
     */
    public function indicarInfracao(Request $request): JsonResponse
    {
        $request->validate([
            'nome' => 'required|string',
            'cnh' => 'required|string',
            'tipo' => 'required|string',
            'ait' => 'required|string',
            'codigo_orgao' => 'nullable|string'
        ]);

        try {
            $resultado = $this->smartecService->indicarInfracao(
                nome: $request->nome,
                cnh: $request->cnh,
                tipo: $request->tipo,
                ait: $request->ait,
                codigoOrgao: $request->codigo_orgao
            );

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Indicação realizada com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao indicar infração: ' . $e->getMessage(), [
                'ait' => $request->ait,
                'cnh' => $request->cnh
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao indicar condutor para a infração',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consulta CNH por CPF
     */
    public function consultarCnh(Request $request): JsonResponse
    {
        $request->validate([
            'cpf' => 'required|string|size:11',
            'tipo' => 'required|string'
        ]);

        try {
            $resultado = $this->smartecService->consultarCnh(
                cpf: $request->cpf,
                tipo: $request->tipo
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao consultar CNH: ' . $e->getMessage(), [
                'cpf' => $request->cpf
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar informações da CNH',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consulta infrações por RENAVAM
     */
    public function consultarInfracoes(Request $request): JsonResponse
    {
        $request->validate([
            'renavam' => 'required|string',
            'tipo' => 'required|string',
            'data_pesquisa' => 'required|date'
        ]);

        try {
            $resultado = $this->smartecService->consultarInfracoes(
                renavam: $request->renavam,
                tipo: $request->tipo,
                dataPesquisa: $request->data_pesquisa
            );

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao consultar infrações: ' . $e->getMessage(), [
                'renavam' => $request->renavam
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar infrações do veículo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera FICI e retorna o link para download
     */
    public function gerarFici(Request $request): JsonResponse
    {
        $request->validate([
            'tipo' => 'required|string',
            'ait' => 'required|string',
            'orgao' => 'required|string'
        ]);

        try {
            $caminhoArquivo = $this->smartecService->gerarFici(
                tipo: $request->tipo,
                ait: $request->ait,
                orgao: $request->orgao
            );

            // Gera URL para download
            $nomeArquivo = basename($caminhoArquivo);
            $urlDownload = asset('storage/fici/' . date('Y/m/') . $nomeArquivo);

            return response()->json([
                'success' => true,
                'message' => 'FICI gerado com sucesso',
                'data' => [
                    'arquivo' => $nomeArquivo,
                    'caminho' => $caminhoArquivo,
                    'url_download' => $urlDownload
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar FICI: ' . $e->getMessage(), [
                'ait' => $request->ait,
                'orgao' => $request->orgao
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar FICI',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Solicita desconto de 40% em infração
     */
    public function solicitarDesconto(Request $request): JsonResponse
    {
        $request->validate([
            'ait' => 'required|string',
            'codigo_orgao' => 'required|string',
            'reconhecer_infracao' => 'required|boolean',
            'tipo' => 'required|string'
        ]);

        try {
            $resultado = $this->smartecService->solicitarDescontoQuarenta(
                ait: $request->ait,
                codigoOrgao: $request->codigo_orgao,
                reconhecerInfracao: $request->reconhecer_infracao,
                tipo: $request->tipo
            );

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Solicitação de desconto enviada com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao solicitar desconto: ' . $e->getMessage(), [
                'ait' => $request->ait,
                'codigo_orgao' => $request->codigo_orgao
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao solicitar desconto de 40%',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
