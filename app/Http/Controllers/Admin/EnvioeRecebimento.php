<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\ManutencaoPneus;
use App\Models\ManutencaoPneusEntrada;
use App\Models\Pneu;
use App\Models\TipoBorrachaPneu;
use App\Models\TipoDesenhoPneu;
use App\Models\TipoReformaPneu;
use Illuminate\Support\Facades\Cache;

class EnvioeRecebimento extends Controller
{
    public function index()
    {

        $envios = ManutencaoPneus::query();
        $recebimento = ManutencaoPneusEntrada::query();
        $filiais = Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();
        $manutencaoPneus = $envios->latest('id_manutencao_pneu')->paginate(15);

        $manutencaoPneusEntrada = $recebimento->paginate(15);

        $formOptions = [
            'pneus' => Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', '=', 'DIAGNOSTICO')
                ->orderBy('label')
                ->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoReforma' => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
            'desenhopneu' => TipoDesenhoPneu::select('descricao_desenho_pneu as label', 'id_desenho_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoborracha' => TipoBorrachaPneu::select('descricao_tipo_borracha as label', 'id_tipo_borracha as value')->orderBy('label')->get()->toArray(),
        ];

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.envioerecebimentopneus.index', compact('envios', 'recebimento', 'filiais', 'fornecedoresFrequentes', 'manutencaoPneus', 'manutencaoPneusEntrada', 'formOptions', 'fornecedoresFrequentes'));
    }

    public function getFornecedoresFrequentes()
    {
        return Cache::remember('fornecedores_frequentes', now()->addMinutes(30), function () {
            return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                ->orderBy('nome_fornecedor')
                ->limit(20)
                ->get();
        });
    }
}
