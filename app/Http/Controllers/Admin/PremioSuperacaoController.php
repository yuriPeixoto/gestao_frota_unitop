<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PremioSuperacao;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PremioSuperacaoController extends Controller
{
    public function index(Request $request)
    {
        $query = PremioSuperacao::query();

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereBetween('data_inclusao', [
                $request->input('data_inclusao'),
                $request->input('data_final')
            ]);
        }

        if ($request->filled('id_premio_superacao')) {
            $query->where('id_premio_superacao', $request->input('id_premio_superacao'));
        }
        if ($request->filled('id_user')) {
            $query->where('id_user', $request->input('id_user'));
        }
        if ($request->filled('situacao')) {
            $query->where('situacao', $request->input('situacao'));
        }

        $user = User::select('id as value', 'name as label')
            ->orderBy('name')
            ->limit(30)
            ->get();

        $situacao = PremioSuperacao::select('situacao as value', 'situacao as label')
            ->distinct()
            ->orderBy('situacao')
            ->get();

        $listagem = $query->latest('id_premio_superacao')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.premiosuperacao.index', compact('user', 'situacao', 'listagem'));
    }

    public function create()
    {
        $premio = null;
        return view('admin.premiosuperacao.create', compact('premio'));
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'data_inicial' => 'required|date',
            'data_final'   => 'required|date',
        ], [
            'data_inicial.required' => 'A Data Inicial é Obrigatória.',
            'data_final.required'   => 'A Data Final é Obrigatória.',
        ]);

        $user = Auth::user()->id;

        try {
            // Verifica se já existe prêmio aberto no mesmo período
            $jaExiste = PremioSuperacao::where('data_inicial', $validate['data_inicial'])
                ->where('data_final', $validate['data_final'])
                ->where('situacao', 'ATIVO')
                ->exists();

            if ($jaExiste) {
                return redirect()
                    ->route('admin.premiosuperacao.index')
                    ->with('warning', 'Atenção: já existe um prêmio aberto para o período informado, não será possível abrir outro.');
            }

            // Cria o prêmio principal (equivalente ao onPremioUnitop)
            DB::beginTransaction();

            $premio = new PremioSuperacao();
            $premio->data_inclusao = now();
            $premio->id_user = $user;
            $premio->data_inicial = $validate['data_inicial'];
            $premio->data_final = $validate['data_final'];
            $premio->situacao = 'ATIVO';
            $premio->save();

            $cod_premio = $premio->id_premio_superacao;

            // Processa RV (resultado variável)
            DB::select("SELECT * FROM fc_premio_rv_gerar_km(?, ?)", [
                $validate['data_inicial'],
                $validate['data_final']
            ]);

            DB::select("SELECT * FROM fc_premio_rv_gerar_calculo(?)", [
                $cod_premio
            ]);

            // Processa Mensal
            DB::select("SELECT * FROM fc_premio_base_motorista_mensal(?, ?)", [
                $validate['data_inicial'],
                $validate['data_final']
            ]);

            DB::select("SELECT * FROM fc_premio_mensal_calculo_base(?, ?, ?)", [
                $validate['data_inicial'],
                $validate['data_final'],
                $cod_premio
            ]);

            DB::commit();

            // Redireciona com mensagem de sucesso
            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('success', 'Prêmio cadastrado e processado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar prêmio: ' . $e->getMessage());

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('error', 'Erro ao cadastrar prêmio: ' . $e->getMessage());
        }
    }

    public function reprocessar(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $premio = PremioSuperacao::findOrFail($id);

            $cod = $premio->id_premio_superacao;
            $data_inicial = $premio->data_inicial;
            $data_final = $premio->data_final;

            // Limpa dados anteriores (equivalente ao DELETE do MadBuilder)
            DB::statement("DELETE FROM calcular_premio_mensal_e_rv WHERE cod_premio = ?", [$cod]);

            // Chama funções do banco
            DB::select("SELECT * FROM fc_premio_rv_gerar_km(?, ?)", [
                $data_inicial,
                $data_final
            ]);

            DB::select("SELECT * FROM fc_premio_rv_gerar_calculo(?)", [
                $cod
            ]);

            DB::select("SELECT * FROM fc_premio_base_motorista_mensal(?, ?)", [
                $data_inicial,
                $data_final
            ]);

            DB::select("SELECT * FROM fc_premio_mensal_calculo_base(?, ?, ?)", [
                $data_inicial,
                $data_final,
                $cod
            ]);

            DB::commit();

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('success', 'Prêmio reprocessado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao reprocessar prêmio: ' . $e->getMessage());

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('error', 'Erro ao reprocessar prêmio: ' . $e->getMessage());
        }
    }

    public function finalizarPremio($id)
    {
        try {
            DB::beginTransaction();

            // Busca o prêmio
            $premio = PremioSuperacao::findOrFail($id);

            // Executa as funções de histórico
            DB::select("SELECT * FROM fc_calcular_premio_rv_historico()");
            DB::select("SELECT * FROM fc_calcular_premio_mensal_historico()");

            // Atualiza situação do prêmio
            $premio->situacao = 'FINALIZADO';
            $premio->save();

            DB::commit();

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('success', 'Prêmio finalizado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao finalizar prêmio: ' . $e->getMessage());

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('error', 'Erro ao finalizar prêmio: ' . $e->getMessage());
        }
    }

    public function confirmarPagamento($id)
    {
        try {
            DB::beginTransaction();

            // Busca o prêmio e marca como pago
            $premio = PremioSuperacao::findOrFail($id);
            $premio->pagamento_realizado = true;
            $premio->save();

            DB::commit();

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('success', 'Pagamento confirmado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao confirmar pagamento do prêmio: ' . $e->getMessage());

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('error', 'Erro ao confirmar pagamento do prêmio: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Busca o prêmio e marca como pago
            $premio = PremioSuperacao::findOrFail($id);
            $status = $premio->situacao;
            $cod = $premio->id_premio_superacao;

            if ($status === 'FINALIZADO') {
                return redirect()
                    ->route('admin.premiosuperacao.index')
                    ->with('error', 'Não é possível excluir premiação pois a mesma já esta finalizada!');
            }


            DB::statement("DELETE FROM calcular_premio_mensal_e_rv WHERE cod_premio = ?", [$cod]);


            $premio->delete();

            DB::commit();

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('success', 'Prêmio excluído com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir prêmio: ' . $e->getMessage());

            return redirect()
                ->route('admin.premiosuperacao.index')
                ->with('error', 'Erro ao excluir prêmio: ' . $e->getMessage());
        }
    }
}
