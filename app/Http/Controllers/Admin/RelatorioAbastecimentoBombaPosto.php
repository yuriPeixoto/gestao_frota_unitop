<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbastecimentoIntegracao;
use App\Models\Bomba;
use App\Models\Fornecedor;
use App\Models\VAbastecimento;
use App\Models\Veiculo;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Traits\ExportableTrait;

class RelatorioAbastecimentoBombaPosto extends Controller
{
    use ExportableTrait;


    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = VAbastecimento::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }

        if ($request->filled('id_bomba')) {
            $query->where('id_bomba', $request->input('id_bomba'));
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        $veiculo = Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->limit(30)->get();
        $fornecedor = Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')->orderBy('nome_fornecedor')->limit(30)->get();
        $bomba = Bomba::select('id_bomba as value', 'descricao_bomba as label')->orderBy('descricao_bomba')->limit(30)->get();


        return view('admin.abastecimentoporbomposto.index', compact('veiculo', 'fornecedor', 'bomba'));
    }

    public function exportPdf(Request $request)
    {
        // Lógica para exportar o relatório em PDF

        try {

            Log::info('Iniciando exportação de PDF', $request->all());

            $query = VAbastecimento::query();

            if ($request->filled('id_fornecedor')) {
                $query->where('id_fornecedor', $request->input('id_fornecedor'));
            }

            if ($request->filled('id_bomba')) {
                $query->where('id_bomba', $request->input('id_bomba'));
            }

            if ($request->filled('id_veiculo')) {
                $query->where('id_veiculo', $request->input('id_veiculo'));
            }

            if ($request->filled('data_inicio') && $request->filled('data_final')) {
                $query->whereBetween('data_inicio', [$request->data_inicio, $request->data_final]);
            } elseif ($request->filled('data_inicio')) {
                $query->whereDate('data_inicio', '>=', $request->data_inicio);
            } elseif ($request->filled('data_final')) {
                $query->whereDate('data_inicio', '<=', $request->data_final);
            }

            $data = $query->get();

            Log::info('Total resultados:', ['count' => $data->count()]);

            $html = View::make('admin.abastecimentoporbomposto._pdf', compact('data'))->render();

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="relatorioabastecimentobombaposto.pdf"');
        } catch (\Exception $e) {
            Log::error('Erro ao exportar PDF: ' . $e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    }

    public function exportXls(Request $request)
    {
        Log::info('Iniciando exportação de EXCEL', $request->all());

        // monta query com filtros
        $query = $this->buildExportQuery($request);

        // busca os dados
        $data = $query->get();

        // agrupa por descricao_bomba
        $grupos = $data->groupBy('descricao_bomba');

        // cria array final para exportar
        $exportData = [];

        foreach ($grupos as $descricao_bomba => $abastecimentos) {
            // linha de título do grupo
            $exportData[] = [
                'descricao_bomba'      => strtoupper($descricao_bomba),
                'data_inicio'          => null,
                'placa'                => null,
                'tipo_combustivel'     => null,
                'volume'               => null,
                'valor'                => null,
                'km_abastecimento'     => null,
                '_is_group'            => true,
            ];

            // adiciona abastecimentos do grupo
            foreach ($abastecimentos as $a) {
                $exportData[] = [
                    'descricao_bomba'      => null,
                    'data_inicio'          => \Carbon\Carbon::parse($a->data_inicio)->format('d/m/Y H:i'),
                    'placa'                => $a->placa,
                    'tipo_combustivel'     => $a->tipocombustivel ?? '-',
                    'volume'               => number_format($a->volume, 2, ',', '.'),
                    'valor'                => 'R$ ' . number_format($a->valor, 2, ',', '.'),
                    'km_abastecimento'     => $a->km_abastecimento ?? '-',
                ];
            }

            // linha de total
            $exportData[] = [
                'descricao_bomba'      => 'Total ' . strtoupper($descricao_bomba),
                'data_inicio'          => null,
                'placa'                => null,
                'tipo_combustivel'     => null,
                'volume'               => number_format($abastecimentos->sum('volume'), 2, ',', '.'),
                'valor'                => 'R$ ' . number_format($abastecimentos->sum('valor'), 2, ',', '.'),
                'km_abastecimento'     => null,
            ];

            // linha em branco entre grupos
            $exportData[] = [
                'descricao_bomba'      => null,
                'data_inicio'          => null,
                'placa'                => null,
                'tipo_combustivel'     => null,
                'volume'               => null,
                'valor'                => null,
                'km_abastecimento'     => null,
            ];
        }

        Log::info('Total de grupos exportados: ' . count($grupos));

        // colunas do Excel
        $columns = [
            'descricao_bomba'  => 'Descricao Bomba / Total',
            'data_inicio'      => 'Data',
            'placa'            => 'Placa',
            'tipo_combustivel' => 'Combustível',
            'volume'           => 'Volume (L)',
            'valor'            => 'Valor',
            'km_abastecimento' => 'Km Abastecimento',
        ];

        return $this->exportToExcel($request, collect($exportData), $columns, 'relatorio_abastecimento_bomba_posto');
    }



    public function buildExportQuery(Request $request)
    {
        $query = VAbastecimento::query();

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->input('id_fornecedor'));
        }

        if ($request->filled('id_bomba')) {
            $query->where('id_bomba', $request->input('id_bomba'));
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        if ($request->filled('data_inicio') && $request->filled('data_final')) {
            $query->whereBetween('data_inicio', [$request->data_inicio, $request->data_final]);
        } elseif ($request->filled('data_inicio')) {
            $query->whereDate('data_inicio', '>=', $request->data_inicio);
        } elseif ($request->filled('data_final')) {
            $query->whereDate('data_inicio', '<=', $request->data_final);
        }




        return $query->orderByDesc('data_inicio');
    }
}
