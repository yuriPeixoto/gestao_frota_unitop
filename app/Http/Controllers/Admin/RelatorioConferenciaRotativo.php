<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Estoque\Models\Estoque;
use App\Models\Filial;
use App\Models\VconferenciaRotativoDiario;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Traits\ExportableTrait;

class RelatorioConferenciaRotativo extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $query = VconferenciaRotativoDiario::query();

        if ($request->filled('data_baixa')) {
            $query->where('data_baixa', $request->input('data_baixa'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }
        if ($request->filled('id_estoque_produto')) {
            $query->where('id_estoque_produto', $request->input('id_estoque_produto'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $estoque = Estoque::select('id_estoque as value', 'descricao_estoque as label')
            ->orderBy('descricao_estoque')
            ->limit(30)
            ->get();

        return view('admin.relatorioconferenciarotativo.index', compact('filial', 'estoque'));
    }

    public function exportPdf(Request $request)
    {
        try {
            Log::info('Iniciando exportação de PDF', $request->all());

            // Corrigido: model correto
            $query  = VconferenciaRotativoDiario::with(['filial', 'estoque', 'produto']);

            // aplica os filtros somente se vierem preenchidos
            if ($request->filled('data_baixa')) {
                $query->where('data_baixa', $request->input('data_baixa'));
            }
            if ($request->filled('id_filial')) {
                $query->where('id_filial', $request->input('id_filial'));
            }
            if ($request->filled('id_estoque_produto')) {
                $query->where('id_estoque_produto', $request->input('id_estoque_produto'));
            }
            $data = $query->get();

            Log::info('Total resultados:', ['count' => $data->count()]);

            $html = View::make('admin.relatorioconferenciarotativo._pdf', compact('data'))->render();

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="relatorioconferenciarotativo.pdf"');
        } catch (Exception $e) {
            Log::error('Erro ao exportar PDF: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }

    public function exportXls(Request $request)
    {
        Log::info('Iniciando exportação de EXCEL', $request->all());

        // monta query com filtros
        $query = $this->buildExportQuery($request);

        $data = $query->get();

        Log::info('Total resultados:', ['count' => $data->count()]);

        $columns = [
            'id_produto' => 'Cód. Produto',
            'id_filial' => 'Cód. Carvalima',
            'produto.descricao_produto' => 'Produto',
            'valor_medio' => 'Valor Médio',
            'quantidade_atual_produto' => 'Quantidade',
            'localizacao_produto' => 'Locação',
            'contagem' => 'Contagem',
        ];

        return $this->exportToExcel($request, $query, $columns, 'relatorios');
    }

    public function buildExportQuery(Request $request)
    {
        $query  = VconferenciaRotativoDiario::with(['filial', 'estoque', 'produto']);


        if ($request->filled('data_baixa')) {
            $query->where('data_baixa', $request->input('data_baixa'));
        }
        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }
        if ($request->filled('id_estoque_produto')) {
            $query->where('id_estoque_produto', $request->input('id_estoque_produto'));
        }


        return $query->with([

            'filial',
            'produto',
            'estoque'

        ])->orderByDesc('id_produto');
    }
}
