<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GerarOrdemServicoAuxiliar;
use App\Modules\Compras\Models\Fornecedor;
use App\Models\VFilial;
use App\Models\Departamento;
use App\Modules\Veiculos\Models\Veiculo;
use App\Models\Manutencao;
use App\Models\Servico;
use App\Models\User;
use App\Models\GerarOSVeiculosAuxiliar;
use App\Models\GerarOSManutencoesAuxiliar;
use App\Models\GerarOSServicosAuxiliar;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrdemServicosAuxiliarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = GerarOrdemServicoAuxiliar::query();

        if ($request->filled('id_os_auxiliar')) {
            $query->where('id_os_auxiliar', $request->id_os_auxiliar);
        }

        if ($request->filled('processado')) {
            $query->where('processado', $request->processado);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao);
        }

        if ($request->filled('id_recepcionista')) {
            $query->where('id_repcionista', '=', $request->id_recepcionista);
        }

        if ($request->filled('recepcionista')) {
            $query->where('id_recepcionista', '=', $request->recepcionista);
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', '=', $request->id_veiculo);
        }

        if ($request->filled('local_manutencao')) {
            $query->where('local_manutencao', '=', $request->local_manutencao);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        $ordemservicoauxiliares = $query->latest('id_os_auxiliar')
            ->paginate(40)
            ->appends($request->query());


        if ($request->header('HX-Request')) {
            return view('admin.ordemservicoauxiliares._table', compact('ordemservicoauxiliares'));
        }

        $usuariosFrequentes = Cache::remember('usuarios_frequentes', now()->addHours(12), function () {
            return User::orderBy('name')
                ->limit(20)
                ->get(['id as value', 'name as label']);
        });

        $visualizarOrdemServico = OrdemServico::where('id_lancamento_os_auxiliar', '>', 0)->orderBy('id_lancamento_os_auxiliar', 'desc')
            ->with('statusOrdemServico', 'veiculo', 'usuario', 'usuarioEncerramento', 'departamento')
            ->get();

        return view('admin.ordemservicoauxiliares.index', compact(
            'ordemservicoauxiliares',
            'usuariosFrequentes',
            'visualizarOrdemServico'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formOptions = $this->getOptions();

        return view('admin.ordemservicoauxiliares.create', compact('formOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::info('dados recebidos', [$request->all()]);

        //Log::info('dados recebidos para gravação: ' . json_encode($request->all()));
        $validacao = $request->validate([
            'data_abertura'   => 'required',
            'id_departamento' => 'required'
        ]);

        try {
            DB::beginTransaction();
            // Instância do modelo
            $osAuxiliar = new GerarOrdemServicoAuxiliar();

            // Atribuição dos campos ao modelo
            $osAuxiliar->data_inclusao    = now();
            $osAuxiliar->data_abertura    = $validacao['data_abertura'];
            $osAuxiliar->id_departamento  = $validacao['id_departamento'];
            $osAuxiliar->id_repcionista   = Auth::User()->id;
            $osAuxiliar->processado       = false;
            $osAuxiliar->local_manutencao = $request->local_manutencao;
            $osAuxiliar->id_filial        = $request->id_filial;
            $osAuxiliar->id_fornecedor    = $request->id_fornecedor;


            // Salvar no banco
            $osAuxiliar->save();

            $veiculosOsAuxiliar = json_decode($request->input('osVeiculos', '[]'), true);

            foreach ($veiculosOsAuxiliar as $veiculoOsAuxiliar_data) {
                $osVeiculoAuxiliar = new GerarOSVeiculosAuxiliar();

                $osVeiculoAuxiliar->data_inclusao  = now();
                $osVeiculoAuxiliar->id_os_auxiliar = $osAuxiliar->id_os_auxiliar;
                $osVeiculoAuxiliar->id_veiculo     = $veiculoOsAuxiliar_data['idVeiculo'];
                $osVeiculoAuxiliar->km_horimetro   = $veiculoOsAuxiliar_data['km'];

                $osVeiculoAuxiliar->save();
            }

            $manutencaoOsAuxiliar = json_decode($request->input('osManutencao', '[]'), true);

            foreach ($manutencaoOsAuxiliar as $manutencaoOsAuxiliar_data) {
                $osManutencaoAuxiliar = new GerarOSManutencoesAuxiliar();

                $osManutencaoAuxiliar->data_inclusao  = now();
                $osManutencaoAuxiliar->id_os_auxiliar = $osAuxiliar->id_os_auxiliar;
                $osManutencaoAuxiliar->id_manutencao  = $manutencaoOsAuxiliar_data['idManutencao'];

                $osManutencaoAuxiliar->save();
            }

            $servicosOsAuxiliar = json_decode($request->input('osServicos', '[]'), true);

            foreach ($servicosOsAuxiliar as $servicoOsAuxiliar_data) {
                $osServicoAuxiliar = new GerarOSServicosAuxiliar();

                $osServicoAuxiliar->data_inclusao  = now();
                $osServicoAuxiliar->id_os_auxiliar = $osAuxiliar->id_os_auxiliar;
                $osServicoAuxiliar->id_servico     = $servicoOsAuxiliar_data['IdServico'];
                // $osServicoAuxiliar->id_mecanico    = $servicoOsAuxiliar_data['idMecanico'];

                $osServicoAuxiliar->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.ordemservicoauxiliares.index')
                ->with('success', 'Ordem de Serviço Auxiliar criada com sucesso!');
        } catch (\Exception $e) {
            LOG::INFO('Erro ao gravar Ordem de Serviço Auxiliar: ' . $e->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao gravar Ordem de Serviço Auxiliar: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $id)
    {
        // Buscar a ordem de serviço com relacionamentos
        $ordemServico = OrdemServico::where('id_lancamento_os_auxiliar', $id)
            ->with('statusOrdemServico', 'veiculo', 'usuario', 'usuarioEncerramento', 'departamento')
            ->get();

        LOG::INFO($ordemServico);

        // Retornar como JSON
        return response()->json($ordemServico);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $ordemservicoauxiliares = GerarOrdemServicoAuxiliar::findOrFail($id);

        $dadosOsVeiculos = GerarOSVeiculosAuxiliar::where('id_os_auxiliar', $id)
            ->select('id_os_veiculos_auxiliar', 'data_inclusao', 'data_alteracao', 'id_veiculo', 'km_horimetro as km_atual')
            ->with('veiculo')
            ->get()
            ->toArray();

        $dadosOsManutencao = GerarOSManutencoesAuxiliar::where('id_os_auxiliar', $id)
            ->select('id_os_manutencoes_auxiliar', 'data_inclusao', 'data_alteracao', 'id_manutencao')
            ->with('manutencao')
            ->get()
            ->toArray();

        $dadosOsServicos = GerarOSServicosAuxiliar::where('id_os_auxiliar', $id)
            ->select('id_os_servicos_auxiliar', 'data_inclusao', 'data_alteracao', 'id_servico', 'id_mecanico')
            ->with('servico', 'mecanico')
            ->get()
            ->toArray();

        $formOptions = $this->getOptions();

        return view('admin.ordemservicoauxiliares.edit', compact('formOptions', 'ordemservicoauxiliares', 'dadosOsVeiculos', 'dadosOsManutencao', 'dadosOsServicos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validacao = $request->validate([
            'data_abertura'   => 'required',
            'id_departamento' => 'required'
        ]);

        try {
            DB::beginTransaction();
            // Instância do modelo
            $osAuxiliar = GerarOrdemServicoAuxiliar::findOrFail($id);

            // Atribuição dos campos ao modelo
            $osAuxiliar->data_alteracao   = now();
            $osAuxiliar->data_abertura    = $validacao['data_abertura'];
            $osAuxiliar->id_departamento  = $validacao['id_departamento'];
            $osAuxiliar->id_repcionista   = Auth::User()->id;
            $osAuxiliar->processado       = false;
            $osAuxiliar->local_manutencao = $request->local_manutencao;
            $osAuxiliar->id_filial        = $request->id_filial;


            // Salvar no banco
            $osAuxiliar->save();

            $veiculosOsAuxiliar = json_decode($request->input('osVeiculos', '[]'), true);

            GerarOSVeiculosAuxiliar::where('id_os_auxiliar', $id)->delete();
            foreach ($veiculosOsAuxiliar as $veiculoOsAuxiliar_data) {

                $osVeiculoAuxiliar = new GerarOSVeiculosAuxiliar();

                $osVeiculoAuxiliar->data_inclusao  = now();
                $osVeiculoAuxiliar->id_os_auxiliar = $id;
                $osVeiculoAuxiliar->id_veiculo     = $veiculoOsAuxiliar_data['idVeiculo'] ?? $veiculoOsAuxiliar_data['id_veiculo'];
                $osVeiculoAuxiliar->km_horimetro   = $veiculoOsAuxiliar_data['km'] ?? $veiculoOsAuxiliar_data['km_atual'];

                $osVeiculoAuxiliar->save();
            }

            $manutencaoOsAuxiliar = json_decode($request->input('osManutencao', '[]'), true);

            GerarOSManutencoesAuxiliar::where('id_os_auxiliar', $id)->delete();
            foreach ($manutencaoOsAuxiliar as $manutencaoOsAuxiliar_data) {

                $osManutencaoAuxiliar = new GerarOSManutencoesAuxiliar();

                $osManutencaoAuxiliar->data_inclusao  = now();
                $osManutencaoAuxiliar->id_os_auxiliar = $id;
                $osManutencaoAuxiliar->id_manutencao  = $manutencaoOsAuxiliar_data['idManutencao'] ?? $manutencaoOsAuxiliar_data['id_manutencao'];

                $osManutencaoAuxiliar->save();
            }

            $servicosOsAuxiliar = json_decode($request->input('osServicos', '[]'), true);

            GerarOSServicosAuxiliar::where('id_os_auxiliar', $id)->delete();
            foreach ($servicosOsAuxiliar as $servicoOsAuxiliar_data) {

                $osServicoAuxiliar = new GerarOSServicosAuxiliar();

                $osServicoAuxiliar->data_inclusao  = now();
                $osServicoAuxiliar->id_os_auxiliar = $id;
                $osServicoAuxiliar->id_servico     = $servicoOsAuxiliar_data['id_servico'] ?? $servicoOsAuxiliar_data['IdServico'];
                // $osServicoAuxiliar->id_mecanico    = $servicoOsAuxiliar_data['id_mecanico'] ?? $servicoOsAuxiliar_data['idMecanico'];

                $osServicoAuxiliar->save();
            }

            DB::commit();

            return redirect()
                ->route('admin.ordemservicoauxiliares.index')
                ->with('success', 'Ordem de Serviço Auxiliar atualizado com sucesso!');
        } catch (\Exception $e) {
            LOG::INFO('Erro ao atualizar Ordem de Serviço Auxiliar: ' . $e->getMessage());
            DB::rollBack();
            return redirect()
                ->route('admin.ordemservicoauxiliares.index')
                ->with('error', 'Erro ao atualizar Ordem de Serviço Auxiliar: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            GerarOSServicosAuxiliar::where('id_os_auxiliar', $id)->delete();
            GerarOSManutencoesAuxiliar::where('id_os_auxiliar', $id)->delete();
            GerarOSVeiculosAuxiliar::where('id_os_auxiliar', $id)->delete();
            GerarOrdemServicoAuxiliar::where('id_os_auxiliar', $id)->delete();

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir OS Auxiliar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function onGerarOsAuxiliar(Request $request)
    {
        Log::info('Iniciando geração de OS auxiliar', [$request->all()]);

        $id_lancamento = $request->id;
        $confirma = $request->confirma;

        $retornoProcessado = GerarOrdemServicoAuxiliar::select('processado')
            ->where('id_os_auxiliar', $id_lancamento)
            ->first();

        $retornoManutencao = GerarOSManutencoesAuxiliar::where('id_os_auxiliar', $id_lancamento)->exists();

        if (!$retornoProcessado) {
            return response()->json(['error' => 'Não será possível Gerar as Ordens de Serviço, nenhuma manutenção salva neste processamento, salve uma manutenção e tente novamente.'], 500);
        }

        if (!$retornoManutencao) {
            return response()->json(['error' => 'Não será possível Gerar as Ordens de Serviço, nenhuma manutenção salva neste processamento, salve uma manutenção e tente novamente.'], 500);
        }

        if (isset($confirma) && $confirma == 1) {
            try {
                $retorno = DB::connection('pgsql')->select("SELECT * from fc_gerar_os_auxiliar(?)", [$id_lancamento]);

                if ($retorno[0]->fc_gerar_os_auxiliar == 1) {

                    DB::connection('pgsql')->table('ordem_servico')
                        ->where('id_lancamento_os_auxiliar', $id_lancamento)
                        ->update(['id_recepcionista' => Auth::user()->id]);

                    return response()->json([
                        'success' => true,
                        'message' => 'Ordens de Serviço Geradas com Sucesso!',
                        'redirect' => route('admin.ordemservicoauxiliares.index')
                    ]);
                }

                return response()->json(['error' => "Não foi possível gerar as Ordens de Serviço."]);
            } catch (\Exception $e) {
                Log::info('Erro ao gerar OS auxiliar', ['message' => $e->getMessage()]);
                return response()->json(['error' => 'Erro inesperado ao gerar as Ordens de Serviço.'], 500);
            }
        }

        return response()->json(['error' => 'A geração das preventivas foi cancelada.']);
    }


    public function getOptions()
    {
        return [
            'fornecedor' => Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')->orderBy('label')->get()->toArray(),
            'filial' => VFilial::select('id as value', 'name as label')->orderBy('label')->get()->toArray(),
            'departamento' => Departamento::select('id_departamento as value', 'descricao_departamento as label')->orderBy('label')->get()->toArray(),
            'usuarios' => User::select('id as value', 'name as label')->orderBy('label')->get()->toArray(),
            'veiculos' => Veiculo::select('id_veiculo as value', 'placa as label')->where('situacao_veiculo', true)->where('is_terceiro', false)->orderBy('label')->get()->toArray(),
            'manutencao' => Manutencao::select('id_manutencao as value', 'descricao_manutencao as label')->where('auxiliar', true)->orderBy('label')->get()->toArray(),
            'servico' => Servico::select(
                'id_servico as value',
                DB::raw("CONCAT('Código: ', id_servico, ' - ', descricao_servico) as label")
            )
                ->where('auxiliar', true)
                ->orderBy('label')
                ->get()
                ->toArray(),
        ];
    }

    public function validarKMAtual(Request $request)
    {
        try {
            $veiculo = Veiculo::find($request->veiculo);
            $kmAtual      = $request->km_atual;
            $dataAbertura = $request->data_abertura;

            if (!$veiculo->is_terceiro && isset($kmAtual)) {
                $kmAbastecimento = $this->BuscarKmAbastecimentoStatico($veiculo->id_veiculo, $dataAbertura); //retorna o último abastecimento conforme a data de abertura
                return response()->json(['success' => true, 'valid' => $kmAtual < $kmAbastecimento]); //$kmAtual < $kmAbastecimento;
            }
        } catch (\Exception $e) {
            log::error('Erro ao validar Km: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao validar Km: ' . $e->getMessage()
            ]); //$kmAtual < $kmAbastecimento;
        }
    }

    public static function BuscarKmAbastecimentoStatico($idVeiculo, $data)
    {
        if (!empty($idVeiculo) && !empty($data)) {
            $objects = DB::connection('pgsql')->select("SELECT
                                        lt.km_abastecimento
                                    FROM v_abastecimento_listar_todos AS lt
                                    JOIN veiculo AS v ON lt.placa = v.placa
                                    WHERE v.id_veiculo = ?
                                    AND lt.data_inicio <= ?
                                    ORDER BY
                                        lt.data_inicio
                                    DESC
                                    LIMIT 1", [intval($idVeiculo), $data]);

            if ($objects) {
                foreach ($objects as $object) {
                    $Km = $object->km_abastecimento;
                }
            }


            $Km = empty($Km) ? 0 : $Km;
            return $Km;
        }
    }

    public function buscarKmVeiculo($id)
    {
        Log::info("Buscando último KM para o veículo ID: $id");
        try {
            $veiculo = Veiculo::findOrFail($id);
            $dataAtual = now()->toDateString();

            $ultimoKm = $this->BuscarKmAbastecimentoStatico($veiculo->id_veiculo, $dataAtual);

            return response()->json([
                'success' => true,
                'km' => $ultimoKm
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar último KM: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar último KM'
            ]);
        }
    }
}
