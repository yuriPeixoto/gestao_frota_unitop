<?php

namespace App\Charts;

use App\Models\v_manutencao_abertas;
use ConsoleTVs\Charts\Classes\Chartjs\Chart;

class ManutecaoPorTipoDeEquipamentoChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct($request)
    {
        parent::__construct();

        $data = v_manutencao_abertas::selectRaw('descricao_tipo, COUNT(*) as total')
            ->when($request->filled('filial'), function ($query) use ($request) {
                $query->where('filial', $request->filial);
            })
            ->when($request->filled('descricao_tipo'), function ($query) use ($request) {
                $query->where('descricao_tipo', $request->descricao_tipo);
            })
            ->when($request->filled('situacao_ordem_servicos'), function ($query) use ($request) {
                $query->where('situacao_ordem_servicos', $request->situacao_ordem_servicos);
            })
            ->groupBy('descricao_tipo')
            ->pluck('total', 'descricao_tipo');

        $this->labels($data->keys());

        $barColors = [
            'rgba(85, 107, 47, 1)',
            'rgba(72, 61, 139, 1)',
            'rgba(139, 69, 19, 1)',
            'rgba(0, 128, 128, 1)',
            'rgba(128, 0, 128, 1)',
            'rgba(169, 169, 169, 1)',
            'rgba(255, 69, 0, 1)',
            'rgba(255, 99, 71, 1)',
            'rgba(46, 139, 87, 1)',
            'rgba(160, 82, 45, 1)',
            'rgba(85, 107, 149, 1)',
            'rgba(95, 158, 160, 1)',
            'rgba(128, 128, 0, 1)',
            'rgba(60, 60, 60, 1)',
            'rgba(34, 139, 34, 1)',
            'rgba(75, 0, 130, 1)',
            'rgba(176, 224, 230, 1)',
        ];

        $this->dataset('Manutenções', 'bar', $data->values())
            ->backgroundColor($barColors);
        $this->options([
            'scales' => [
                'x' => [
                ],
            ],
            'indexAxis' => 'y',
        ]);

    }
}
