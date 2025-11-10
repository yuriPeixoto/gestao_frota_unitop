<?php

namespace App\Charts;

use ConsoleTVs\Charts\Classes\Chartjs\Chart;
use App\Models\v_manutencao_abertas;


class ManutecoesSituacaoChart extends Chart
{
    /**
     * Initializes the chart.
     *
     * @return void
     */
    public function __construct($request)
    {
        parent::__construct();
        
        $data = v_manutencao_abertas::selectRaw('situacao_ordem_servico, COUNT(*) as total')
        ->when($request->filled('filial'), function ($query) use ($request) {
            $query->where('filial', $request->filial);
        })
        ->when($request->filled('descricao_tipo'), function ($query) use ($request) {
            $query->where('descricao_tipo', $request->descricao_tipo);
        })
        ->when($request->filled('situacao_ordem_servicos'), function ($query) use ($request) {
            $query->where('situacao_ordem_servicos', $request->situacao_ordem_servicos);
        })
        ->groupBy('situacao_ordem_servico')
        ->pluck('total', 'situacao_ordem_servico'); 

        $this->labels($data->keys()); 
      
        $barColors = [
            'rgba(85, 107, 47, 1)',    // Cor média escura 1 (Olive drab)
            'rgba(72, 61, 139, 1)',    // Cor média escura 2 (Dark slate blue)
            'rgba(139, 69, 19, 1)',    // Cor média escura 3 (Saddle brown)
            'rgba(0, 128, 128, 1)',    // Cor média escura 4 (Teal)
            'rgba(128, 0, 128, 1)',    // Cor média escura 5 (Purple)
            'rgba(169, 169, 169, 1)',  // Cor média escura 6 (Dark gray)
            'rgba(255, 69, 0, 1)',     // Cor média escura 7 (Red-orange)
            'rgba(255, 99, 71, 1)',    // Cor média escura 8 (Tomato)
            'rgba(46, 139, 87, 1)',    // Cor média escura 9 (Sea green)
            'rgba(160, 82, 45, 1)',    // Cor média escura 10 (Sienna)
            'rgba(85, 107, 149, 1)',   // Cor média escura 11 (Slate blue)
            'rgba(95, 158, 160, 1)',   // Cor média escura 12 (Cadet blue)
            'rgba(128, 128, 0, 1)',    // Cor média escura 13 (Olive)
            'rgba(60, 60, 60, 1)',     // Cor média escura 14 (Dim gray)
            'rgba(34, 139, 34, 1)',    // Cor média escura 15 (Forest green)
            'rgba(75, 0, 130, 1)',     // Cor média escura 16 (Indigo)
            'rgba(176, 224, 230, 1)',  // Cor média escura 17 (Powder blue)
        ];

        $this->dataset('Manutenções', 'pie', $data->values()) 
                   ->backgroundColor($barColors); 
        $this->options([
            'datalabels' => [
                'display' => true,
                'formatter' => function ($value, $context) use ($data) {
                    $total = array_sum($data->values);
                    $percent = ($value / $total) * 100;
                    return round($percent, 2) . '%'; // Exibe as porcentagens
                },
                'color' => '#fff', // Cor do texto das porcentagens
                'font' => [
                    'weight' => 'bold',
                    'size' => 12
                ]
            ]

        ]);
    }
}
