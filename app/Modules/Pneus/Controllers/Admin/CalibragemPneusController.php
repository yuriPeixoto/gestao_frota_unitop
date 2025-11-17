<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CalibragemPneus;
use App\Models\CalibragemPneusItens;
use App\Models\Veiculo;
use App\Modules\Pessoal\Models\Pessoal;
use App\Models\Filial;
use App\Models\UserFilial;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\HistoricoCalibragemMedicao;
use App\Models\PneusAplicados;
use App\Models\VeiculoXPneu;
use Exception;

class CalibragemPneusController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        //dd($request->all());
        $filiais = \App\Models\Filial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();
        $veiculos = \App\Models\Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->limit(30)->get();
        $usuario = \App\Models\User::select('id as value', 'name as label')->orderBy('name')->get();
        $codCalibragem  = $request->input('id_calibragem_pneu');
        $dataInicial     = $request->input('data_inclusao');
        $dataFinal       = $request->input('data_alteracao');


        // Filtros
        $query = CalibragemPneus::with(['filial', 'veiculo', 'user']);

        if ($request->filled('id_filial')) { // Verifica se o campo 'id_filial' foi preenchido na requisição
            $query->where('id_filial', $request->input('id_filial')); // Aplica o filtro com o valor informado
        }

        if ($request->filled('id_calibragem_pneu')) { // Verifica se o campo 'id_calibragem_pneu' foi preenchido na requisição
            $query->where('id_calibragem_pneu', $request->input('id_calibragem_pneu')); // Aplica o filtro com o valor informado
        }

        if ($request->filled('id_veiculo')) { // Verifica se o campo 'id_veiculo' foi preenchido na requisição
            $query->where('id_veiculo', $request->input('id_veiculo')); // Aplica o filtro com o valor informado
        }

        if ($request->filled('id_user_calibragem')) { // Verifica se o campo 'id_user_calibragem' foi preenchido na requisição
            $query->where('id_user_calibragem', $request->input('id_user_calibragem')); // Aplica o filtro com o valor informado
        }

        if ($request->filled('data_inclusao') && $request->filled('data_alteracao')) { // Verifica se o campo 'data_inclusao' 
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_alteracao')
            ]);
        }
        if ($dataInicial && $dataFinal) {
            $query->whereBetween('data_inclusao', [$dataInicial, $dataFinal]);
        }
        //
        $placaSelecionada = null;

        if ($request->filled('id_veiculo')) {
            $veiculo = \App\Models\Veiculo::find($request->input('id_veiculo'));

            if ($veiculo) {
                $placaSelecionada = [
                    'value' => $veiculo->id_veiculo,
                    'label' => $veiculo->placa,
                ];
            }
        }
        // Aqui você usa o $query com filtros e adiciona o selectSub e ordenação:
        $calibragens = $query
            ->withCount('pneus')
            ->orderByDesc('data_inclusao')
            ->paginate(10);

        //$calibragem = CalibragemPneus::with('user')->first();
        //dd($calibragem->user);

        return view(
            'admin.calibragempneus.index',
            [
                'filiais' => $filiais,
                'veiculos' => $veiculos,
                'usuario' => $usuario,
                'calibragens' => $calibragens,
                'placaSelecionada' => $placaSelecionada,
                'codCalibragem' => $codCalibragem
            ]
        );
    }

    public function create()
    {
        $veiculos = Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->limit(30)->get();
        $filiais = Filial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();
        $pneus = [];
        return view('admin.calibragempneus.create', compact('veiculos', 'filiais', 'pneus'));
    }



    public function store(Request $request)
    {
        $request->validate([
            //'data_inclusao' => 'required|date',
            'id_veiculo'         => 'required|exists:veiculo,id_veiculo',
        ]);

        $idVeiculo = (int) $request->input('id_veiculo');


        // Valida se existe calibragem nos últimos 7 dias
        if ($this->hasRecentCalibragem($idVeiculo, 7)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['placa' => 'Já existe uma calibragem para este veículo nos últimos 7 dias.']);
        }

        // Captura a filial do usuário logado
        $userFilial = UserFilial::where('user_id', Auth::id())->first();
        $filialId = $userFilial?->filial_id;

        // Cria a calibragem
        $calibragem = CalibragemPneus::create([
            'data_inclusao'      => Carbon::now(),
            'data_alteracao'     => Carbon::now(),
            'id_veiculo'         => $idVeiculo,
            'id_user_calibragem' => Auth::id(),
            'id_filial'          => $filialId,
            'finalizada'         => false,
        ]);

        $librasGeral = $request->input('libras');

        foreach ($request->pneus as $pneu) {
            $librasIndividuais = $pneu['libras'] ?? null;

            // Se não tiver valor individual, usa o valor geral
            $libras = $librasIndividuais ?? $librasGeral;

            CalibragemPneusItens::create([
                'data_inclusao'     => Carbon::now(),
                'data_alteracao'    => Carbon::now(),
                'id_calibragem'     => $calibragem->id_calibragem_pneu,
                'id_numero_fogo'    => $pneu['id_numero_fogo'] ?? null,
                'localizacao'       => $pneu['localizacao'] ?? null,
                'libras'            => isset($libras) ? number_format((float) str_replace(',', '.', $libras), 2, '.', '') : null,
                'sulco_pneu'        => $pneu['sulco_pneu'] ?? null,
                'calibrado'         => $pneu['calibrado'] ?? false,
            ]);
        }


        return redirect()->route('admin.calibragempneus.edit', $calibragem->id_calibragem_pneu);
    }


    public function edit($id)
    {
        $calibragem = CalibragemPneus::with(['veiculo', 'user', 'filial'])->findOrFail($id);
        $pneus = CalibragemPneusItens::where('id_calibragem', $id)->get();

        // ADICIONAR AS VARIÁVEIS QUE A VIEW USA
        $veiculos = Veiculo::select('id_veiculo as value', 'placa as label')->orderBy('placa')->get();
        $filiais = Filial::select('id as value', 'name as label')->orderBy('name')->limit(30)->get();

        return view('admin.calibragempneus.edit', compact('calibragem', 'pneus', 'veiculos', 'filiais'));
    }



    public function update(Request $request, $id)
    {
        $request->validate([
            //'data_inclusao' => 'required|date',
            'id_veiculo'           => 'required|exists:veiculo,id_veiculo',
            // 'id_filial'          => 'required|exists:filiais,id',
        ]);

        try {
            $calibragem = CalibragemPneus::findOrFail($id);
            //$idVeiculo = $calibragem->id_veiculo;
            //$idVeiculo = $request->placa;
            $idVeiculo = $request->id_veiculo;

            $dataLimite = Carbon::now()->subDays(7);

            $userFilial = UserFilial::where('user_id', Auth::id())->first();
            $filialId = $userFilial?->filial_id;

            $existeOutroRegistro = CalibragemPneus::where('id_veiculo', $idVeiculo)
                ->where('id_calibragem_pneu', '<>', $id)
                ->where('data_inclusao', '>=', $dataLimite)
                ->exists();

            if ($existeOutroRegistro) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['placa' => 'Já existe uma calibragem para este veículo nos últimos 7 dias.']);
            }

            $calibragem->update([
                'data_alteracao'     => Carbon::now(),
                'id_veiculo'         => $idVeiculo,
                // 'id_filial'          => $request->filial,
                // Se quiser registrar o usuário da alteração:
                'id_user_calibragem' => Auth::id(),
            ]);

            $pneu = CalibragemPneusItens::where('id_calibragem', $id)->get();

            Log::debug('Pneus recebidos:', $request->pneus);
            foreach ($request->pneus as $pneuData) {
                if (isset($pneuData['id_calibragem_pneus_itens'])) {
                    $pneu = CalibragemPneusItens::find($pneuData['id_calibragem_pneus_itens']);

                    if ($pneu) {
                        // Salva histórico antes de atualizar
                        Log::debug('Salvando histórico:', [
                            'id_veiculo' => $idVeiculo,
                            'id_pneu' => $pneu->id_numero_fogo,
                            'data_medicao' => $pneu->data_inclusao,
                            'libras' => $pneu->libras,
                            'milimetro' => $pneu->sulco_pneu,
                            'id_calibragem_pneu' => $calibragem->id_calibragem_pneu,
                        ]);
                        HistoricoCalibragemMedicao::create([
                            'data_inclusao'       => Carbon::now(),
                            'id_veiculo'          => $calibragem->id_veiculo,
                            'id_pneu'             => $pneu->id_numero_fogo,
                            'data_medicao'        => $pneu->data_inclusao,
                            'libras'              => $pneu->libras,
                            'milimetro'           => $pneu->sulco_pneu,
                            'id_calibragem_pneu'  => $calibragem->id_calibragem_pneu,
                        ]);

                        // Atualiza o registro
                        $pneu->update([
                            'data_inclusao'  => Carbon::now(),
                            'data_alteracao' => Carbon::now(),
                            'libras'         => isset($pneuData['libras']) ? number_format((float) str_replace(',', '.', $pneuData['libras']), 2, '.', '') : null,
                            'calibrado'      => $pneuData['calibrado'],
                            'sulco_pneu'     => $pneuData['sulco_pneu'],
                        ]);
                    }
                }
            }


            //dd($pneu);

            return redirect()->route('admin.calibragempneus.index');
        } catch (Exception $e) {
            Log::debug('Deu erro' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $calibragem = CalibragemPneus::findOrFail($id);
        $calibragem->delete();

        return redirect()->route('admin.calibragempneus.index');
    }

    public function getUltimaDataCalibragem($idveiculo)
    {
        $ultimaCalibragem = CalibragemPneus::where('id_veiculo', $idveiculo)
            ->orderByDesc('data_inclusao')
            ->first();

        if ($ultimaCalibragem) {
            // Garante que é objeto Carbon
            $ultimaCalibragem->data_inclusao = \Carbon\Carbon::parse($ultimaCalibragem->data_inclusao);
        }

        return response()->json([
            'data' => $ultimaCalibragem ? $ultimaCalibragem->data_inclusao->format('Y-m-d') : null
        ]);
    }

    //Validação de calibragem nos ultimos 7 dias
    private function hasRecentCalibragem(?int $idVeiculo, int $dias = 7): bool
    {
        if (!$idVeiculo) {
            return false; // ou lançar uma exceção, se preferir
        }

        $dataLimite = Carbon::now()->subDays($dias);

        return CalibragemPneus::where('id_veiculo', $idVeiculo)
            ->where('data_alteracao', '>=', $dataLimite)
            ->exists();
    }

    public function verificaCalibragemRecente($idVeiculo)
    {
        $existe = $this->hasRecentCalibragem((int) $idVeiculo);

        return response()->json([
            'existe' => $existe,
            'mensagem' => $existe
                ? 'Já existe uma calibragem para este veículo nos últimos 7 dias.'
                : null,
        ]);
    }

    public function mostrarHistorico($id_calibragem_pneus_itens)
    {
        $item = CalibragemPneusItens::find($id_calibragem_pneus_itens);

        if (!$item || !$item->id_numero_fogo) {
            return response()->json(['error' => 'Pneu ou número de fogo não encontrado'], 404);
        }

        try {
            $historico = HistoricoCalibragemMedicao::where('id_pneu', $item->id_numero_fogo)
                ->orderByDesc('data_medicao')
                ->limit(3)
                ->get();

            // Anexa o id_calibragem_pneus_itens a cada item do histórico
            foreach ($historico as $h) {
                $h->id_calibragem_pneus_itens = $item->id_calibragem_pneus_itens;
            }

            return response()->json($historico);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar histórico:', ['exception' => $e]);
            return response()->json(['error' => 'Erro interno ao buscar histórico'], 500);
        }
    }

    public function getPneusVeiculo($idVeiculo)
    {
        // Busca os vínculos de pneus ativos do veículo
        $veiculoXPneus = VeiculoXPneu::where('id_veiculo', $idVeiculo)
            ->where('situacao', true)
            ->pluck('id_veiculo_pneu');

        Log::debug('IDs de veiculo_x_pneu:', $veiculoXPneus->toArray());
        // Busca os pneus aplicados ativos, com os dados do pneu original (numero_fogo)
        $pneus = PneusAplicados::with('pneu') // traz a relação para acessar numero_fogo
            ->whereIn('id_veiculo_x_pneu', $veiculoXPneus)
            ->where('deleted_at', null) // <-- filtro para pegar apenas os pneus ativos
            ->get()
            ->map(function ($pneu) {
                return [
                    'id_pneu_aplicado' => $pneu->id_pneu_aplicado,
                    'id_numero_fogo'   => $pneu->id_pneu,
                    'localizacao'      => $pneu->localizacao,
                    'data_inclusao'    => optional($pneu->data_inclusao)->format('Y-m-d'),
                ];
            });

        Log::debug('Pneus aplicados retornados:', $pneus->toArray());
        return response()->json($pneus);
    }















































































    /*
    public function restaurarHistorico(Request $request, $id_calibragem_pneus_itens)
    {
        Log::debug('Dados recebidos para restauração', [
            'id_calibragem_pneus_itens' => $id_calibragem_pneus_itens,
            'id_calibragem_medicao' => $request->id_calibragem_medicao,
        ]);

        // Busca o item corretamente pelo ID do item, e não pelo número de fogo
        $item = CalibragemPneusItens::find($id_calibragem_pneus_itens);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Item de Calibragem não encontrado.'
            ], 404);
        }

        $historico = HistoricoCalibragemMedicao::find($request->id_calibragem_medicao);

        if (!$historico) {
            return response()->json([
                'success' => false,
                'message' => 'Histórico não encontrado.'
            ], 400);
        }

        // Aqui sim: o histórico deve bater com o ID do pneu (id_numero_fogo)
        if ($historico->id_pneu !== $item->id_numero_fogo) {
            return response()->json([
                'success' => false,
                'message' => 'Histórico não pertence a este pneu.'
            ], 400);
        }

        try {
            $item->update([
                'sulco_pneu' => $historico->milimetro,
                'libras' => $historico->libras,
            ]);

            return response()->json([
                'success' => true,
                'item' => $item->fresh(),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao restaurar histórico de calibragem', [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao tentar restaurar.'
            ], 500);
        }
    }
        */
}
