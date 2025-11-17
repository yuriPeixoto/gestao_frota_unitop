<?php

namespace App\Modules\Veiculos\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\TransferenciaVeiculo;
use App\Models\Veiculo;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Traits\ExportableTrait;
use Carbon\Carbon;

class RelatorioTransferenciaVeiculo extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = TransferenciaVeiculo::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                Carbon::parse($request->input('data_inclusao'))->startOfDay(),
                Carbon::parse($request->input('data_final'))->endOfDay(),
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        if ($request->filled('id_filial_origem')) {
            $query->where('id_filial_origem', $request->input('id_filial_origem'));
        }

        if ($request->filled('id_filial_destino')) {
            $query->where('id_filial_destino', $request->input('id_filial_destino'));
        }

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $filialdestino = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $placa = Veiculo::select('id_veiculo as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        return view('admin.relatoriotransferenciaveiculo.index', compact('filial', 'filialdestino', 'placa'));
    }

    public function exportPdf(Request $request)
    {
        try {
            Log::info('Iniciando exportação de PDF', $request->all());

            // Corrigido: model correto
            $query  = TransferenciaVeiculo::with(['filialOrigem', 'veiculo', 'filialDestino']); // se precisar dos relacionamentos

            // aplica os filtros somente se vierem preenchidos
            if ($request->filled('data_inclusao') && $request->filled('data_final')) {
                $query->whereBetween('data_inclusao', [
                    $request->input('data_inclusao'),
                    $request->input('data_final')
                ]);
            }

            if ($request->filled('id_veiculo')) {
                $query->where('id_veiculo', $request->input('id_veiculo'));
            }

            if ($request->filled('id_filial_origem')) {
                $query->where('id_filial_origem', $request->input('id_filial_origem'));
            }

            if ($request->filled('id_filial_destino')) {
                $query->where('id_filial_destino', $request->input('id_filial_destino'));
            }

            $data = $query->get();

            Log::info('Total resultados:', ['count' => $data->count()]);

            $html = View::make('admin.relatoriotransferenciaveiculo._pdf', compact('data'))->render();

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="relatoriotransferenciaveiculo.pdf"');
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
            'filialOrigem.name' => 'Cód Filial Origem',
            'filialDestino.name' => 'Cód Filial Destino',
            'km_transferencia' => 'KM Transferencia',
            'veiculo.placa' => 'Cód. Veiculo',
            'data_transferencia' => 'Data Transferencia',

        ];

        return $this->exportToExcel($request, $query, $columns, 'relatorios');
    }

    public function buildExportQuery(Request $request)
    {
        $query  = TransferenciaVeiculo::with(['filialOrigem', 'veiculo', 'filialDestino']);


        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                Carbon::parse($request->input('data_inclusao'))->startOfDay(),
                Carbon::parse($request->input('data_final'))->endOfDay(),
            ]);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->input('id_veiculo'));
        }

        if ($request->filled('id_filial_origem')) {
            $query->where('id_filial_origem', $request->input('id_filial_origem'));
        }

        if ($request->filled('id_filial_destino')) {
            $query->where('id_filial_destino', $request->input('id_filial_destino'));
        }

        return $query->with([

            'filialOrigem',
            'veiculo',
            'filialDestino'

        ])->orderByDesc('id_transferencia');
    }
}
