<?php

namespace App\Modules\Imobilizados\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Produto;
use App\Modules\Imobilizados\Models\RelacaoImobilizados;
use App\Modules\Imobilizados\Models\RelacaoImobilizadosItens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class SolicitacaoImobilizadoController extends Controller
{
    public function index(Request $request)
    {
        $query = RelacaoImobilizados::query()
            ->with(['departamento', 'user', 'filial']);

        // Aplicar filtros
        if ($request->filled('id_relacao_imobilizados')) {
            $query->where('id_relacao_imobilizados', $request->id_relacao_imobilizados);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereRaw("data_inclusao::date >= ?", [$request->data_inclusao]);
        }

        if ($request->filled('data_alteracao')) {
            $query->whereRaw("data_alteracao::date <= ?", [$request->data_alteracao]);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled('id_usuario')) {
            $query->where('id_usuario', $request->id_usuario);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $relacaoImobilizados = $query->latest('id_relacao_imobilizados')
            ->orderBy('id_relacao_imobilizados', 'desc')
            ->where('status', '!=', 'FINALIZADA')
            ->paginate(15)
            ->appends($request->query());


        $id_relacao_imobilizados = $this->getIdRelacaoImobilizados();

        $requisicaoImobilizadosItens = $this->getRequisicaoItens();

        $id_departamento_relacao_imobilizados = $this->getIdDepartamentoRelacaoImobilizados();

        $id_usuario_relacao_imobilizados = $this->getIdUsuarioRelacaoImobilizados();

        $status_relacao_imobilizados = $this->getStatusRelacaoImobilizados();

        return view(
            'admin.solicitacaoimobilizado.index',
            compact(
                'relacaoImobilizados',
                'id_relacao_imobilizados',
                'requisicaoImobilizadosItens',
                'id_departamento_relacao_imobilizados',
                'id_usuario_relacao_imobilizados',
                'status_relacao_imobilizados',
            )
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departamento = Departamento::select('id_departamento as value', 'descricao_departamento as label')
            ->orderBy('descricao_departamento')
            ->limit(20)
            ->get()
            ->toArray();

        $users = User::select('id as value', 'name as label')
            ->orderBy('label')
            ->get()
            ->toArray();

        $filial = Filial::select('id as value', 'name as label')->get();

        $produto = Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_imobilizado', '=', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();

        $produtoDescricao = Produto::all()
            ->pluck('descricao_produto', 'id_produto')
            ->toArray();

        return view(
            'admin.solicitacaoimobilizado.create',
            compact('departamento', 'users', 'filial', 'produto', 'produtoDescricao')
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        // dd($request);
        $relacaoImobilizadosValidate = $request->validate([
            'id_usuario'           => 'required',
            'id_departamento'      => 'required',
            'id_filial'            => 'required',
            'motivo_transferencia' => 'required',
        ]);

        DB::beginTransaction();

        try {

            $historicoRelacao = json_decode($request->historicos);
            $relacaoImobilizados = null;

            // dd($historicoRelacao);

            foreach ($historicoRelacao as $index => $item) {
                // dd($relacaoImobilizadosValidate);

                // Cria a manutenção só uma vez, no primeiro item
                if ($index == 0) {
                    $relacaoImobilizados = RelacaoImobilizados::create([
                        'data_inclusao'              => now(),
                        'id_usuario'                 => $relacaoImobilizadosValidate['id_usuario'],
                        'id_departamento'            => $relacaoImobilizadosValidate['id_departamento'],
                        'id_filial'                  => $relacaoImobilizadosValidate['id_filial'],
                        'motivo_transferencia'       => $relacaoImobilizadosValidate['motivo_transferencia'],
                    ]);
                }

                // dd($relacaoImobilizados);

                // Itens da manutenção
                RelacaoImobilizadosItens::create([
                    'data_inclusao'              => now(),
                    'id_produtos'                => $item->id_produtos,
                    'id_relacao_imobilizados'    => $relacaoImobilizados->id_relacao_imobilizados,
                    'id_departamento'            => $relacaoImobilizados->id_departamento,
                ]);
            }


            DB::commit();

            return redirect()
                ->route('admin.solicitacaoimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Solicitação Imobilizado cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação de Solicitação Imobilizado:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.solicitacaoimobilizado.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar a Solicitação Imobilizado." . $e->getMessage()
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
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

    private function getIdRelacaoImobilizados()
    {
        return RelacaoImobilizados::select('id_relacao_imobilizados as value', 'id_relacao_imobilizados as label')
            ->where('status', '!=', 'FINALIZADA')
            ->orderBy('id_relacao_imobilizados', 'desc')
            ->get()
            ->toArray();
    }

    private function getRequisicaoItens()
    {
        return RelacaoImobilizadosItens::orderBy('id_relacao_imobilizados_itens')
            ->with('produto')
            ->get()
            ->map(function ($itens) {
                return [
                    'label' => $itens->id_relacao_imobilizados_itens,
                    'value' => $itens->id_relacao_imobilizados,
                    'produto' => $itens->produto->descricao_produto ?? "Não informado",
                    'data_inclusao' => $itens->data_inclusao
                ];
            })
            ->values()
            ->toArray();
    }

    private function getIdDepartamentoRelacaoImobilizados()
    {
        return RelacaoImobilizados::join('departamento', 'relacao_imobilizados.id_departamento', '=', 'departamento.id_departamento')
            ->select('relacao_imobilizados.id_departamento as value', 'departamento.descricao_departamento as label')
            ->groupBy('relacao_imobilizados.id_departamento', 'departamento.descricao_departamento')
            ->orderBy('relacao_imobilizados.id_departamento', 'desc')
            ->get()
            ->toArray();
    }

    private function getStatusRelacaoImobilizados()
    {
        return RelacaoImobilizados::select('status as value', 'status as label')
            ->distinct()
            ->orderBy('status', 'desc')
            ->get()
            ->toArray();
    }

    private function getIdUsuarioRelacaoImobilizados()
    {
        return RelacaoImobilizados::join('users', 'relacao_imobilizados.id_usuario', '=', 'users.id')
            ->select('relacao_imobilizados.id_usuario as value', 'users.name as label')
            ->groupBy('relacao_imobilizados.id_usuario', 'users.name')
            ->orderBy('relacao_imobilizados.id_usuario', 'desc')
            ->get()
            ->toArray();
    }
}
