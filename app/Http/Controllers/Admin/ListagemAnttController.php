<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\ListagemAntt;
use Illuminate\Http\Request;
use App\Traits\ExportableTrait;
use App\Traits\LoteDownloadTrait;
use Exception;
use Illuminate\Support\Facades\Log;

class ListagemAnttController extends Controller
{
    use ExportableTrait, LoteDownloadTrait;

    public function index(Request $request)
    {
        $query = ListagemAntt::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('ait')) {
            $query->where('ait', $request->ait);
        }

        if ($request->filled('processo')) {
            $query->where('processo', $request->processo);
        }

        $query->orderBy('placa', 'desc');

        $listagemAntts = $query->paginate(10);

        $placa = $this->getPlaca();

        $processo = $this->getProcesso();

        $ait = $this->getAit();

        // Retornar a view
        return view('admin.listagemantt.index', compact('listagemAntts', 'placa', 'processo', 'ait'));
    }

    private function getPlaca()
    {
        return ListagemAntt::select(
            'placa as value',
            'placa as label'
        )
            ->orderBy('placa', 'asc')
            ->get()
            ->toArray();
    }

    private function getProcesso()
    {

        return ListagemAntt::select(
            'processo as value',
            'processo as label'
        )
            ->orderBy('processo', 'asc')
            ->get()
            ->toArray();
    }

    private function getAit()
    {

        return ListagemAntt::select(
            'ait as value',
            'ait as label'
        )
            ->orderBy('ait', 'asc')
            ->get()
            ->toArray();
    }

    protected function buildExportQuery(Request $request)
    {
        $query = ListagemAntt::orderBy('placa', 'desc');

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
                $pdf->loadView('admin.listagemantt.pdf', compact('data'));

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
            'cnpj' => 'CNPJ',
            'processo' => 'Processo',
            'ait' => 'AIT',
            'data_emissao' => 'Data Infração',
            'codigo' => 'Codigo',
            'descricao' => 'Descrição',
            'situacao' => 'Situação',
            'data_notificacao' => 'Data Notificação',
            'local' => 'Local',
            'valor' => 'Valor',
        ];

        return $this->exportToCsv($request, $query, $columns, 'listagemantt', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'placa' => 'Placa',
            'cnpj' => 'CNPJ',
            'processo' => 'Processo',
            'ait' => 'AIT',
            'data_emissao' => 'Data Infração',
            'codigo' => 'Codigo',
            'descricao' => 'Descrição',
            'situacao' => 'Situação',
            'data_notificacao' => 'Data Notificação',
            'local' => 'Local',
            'valor' => 'Valor',
        ];

        return $this->exportToExcel($request, $query, $columns, 'listagemantt', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'placa' => 'placa',
            'CNPJ' => 'cnpj',
            'processo' =>  'processo',
            'AIT' => 'ait',
            'data_infração' => 'data_emissao',
            'codigo' =>  'codigo',
            'descrição' => 'descricao',
            'situação' => 'situacao',
            'data_notificação' => 'data_notificacao',
            'local' => 'local',
            'vlistagemanttlistagemanttalor' =>  'valor',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'listagemantts',
            'listagemantt',
            'listagemantts ',
            $this->getValidExportFilters()
        );
    }
}
