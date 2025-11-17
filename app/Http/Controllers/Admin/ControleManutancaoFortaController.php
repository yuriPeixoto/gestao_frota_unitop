<?php

namespace App\Http\Controllers\Admin;


use App\Models\VControleManutencaoFrota;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ExportableTrait;
use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Veiculos\Models\ModeloVeiculo;
use App\Modules\Manutencao\Models\OrdemServico;
use App\Modules\Manutencao\Models\StatusOrdemServico;
use App\Models\TipoOrdemServico;
use App\Modules\Veiculos\Models\Veiculo;

class ControleManutancaoFortaController extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $query = VControleManutencaoFrota::query();


        if ($request->filled('dataentrada')) {
            $query->whereDate('dataentrada', Carbon::createFromFormat('Y-m-d', $request->dataentrada));
        }

        if ($request->filled('dataprevisaosaida')) {
            $query->whereDate('dataprevisaosaida', Carbon::createFromFormat('Y-m-d', $request->dataprevisaosaida));
        }

        if ($request->filled('data_encerramento')) {
            $query->whereDate('data_encerramento', Carbon::createFromFormat('Y-m-d', $request->data_encerramento));
        }

        if ($request->filled('os')) {
            $query->where('os', $request->os);
        }

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('modeloveiculo')) {
            $query->where('modeloveiculo', $request->modeloveiculo);
        }

        if ($request->filled('tipoos')) {
            $query->where('tipoos', $request->tipoos);
        }

        if ($request->filled('statusordem')) {
            $query->where('statusordem', $request->statusordem);
        }

        if ($request->filled('localmanutancao')) {
            $query->where('localmanutancao', $request->localmanutancao);
        }

        if ($request->filled('filial')) {
            $query->where('filial', $request->filial);
        }
        $os = TipoOrdemServico::select('descricao_tipo_ordem as value', 'descricao_tipo_ordem as label')
            ->orderBy('descricao_tipo_ordem')
            ->get();
        $placa =  Veiculo::select('placa as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();
        $modelo = ModeloVeiculo::select('descricao_modelo_veiculo as value', 'descricao_modelo_veiculo as label')
            ->orderBy('descricao_modelo_veiculo')
            ->limit(30)
            ->get();
        $filial = Filial::select('name as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();
        $status = StatusOrdemServico::select('situacao_ordem_servico as value', 'situacao_ordem_servico as label')
            ->orderBy('id_status_ordem_servico')
            ->limit(30)
            ->get();


        $controleManutencaoFrota = $query->latest('os')
            ->paginate(10);

        if ($request->header('HX-Request')) {
            return view('admin.controlemanutancaofrota._table', compact(
                'controleManutencaoFrota',
                'placa',
                'modelo',
                'filial',
                'status',

            ));
        }

        // dd($controleManutencaoFrota);

        return view('admin.controlemanutancaofrota.index', array_merge(
            [
                'controleManutencaoFrota' => $controleManutencaoFrota,
                'controleManutencaoFrota' => $controleManutencaoFrota,
                'placa' => $placa,
                'modelo' => $modelo,
                'filial' => $filial,
                'status' => $status,
                'os'    => $os
            ]
        ));
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

                // Configurar opções do PDF de forma mais simples
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.controlemanutancaofrota.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('ControleFrota_' . date('Y-m-d_His') . '.pdf');
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
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

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
            'os' => 'Cod. O.S.',
            'placa' => 'Placa',
            'modeloveiculo' => 'Modelo do Veiculo',
            'dataentrada' => 'Data Entrada',
            'dataprevisaosaida' => 'Data Previsão Saida',
            'data_encerramento' => 'Data Encerramento',
            'tipoos' => 'Tipo O.S.',
            'statusordem' => 'Status O.S.',
            'localmanutencao' => 'Local Manutencao',
            'filial' => 'Filial'
        ];

        return $this->exportToCsv($request, $query, $columns, 'ControleManutencaoFrota', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'os' => 'Cod. O.S.',
            'placa' => 'Placa',
            'modeloveiculo' => 'Modelo do Veiculo',
            'dataentrada' => 'Data Entrada',
            'dataprevisaosaida' => 'Data Previsão Saida',
            'data_encerramento' => 'Data Encerramento',
            'tipoos' => 'Tipo O.S.',
            'statusordem' => 'Status O.S.',
            'localmanutencao' => 'Local Manutencao',
            'filial' => 'Filial'
        ];

        return $this->exportToExcel($request, $query, $columns, 'ControleManutencaoFrota', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'CodOS' => 'os',
            'Placa' => 'placa',
            'ModeloVeiculo' => 'modeloveiculo',
            'DataEntrada' => 'dataentrada',
            'DataPrevisaoSaida' => 'dataprevisaosaida',
            'DataEncerramento' => 'data_encerramento',
            'TipoOS' => 'tipoos',
            'StatusOS' => 'statusordem',
            'LocalManutencao' => 'localmanutencao',
            'Filial' => 'filial'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'ControleManutencaoFrotas',
            'ControleManutencaoFrota',
            'controle_manutencao_frota',
            $this->getValidExportFilters()
        );
    }

    protected function buildExportQuery(Request $request)
    {
        $query = VControleManutencaoFrota::query();


        if ($request->filled('dataentrada')) {
            $query->whereDate('dataentrada', Carbon::createFromFormat('Y-m-d', $request->dataentrada));
        }

        if ($request->filled('dataprevisaosaida')) {
            $query->whereDate('dataprevisaosaida', Carbon::createFromFormat('Y-m-d', $request->dataprevisaosaida));
        }

        if ($request->filled('data_encerramento')) {
            $query->whereDate('data_encerramento', Carbon::createFromFormat('Y-m-d', $request->data_encerramento));
        }

        if ($request->filled('os')) {
            $query->where('os', $request->os);
        }

        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('modeloveiculo')) {
            $query->where('modeloveiculo', $request->modeloveiculo);
        }

        if ($request->filled('tipoos')) {
            $query->where('tipoos', $request->tipoos);
        }

        if ($request->filled('statusordem')) {
            $query->where('statusordem', $request->statusordem);
        }

        if ($request->filled('localmanutancao')) {
            $query->where('localmanutancao', $request->localmanutancao);
        }

        if ($request->filled('filial')) {
            $query->where('filial', $request->filial);
        }

        return $query->latest('os');
    }

    protected function getValidExportFilters()
    {
        return [
            'os',
            'placa',
            'modeloveiculo',
            'dataentrada',
            'dataprevisaosaida',
            'data_encerramento',
            'tipoos',
            'statusordem',
            'localmanutencao',
            'filial'
        ];
    }
}
