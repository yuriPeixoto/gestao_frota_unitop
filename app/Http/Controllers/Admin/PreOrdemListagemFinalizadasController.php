<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\GrupoResolvedor;
use App\Models\Motorista;
use App\Models\PreOrdemServico;
use App\Models\Pessoal;
use App\Models\PreOrdemServicoServicos;
use App\Models\TipoStatusPreOs;
use App\Models\User;
use App\Models\Veiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use App\Traits\ExportableTrait;



class PreOrdemListagemFinalizadasController extends Controller
{
    use ExportableTrait;


    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = PreOrdemServico::where('id_status', 3);

        if ($request->filled('id_pre_os')) {
            $query->where('id_pre_os', $request->id_pre_os);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('idobtermotorista')) {
            $query->where('id_motorista', $request->idobtermotorista);
        }

        if ($request->filled('id_tipostatus_pre_os')) {
            $query->where('id_status', $request->id_tipostatus_pre_os);
        }

        if ($request->filled('id_tipostatus_pre_os')) {
            $query->where('id_status', $request->id_tipostatus_pre_os);
        }

        if ($request->filled('id')) {
            $query->where('id_recepcionista', $request->id);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled('id_grupo_resolvedor')) {
            $query->where('id_grupo_resolvedor', $request->id_grupo_resolvedor);
        }

        $preOrdemOs = $query->latest('id_pre_os')
            ->paginate(10)
            ->withQueryString();

        // dd($preOrdemOs);

        $referenceDatas = $this->getReferenceDatas();
        // dd($referenceDatas);
        return view('admin.manutencaopreordemservicofinalizada.index', array_merge(
            [
                'preOrdemOs'      => $preOrdemOs,
                'referenceDatas'  => $referenceDatas,
            ]
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('pre_ordem_listagem', now()->addHours(12), function () {
            return [
                'veiculosFrequentes' => Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']),

                'filiais' => Filial::orderBy('name')
                    ->get(['id as value', 'name as label']),

                'pessoal' => Pessoal::orderBy('nome')
                    ->get(['id_pessoal as value', 'nome as label']),

                'departamentos' => Departamento::orderBy('descricao_departamento')
                    ->get('descricao_departamento'),

                'motoristas' => Motorista::where('ativo', true)
                    ->orderBy('nome')
                    ->limit(20)
                    ->get(['idmotorista as value', 'nome as label']),

                'statusPreOs' => TipoStatusPreOs::orderBy('id_tipostatus_pre_os')->get(['id_tipostatus_pre_os as value', 'descricao_tipo_status as label']),

                'recepcinista'  => User::orderBy('name')
                    ->limit(20)
                    ->get(['id as value', 'name as label']),

                'grupoResolvedor'   =>  GrupoResolvedor::orderBy('id_grupo_resolvedor')
                    ->get(['id_grupo_resolvedor as value', 'descricao_grupo_resolvedor as label']),
            ];
        });
    }

    public function edit($id)
    {
        // dd('Teste',$id);
        $preOrdemFinalizada = PreOrdemServico::where('id_pre_os', $id)->first();
        // dd($preOrdemFinalizada);

        $preOrdemServicosFinalizadas = PreOrdemServicoServicos::where('id_pre_os', $id)->first();
        // dd($preOrdemServicosFinalizadas);

        return view('admin.manutencaopreordemservicofinalizada.edit', compact('preOrdemFinalizada', 'preOrdemServicosFinalizadas'));
    }

    protected function buildExportQuery(Request $request)
    {
        $query = PreOrdemServico::where('id_status', 3)
            ->with('veiculo')
            ->with('pessoal')
            ->with('user')
            ->with('filial');

        if ($request->filled('id_pre_os')) {
            $query->where('id_pre_os', $request->id_pre_os);
        }

        if ($request->filled('data_inclusao')) {
            $query->where('data_inclusao', $request->id_veiculo);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('id_motorista')) {
            $query->where('id_motorista', $request->id_tipostatus_pre_os);
        }

        if ($request->filled('descricao_reclamacao')) {
            $query->where('descricao_reclamacao', $request->id);
        }

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->id_filial);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_departamento);
        }

        if ($request->filled('id_status')) {
            $query->where('id_status', $request->id_departamento);
        }

        if ($request->filled('id_grupo_resolvedor')) {
            $query->where('id_grupo_resolvedor', $request->id_grupo_resolvedor);
        }

        return $query->orderBy('id_pre_os', 'desc');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_pre_os',
            'id_veiculo',
            'idobtermotorista',
            'id_tipostatus_pre_os',
            'id',
            'id_filial',
            'id_departamento',
            'id_grupo_resolvedor',
        ];
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = $this->buildExportQuery($request);
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');
                $pdf->loadView('admin.manutencaopreordemservicofinalizada.pdf', compact('data'));

                return $pdf->download('preOrdemOs_' . date('Y-m-d_His') . '.pdf');
            } else {
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
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
            'id_pre_os' => 'Código pré-OS',
            'data_inclusao' => 'Data Inclusão',
            'veiculo.placa' => 'Placa',
            'pessoal.nome' => 'Motorista',
            'descricao_reclamacao' => 'Descrição Reclamação',
            'user.name' => 'Usuário',
            'tipoStatusPreOs.descricao_tipo_status' => 'Status',
            'filial.name' => 'Filial',
            'id_grupo_resolvedor' => 'Grupo Resolvedor'
        ];

        return $this->exportToCsv($request, $query, $columns, 'preOrdemListagemFinalizadas', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_pre_os' => 'Código pré-OS',
            'data_inclusao' => 'Data Inclusão',
            'veiculo.placa' => 'Placa',
            'pessoal.nome' => 'Motorista',
            'descricao_reclamacao' => 'Descrição Reclamação',
            'user.name' => 'Usuário',
            'tipoStatusPreOs.descricao_tipo_status' => 'Status',
            'filial.name' => 'Filial',
            'id_grupo_resolvedor' => 'Grupo Resolvedor'
        ];

        return $this->exportToExcel($request, $query, $columns, 'preOrdemListagemFinalizadas', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'codigo_preOs' => 'id_pre_os',
            'data_inclusao' => 'data_inclusao',
            'placa' => 'veiculo.placa',
            'motorista' => 'pessoal.nome',
            'descricao_reclamacao' => 'descricao_reclamacao',
            'usuario' => 'user.name',
            'status' => 'tipoStatusPreOs.descricao_tipo_status',
            'filial' => 'filial.name',
            'grupo_resolvedor' => 'id_grupo_resolvedor'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'preOrdemListagemFinalizadas',
            'preOrdemListagemFinalizada',
            'preOrdemListagemFinalizadas',
            $this->getValidExportFilters()
        );
    }
}
