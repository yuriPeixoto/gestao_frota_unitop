<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IntegradorSmartecService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('services.smartec.base_url', 'https://sistema.smartec.com.br/api');
        $this->token = config('services.smartec.token', env('SMARTEC_TOKEN'));
    }

    /**
     * Consulta informações de um veículo
     */
    public function consultarVeiculo(
        string $placa,
        string $uf,
        string $frota,
        string $prefixo,
        string $cnpjCpf,
        string $dataBase,
        string $renavam,
        string $tipo
    ): array|object {
        $dados = [
            'Token'     => $this->token,
            'Renavam'   => $renavam,
            'Placa'     => $placa,
            'Uf'        => $uf,
            'Frota'     => $frota,
            'Prefixo'   => $prefixo,
            'CnpjCpf'   => $cnpjCpf,
            'DataBase'  => $dataBase,
            'Tipo'      => $tipo,

        ];


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
            ->post("{$this->baseUrl}/Veiculo", $dados);

        Log::info('Status da resposta CNH', ['status' => $response->status()]);
        Log::info('Headers da resposta CNH', $response->headers());
        Log::info('Status completo CNH', [
            'status' => $response->status(),
            'ok' => $response->ok(),
            'successful' => $response->successful(),
            'body' => $response->body()
        ]);

        return $this->makeRequest('POST', '/Veiculo', $dados);
    }

    /**
     * Indica um condutor para uma infração
     */
    public function indicarInfracao(
        string $nome,
        string $cnh,
        string $tipo,
        string $ait,
        ?string $codigoOrgao = null
    ): array|object {
        $dados = [
            'Nome'           => $nome,
            'Ait'            => $ait,
            'Cnh'            => $cnh,
            'Tipo'           => $tipo,
            'Token'          => $this->token
        ];


        if ($codigoOrgao) {
            $dados['CodigoOrgao'] = $codigoOrgao;
        }

        return $this->makeRequest('POST', '/Infracoes', $dados);
    }

    /**
     * Exclui uma indicação de infração
     */
    public function excluirIndicacao(string $tipo, string $ait): array|object
    {
        $dados = [
            'Ait'            => $ait,
            'Tipo'           => $tipo,
            'Token'          => $this->token
        ];

        return $this->makeRequest('POST', '/Infracoes', $dados);
    }

    /**
     * Cadastra dados de CNH
     */
    public function cadastrarCnh(array $dadosCnh, string $tipo): array|object
    {
        $dados = [
            'Token' => $this->token,
            'Tipo' => $tipo,
            'Nome' => $dadosCnh['Nome'] ?? '',
            'Cpf' => $dadosCnh['Cpf'] ?? '',
            'Cnh' => $dadosCnh['Cnh'] ?? '',
            'Uf' => $dadosCnh['Uf'] ?? '',
            'RenaCh' => $dadosCnh['RenaCh'] ?? '',
            'Validade' => $dadosCnh['Validade'] ?? '',
            'DataNascimento' => $dadosCnh['DataNascimento'] ?? '',
            'Cedula' => $dadosCnh['Cedula'] ?? '',
            'Data1Habilitacao' => $dadosCnh['Data1Habilitacao'] ?? '',
            'Rg' => $dadosCnh['Rg'] ?? '',
            'UfNascimento' => $dadosCnh['UfNascimento'] ?? '',
            'MunicipioNascimento' => $dadosCnh['MunicipioNascimento'] ?? '',
            'Municipio' => $dadosCnh['Municipio'] ?? '',
            'CodigoSeguranca' => $dadosCnh['CodigoSeguranca'] ?? '',
            'Categoria' => $dadosCnh['Categoria'] ?? '',
            'Grupo' => $dadosCnh['Grupo'] ?? '',
            'Apelido' => $dadosCnh['Apelido'] ?? '',
        ];

        Log::info('Payload final da CNH', $dados);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
            ->post("{$this->baseUrl}/cnh", $dados);

        try {
            $body = json_decode($response->body());

            if ($body === null) {
                throw new \Exception('Resposta da API vazia ou inválida JSON');
            }

            Log::info('Resposta CNH', (array) $body);

            return $body; // pode ser array ou object, conforme declarado
        } catch (\Exception $e) {
            Log::error('Erro ao converter resposta da API CNH para objeto', [
                'resposta' => $response->body(),
                'erro' => $e->getMessage(),
            ]);

            Log::info('Status da resposta CNH', ['status' => $response->status()]);
            Log::info('Headers da resposta CNH', $response->headers());
            Log::info('Status completo CNH', [
                'status' => $response->status(),
                'ok' => $response->ok(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);
            $dados = array_filter($dados, fn($value) => $value !== '');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/cnh", $dados);
            return (object)[
                'Message' => 'Erro na resposta da API: ' . $e->getMessage(),
                'RawResponse' => $response->body()
            ];
        }
    }





    /**
     * Consulta CNH por CPF
     */
    public function excluirCnh(string $cpf): array|object
    {
        return $this->makeRequest('POST', '/Cnh', [
            'Cpf'   => $cpf,
            'Tipo'  => 'EXCLUIR',
            'Token' => $this->token
        ]);
    }

    public function consultarCnh(string $cpf): array|object|null
    {
        return $this->makeRequest('POST', '/Cnh', [
            'Cpf'   => $cpf,
            'Tipo'  => 'CONSULTAR',
            'Token' => $this->token
        ]);
    }
    /**
     * Consulta infrações por RENAVAM
     */
    public function consultarInfracoes(
        string $renavam,
        string $tipo,
        string $dataPesquisa
    ): object|array {  // aceita objeto ou array
        $dados = [
            'Renavam'      => $renavam,
            'Tipo'         => $tipo,
            'Token'        => $this->token,
            'DataPesquisa' => $dataPesquisa
        ];

        return $this->makeRequest('POST', '/Infracoes', $dados);
    }


    /**
     * Gera FICI e salva o arquivo PDF
     */
    public function gerarFici(string $tipo, string $ait, string $orgao): string
    {
        $dados = [
            'Ait'            => $ait,
            'Tipo'           => $tipo,
            'Token'          => $this->token,
            'CodigoOrgao'    => $orgao
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/Infracoes', $dados);

            if ($response->successful()) {
                $headers = $response->headers();
                $body = $response->body();

                // Extrai o nome do arquivo do cabeçalho Content-Disposition
                $filename = 'FICI.pdf';
                if (isset($headers['Content-Disposition'])) {
                    preg_match('/filename="([^"]+)"/', $headers['Content-Disposition'][0] ?? '', $matches);
                    $filename = $matches[1] ?? 'FICI.pdf';
                }

                // Caminho relativo dentro da pasta public
                $caminhoArquivo = 'fici/' . date('Y/m/') . $filename;

                // Salva o arquivo
                Storage::disk('public')->put($caminhoArquivo, $body);

                // ✅ Retorna apenas o caminho relativo para uso com Storage::url()
                return $caminhoArquivo;
            }

            throw new \Exception('Erro ao gerar FICI: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Erro ao gerar FICI: ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Solicita desconto de 40% em infração
     */
    public function solicitarDescontoQuarenta(
        string $ait,
        string $codigoOrgao,
        bool $reconhecerInfracao,
        string $tipo
    ): object {
        $codigoOrgao = str_pad($codigoOrgao, 6, '0', STR_PAD_LEFT);
        $chaveInfracao = $ait . $codigoOrgao;

        $dados = [
            'ChaveInfracao'      => $chaveInfracao,
            'ReconhecerInfracao' => $reconhecerInfracao,
            'Tipo'               => $tipo,
            'Token'              => $this->token
        ];

        return $this->makeRequest('POST', '/Infracoes', $dados);
    }


    public function infracoes(
        string $renavam,
        string $tipo,
        string $dataPesquisa
    ): object | array {
        $dados = array(

            'Renavam'        => $renavam,
            'Tipo'           => $tipo,
            'Token'          => $this->token,
            'DataPesquisa'   => $dataPesquisa
        );

        return $this->makeRequest('POST', '/Infracoes', $dados);
    }
    /**
     * Método privado para fazer requisições HTTP
     */
    private function makeRequest(string $method, string $endpoint, array $data): array|object
    {
        try {
            $timeout = config('services.smartec.timeout', 30);

            Log::info('Enviando dados para Smartec ' . $endpoint, ['dados' => $data]);

            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json'
                ])
                ->{strtolower($method)}($this->baseUrl . $endpoint, $data);

            Log::info('Resposta bruta da API Smartec', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $body = $response->body();
            $obj = json_decode($body);

            // Body vazio ou inválido
            if (empty($obj)) {
                Log::warning('Resposta 200 recebida, mas sem body válido', [
                    'status' => $response->status(),
                    'body' => $body
                ]);

                return (object)[
                    'IdErro' => -2,
                    'MensagemErro' => 'API retornou sucesso, mas sem conteúdo.',
                    'Detalhes' => 'Body vazio ou inválido'
                ];
            }

            // Mesmo que status seja 400, mas o body indica IdErro 2000, consideramos sucesso
            if ($response->status() === 400 && is_array($obj) && isset($obj[0]->IdErro) && $obj[0]->IdErro == 2000) {
                return $obj;
            }

            if ($response->successful()) {
                return $obj;
            }

            // Erro real
            Log::error('Erro na requisição Smartec', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'response' => $body
            ]);

            throw new \Exception('Erro na API Smartec: ' . $body);
        } catch (\Exception $e) {
            Log::error('Erro na integração Smartec: ' . $e->getMessage(), [
                'endpoint' => $endpoint,
                'data' => $data
            ]);

            return (object)[
                'IdErro' => -1,
                'MensagemErro' => 'Falha na comunicação com Smartec',
                'Detalhes' => $e->getMessage()
            ];
        }
    }


    public function licenca(
        string $pagina,
        string $tipo,
    ): array|object {
        $dados = [
            'Pagina'           => $pagina,
            'Tipo'           => $tipo,
            'Token'          => $this->token
        ];

        return $this->makeRequest('POST', '/Infracoes', $dados);
    }

    public function consultarCronotacografo(
        string $renavam,
        string $tipo,
    ): array|object {
        $dados = [
            'Renavam'       => $renavam,
            'Tipo'          => $tipo,
            'Token'         => $this->token
        ];

        return $this->makeRequest('POST', '/Cronotacografo', $dados);
    }

    public function consultarLicenciamento(
        string $dataBase,
        string $mesAdicional,
        string $renavam,
        string $tipo,
    ): array|object {
        $dados = [
            'DataBase'      => $dataBase,
            'MesAdicional'  => $mesAdicional,
            'Renavam'       => $renavam,
            'Tipo'          => $tipo,
            'Token'         => $this->token
        ];

        return $this->makeRequest('POST', '/Licenciamento', $dados);
    }

    public function consultarAntt(
        string $pdfBase64,
        string $pagina,
        string $tipo
    ): array|object {

        $dados = [
            'PdfBase64'     => $pdfBase64,
            'Pagina'        => $pagina,
            'Tipo'          => $tipo,
            'Token'         => $this->token
        ];
        return $this->makeRequest('POST', '/Antt', $dados);
    }

    public function consultarIpva(
        string $renavam,
        string $dataBase,
        string $tipo,
    ) {
        $dados = [
            'Renavam'       => $renavam,
            'DataBase'      => $dataBase,
            'Tipo'          => $tipo,
            'Token'         => $this->token,
        ];

        return $this->makeRequest('POST', '/Ipva', $dados);
    }
}
