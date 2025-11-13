<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\ProdutosPorFilial;
use App\Models\ConsumoProduto; // supondo que você tenha histórico de consumo
use App\Modules\Estoque\Models\HistoricoMovimentacaoEstoque;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EstoqueGraficoController extends Controller
{
    public function index(Request $request)
    {
        $produtos = Produto::select('id_produto as value', 'descricao_produto as label')
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get();

        return view('admin.consultaprodutografico.index', compact('produtos'));
    }

    public function getDadosProduto(Request $request, $id)
    {
        $filialId = Auth::user()->filial_id;
        $produto = Produto::findOrFail($id);

        Log::info('filial usuario: ' . $filialId);

        // Estoque atual
        $estoque = ProdutosPorFilial::where('id_produto_unitop', $produto->id_produto)
            ->where('id_filial', $filialId)
            ->sum('quantidade_produto');

        Log::info('Estoque Filial: ' . $estoque);

        // Consumo mensal (últimos 6 meses)
        $consumoMensal = HistoricoMovimentacaoEstoque::selectRaw("
        TO_CHAR(data_inclusao, 'MM-YYYY') as mes,
        DATE_TRUNC('month', data_inclusao) as mes_ordenado,
        SUM(qtde_baixa) as total
    ")
            ->where('id_filial', $filialId)
            ->where('id_produto', $produto->id_produto)
            ->where('qtde_baixa', '>', 0)
            ->groupBy('mes', 'mes_ordenado')
            ->orderBy('mes_ordenado')
            ->get();

        // Consumo semanal (últimas 6 semanas)
        $consumoSemanal = HistoricoMovimentacaoEstoque::selectRaw("
        TO_CHAR(data_inclusao, 'IW-IYYY') as semana,
        DATE_TRUNC('week', data_inclusao) as semana_ordenada,
        SUM(qtde_baixa) as total
    ")
            ->where('id_filial', $filialId)
            ->where('id_produto', $produto->id_produto)
            ->where('qtde_baixa', '>', 0)
            ->groupBy('semana', 'semana_ordenada')
            ->orderBy('semana_ordenada')
            ->get();

        // Médias
        $mediaMensal = $consumoMensal->avg('total') ?? 0;
        $mediaSemanal = $consumoSemanal->avg('total') ?? 0;

        // Estoque mínimo (2x média semanal)
        $estoqueMinimo = round($mediaSemanal * 2);

        // Consumo médio diário
        $consumoDiario = $mediaSemanal > 0 ? ($mediaSemanal / 7) : 0;

        // Duração estimada do estoque (em dias)
        $diasDuracao = $consumoDiario > 0 ? round($estoque / $consumoDiario) : 0;

        return response()->json([
            'estoque' => $estoque,
            'estoque_minimo' => $estoqueMinimo,
            'consumo_mensal' => [
                'labels' => $consumoMensal->pluck('mes'),
                'valores' => $consumoMensal->pluck('total'),
            ],
            'consumo_semanal' => [
                'labels' => $consumoSemanal->pluck('semana'),
                'valores' => $consumoSemanal->pluck('total'),
            ],
            'consumo_diario' => round($consumoDiario, 2),
            'dias_duracao' => $diasDuracao,
        ]);
    }
}
