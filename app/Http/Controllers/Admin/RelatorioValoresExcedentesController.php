<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalcularPremioMensaleRV;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\JasperServerIntegration as TraitsJasperServerIntegration;

class RelatorioValoresExcedentesController extends Controller
{
    public function index(Request $request)
    {
        $query = CalcularPremioMensaleRV::query();


        if ($request->filled('id_mot_unitop')) {
            $query->where('id_mot_unitop', $request->input('id_mot_unitop'));
        }
        if ($request->filled('placa_sascar')) {
            $query->where('placa_sascar', $request->input('placa_sascar'));
        }

        $placa = CalcularPremioMensaleRV::select('placa_sascar as value', 'placa_sascar as label')
            ->orderBy('placa_sascar')
            ->limit(30)
            ->get();

        $motorista = CalcularPremioMensaleRV::select('nome_motorista as value', 'nome_motorista as label')
            ->orderBy('nome_motorista')
            ->limit(30)
            ->get();

        return view('admin.relatoriovaloresexcedentes.index', compact('motorista', 'placa'));
    }

    public function gerarPdf(Request $request)
    {

        if (empty($request['id_mot_unitop'])) {
            $P_in_id_nome  = '!=';
            $P_id_id_nome  = '0';
        } else {
            $P_in_id_nome  = '=';
            $P_id_id_nome  = $request['id_mot_unitop'];
        }

        $P_in_placa = '!=';
        $P_id_placa = '0';

        if (!empty($request['placa_sascar'])) {
            if (is_array($request['placa_sascar'])) {
                $P_in_placa  = 'IN';
                $P_id_placa  = implode(',', $request['placa_sascar']);
            } else {
                $P_in_placa  = '=';
                $P_id_placa  = $request['placa_sascar'];
            }
        }

        $parametros = array(
            'P_in_motorista' => $P_in_id_nome,
            'P_id_motorista' => $P_id_id_nome,
            'P_in_placa' => $P_in_placa,
            'P_id_placa' => $P_id_placa
        );

        //== define pararemetros relatorios  
        $name  = 'Relatorio_premio_de_valores_excedente_previa';
        $agora = now()->format('d-m-Y_H-i');
        $relatorio = "{$name}_{$agora}.pdf";

        $host = $request->getHost();
        $pathrel = explode('.', $host);
        $dominio = $pathrel[0];

        Log::info('Configurações do servidor:', [
            'host' => $host,
            'dominio' => $dominio,
            'relatorio' => $relatorio
        ]);

        if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
            $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
            $pastarelatorio = '/reports/carvalima/' . $name;

            Log::info('Usando servidor de homologação');
        } elseif ($dominio == 'lcarvalima') {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/carvalima/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;

            Log::info('Usando servidor de produção');
        } else {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/' . $dominio . '/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;

            Log::info('Usando servidor de produção');
        }

        Log::info("Gerando PDF: {$relatorio}");
        Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
        Log::info("Parâmetros:", $parametros);

        try {
            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            $data = $jsi->execute();

            // Verifica se retorno está vazio ou muito pequeno
            if (empty($data) || strlen($data) < 100) {
                Log::error("Relatório pdf gerado vazio ou muito pequeno: tamanho " . strlen($data));
                return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            // Salva local para debug (opcional)
            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao gerar pdf: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório pdf.'], 500);
        }
    }

    public function gerarExcel(Request $request)
    {
        if (empty($request['id_mot_unitop'])) {
            $P_in_id_nome  = '!=';
            $P_id_id_nome  = '0';
        } else {
            $P_in_id_nome  = '=';
            $P_id_id_nome  = $request['id_mot_unitop'];
        }

        $P_in_placa = '!=';
        $P_id_placa = '0';

        if (!empty($request['placa_sascar'])) {
            if (is_array($request['placa_sascar'])) {
                $P_in_placa  = 'IN';
                $P_id_placa  = implode(',', $request['placa_sascar']);
            } else {
                $P_in_placa  = '=';
                $P_id_placa  = $request['placa_sascar'];
            }
        }

        $parametros = array(
            'P_in_motorista' => $P_in_id_nome,
            'P_id_motorista' => $P_id_id_nome,
            'P_in_placa' => $P_in_placa,
            'P_id_placa' => $P_id_placa
        );

        //== define pararemetros relatorios  
        $name  = 'Relatorio_premio_de_valores_excedente_previa';
        $agora = now()->format('d-m-Y_H-i');
        $relatorio = "{$name}_{$agora}.xls";

        $host = $request->getHost();
        $pathrel = explode('.', $host);
        $dominio = $pathrel[0];

        Log::info('Configurações do servidor:', [
            'host' => $host,
            'dominio' => $dominio,
            'relatorio' => $relatorio
        ]);

        if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
            $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
            $pastarelatorio = '/reports/carvalima/' . $name;

            Log::info('Usando servidor de homologação');
        } elseif ($dominio == 'lcarvalima') {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/carvalima/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;

            Log::info('Usando servidor de produção');
        } else {
            $jasperserver = 'http://10.10.1.8:8080/jasperserver';
            $input = '/reports/' . $dominio . '/' . $name;

            // Verificar se o diretório existe antes de tentar chmod
            if (is_dir($input)) {
                chmod($input, 0777);
                Log::info('Permissões do diretório alteradas: ' . $input);
            } else {
                Log::warning('Diretório não encontrado: ' . $input);
            }

            $pastarelatorio = $input;

            Log::info('Usando servidor de produção');
        }

        Log::info("Gerando XLS: {$relatorio}");
        Log::info("Servidor: {$jasperserver}, Caminho: {$pastarelatorio}");
        Log::info("Parâmetros:", $parametros);

        try {
            $jsi = new TraitsJasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'xls',
                'unitop',
                'unitop2022',
                $parametros
            );

            $data = $jsi->execute();

            // Verifica se retorno está vazio ou muito pequeno
            if (empty($data) || strlen($data) < 100) {
                Log::error("Relatório xls gerado vazio ou muito pequeno: tamanho " . strlen($data));
                return response()->json(['message' => 'O relatório retornou vazio ou inválido.'], 500);
            }

            // Salva local para debug (opcional)
            file_put_contents(storage_path("app/public/{$relatorio}"), $data);

            return response($data, 200, [
                'Content-Type' => 'application/xls',
                'Content-Disposition' => "attachment; filename=\"{$relatorio}\"",
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao gerar xls: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao gerar o relatório pdf.'], 500);
        }
    }
}
