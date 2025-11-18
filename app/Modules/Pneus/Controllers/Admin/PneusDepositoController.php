<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Configuracoes\Models\Departamento;
use App\Modules\Configuracoes\Models\Filial;
use App\Models\HistoricoPneu;
use App\Models\Pneu;
use App\Models\PneusDeposito;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PneusDepositoController extends Controller
{
    public function index(Request $request)
    {
        $query = PneusDeposito::query()
            ->with(['pneus.departamentoPneu']);

        $this->applyFilters($request, $query);

        $local = PneusDeposito::select('descricao_destino as value', 'descricao_destino as label')
            ->distinct()
            ->orderBy('descricao_destino')
            ->get();

        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->get();

        $filial = Filial::select('id as value', 'name as label')
            ->orderBy('name')
            ->get();

        $deposito = $query->select('pneudeposito.*')
            ->latest('id_deposito_pneu')
            ->join('pneu as p', 'pneudeposito.id_pneu', '=', 'p.id_pneu')
            ->where('datahora_processamento', null) // filtra apenas pneus não processados
            ->where('p.id_filial', GetterFilial()) // filtra pela filial do usuário logado
            ->paginate(20)
            ->appends($request->query());

        return view('admin.pneusdeposito.index', compact('deposito', 'local', 'departamento', 'filial'));
    }

    public function EnviarManutencao(Request $request)
    {
        $idCheck = $request->input('pneus', []); // Recebe o pneu selecionado

        if (empty($idCheck)) {
            return redirect()->back()->with('error', 'Selecione pelo menos um pneu para realizar o envio à manutenção.');
        }

        // Validação adicional no servidor: não permitir enviar para manutenção
        // quando a destinacao solicitada do pneu indicar que deve ir para o estoque.
        $pneusParaValidar = PneusDeposito::whereIn('id_deposito_pneu', (array) $idCheck)->get();
        $invalidos = [];
        foreach ($pneusParaValidar as $pd) {
            $dest = mb_strtolower(trim($pd->destinacao_solicitada ?? $pd->descricao_destino ?? ''));
            if ($dest !== '' && (str_contains($dest, 'estoque') || str_contains($dest, 'stock'))) {
                $invalidos[] = $pd->id_deposito_pneu;
            }
        }

        if (!empty($invalidos)) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível enviar para manutenção: pelo menos um dos pneus selecionados foi solicitado para envio ao Estoque.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Verifica  se existe o pneu na matriz
            $matriz = PneusDeposito::where('id_deposito_pneu', $idCheck)
                ->whereHas('pneus', function ($query) {
                    $query->where('id_filial', 1);
                })
                ->count();

            $totalSelecionado = count($idCheck);

            if ($matriz > 0 && $matriz < $totalSelecionado) {
                // mistura matriz e filial
                return response()->json([
                    'success' => false,
                    'message' => 'Atenção: Para continuar, é necessário selecionar somente pneus da matriz OU somente pneus das filiais.'
                ], 400);
            }

            foreach ($idCheck as $id) {
                $pneusDeposito = PneusDeposito::findOrFail($id);
                $pneu = Pneu::findOrFail($pneusDeposito->id_pneu);

                $pneusDeposito->update([
                    'datahora_processamento' => now(),
                    'data_alteracao' => now(),
                    'descricao_destino' => 'EM MANUTENÇÃO',
                ]);

                $pneu->update([
                    'status_pneu' => 'EM MANUTENÇÃO',
                ]);

                // Registrar no histórico corretamente
                HistoricoPneu::create([
                    'id_pneu' => $pneu->id_pneu,
                    'data_inclusao' => now(),
                    'status_movimentacao' => 'PNEU ENVIADO PARA MANUTENÇÃO',
                    // Se quiser, pode adicionar outros campos preenchidos automaticamente:
                    // 'origem_operacao'    => 'MANUAL',
                    // 'observacoes_operacao' => 'Enviado para manutenção via sistema'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pneus enviados para Manutenção com sucesso!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function EnviarEstoque(Request $request)
    {
        $idCheck = $request->input('pneus', []); // Recebe o pneu selecionado

        if (empty($idCheck)) {
            return redirect()->back()->with('error', 'Selecione ao menos um pneu para enviar ao Estoque!');
        }

        // Validação servidor: não permitir enviar ao estoque quando a destinacao solicitada for para manutenção
        $pneusParaValidar = PneusDeposito::whereIn('id_deposito_pneu', (array) $idCheck)->get();
        $invalidos = [];
        foreach ($pneusParaValidar as $pd) {
            $dest = mb_strtolower(trim($pd->destinacao_solicitada ?? $pd->descricao_destino ?? ''));
            if ($dest !== '' && (str_contains($dest, 'manuten') || str_contains($dest, 'manutenção') || str_contains($dest, 'manutencao'))) {
                $invalidos[] = $pd->id_deposito_pneu;
            }
        }

        if (!empty($invalidos)) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível enviar para Estoque: pelo menos um dos pneus selecionados foi solicitado para envio à Manutenção.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($idCheck) {
                foreach ($idCheck as $id) {
                    $pneuSelecionado = PneusDeposito::findOrFail($id);

                    $pneu = Pneu::findOrFail($pneuSelecionado->id_pneu);

                    $pneuSelecionado->update([
                        'datahora_processamento' => now(),
                        'data_alteracao' => now(),
                        'descricao_destino' => 'ESTOQUE',
                    ]);

                    $pneu->update([
                        'status_pneu' => 'ESTOQUE',
                    ]);

                    // Registrar no histórico corretamente
                    HistoricoPneu::create([
                        'id_pneu' => $pneu->id_pneu,
                        'data_inclusao' => now(),
                        'status_movimentacao' => 'PNEU ENVIADO PARA ESTOQUE',
                        // Se quiser, pode adicionar outros campos preenchidos automaticamente:
                        // 'origem_operacao'    => 'MANUAL',
                        // 'observacoes_operacao' => 'Enviado para estoque via sistema'
                    ]);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pneus enviados para Estoque com sucesso!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function EnviarDescarte(Request $request)
    {
        $idCheck = $request->input('pneus', []); // Recebe o pneu selecionado

        if (empty($idCheck)) {
            return redirect()->back()->with('error', 'Selecione ao menos um pneu para enviar ao Descarte!');
        }

        try {
            DB::beginTransaction();

            if ($idCheck) {
                foreach ($idCheck as $id) {
                    $pneuSelecionado = PneusDeposito::findOrFail($id);

                    $pneu = Pneu::findOrFail($pneuSelecionado->id_pneu);

                    $pneuSelecionado->update([
                        'datahora_processamento' => now(),
                        'data_alteracao' => now(),
                        'descricao_destino' => 'DESCARTE',
                    ]);

                    $pneu->update([
                        'status_pneu' => 'DESCARTE',
                    ]);

                    // Registrar no histórico corretamente
                    HistoricoPneu::create([
                        'id_pneu' => $pneu->id_pneu,
                        'data_inclusao' => now(),
                        'status_movimentacao' => 'PNEU ENVIADO PARA DESCARTE',
                        // Se quiser, pode adicionar outros campos preenchidos automaticamente:
                        // 'observacoes_operacao' => 'Enviado para descarte via sistema'
                    ]);
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pneus enviados para Descarte com sucesso!',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }

        return view('pneus.admin.index', compact('idCheck', 'pneuSelecionado', 'pneu'));
    }

    public function applyFilters(Request $request, $query)
    {
        if ($request->filled('id_pneu')) {
            $query->where('id_pneu', $request->id_pneu);
        }

        if ($request->filled('descricao_destino')) {
            $query->where('descricao_destino', $request->descricao_destino);
        }

        if ($request->filled(['data_inclusao_inicial', 'data_inclusao_final'])) {
            $query->whereBetween('data_inclusao', [
                $request->data_inclusao_inicial,
                $request->data_inclusao_final,
            ]);
        }

        if ($request->filled('destinacao_solicitada')) {
            $query->where('destinacao_solicitada', $request->destinacao_solicitada);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled('status_pneu')) {
            $query->where('status_pneu', $request->status_pneu);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }
    }
}
