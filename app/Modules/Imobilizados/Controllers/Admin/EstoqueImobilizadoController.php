<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Imobilizados\Models\VEstoqueImobilizado;
use App\Traits\ExportableTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class EstoqueImobilizadoController extends Controller
{

    use ExportableTrait;

    public function index(Request $request)
    {


        $query = VEstoqueImobilizado::query();

        if ($request->filled('id_produto_unitop')) {
            $query->where('id_produto_unitop', $request->id_produto_unitop);
        }

        if ($request->filled('descricao_filial')) {
            $query->where('descricao_filial', $request->descricao_filial);
        }

        if ($request->filled('descricao_departamento')) {
            $query->where('descricao_departamento', $request->descricao_departamento);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $estoqueImobilizados = $query->latest('id_produto_unitop')
            ->orderBy('id_produto_unitop', 'desc')
            ->paginate(30)
            ->appends($request->query());

        $id_produto_unitop = $this->getIdProdutoUnitop();

        $filial = $this->getFilial();

        $departamento = $this->getDepartamento();

        $status = $this->getStatus();

        return view(
            'admin.estoqueimobilizado.index',
            compact(
                'estoqueImobilizados',
                'id_produto_unitop',
                'filial',
                'departamento',
                'status'
            )
        );
    }


    private function getIdProdutoUnitop()
    {
        return VEstoqueImobilizado::select('id_produto_unitop as value', 'id_produto_unitop as label')
            ->orderBy('id_produto_unitop', 'desc')
            ->get()
            ->toArray();
    }

    private function getFilial()
    {
        return VEstoqueImobilizado::select('descricao_filial as value', 'descricao_filial as label')
            ->orderBy('descricao_filial', 'desc')
            ->get()
            ->toArray();
    }

    private function getDepartamento()
    {
        return VEstoqueImobilizado::select('descricao_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento', 'desc')
            ->get()
            ->toArray();
    }

    private function getStatus()
    {
        return VEstoqueImobilizado::select('status as value', 'status as label')
            ->orderBy('status', 'desc')
            ->get()
            ->toArray();
    }

    protected function buildExportQuery(Request $request)
    {

        $query = VEstoqueImobilizado::query();

        if ($request->filled('id_produto_unitop')) {
            $query->where('id_produto_unitop', $request->id_produto_unitop);
        }

        if ($request->filled('descricao_filial')) {
            $query->where('descricao_filial', $request->descricao_filial);
        }

        if ($request->filled('descricao_departamento')) {
            $query->where('descricao_departamento', $request->descricao_departamento);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    protected function getValidExportFilters()
    {
        return [
            'id_produto_unitop',
            'descricao_filial',
            'descricao_departamento',
            'status',
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
                $pdf->loadView('admin.estoqueimobilizado.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('estoqueimobilizado_' . date('Y-m-d_His') . '.pdf');
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
            'id_produto_unitop' => 'Código Produto',
            'descricao_produto' => 'Descrição Produto',
            'descricao_filial' => 'Filial',
            'descricao_departamento' => 'Departamento',
            'status' => 'Status',
            'quantidade_imobilizados' => 'Quantidade',
            'valor_medio' => 'Valor Médio',
            'total' => 'Total'
        ];

        return $this->exportToCsv($request, $query, $columns, 'estoqueimobilizado', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_produto_unitop' => 'Código Produto',
            'descricao_produto' => 'Descrição Produto',
            'descricao_filial' => 'Filial',
            'descricao_departamento' => 'Departamento',
            'status' => 'Status',
            'quantidade_imobilizados' => 'Quantidade',
            'valor_medio' => 'Valor Médio',
            'total' => 'Total'
        ];

        return $this->exportToExcel($request, $query, $columns, 'estoqueimobilizado', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'codigo_produto' =>  'id_produto_unitop',
            'descricao_produto' => 'descricao_produto',
            'filial' => 'descricao_filial',
            'departamento' => 'descricao_departamento',
            'status' => 'status',
            'quantidade' => 'quantidade_imobilizados',
            'valor_medio' => 'valor_medio',
            'total' => 'total',
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'estoqueimobilizados',
            'estoqueimobilizado',
            'estoqueimobilizados',
            $this->getValidExportFilters()
        );
    }
}
