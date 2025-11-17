<?php

namespace App\Modules\Manutencao\Controllers\Admin;

use App\Modules\Compras\Models\Fornecedor;
use App\Modules\Compras\Models\FornecedorXMecanico;
use App\Models\OrdemServico;
use App\Models\OrdemServicoServicos;
use App\Models\ServicosMecanico;
use App\Modules\Veiculos\Models\Veiculo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Traits\ExportableTrait;
use App\Models\Servico;
use Carbon\Carbon;
use Exception;

class ManutencaoServicosMecanicosControlller extends Controller
{
    use ExportableTrait;

    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);

        $query = ServicosMecanico::where('status_servico', '!=', 'FINALIZADO');

        if ($request->filled('id_servico_mecanico')) {
            $query->where('id_servico_mecanico', $request->id_servico_mecanico);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('id_veiculo')) {
            $id = Veiculo::where('placa', 'ilike', '%' . $request->id_veiculo . '%')->first()->id_veiculo;
            $query->where('id_veiculo', $id);
        }

        if ($request->filled('id_os')) {
            $query->where('id_os', $request->id_os);
        }

        $manutancaoServicoMec = $query->latest('id_servico_mecanico')
            ->with('fornecedor', 'veiculo', 'servico', 'pessoal')
            ->paginate(10)
            ->withQueryString();

        $referenceDatas = $this->getReferenceDatas();

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                ->orderBy('nome_fornecedor')
                ->limit(20)
                ->get();
        });

        if ($request->header('HX-Request')) {
            return view('admin.manutencaoservicosmecanico._table', compact('manutancaoServicoMec', 'fornecedoresFrequentes'));
        }

        return view('admin.manutencaoservicosmecanico.index', [
            'manutancaoServicoMec'      => $manutancaoServicoMec,
            'fornecedoresFrequentes'    => $fornecedoresFrequentes,
        ] + $referenceDatas);
    }

    public function edit($id)
    {
        $manutancaoServicoMec = ServicosMecanico::where('id_servico_mecanico', $id)->first();

        $mecanicos = FornecedorXMecanico::select('id_fornecedor_mecanico as value', 'nome_mecanico as label')
            ->where('nome_mecanico', '!=', null)
            ->orderBy('id_fornecedor_mecanico')
            ->get();

        $fornecedoresFrequentes = Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                ->orderBy('nome_fornecedor')
                ->limit(20)
                ->get();
        });

        return view('admin.manutencaoservicosmecanico.edit', compact(
            'mecanicos',
            'manutancaoServicoMec',
            'fornecedoresFrequentes'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id_user_mecanico'        => 'required|string',
            'data_inicial_servicos'   => 'required|string',
        ]);

        try {
            $idservicomecanico = $request->id;
            $idmecanico        = $request->id_user_mecanico;
            $datainicio        = $request->data_inicial_servicos;
            $statusservico     = ServicosMecanico::select('status_servico')->where('id_servico_mecanico', '=', $idservicomecanico)->first()->status_servico;

            if ($statusservico == 'PENDENTE DE INICIALIZAÇÃO') {
                if (!empty($idservicomecanico) && !empty($idmecanico)) {
                    $this->atualizartempodiagnosticoinicio($idservicomecanico, $idmecanico, $datainicio);
                    $this->atualizarstatusosinicio($idservicomecanico, $idmecanico);

                    return redirect()->route('admin.manutencaoservicosmecanico.index')->with('success', "Serviço iniciado para o Mecânico!");
                } else {
                    return redirect()->route('admin.manutencaoservicosmecanico.index')->with('error', "Não é possível iniciar sem Mecânico!");
                }
            } else {
                return redirect()->route('admin.manutencaoservicosmecanico.index')->with('error', "Não é possível INICIAR o Serviço sem estar PENDENTE DE INICIALIZAÇÃO.");
            }
        } catch (Exception $e) {
            return redirect()->route('admin.manutencaoservicosmecanico.index')->with('error', $e->getMessage());
        }
    }

    public function finalizarTodos(Request $request)
    {
        // Converte a string de IDs em um array
        $array_ids = explode(",", $request->ids);
        try {
            foreach ($array_ids as $id) {
                $idservicomecanico = $id;
                $os_servico        = ServicosMecanico::where('id_servico_mecanico', '=', $idservicomecanico)->first();

                if (isset($os_servico)) {
                    $idmecanico    = $os_servico->id_mecanico;
                    $datafinal     = $os_servico->data_inicial_servicos;
                    $statusservico = $os_servico->status_servico;
                }

                if ($statusservico == 'INICIADO') {
                    if (!empty($idservicomecanico) && !empty($idmecanico)) {

                        $this->atualizartempodiagnosticofim($idservicomecanico, $idmecanico, $datafinal);
                        $this->atualizarstatusosfim($idservicomecanico, $idmecanico);

                        return redirect()->back()->with('success', "Serviço finalizado para o Mecânico!");
                    } else {
                        return redirect()->back()->with('info', "Não é possível iniciar sem Mecânico!");
                    }
                } else {
                    return redirect()->back()->with('info', "Não é possível Finalizar o Serviço sem ter Iniciado.");
                }
                return redirect()->back()->with('error', "Não é possível Finalizar o Serviço sem ter Iniciado.");
            }
        } catch (Exception $e) {
            LOG::info($e->getMessage());
        }
    }

    public function atualizartempodiagnosticoinicio($idservicomecanico, $idmecanico, $datainicio)
    {
        try {
            // Busca segura do id_servico
            $registro = ServicosMecanico::select('id_servico')
                ->where('id_servico_mecanico', $idservicomecanico)
                ->first();

            if (!$registro) {
                LOG::error("Serviço mecânico não encontrado: id_servico_mecanico = $idservicomecanico");
                return false; // ou lançar uma exceção se preferir
            }

            $idservico = $registro->id_servico;

            // Lista de serviços especiais
            $idservicoss = [99901, 530005, 530008, 3701345, 590002];

            // Busca do registro do serviço mecânico
            $inicio = ServicosMecanico::find($idservicomecanico);

            if (!$inicio) {
                LOG::error("Serviço mecânico não encontrado para update: id_servico_mecanico = $idservicomecanico");
                return false;
            }

            if (in_array($idservico, $idservicoss)) {
                $inicio->data_inicial_diagnostico = $datainicio;
            } else {
                $inicio->data_inicial_servicos = $datainicio;
            }

            $inicio->status_servico = 'INICIADO';
            $inicio->id_mec_inicial = $idmecanico;

            $inicio->save();

            return true;
        } catch (\Exception $e) {
            LOG::error('ERRO AO ATUALIZAR O STATUS DO SERVIÇO: ' . $e->getMessage());
            return false;
        }
    }


    public function atualizarstatusosinicio($idservicomecanico, $idmecanico)
    {
        $idservico  = ServicosMecanico::select('id_servico')->where('id_servico_mecanico', $idservicomecanico)->first()->id_servico;
        $idos       = ServicosMecanico::select('id_os')->where('id_servico_mecanico', $idservicomecanico)->first()->id_os;

        try {
            db::beginTransaction();
            $ordemServico = OrdemServico::findorFail($idos);

            $ordemServico->data_alteracao = now();
            $ordemServico->id_status_ordem_servico = 2;

            $ordemServico->update();

            $ordemServicoServicos = OrdemServicoServicos::where('id_ordem_servico', $idos)
                ->where('user_mec', $idmecanico)
                ->where('id_servicos', $idservico)
                ->first();

            if (isset($ordemServicoServicos)) {
                $ordemServicoServicos->data_alteracao = now();
                $ordemServicoServicos->status_servico = 'INICIADO';
                $ordemServicoServicos->update();
            } else {
                throw new \Exception("Nenhum serviço encontrado para id_ordem_servico=$idos, user_mec=$idmecanico, id_servicos=$idservico");
                return redirect()->route('admin.manutencaoservicosmecanico.index')->with('error', 'Ocorreu um erro ao atualizar o status do serviço.');
            }

            db::commit();
        } catch (\Exception $e) {
            db::rollBack();
            Log::error('Erro ao atualizar status da Ordem de serviço: ' . $e->getMessage());
        }
    }

    public function atualizartempodiagnosticofim($idservicomecanico, $idmecanico, $datafinal)
    {

        $idservico = ServicosMecanico::select('id_servico')->where('id_servico_mecanico', $idservicomecanico)->first()->id_servico;
        $idservicoss = array(99901, 530005, 530008, 3701345, 590002);

        if (in_array($idservico, $idservicoss)) {

            $diagnosticoUpdate = ServicosMecanico::findorFail($idservicomecanico);

            if (isset($diagnosticoUpdate)) {
                $diagnosticoUpdate->data_alteracao         = now();
                $diagnosticoUpdate->data_final_diagnostico = now();
                $diagnosticoUpdate->status_servico         = 'FINALIZADO';
                $diagnosticoUpdate->id_mec_final           = $idmecanico;

                $diagnosticoUpdate->update();
            } else {
                throw new \Exception("Nenhum serviço encontrado para id_servico_mecanico=$idservicomecanico");
            }
        } else {
            $servicoUpdate = ServicosMecanico::findorFail($idservicomecanico);
            if (isset($servicoUpdate)) {
                $servicoUpdate->data_alteracao         = now();
                $servicoUpdate->data_final_servicos    = now();
                $servicoUpdate->status_servico         = 'FINALIZADO';
                $servicoUpdate->id_mec_final           = $idmecanico;

                $servicoUpdate->update();
            } else {
                throw new \Exception("Nenhum serviço encontrado para id_servico_mecanico=$idservicomecanico");
            }
        }
    }

    public function atualizarstatusosfim($idservicomecanico, $idmecanico)
    {
        $objects = ServicosMecanico::where('id_servico_mecanico', $idservicomecanico)->first();

        if ($objects) {
            $idos       = $objects->id_os;
            $idservico  = $objects->id_servico;
            $idmecanico = $objects->id_mecanico;

            $ordemServicoServicos = OrdemServicoServicos::where('id_ordem_servico', $idos)
                ->where('id_servicos', $idservico)
                ->where('user_mec', $idmecanico)
                ->first();

            if (isset($ordemServicoServicos)) {

                $ordemServicoServicos->data_alteracao = now();
                $ordemServicoServicos->status_servico = 'FINALIZADO';
                $ordemServicoServicos->update();
            } else {
                return redirect()->back()->with('error', 'Nenhuma Ordem de serviço encontrado para Ordem Serviço ' . $idos);
            }

            $statusfinalizado = DB::connection('pgsql')->table('ordem_servico_servicos')
                ->where('id_ordem_servico', $idos)
                ->where('id_fornecedor', '<>', 1)
                ->where('status_servico', '<>', 'FINALIZADO')
                ->count() === 0;

            if ($statusfinalizado == false) {

                $updateOrdemServico = OrdemServico::where('id_ordem_servico', $idos)->first();

                if (isset($updateOrdemServico)) {
                    $updateOrdemServico->data_alteracao = now();
                    $updateOrdemServico->id_status_ordem_servico = 11;
                    $updateOrdemServico->update();
                } else {
                    throw new \Exception("(atualizarstatusosfim)->Nenhuma Ordem de serviço encontrado para Ordem de Serviço = $idos");
                }
            } else {
                $statusfinalizado = 2;
            }
        } else {
            throw new \Exception("(atualizarstatusosfim)->Nenhum serviço encontrado para id_servico_mecanico=$idservicomecanico");
        }
    }

    public function getReferenceDatas()
    {
        return Cache::remember('manutencaoServicosMecanicos_reference_datas', now()->addHours(12), function () {
            return [
                'fornecedoresFrequentes' => Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                    ->orderBy('nome_fornecedor')
                    ->limit(20)
                    ->get()
            ];
        });
    }

    public function exportPdf(Request $request)
    {
        try {
            LOG::INFO('Exportando PDF...');
            $query = $this->buildExportQuery($request);

            // Se a exportação direta pelo trait não funcionar, tente um método alternativo
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                // Configurar opções do PDF de forma mais simples
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar a view
                $pdf->loadView('admin.manutencaoservicosmecanico.pdf', compact('data'));

                // Forçar download em vez de exibir no navegador
                return $pdf->download('ServicoMecanicos_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            // Log detalhado do erro
            \Illuminate\Support\Facades\Log::error('Erro ao gerar PDF: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }

    public function exportCsv(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_servico_mecanico' => 'Cód. Serviço Mecanico',
            'fornecedor.nome_fornecedor' => 'Fornecedor',
            'servico.descricao_servico' => 'Serviço',
            'id_os' => 'Cod. O.S.',
            'veiculo.placa' => 'Placa',
            'status_servico' => 'Status'
        ];

        return $this->exportToCsv($request, $query, $columns, 'servico_mecanicos', $this->getValidExportFilters());
    }

    public function exportXls(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $columns = [
            'id_servico_mecanico' => 'Cód. Serviço Mecanico',
            'fornecedor.nome_fornecedor' => 'Fornecedor',
            'servico.descricao_servico' => 'Serviço',
            'id_os' => 'Cod. O.S.',
            'veiculo.placa' => 'Placa',
            'status_servico' => 'Status'
        ];

        return $this->exportToExcel($request, $query, $columns, 'servicos_mecanicos', $this->getValidExportFilters());
    }

    public function exportXml(Request $request)
    {
        $query = $this->buildExportQuery($request);

        $structure = [
            'CodServicoMecanico' => 'id_servico_mecanico',
            'Fornecedor' => 'fornecedor.nome_fornecedor',
            'Servico' => 'servico.descricao_servico',
            'CodOS' => 'id_os',
            'Placa' => 'veiculo.placa',
            'Status' => 'status_servico'
        ];

        return $this->exportToXml(
            $request,
            $query,
            $structure,
            'ServicosMecanicos',
            'ServicoMecanico',
            'servico_mecanicos',
            $this->getValidExportFilters()
        );
    }

    protected function buildExportQuery(Request $request)
    {
        Log::info('Parâmetros recebidos:', $request->all());

        $query = ServicosMecanico::query()
            ->with('fornecedor', 'veiculo', 'servico', 'pessoal');

        if ($request->filled('id_servico_mecanico')) {
            $query->where('id_servico_mecanico', $request->id_servico_mecanico);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }

        if ($request->filled('id_veiculo')) {
            $veiculo = Veiculo::where('placa', 'ilike', '%' . $request->id_veiculo . '%')->first();
            $query->where('id_veiculo', $veiculo->id_veiculo);
        }

        if ($request->filled('id_os')) {
            $query->where('id_os', $request->id_os);
        }

        return $query->latest('id_servico_mecanico');
    }

    protected function getValidExportFilters()
    {
        return [
            'id_servico_mecanico',
            'id_fornecedor',
            'id_veiculo',
            'id_servico',
            'id_os',
            'status_servico'
        ];
    }
}
