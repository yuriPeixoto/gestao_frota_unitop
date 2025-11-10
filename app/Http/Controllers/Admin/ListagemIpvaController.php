<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ListagemIpva;
use Illuminate\Http\Request;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Exception;
use Illuminate\Support\Facades\Log;

class ListagemIpvaController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;

    public function index(Request $request)
    {
        $query = ListagemIpva::query();

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

        $listagemIpvas = $query->paginate(10);

        $placa = $this->getPlaca();

        $renavam = $this->getRenavam();

        // Retornar a view
        return view('admin.listagemipva.index', compact('listagemIpvas', 'placa', 'renavam'));
    }

    private function getPlaca()
    {
        return ListagemIpva::select(
            'placa as value',
            'placa as label'
        )
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }

    private function getRenavam()
    {

        return ListagemIpva::select(
            'renavam as value',
            'renavam as label'
        )
            ->orderBy('renavam', 'asc')
            ->get()
            ->toArray();
    }

    protected function buildExportQuery(Request $request)
    {
        $query = ListagemIpva::orderBy('placa', 'desc');

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
                $pdf->loadView('admin.listagemIpva.pdf', compact('data'));

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
            'proprietario' => 'Proprietario',
            'tipo' => 'Tipo',
            'uf' => 'uf',
            'cota_unica_sem_desconto' => 'Cota Unica sem desconto',
            'cota_unica_desconto1' => 'Cota Unica desconto',
            'boleto_cota_unica_vencimento' => 'Boleto cota unica vencimento',
        ];

        return $this->exportToCsv($request, $query, $columns, 'listagemIpva', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'renavam' => 'Renavam',
            'proprietario' => 'Proprietario',
            'tipo' => 'Tipo',
            'uf' => 'Uf',
            'cota_unica_sem_desconto' => 'Cota Unica sem desconto',
            'cota_unica_desconto1' => 'Cota Unica desconto',
            'boleto_cota_unica_vencimento' => 'Boleto cota unica vencimento',
        ];

        return $this->exportToExcel($request, $query, $columns, 'listagemIpva', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'placa' => 'placa',
            'renavam' => 'renavam',
            'proprietario' => 'Proprietario',
            'tipo' => 'tipo',
            'uf' => 'Uf',
            'cota_unica_sem_desconto' => 'cota_unica_sem_desconto',
            'cota_unica_desconto' => 'cota_unica_desconto1',
            'boleto_cota_unica_vencimento' => 'boleto_cota_unica_vencimento',

        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'listagemIpvas',
            'listagemIpva',
            'listagemIpvas',
            $this->getValidExportFilters()
        );
    }

    public function baixarLote(Request $request)
    {

        if (!$request->filled('placa')) {
            return back()->withErrors(['placa' => 'Placa obrigatória']);
        }

        return $this->gerarZipDeArquivos([
            'tabela' => 'smartec_vencimentos_ipva',
            'coluna_url' => 'url_cota_unica',
            'coluna_nome' => 'placa',
            'filtros' => [['placa', '=', $request->input('placa')]],
            'prefixo' => 'boletos',
        ]);
    }
}
