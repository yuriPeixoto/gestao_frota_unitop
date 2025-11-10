<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CadastroImobilizado;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\RegistroCompraVenda;
use App\Models\StatusCadastroImobilizado;
use App\Models\TipoImobilizado;
use App\Models\Veiculo;
use App\Models\VFilial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CadastroImobilizadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $query = CadastroImobilizado::query();

            if ($request->filled('id_cadastro_imobilizado')) {
                $query->where('id_cadastro_imobilizado', $request->id_cadastro_imobilizado);
            }

            $cadastroImobilizado = $query->latest('id_cadastro_imobilizado')
                ->paginate(40)
                ->appends($request->query());


            $id_cadastro_imobilizado = $request->id_cadastro_imobilizado;

            return view('admin.cadastroimobilizado.index', compact('cadastroImobilizado', 'id_cadastro_imobilizado'));
        } catch (\Exception $e) {
            Log::error('Erro ao listar tipos de Solicitação: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Não foi possível carregar a lista de tipos de Solicitação.'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $tipoImobilizado = $this->getTipoImobilizado();

        $filial = $this->getFiliais();

        $fornecedor = $this->getFornecedor();

        $veiculo = $this->getVeiculo();

        $statusCadastroImobilizado = $this->getStatusCadastroImobilizado();

        $produto = $this->getProduto();

        return view(
            'admin.cadastroimobilizado.create',
            compact(
                'tipoImobilizado',
                'filial',
                'fornecedor',
                'veiculo',
                'statusCadastroImobilizado',
                'produto'
            )
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação mais robusta
        $validatedData = $request->validate([
            'id_usuario' => 'required',
            'status_cadastro_imobilizado' => 'required',
            'id_filial' => 'required',
            'observacao' => 'required',
            'id_fornecedor' => 'required',
            'chave_nf' => 'required',
            'numero_nota_fiscal' => 'nullable',
            'id_tipo_imobilizado' => 'required',
        ]);

        $dadosCompraVeiculo = $request->validate([
            'financiador'               => 'nullable',
            'data_inicio_financiamento' => 'nullable',
            'valor_do_bem'              => 'nullable',
            'numero_de_parcelas'        => 'nullable',
            'valor_parcela'             => 'nullable',
            'numero_processo'           => 'nullable',
            'reclamante_nome'           => 'nullable',
            'valor_processo'            => 'nullable',
            'valor_da_compra'           => 'nullable',
            'data_compra'               => 'nullable',
            'numero_patrimonio'         => 'nullable',
            'data_venda'                => 'nullable',
            'valor_da_venda'            => 'nullable',
            'km_final'                  => 'nullable',
            'hora_final'                => 'nullable',
            'motivo_venda'              => 'nullable',
        ]);


        try {
            DB::beginTransaction();

            // Buscar o tipo de imobilizado antes de criar o registro
            $tipoImobilizado = TipoImobilizado::find($validatedData['id_tipo_imobilizado']);

            // Verificar se o tipo existe
            if (!$tipoImobilizado) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Tipo de imobilizado não encontrado!');
            }

            // Verificar se o tipo começa com 'VEÍCULOS'
            if (!str_starts_with($tipoImobilizado->descricao_tipo_imobilizados, 'VEÍCULOS')) {
                Log::error('Tipo de imobilizado inválido: ', [
                    'descricao' => $tipoImobilizado->descricao_tipo_imobilizados
                ]);

                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'O tipo de imobilizado é inválido. Deve ser um veículo.');
            }

            // Cria a manutenção
            $cadastroImobilizado = CadastroImobilizado::create([
                'usuario'                     => $validatedData['id_usuario'],
                'status_cadastro_imobilizado' => $validatedData['status_cadastro_imobilizado'],
                'id_tipo_imobilizado'         => $validatedData['id_tipo_imobilizado'],
                'id_filial'                   => $validatedData['id_filial'],
                'observacao'                  => $validatedData['observacao'],
                'id_fornecedor'               => $validatedData['id_fornecedor'],
                'chave_nf'                    => $validatedData['chave_nf'],
                'numero_nota_fiscal'          => $validatedData['numero_nota_fiscal'],
                'data_inclusao'               => now(),
            ]);
            $cadastroImobilizadoId = $cadastroImobilizado->id_cadastro_imobilizado;

            $veiculo = Veiculo::create([
                'id_cadastro_imobilizado'     => $cadastroImobilizadoId,
                'status_cadastro_imobilizado' => $validatedData['status_cadastro_imobilizado'],
                'id_filial'                   => $validatedData['id_filial'],
                'id_user_cadastro'            => $validatedData['id_usuario'],
                'situacao_veiculo'            => true,
                'placa'                       => 'N/A',
                'data_inclusao'               => now(),
            ]);

            $veiculoId = $veiculo->id_veiculo;

            $cadastroImobilizado->update([
                'id_veiculo' => $veiculoId
            ]);

            if (!empty($dadosCompraVeiculo['financiador']) || !empty($dadosCompraVeiculo['data_venda'])) {
                $dadosCompraVeiculo['data_inclusao']       = now();
                $dadosCompraVeiculo['data_compra']         = $dadosCompraVeiculo['data_compra'] ?? null;
                $dadosCompraVeiculo['id_filial']           = $validatedData['id_filial'];
                $dadosCompraVeiculo['id_veiculo']          = $veiculoId;
                $dadosCompraVeiculo['id_usuario_cadastro'] = Auth::id();

                $dadosCompraVeiculo['valor_do_bem']        = $this->sanitizeMoney($dadosCompraVeiculo['valor_do_bem']);
                $dadosCompraVeiculo['valor_parcela']       = $this->sanitizeMoney($dadosCompraVeiculo['valor_parcela']);
                $dadosCompraVeiculo['valor_processo']      = $this->sanitizeMoney($dadosCompraVeiculo['valor_processo']);
                $dadosCompraVeiculo['valor_da_compra']     = $this->sanitizeMoney($dadosCompraVeiculo['valor_da_compra']);

                // Criar o registro de compra/venda.
                RegistroCompraVenda::create($dadosCompraVeiculo);
            }

            DB::commit();

            return redirect()
                ->route('admin.cadastroimobilizado.index')
                ->with('success', 'Cadastro imobilizado cadastrado com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar Cadastro imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Não foi possível cadastrar o Cadastro imobilizado. ' . $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(CadastroImobilizado $cadastroImobilizado)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cadastroImobilizado = CadastroImobilizado::find($id);

        $registroCompra = RegistroCompraVenda::where('id_veiculo', '=', $cadastroImobilizado->id_veiculo)->first();

        $tipoImobilizado = $this->getTipoImobilizado();

        $filial = $this->getFiliais();

        $fornecedor = $this->getFornecedor();

        $veiculo = $this->getVeiculo();

        $statusCadastroImobilizado = $this->getStatusCadastroImobilizado();

        $produto = $this->getProduto();

        return view(
            'admin.cadastroimobilizado.edit',
            compact(
                'cadastroImobilizado',
                'registroCompra',
                'tipoImobilizado',
                'filial',
                'fornecedor',
                'veiculo',
                'statusCadastroImobilizado',
                'produto'
            )
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Validação mais robusta
        $validatedData = $request->validate([
            'id_usuario' => 'required',
            'status_cadastro_imobilizado' => 'required',
            'id_tipo_imobilizado' => 'required',
            'id_filial' => 'required',
            'observacao' => 'required',
            'id_fornecedor' => 'required',
            'chave_nf' => 'required',
            'numero_nota_fiscal' => 'nullable',
        ]);

        $dadosCompraVeiculo = $request->validate([
            'financiador'               => 'nullable',
            'data_inicio_financiamento' => 'nullable',
            'valor_do_bem'              => 'nullable',
            'numero_de_parcelas'        => 'nullable',
            'valor_parcela'             => 'nullable',
            'numero_processo'           => 'nullable',
            'reclamante_nome'           => 'nullable',
            'valor_processo'            => 'nullable',
            'valor_da_compra'           => 'nullable',
            'data_compra'               => 'nullable',
            'numero_patrimonio'         => 'nullable',
            'id_fornecedor_comprador'   => 'nullable',
            'data_venda'                => 'nullable',
            'valor_da_venda'            => 'nullable',
            'km_final'                  => 'nullable',
            'hora_final'                => 'nullable',
            'motivo_venda'              => 'nullable',
        ]);


        // dd($validatedData);

        try {
            DB::beginTransaction();

            // Atualiza a manutenção existente
            $cadastroImobilizado = CadastroImobilizado::findOrFail($id);
            $cadastroImobilizado->update([
                'usuario_solicitante'         => $validatedData['id_usuario'],
                'status_cadastro_imobilizado' => $validatedData['status_cadastro_imobilizado'],
                'id_tipo_imobilizado'         => $validatedData['id_tipo_imobilizado'],
                'id_filial'                   => $validatedData['id_filial'],
                'observacao'                  => $validatedData['observacao'],
                'id_fornecedor'               => $validatedData['id_fornecedor'],
                'chave_nf'                    => $validatedData['chave_nf'],
                'numero_nota_fiscal'          => $validatedData['numero_nota_fiscal'],
                'data_alteracao'               => now(),
            ]);

            $cadastroImobilizadoIdVeiculo = $cadastroImobilizado->id_veiculo;

            // dd($cadastroImobilizado, $validatedData);
            if (!empty($dadosCompraVeiculo['financiador']) || !empty($dadosCompraVeiculo['data_venda'])) {
                $registroCompraVenda = RegistroCompraVenda::where('id_veiculo', $cadastroImobilizadoIdVeiculo)->first();

                if ($registroCompraVenda) {
                    $registroCompraVenda->update($dadosCompraVeiculo);
                } else {
                    $dadosCompraVeiculo['data_inclusao']       = now();
                    $dadosCompraVeiculo['data_compra']         = $dadosCompraVeiculo['data_compra'];
                    $dadosCompraVeiculo['id_filial']           = $validatedData['id_filial'];
                    $dadosCompraVeiculo['id_veiculo']          = $cadastroImobilizadoIdVeiculo;
                    $dadosCompraVeiculo['id_usuario_cadastro'] = Auth::user()->id;

                    // Criar o registro de compra/venda.
                    RegistroCompraVenda::create($dadosCompraVeiculo);
                }
            }
            DB::commit();

            return redirect()
                ->route('admin.cadastroimobilizado.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Ordem de serviço imobilizado atualizada com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar manutenção de imobilizado: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return back()
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível atualizar a Ordem de serviço imobilizado." . $e->getMessage()
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $cadastroImobilizado = CadastroImobilizado::findOrFail($id);

            if (!empty($cadastroImobilizado->id_veiculo)) {
                $veiculo = Veiculo::findOrFail($cadastroImobilizado->id_veiculo);
                if ($veiculo->situacao_veiculo == true) {
                    return response()->json([
                        'notification' => [
                            'title'   => 'Erro',
                            'type'    => 'error',
                            'message' => 'Veiculo ativo, impossivel excluir'
                        ]
                    ]);
                }
            }

            $cadastroImobilizado->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Requisição Imobilizado excluída',
                    'type'    => 'success',
                    'message' => 'Requisição Imobilizado excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao excluir ordem de serviço imobilizado: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => $e->getMessage()
                ]
            ]);
        }
    }

    private function getTipoImobilizado()
    {
        return TipoImobilizado::select(
            'id_tipo_imobilizados as value',
            'descricao_tipo_imobilizados as label'
        )
            ->orderBy('descricao_tipo_imobilizados', 'asc')
            ->get()
            ->toArray();
    }

    private function getFiliais()
    {
        return VFilial::select(
            'id as value',
            'name as label'
        )
            ->orderBy('name', 'asc')
            ->get()
            ->toArray();
    }

    private function getFornecedor()
    {
        return Fornecedor::select(
            'id_fornecedor as value',
            'nome_fornecedor as label'
        )
            ->orderBy('nome_fornecedor', 'asc')
            ->limit(30)
            ->get()
            ->toArray();
    }

    private function getVeiculo()
    {
        return Veiculo::select(
            'id_veiculo as value',
            'placa as label'
        )
            ->orderBy('placa', 'asc')
            // ->limit(30)
            ->get()
            ->toArray();
    }

    private function getStatusCadastroImobilizado()
    {
        return StatusCadastroImobilizado::select(
            'id as value',
            'descricao as label'
        )
            ->orderBy('descricao', 'asc')
            ->get()
            ->toArray();
    }

    private function getProduto()
    {
        return Produto::select('id_produto as value', 'descricao_produto as label')
            ->where('is_imobilizado', '=', true)
            ->orderBy('descricao_produto')
            ->limit(30)
            ->get()
            ->toArray();
    }

    private function sanitizeMoney($value)
    {
        if (is_null($value)) {
            return 0.0;
        }

        // Remove qualquer coisa que não seja número ou vírgula
        $value = preg_replace('/[^\d,]/', '', $value);

        // Substitui vírgula por ponto para conversão float
        $value = str_replace(',', '', $value);

        // Converte para float
        return (float) $value;
    }
}
