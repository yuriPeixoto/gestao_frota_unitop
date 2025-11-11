<?php

namespace App\Modules\Certificados\Controllers\Vencimentario;

use App\Http\Controllers\Controller;

use App\Modules\Certificados\Models\CronotacografoVencimentario;
use Illuminate\Http\Request;
use App\Traits\ExportableTrait;
use Illuminate\Support\Facades\Log;

class CronotacografoVencimentarioController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $query = CronotacografoVencimentario::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }


        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('datainspecao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('datainspecao', '<=', $request->data_inclusao_final);
        }

        $query->orderBy('renavam', 'desc');

        $cronotacografoVencimentarios = $query->paginate(10);

        $placa = $this->getPlaca();

        $renavam = $this->getRenavam();

        // Retornar a view
        return view('admin.cronotacografovencimentario.index', compact('cronotacografoVencimentarios', 'placa', 'renavam'));
    }


    private function getPlaca()
    {
        return CronotacografoVencimentario::select(
            'placa as value',
            'placa as label'
        )
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }

    private function getRenavam()
    {
        return CronotacografoVencimentario::select(
            'renavam as value',
            'renavam as label'
        )
            ->orderBy('renavam', 'asc')
            ->get()
            ->toArray();
    }

    protected function buildExportQuery(Request $request)
    {

        $query = CronotacografoVencimentario::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }


        if ($request->filled('data_inclusao_inicial')) {
            $query->whereDate('datainspecao', '>=', $request->data_inclusao_inicial);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('datainspecao', '<=', $request->data_inclusao_final);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'placa',
            'renavam',
            'data_inclusao_inicial',
            'data_inclusao_final',
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.cronotacografovencimentario.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('veiculos_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'renavam' => 'Renavam',
            'vencimento' => 'Vencimento',
            'status' => 'Status',
            'emissao' => 'Emissão',
            'documento' => 'Documento',
            'documento_n' => 'N° Documento',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'serie' => 'Serie',
        ];

        return $this->exportToCsv($request, $query, $columns, 'cronotacografovencimentario', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'renavam' => 'Renavam',
            'vencimento' => 'Vencimento',
            'status' => 'Status',
            'emissao' => 'Emissão',
            'documento' => 'Documento',
            'documento_n' => 'N° Documento',
            'marca' => 'Marca',
            'modelo' => 'Modelo',
            'serie' => 'Serie',
        ];

        return $this->exportToExcel($request, $query, $columns, 'cronotacografovencimentario', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'placa' => 'placa',
            'renavam' => 'renavam',
            'vencimento' => 'vencimento',
            'status' => 'status',
            'emissão' => 'emissao',
            'documento' => 'documento',
            'n_documento' => 'documento_n',
            'marca' => 'marca',
            'modelo' => 'modelo',
            'serie' => 'serie',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'controlelicencavencimentarios',
            'cronotacografovencimentario',
            'controlelicencavencimentarios',
            $this->getValidExportFilters()
        );
    }
}
