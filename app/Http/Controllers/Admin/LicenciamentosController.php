<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Licenciamentos;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LicenciamentosController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;

    public function index(Request $request)
    {
        $query = Licenciamentos::query()
            ->where('valor', '!=', '0');

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        $query->orderBy('placa', 'desc');

        $licenciamentos = $query->paginate(10);

        $placa = $this->getPlaca();

        $renavam = $this->getRenavam();

        // Retornar a view
        return view('admin.licenciamentos.index', compact('licenciamentos', 'placa', 'renavam'));
    }

    private function getPlaca()
    {
        return Licenciamentos::where('valor', '!=', '0')
            ->select(
                'placa as value',
                'placa as label'
            )
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }

    private function getRenavam()
    {

        return Licenciamentos::where('valor', '!=', '0')
            ->select(
                'renavam as value',
                'renavam as label'
            )
            ->orderBy('renavam', 'asc')
            ->get()
            ->toArray();
    }

    protected function buildExportQuery(Request $request)
    {
        $query = Licenciamentos::orderBy('placa', 'desc');

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }


        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'placa',
            'renavam',
            'ano'
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
                $pdf->loadView('admin.licenciamentos.pdf', compact('data'));

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
            'tipo' => 'Tipo',
            'uf' => 'Uf',
            'mes' => 'Mes',
            'ano' => 'Ano',
            'status' => 'Status',
            'valor' => 'Valor'
        ];

        return $this->exportToCsv($request, $query, $columns, 'licenciamentos', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'tipo' => 'Tipo',
            'uf' => 'Uf',
            'mes' => 'Mes',
            'ano' => 'Ano',
            'status' => 'Status',
            'valor' => 'Valor'
        ];

        return $this->exportToExcel($request, $query, $columns, 'licenciamentos', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'Placa' => 'placa',
            'Tipo' => 'tipo',
            'Uf' => 'uf',
            'Mes' => 'mes',
            'Ano' => 'ano',
            'Status' => 'status',
            'Valor' => 'valor',

        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'licenciamentos',
            'licenciamento',
            'licenciamentos',
            $this->getValidExportFilters()
        );
    }

    public function baixarLote(Request $request)
    {
        if (!$request->filled('placa')) {
            return back()->withErrors(['placa' => 'Placa obrigatória']);
        }

        $placas = explode(',', $request->input('placa'));

        return $this->gerarZipDeArquivos([
            'tabela' => 'v_smartec_licenciamento',
            'coluna_url' => 'guia',
            'coluna_nome' => 'placa',
            'filtros' => [
                'placa' => $placas
            ],
            'prefixo' => 'boletos',
        ]);
    }
}
