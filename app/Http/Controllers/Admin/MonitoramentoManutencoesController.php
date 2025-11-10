<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\v_manutencao_abertas;
use App\Models\VFilial;
use Illuminate\Support\Facades\DB;
use App\Charts\ManutecaoPorTipoDeEquipamentoChart;
use App\Charts\ManutecoesSituacaoChart;
use Illuminate\Support\Facades\Log;

class MonitoramentoManutencoesController extends Controller
{
    protected $manutencao_abertas;
    protected $filial;

    public function __construct(v_manutencao_abertas $manutencao_abertas, VFilial $filial)
    {
        $this->manutencao_abertas = $manutencao_abertas;
        $this->filial = $filial;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $filiais = $this->getFilial();
        $filiais = $this->getFilial();
        $tipoVeiculo = $this->getTipoVeiculo();
        $statusOS = $this->getStatusOs();
        $tipoOS = $this->getTipoOs();
        $manutecaoChart = new ManutecaoPorTipoDeEquipamentoChart($request);
        $manutecaoSituacaoChart = new ManutecoesSituacaoChart($request);

        $preventivaCount = $this->manutencao_abertas
            ->where('descricao_tipo_ordem', 'Ordem de Serviço Preventiva')
            ->when($request->filled('filial'), function ($query) use ($request) {
                $query->where('filial', $request->filial);
            })
            ->when($request->filled('descricao_tipo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->descricao_tipo);
            })
            ->when($request->filled('categoria_veiculo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->categoria_veiculo);
            })
            ->when($request->filled('situacao_ordem_servicos'), function ($query) use ($request) {
                $query->where('situacao_ordem_servicos', $request->situacao_ordem_servicos);
            })
            ->count();

        $corretivaCount = $this->manutencao_abertas
            ->where('descricao_tipo_ordem', 'Ordem de Serviço Preventiva')
            ->when($request->filled('filial'), function ($query) use ($request) {
                $query->where('filial', $request->filial);
            })
            ->when($request->filled('descricao_tipo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->descricao_tipo);
            })
            ->when($request->filled('categoria_veiculo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->categoria_veiculo);
            })
            ->when($request->filled('situacao_ordem_servicos'), function ($query) use ($request) {
                $query->where('situacao_ordem_servicos', $request->situacao_ordem_servicos);
            })
            ->count();

        $atrasadasCount = $this->manutencao_abertas
            ->where('descricao_tipo_ordem', 'Ordem de Serviço Preventiva')
            ->when($request->filled('filial'), function ($query) use ($request) {
                $query->where('filial', $request->filial);
            })
            ->when($request->filled('descricao_tipo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->descricao_tipo);
            })
            ->when($request->filled('categoria_veiculo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->categoria_veiculo);
            })
            ->when($request->filled('situacao_ordem_servicos'), function ($query) use ($request) {
                $query->where('situacao_ordem_servicos', $request->situacao_ordem_servicos);
            })
            ->count();

        LOG::DEBUG([
            'atrasadasCount' => $atrasadasCount,
            'corretivaCount' => $corretivaCount,
            'preventivaCount' => $preventivaCount
        ]);

        $query = $this->manutencao_abertas;

        $query->when($request->has('search'), function ($query) use ($request) {
            return $query->whereRaw('LOWER(placa) LIKE LOWER(?)', ['%' . $request->search . '%']);
        });

        $list = $query->paginate();


        $list->getCollection()->transform(function ($item) {
            $item->data_abertura = \Carbon\Carbon::parse($item->data_abertura)->format('d/m/Y H:i');
            $item->data_previsao_saida = \Carbon\Carbon::parse($item->data_previsao_saida)->format('d/m/Y H:i');
            return $item;
        });


        return view(
            'admin.monitoramentoDasManutencoes.index',
            compact('preventivaCount', 'request', 'corretivaCount', 'filiais', 'list', 'tipoVeiculo', 'statusOS', 'tipoOS', 'atrasadasCount', 'manutecaoChart', 'manutecaoSituacaoChart'),
        );
    }

    public function getFilial()
    {

        return $this->filial->get()
            ->map(function ($filial) {
                return (object)[
                    'value' => $filial->name,
                    'label' => $filial->name
                ];
            });
    }

    public function getTipoVeiculo()
    {
        return DB::connection('pgsql')->table('categoria_veiculo')
            ->select('descricao_categoria', 'id_categoria')
            ->distinct()
            ->get()
            ->map(function ($categoria) {
                return (object)[
                    'value' => $categoria->descricao_categoria,
                    'label' => $categoria->descricao_categoria,
                ];
            });
    }

    public function getStatusOs()
    {
        return DB::connection('pgsql')->table('status_ordem_servico')
            ->select('situacao_ordem_servico', 'id_status_ordem_servico')
            ->distinct()
            ->get()
            ->map(function ($categoria) {
                return (object)[
                    'value' => $categoria->situacao_ordem_servico,
                    'label' => $categoria->situacao_ordem_servico,
                ];
            });
    }


    public function getTipoOs()
    {
        return DB::connection('pgsql')->table('tipo_ordem_servico')
            ->select('descricao_tipo_ordem', 'id_tipo_ordem_servico')
            ->distinct()
            ->get()
            ->map(function ($categoria) {
                return (object)[
                    'value' => $categoria->descricao_tipo_ordem,
                    'label' => $categoria->descricao_tipo_ordem,
                ];
            });
    }

    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
