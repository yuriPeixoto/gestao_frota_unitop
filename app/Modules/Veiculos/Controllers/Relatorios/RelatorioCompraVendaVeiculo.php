<?php

namespace App\Modules\Veiculos\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\ModeloVeiculo;
use App\Models\Veiculo;
use App\Models\VveiculoCompraeBaixa;
use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use App\Traits\ExportableTrait;

class RelatorioCompraVendaVeiculo extends Controller
{
    use ExportableTrait;


    public function index(Request $request)
    {
        $query = VveiculoCompraeBaixa::query();

        if ($request->filled('placa')) {
            $query->where('placa', $request->input('placa'));
        }

        if ($request->filled('chassi')) {
            $query->where('chassi', $request->input('chassi'));
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->input('renavam'));
        }

        if ($request->filled('descricao_modelo_veiculo')) {
            $query->where('descricao_modelo_veiculo', $request->input('descricao_modelo_veiculo'));
        }

        if ($request->filled('ano_fabricacao')) {
            $query->where('ano_fabricacao', $request->input('ano_fabricacao'));
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->input('id_filial'));
        }

        if ($request->filled('descricao_departamento')) {
            $query->where('descricao_departamento', $request->input('descricao_departamento'));
        }

        if ($request->filled('data_compra')) {
            $query->where('data_compra', $request->input('data_compra'));
        }

        if ($request->filled('data_venda')) {
            $query->where('data_venda', $request->input('data_venda'));
        }

        $modelo = ModeloVeiculo::select('id_modelo_veiculo as value', 'descricao_modelo_veiculo as label')
            ->orderBy('descricao_modelo_veiculo')
            ->limit(30)
            ->get();

        $placa = Veiculo::select('placa as value', 'placa as label')
            ->orderBy('placa')
            ->limit(30)
            ->get();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $departamento = Departamento::select('id_departamento as departamento', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(30)
            ->get();

        return view('admin.relatoriocompraevendaveiculo.index', compact('modelo', 'placa', 'filial', 'departamento'));
    }

    public function exportPdf(Request $request)
    {
        try {
            Log::info('Iniciando exportação de PDF', $request->all());

            // Corrigido: model correto
            $query  = VveiculoCompraeBaixa::with(['filial']); // se precisar dos relacionamentos

            // aplica os filtros somente se vierem preenchidos
            if ($request->filled('placa')) {
                $query->where('placa', $request->placa);
            }

            if ($request->filled('chassi')) {
                $query->where('chassi', $request->chassi);
            }

            if ($request->filled('renavam')) {
                $query->where('renavam', $request->renavam);
            }

            if ($request->filled('ano_fabricacao')) {
                // Se o request vier como "2023-01-01", extrai apenas o ano
                $ano = \Carbon\Carbon::parse($request->ano_fabricacao)->year;
                $query->where('ano_fabricacao', $ano);
            }

            if ($request->filled('id_filial')) {
                $query->where('id_filial', $request->id_filial);
            }

            if ($request->filled('descricao_departamento')) {
                $query->where('descricao_departamento', 'like', '%' . $request->descricao_departamento . '%');
            }

            if ($request->filled('descricao_modelo_veiculo')) {
                $query->where('descricao_modelo_veiculo', 'like', '%' . $request->descricao_modelo_veiculo . '%');
            }

            if ($request->filled('data_compra') && $request->filled('data_venda')) {
                $query->whereBetween('data_compra', [$request->data_compra, $request->data_venda]);
            } elseif ($request->filled('data_compra')) {
                $query->whereDate('data_compra', '>=', $request->data_compra);
            } elseif ($request->filled('data_venda')) {
                $query->whereDate('data_compra', '<=', $request->data_venda);
            }

            $data = $query->get();

            Log::info('Total resultados:', ['count' => $data->count()]);

            $html = View::make('admin.relatoriocompraevendaveiculo._pdf', compact('data'))->render();

            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="relatoriocompraevendaveiculo.pdf"');
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
            'placa' => 'Placa',
            'chassi' => 'Chassi',
            'renavam' => 'Renavam',
            'descricao_modelo_veiculo' => 'Modelo Veiculo',
            'ano_fabricacao' => 'Ano Fabricação',
            'filial.name' => 'Filial',
            'descricao_departamento' => 'Departamento',
            'data_compra' => 'Data Compra',
            'data_venda' => 'Data Venda'
        ];

        return $this->exportToExcel($request, $query, $columns, 'relatorios');
    }

    public function buildExportQuery(Request $request)
    {
        $query  = VveiculoCompraeBaixa::with(['filial']);


        if ($request->filled('placa')) {
            $query->where('placa', $request->placa);
        }

        if ($request->filled('chassi')) {
            $query->where('chassi', $request->chassi);
        }

        if ($request->filled('renavam')) {
            $query->where('renavam', $request->renavam);
        }

        if ($request->filled('ano_fabricacao')) {
            // Se o request vier como "2023-01-01", extrai apenas o ano
            $ano = \Carbon\Carbon::parse($request->ano_fabricacao)->year;
            $query->where('ano_fabricacao', $ano);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('descricao_departamento')) {
            $query->where('descricao_departamento', 'like', '%' . $request->descricao_departamento . '%');
        }

        if ($request->filled('descricao_modelo_veiculo')) {
            $query->where('descricao_modelo_veiculo', 'like', '%' . $request->descricao_modelo_veiculo . '%');
        }

        if ($request->filled('data_compra') && $request->filled('data_venda')) {
            $query->where(function ($q) use ($request) {
                $q->whereBetween('data_compra', [$request->data_compra, $request->data_venda])
                    ->orWhereBetween('data_venda', [$request->data_compra, $request->data_venda]);
            });
        } elseif ($request->filled('data_compra')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('data_compra', '>=', $request->data_compra)
                    ->orWhereDate('data_venda', '>=', $request->data_compra);
            });
        } elseif ($request->filled('data_venda')) {
            $query->where(function ($q) use ($request) {
                $q->whereDate('data_compra', '<=', $request->data_venda)
                    ->orWhereDate('data_venda', '<=', $request->data_venda);
            });
        }



        return $query->with([

            'filial',

        ])->orderByDesc('data_compra');
    }
}
