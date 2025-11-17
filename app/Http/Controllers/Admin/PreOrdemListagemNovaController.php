<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\GrupoResolvedor;
use App\Modules\Pessoal\Models\Pessoal;
use App\Models\Telefone;
use App\Models\OrdemServico;
use App\Models\PreOrdemServico;
use App\Models\PreOrdemServicoServicos;
use App\Models\Servico;
use App\Models\TipoStatusPreOs;
use App\Models\User;
use App\Modules\Veiculos\Models\Veiculo;
use App\Models\VUltimasManutencoesVeiculo;
use App\Models\VUltimasPreventivasVeiculo;
use App\Models\VManutencaoVencida;
use App\Traits\JasperServerIntegration;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class PreOrdemListagemNovaController extends Controller
{
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        
        $query = PreOrdemServico::query()
            ->with(['user', 'veiculo', 'grupoResolvedor', 'ordemServico']);

        if ($request->filled('id_pre_os')) {
            $query->where('id_pre_os', $request->id_pre_os);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('idobtermotorista')) {
            $query->where('id_motorista', $request->idobtermotorista);
        }

        if ($request->filled('id_tipostatus_pre_os')) {
            $query->where('id_status', $request->id_tipostatus_pre_os);
        }

        if ($request->filled('id_tipostatus_pre_os')) {
            $query->where('id_status', $request->id_tipostatus_pre_os);
        }

        if ($request->filled('id')) {
            $query->where('id_recepcionista', $request->id);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_departamento')) {
            $query->where('id_departamento', $request->id_departamento);
        }

        if ($request->filled('id_grupo_resolvedor')) {
            $query->where('id_grupo_resolvedor', $request->id_grupo_resolvedor);
        }

        $preOrdemOs = $query->latest('id_pre_os')
            ->paginate(10);


        $referenceDatas = $this->getReferenceDatas();
        return view('admin.manutencaopreordemserviconova.index', array_merge(
            [
                'preOrdemOs'      => $preOrdemOs,
                'referenceDatas'  => $referenceDatas,
            ]
        ));
    }

    public function getReferenceDatas()
    {
        return Cache::remember('pre_ordem_listagem', now()->addHours(12), function () {
            return [
                'veiculosFrequentes' => Veiculo::where('situacao_veiculo', true)
                    ->orderBy('placa')
                    ->limit(20)
                    ->get(['id_veiculo as value', 'placa as label']),

                'filiais' => Filial::orderBy('name')
                    ->get(['id as value', 'name as label']),

                'departamentos' => Departamento::select('id_departamento as value', 'descricao_departamento as label')
                    ->orderBy('descricao_departamento')
                    ->get(),

                'motoristas' => Pessoal::where('ativo', true)
                    ->orderBy('nome')
                    ->limit(20)
                    ->get(['id_pessoal as value', 'nome as label']),

                'statusPreOs' => TipoStatusPreOs::orderBy('id_tipostatus_pre_os')->get(['id_tipostatus_pre_os as value', 'descricao_tipo_status as label']),

                'recepcinista'  => User::orderBy('name')
                    ->limit(20)
                    ->get(['id as value', 'name as label']),

                'grupoResolvedor'   =>  GrupoResolvedor::orderBy('id_grupo_resolvedor')
                    ->get(['id_grupo_resolvedor as value', 'descricao_grupo_resolvedor as label']),
            ];
        });
    }

    public function create()
    {

        $servico = Servico::all()
            ->pluck('descricao_servico', 'id_servico')
            ->toArray();

        $selectOptions = [
            'motoristas'        => Pessoal::where('ativo', true)->orderBy('nome')->get(['id_pessoal as value', 'nome as label']),
            'filiais'           => Filial::orderBy('name')->get(['id as value', 'name as label']),
            'placa'             => Veiculo::where('situacao_veiculo', true)->orderBy('placa')->get(['id_veiculo as value', 'placa as label']),
            'departamentos'     => Departamento::orderBy('descricao_departamento')->get(['id_departamento as value', 'descricao_departamento as label']),
            'recepcinista'      => User::orderBy('name')->get(['id as value', 'name as label']),
            'grupoResolvedor'   => GrupoResolvedor::orderBy('id_grupo_resolvedor')->get(['id_grupo_resolvedor as value', 'descricao_grupo_resolvedor as label']),
            'statusPreOs'       => TipoStatusPreOs::orderBy('id_tipostatus_pre_os')->get(['id_tipostatus_pre_os as value', 'descricao_tipo_status as label'])
        ];

        $servicosFrequentes = Cache::remember('servicos_frequentes', now()->addHours(2), function () {
            return Servico::select('id_servico as value', 'descricao_servico as label')
                ->orderBy('id_servico')
                ->get();
        });


        return view('admin.manutencaopreordemserviconova.create', compact(
            'selectOptions',
            'servicosFrequentes',
            'servico'
        ));
    }

    public function edit($id)
    {

        $preOrdemFinalizada = PreOrdemServico::where('id_pre_os', $id)
            ->with('servico')
            ->first();

        $preOrdemServicoServicos = PreOrdemServicoServicos::all();

        $ordemServicoServicos = PreOrdemServicoServicos::where('id_pre_os', $id)->get();

        $vManutencaoVencidas = VManutencaoVencida::where('id_veiculo', $preOrdemFinalizada->id_veiculo)->get();

        $servico = Servico::all()
            ->pluck('descricao_servico', 'id_servico')
            ->toArray();


        $selectOptions = [
            'motoristas'        => Pessoal::where('ativo', true)->orderBy('nome')->get(['id_pessoal as value', 'nome as label']),
            'filiais'           => Filial::orderBy('name')->get(['id as value', 'name as label']),
            'placa'             => Veiculo::where('situacao_veiculo', true)->orderBy('placa')->get(['id_veiculo as value', 'placa as label']),
            'departamentos'     => Departamento::orderBy('descricao_departamento')->get(['id_departamento as value', 'descricao_departamento as label']),
            'recepcinista'      => User::orderBy('name')->get(['id as value', 'name as label']),
            'grupoResolvedor'   => GrupoResolvedor::orderBy('id_grupo_resolvedor')->get(['id_grupo_resolvedor as value', 'descricao_grupo_resolvedor as label']),
            'statusPreOs'       => TipoStatusPreOs::orderBy('id_tipostatus_pre_os')->get(['id_tipostatus_pre_os as value', 'descricao_tipo_status as label']),
        ];

        $servicosFrequentes = Cache::remember('servicos_frequentes', now()->addHours(12), function () {
            return Servico::select('id_servico as value', 'descricao_servico as label')
                ->orderBy('id_servico')
                ->limit(20)
                ->get();
        });

        return view('admin.manutencaopreordemserviconova.edit', compact(
            'preOrdemFinalizada',
            'selectOptions',
            'servicosFrequentes',
            'preOrdemServicoServicos',
            'ordemServicoServicos',
            'servico',
            'vManutencaoVencidas'
        ));
    }

    public function store(Request $request)
    {
        $cadastro = $request->validate([
            'id_motorista'          => 'required|integer',
            'telefone_motorista'    => 'required|string',
            'id_status'             => 'required|integer',
            'id_filial'             => 'required|integer',
            'id_veiculo'            => 'required|integer',
            'id_departamento'       => 'required|integer',
            'id_recepcionista'      => 'required|integer',
            'id_usuario'            => 'required|integer',
            'local_execucao'        => 'required|string',
            'situacao_pre_os'       => 'required|string',
            'km_realizacao'         => 'required|string',
            'horimetro_tk'          => 'required|string',
            'descricao_reclamacao'  => 'required|string',
            'observacoes'           => 'required|string',
        ], [
            'id_motorista.required' => 'O campo Motorista é obrigatório.',
            'telefone_motorista.required' => 'O campo Telefone é obrigatório.',
            'id_status.required' => 'O campo Status é obrigatório.',
            'id_filial.required' => 'O campo Filial é obrigatório.',
            'id_veiculo.required' => 'O campo Placa é obrigatório.',
            'id_departamento.required' => 'O campo Departamento é obrigatório.',
            'id_recepcionista.required' => 'O campo Recepcionista é obrigatório.',
            'id_usuario.required' => 'O campo Usuário é obrigatório.',
            'local_execucao.required' => 'O campo Local Execução é obrigatório.',
            'situacao_pre_os.required' => 'O campo Prioridade Pré O.S é obrigatório.',
            'km_realizacao.required' => 'O campo KM Realização é obrigatório.',
            'horimetro_tk.required' => 'O campo Horimetro TK é obrigatório.',
            'descricao_reclamacao.required' => 'O campo Descrição Reclamação é obrigatório.',
            'observacoes.required' => 'O campo Observações é obrigatório.',
            'id_usuario.required' => 'O campo Usuário é obrigatório.',
            'id_recepcionista.required' => 'O campo Recepcionista é obrigatório.',
        ]);

        $cadastro['data_inclusao'] = now();


        try {
            DB::beginTransaction();

            $id = PreOrdemServico::create(array_merge(
                [
                    'id_usuario' => auth()->user()->id,
                    'id_recepcionista' => auth()->user()->id
                ],
                $cadastro
            ));

            // Validação e tratamento do campo servicos
            if (!$request->has('servicos') || empty($request->servicos)) {
                throw new Exception('Nenhum serviço foi selecionado');
            }

            $servicos = json_decode($request->servicos);

            // Verificação adicional se o JSON foi decodificado corretamente
            if ($servicos === null) {
                throw new Exception('Dados de serviços inválidos');
            }

            // Verificação se é um array válido
            if (!is_array($servicos) || empty($servicos)) {
                throw new Exception('Pelo menos um serviço deve ser selecionado');
            }

            // Prepara dados para inserção em massa (mais eficiente)
            $servicosToInsert = [];

            foreach ($servicos as $servicoData) {
                // Validação adicional dos dados do serviço
                if (!isset($servicoData->id_servico)) {
                    throw new Exception('Dados de serviço incompletos');
                }

                $servicosToInsert[] = [
                    'data_inclusao' => now(),
                    'id_servico' => $servicoData->id_servico,
                    'id_pre_os' => $id->id_pre_os, // CORREÇÃO: usar $id->id_pre_os em vez de $id
                    'observacao' => $servicoData->observacao ?? null,
                ];
            }

            PreOrdemServicoServicos::insert($servicosToInsert);

            DB::commit();

            return redirect()->route('admin.manutencaopreordemserviconova.index')->with('success', 'Pré O.S cadastrada com sucesso');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar Pré O.S:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.manutencaopreordemserviconova.index')->with('error', 'Não foi possível criar a Pré O.S.');
        }
    }

    public function update(Request $request, $id)
    {

        $cadastro = $request->validate([
            'id_motorista'          => 'required|string',
            'telefone_motorista'    => 'required|string',
            'id_status'             => 'required|string',
            'id_filial'             => 'required|string',
            'id_veiculo'            => 'required|string',
            'id_departamento'       => 'required|string',
            'id_recepcionista'      => 'required|string',
            'id_usuario'            => 'required|string',
            'local_execucao'        => 'required|string',
            'situacao_pre_os'       => 'required|string',
            'km_realizacao'         => 'required|string',
            'horimetro_tk'          => 'required|string',
            'descricao_reclamacao'  => 'required|string',
            'observacoes'           => 'required|string',
        ], [
            'id_motorista.required' => 'O campo Motorista é obrigatório.',
            'telefone_motorista.required' => 'O campo Telefone é obrigatório.',
            'id_status.required' => 'O campo Status é obrigatório.',
            'id_filial.required' => 'O campo Filial é obrigatório.',
            'id_veiculo.required' => 'O campo Placa é obrigatório.',
            'id_departamento.required' => 'O campo Departamento é obrigatório.',
            'id_recepcionista.required' => 'O campo Recepcionista é obrigatório.',
            'id_usuario.required' => 'O campo Usuário é obrigatório.',
            'local_execucao.required' => 'O campo Local Execução é obrigatório.',
            'situacao_pre_os.required' => 'O campo Prioridade Pré O.S é obrigatório.',
            'km_realizacao.required' => 'O campo KM Realização é obrigatório.',
            'horimetro_tk.required' => 'O campo Horimetro TK é obrigatório.',
            'descricao_reclamacao.required' => 'O campo Descrição Reclamação é obrigatório.',
            'observacoes.required' => 'O campo Observações é obrigatório.',
            'id_usuario.required' => 'O campo Usuário é obrigatório.',
            'id_recepcionista.required' => 'O campo Recepcionista é obrigatório.',
        ]);

        $cadastro['data_inclusao'] = now();

        $servico = $request->validate([
            'id_servico'            => 'nullable|string',
            'observacao'            => 'nullable|string',
        ]);


        $servico['data_inclusao'] = now();

        try {
            DB::beginTransaction();

            $preOrdem = PreOrdemServico::findorFail($id);
            $preOrdem->data_alteracao       = now();
            $preOrdem->id_veiculo           = $cadastro['id_veiculo'];
            $preOrdem->id_motorista         = $cadastro['id_motorista'];
            $preOrdem->telefone_motorista   = $cadastro['telefone_motorista'];
            $preOrdem->km_realizacao        = $cadastro['km_realizacao'];
            $preOrdem->horimetro_tk         = $cadastro['horimetro_tk'];
            $preOrdem->local_execucao       = $cadastro['local_execucao'];
            $preOrdem->descricao_reclamacao = $cadastro['descricao_reclamacao'];
            $preOrdem->id_usuario           = $cadastro['id_usuario'];
            $preOrdem->id_status            = $cadastro['id_status'];
            $preOrdem->observacoes          = $cadastro['observacoes'];
            $preOrdem->id_recepcionista     = $cadastro['id_recepcionista'];
            $preOrdem->id_filial            = $cadastro['id_filial'];
            $preOrdem->id_departamento      = $cadastro['id_departamento'];
            $preOrdem->id_grupo_resolvedor  = $request->id_grupo_resolvedor ?? null;
            $preOrdem->situacao_pre_os      = $cadastro['situacao_pre_os'];
            $preOrdem->id_user_create       = Auth::user()->id;

            $preOrdem->save();

            // Validação e processamento dos serviços
            if (!$request->has('servicos') || empty($request->servicos)) {
                throw new Exception('Nenhum serviço foi selecionado');
            }

            $servicosRecebidos = json_decode($request->servicos);
            Log::info('→ Histórico recebido', ['historico' => $servicosRecebidos]);

            if ($servicosRecebidos === null || !is_array($servicosRecebidos) || empty($servicosRecebidos)) {
                throw new Exception('Dados de serviços inválidos');
            }

            // Obtenha os IDs dos serviços do histórico para comparação
            $idsServicosHistorico = collect($servicosRecebidos)->pluck('id_pre_os_servicos')->filter()->toArray();
            Log::info('→ IDs dos serviços do histórico', ['ids' => $idsServicosHistorico]);

            // Busque os itens existentes e remova os que não estão no histórico
            $itensExistentes = PreOrdemServicoServicos::where('id_pre_os', $id)->get();
            Log::info('→ Itens existentes no banco', ['itens' => $itensExistentes]);

            foreach ($itensExistentes as $item) {
                if (!in_array($item->id_pre_os_servicos, $idsServicosHistorico)) {
                    Log::warning('→ Removendo item que não está mais no histórico', ['item' => $item]);
                    $item->delete();
                }
            }

            Log::info('→ Iniciando processamento do histórico');
            foreach ($servicosRecebidos as $index => $itemServico) {
                Log::info("→ Processando item $index", ['item' => $itemServico]);

                // Se tem ID, atualiza o existente
                if (isset($itemServico->id_pre_os_servicos)) {
                    $servicoExistente = PreOrdemServicoServicos::find($itemServico->id_pre_os_servicos);
                    if ($servicoExistente) {
                        $servicoExistente->update([
                            'data_alteracao' => now(),
                            'id_servico' => $itemServico->id_servico,
                            'observacao' => $itemServico->observacao ?? null,
                        ]);
                        Log::info('→ Serviço atualizado', ['servico' => $servicoExistente]);
                    }
                } else {
                    // Cria novo item
                    $novoItem = PreOrdemServicoServicos::create([
                        'data_inclusao' => now(),
                        'id_servico' => $itemServico->id_servico,
                        'id_pre_os' => $id,
                        'observacao' => $itemServico->observacao ?? null,
                    ]);
                    Log::info('→ Item criado', ['novoItem' => $novoItem]);
                }
            }

            DB::commit();

            return redirect()->route('admin.manutencaopreordemserviconova.index')->with('success', 'Pré O.S Atualizada com sucesso');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao editar Pré O.S:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('admin.manutencaopreordemserviconova.index')->with('error', 'Não foi possível atualizar a Pré O.S.');
        }
    }

    public function assumirPreOs($id)
    {
        $preOrdemassumir = PreOrdemServico::where('id_pre_os', $id)->first();

        if ($preOrdemassumir->id_usuario) {
            return response()->json(['success' => false, 'message' => 'Essa pré os já foi assumida, não podendo ser assumida de novo'], 500);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user()->id;

            $preOrdemassumir->update([
                'id_status'      => 2,
                'id_usuario'     => $user,
                'data_alteracao' => now(),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function finalizarOs($id)
    {
        $preOsFilnalizar = PreOrdemServico::where('id_pre_os', $id)->first();

        try {
            DB::beginTransaction();

            $preOsFilnalizar->update([
                'id_status'      => 3,
                'data_alteracao' => now(),
            ]);

            DB::commit();

            Log::info('Pré O.S finalizada com sucesso', ['id_pre_os' => $id]);

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::info('Erro ao finalizar Pré O.S', ['id_pre_os' => $id, 'message' => $e->getMessage()]);

            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $preOSServicos = PreOrdemServicoServicos::where('id_pre_os', $id)->first();
            if ($preOSServicos) {
                $preOSServicos->delete();
            }
            $preOsExcluir = PreOrdemServico::where('id_pre_os', $id)->first();
            $preOsExcluir->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function preventiva($id)
    {
        $preOs = PreOrdemServico::where('id_pre_os', $id)->first();

        $preOrdemOs = VUltimasPreventivasVeiculo::where('id_veiculo', $preOs->id_veiculo)->get();

        return view('admin.manutencaopreordemserviconova.preventiva', array_merge(
            [
                'preOrdemOs'      => $preOrdemOs,
                'idPreOs'        => $preOs->id_pre_os,
            ]
        ));
    }

    public function gerarPreventiva(Request $request)
    {
        Log::debug('Iniciando geração de preventiva', ['request' => $request->all()]);
        $id = $request->preos;
        $idsArray = explode(',', $request->ids); // Converte a string em array

        try {
            DB::beginTransaction();

            // Criação do array PostgreSQL com bind correto
            $placeholders = implode(',', array_fill(0, count($idsArray), '?'));
            $sql = "SELECT * FROM public.fc_gerar_preventiva_pre_os(?, ARRAY[$placeholders]::bigint[])";

            Log::debug('SQL gerado para chamada da função', ['sql' => $sql]);

            // Merge dos parâmetros: primeiro o ids original, depois os itens do array
            $params = array_merge([$id], $idsArray);

            $resultado = DB::connection('pgsql')->select($sql, $params);

            if (empty($resultado) || !$resultado[0]->fc_gerar_preventiva_pre_os) {
                return redirect()->route('admin.manutencaopreordemserviconova.index')
                    ->with('error', 'Ocorreu um erro ao gerar O.S preventiva. Tente novamente.');
            }

            $preOsFilnalizar = PreOrdemServico::where('id_pre_os', $id)->first();

            $preOsFilnalizar->update([
                'id_status'      => 3,
                'data_alteracao' => now(),
            ]);

            DB::commit();
            return redirect("admin/ordemservicos_preventiva/{$resultado[0]->fc_gerar_preventiva_pre_os}/edit_preventiva");
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao gerar preventiva: ' . $e->getMessage());

            return redirect()->route('admin.manutencaopreordemserviconova.index')
                ->with('error', 'Ocorreu um erro ao gerar O.S preventiva. Tente novamente.');
        }
    }

    public function gerarCorretiva($id)
    {
        $ordemServico = OrdemServico::where('id_pre_os', $id)->first();

        Log::info('Verificando existência de O.S para pré O.S ID: ' . $id, ['ordem_servico' => $ordemServico]);

        if ($ordemServico) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Essa Pre O.S já tem uma O.S vinculada',
                ], 400);
            }
            return redirect()->route('admin.manutencaopreordemserviconova.index')
                ->with('error', 'Essa Pre O.S já tem uma O.S vinculada');
        }

        try {
            DB::beginTransaction();

            $retorno = DB::connection('pgsql')->select("SELECT * FROM fc_gerar_corretiva_pre_os(?)", [$id]);

            $preOsFilnalizar = PreOrdemServico::where('id_pre_os', $id)->first();

            $preOsFilnalizar->update([
                'id_status'      => 3,
                'data_alteracao' => now(),
            ]);

            DB::commit();

            if (empty($retorno) || !$retorno[0]->fc_gerar_corretiva_pre_os) {
                if (request()->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ocorreu um erro ao gerar O.S preventiva. Tente novamente.'
                    ], 400);
                }
                return redirect()->route('admin.manutencaopreordemserviconova.index')
                    ->with('error', 'Ocorreu um erro ao gerar O.S preventiva. Tente novamente.');
            }

            // Sucesso
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'O.S corretiva gerada com sucesso',
                    'redirect' => url("admin/ordemservicos/{$retorno[0]->fc_gerar_corretiva_pre_os}/edit")
                ]);
            }
            return redirect("admin/ordemservicos/{$retorno[0]->fc_gerar_corretiva_pre_os}/edit");
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao gerar O.S corretiva: ' . $e->getMessage(), [
                'exception' => $e,
                'pre_os_id' => $id,
            ]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao gerar O.S corretiva. Detalhes: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('admin.manutencaopreordemserviconova.index')
                ->with('error', 'Erro ao gerar O.S corretiva. Detalhes: ' . $e->getMessage());
        }
    }

    public function historico($id)
    {
        $preOs = PreOrdemServico::where('id_pre_os', $id)->first();

        $query = VUltimasManutencoesVeiculo::where('id_veiculo', $preOs->id_veiculo)->where('id_ordem_servico', '!=', null);

        $preOrdemOs = $query->latest('id_ordem_servico')
            ->paginate(10)
            ->withQueryString();

        return view('admin.manutencaopreordemserviconova.historico', array_merge(
            [
                'preOrdemOs'      => $preOrdemOs,
                'idPreOs'        => $preOs->id_pre_os,
            ]
        ));
    }

    public function getInfoVeiculo(Request $request)
    {
        $veiculo = Veiculo::where('id_veiculo', $request->id_veiculo)
            ->with('filial')
            ->first();




        if ($veiculo) {
            return response()->json([
                'success' => true,
                'data' => [
                    'categoria' => $veiculo->categoriaVeiculo->descricao_categoria,
                    'modelo' => $veiculo->marca_veiculo,
                    'tipo_equipamento' => $veiculo->tipoEquipamento->descricao_tipo ?? '',
                    'chassis' => $veiculo->chassi,  //campo adicionado para suprir necessidade em ordem de serviço
                    'id_filial' => $veiculo->filial->name, //campo adicionado para suprir necessidade em ordem de serviço
                    'placa' => $veiculo->placa  //campo adicionado para suprir necessidade em ordem de serviço
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Veículo não encontrado'], 404);
    }

    public function getTelefoneMotorista(Request $request)
    {
        $motorista = Telefone::where('id_pessoal', $request->id_motorista)->first();

        if ($motorista) {
            return response()->json([
                'success' => true,
                'data' => [
                    'telefone_motorista' => $motorista->telefone_celular,
                ]
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Motorista não encontrado'], 404);
    }

    public function onImprimir(Request $request)
    {
        Log::info('=== DEBUG COMPLETO ===');
        Log::info('Método HTTP: ' . $request->method());
        Log::info('Todos os inputs: ', $request->all());

        try {
            // Receber como arrays ou converter para arrays se necessário
            $id_pre_os = $request->input('id', []);

            // Se for array, pegar o primeiro elemento; se for string/número, usar diretamente
            if (is_array($id_pre_os)) {
                $id_pre_os = !empty($id_pre_os) ? $id_pre_os[0] : null;
            }

            // Verificar se temos um ID válido
            if (empty($id_pre_os)) {
                throw new Exception('ID da Pré O.S não fornecido');
            }

            // Converter para string para garantir compatibilidade
            $id_pre_os = (string) $id_pre_os;

            $parametros = array('P_id_pre_os' => $id_pre_os);

            Log::info('Parâmetros processados: ', $parametros);

            // Resto da lógica do relatório...
            $name = 'relatorio_pre_os';
            $agora = date('d-m-YH:i');
            $tipo = '.pdf';
            $relatorio = $name . $agora . $tipo;

            $partes = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;

                Log::info('Usando servidor de homologação');
            } elseif ($dominio == 'lcarvalima') {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/carvalima/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                    Log::info('Permissões do diretório alteradas: ' . $input);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;

                Log::info('Usando servidor de produção');
            } else {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/' . $dominio . '/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                    Log::info('Permissões do diretório alteradas: ' . $input);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;

                Log::info('Usando servidor de produção');
            }

            $jsi = new JasperServerIntegration(
                $jasperserver,
                $pastarelatorio,
                'pdf',
                'unitop',
                'unitop2022',
                $parametros
            );

            try {
                $data = $jsi->execute();
                return response($data, 200)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $relatorio . '"');
            } catch (Exception $e) {
                Log::error('Erro ao gerar relatório: ' . $e->getMessage());
                return back()->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível gerar o relatório. ' . $e->getMessage()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Erro geral: ' . $e->getMessage());
            return back()->withNotification([
                'title'   => 'Erro!',
                'type'    => 'error',
                'message' => 'Erro inesperado: ' . $e->getMessage()
            ]);
        }
    }
}
