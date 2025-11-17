<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\TransferenciaPneuItens;
use App\Models\TransferenciaPneus;
use App\Models\TransferenciaPneusModelos;
use App\Models\Pneu;
use App\Models\Historicopneu;
use App\Models\Filial;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\SanitizesMonetaryValues;

class TransferenciaPneusController extends Controller
{
    use SanitizesMonetaryValues;
    use SanitizesMonetaryValues;

    public function index(Request $request)
    {
        if (GetterFilial() == 1) {
            $query = TransferenciaPneus::query()
                ->with(['filial', 'filialBaixa', 'usuario', 'usuarioBaixa']);
        } else {
            $query = TransferenciaPneus::query()
                ->with(['filial', 'filialBaixa', 'usuario', 'usuarioBaixa'])
                ->where('id_filial', GetterFilial());
        }

        // Aplicar filtros
        if ($request->filled('id_transferencia_pneus')) {
            $query->where('id_transferencia_pneus', $request->id_transferencia_pneus);
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_inclusao_final')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao_final);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        // Usar whereRaw para data_inclusao
        if ($request->filled('id_usuario')) {
            $query->where("id_usuario", [$request->id_usuario]);
        }

        // Executar a consulta com paginação
        $transfPneus = $query->latest('id_transferencia_pneus')
            ->paginate(40)
            ->appends($request->query());

        $filiais = Filial::orderBy('name')->get(['id as value', 'name as label']);

        $usuarios = User::orderBy('name')->get(['id as value', 'name as label']);

        // Verificar se é uma requisição HTMX (para atualização parcial)
        if ($request->header('HX-Request')) {
            return view('admin.transferenciapneus._table', compact('transfPneus', 'filiais', 'usuarios'));
        }

        return view('admin.transferenciapneus.index', compact('transfPneus', 'filiais', 'usuarios'));
    }

    public function edit($id)
    {
        $transferenciaPneus = TransferenciaPneus::with(['filial', 'filialBaixa', 'usuario', 'usuarioBaixa'])->findOrFail($id);


        $transferenciaPneuModelos = TransferenciaPneusModelos::with('modeloPneu')->where('id_transferencia_pneu', $id)->get();

        // Extrai todos os IDs de modelos em um array
        $idsModelos = $transferenciaPneuModelos->pluck('id_transferencia_pneus_modelos')->toArray();

        // Consulta usando whereIn com todos os IDs
        $transferenciaPneusItens = TransferenciaPneuItens::with('transferenciaPneuModelos', 'transferenciaPneuModelos.modeloPneu', 'transferenciaPneuModelos.modeloPneu.controleVida')->whereIn('id_transferencia_modelo', $idsModelos)->get();


        return view('admin.transferenciapneus.edit', compact('transferenciaPneus', 'transferenciaPneuModelos', 'transferenciaPneusItens'));
    }

    public function update(Request $request, $id)
    {
        $tranferenciaPneus = $request->validate([
            'observacao_saida' => 'nullable|string',
            'recebido' => 'nullable|string',
        ]);

        $pneuRequisitados = json_decode($request->input('tranfPneus'), true);
        $pneuSelecionados = json_decode($request->input('pneusSelecionados'), true);

        if (empty($pneuRequisitados)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum pneu requisitado'
            ]);
        }

        if (empty($pneuSelecionados)) {
            $pneuSelecionados = TransferenciaPneusModelos::where('id_transferencia_pneu', $id)
                ->with('modeloPneu', 'transferenciaPneusItens')
                ->get()
                ->flatMap(function ($item) {
                    return collect($item->transferenciaPneusItens)->map(function ($subItem) use ($item) {
                        return [
                            'id_pneu' => $subItem->id_pneu, // Acessa cada item individualmente
                            'codigo_modelo' => $item->modeloPneu->id_modelo_pneu ?? null,
                            'quantidade_baixa' => $item->quantidade_baixa ?? null,
                            'selecionado' => $subItem->recebido,
                        ];
                    });
                })
                ->toArray();
        }

        $qtdModelo = $this->contarSelecionadosPorCodigoModelo($pneuSelecionados);

        $qtdDict = [];
        foreach ($qtdModelo as $item) {
            $qtdDict[$item['codigo_modelo']] = $item['qtd'];
        }

        $modelo = [];
        foreach ($pneuRequisitados as $item) {
            $codigo = $item['id_modelos_requisitados'];

            // Só atualiza se quantidade_baixa for null
            if ($item['quantidade_baixa'] === null && $item['quantidade_baixa'] >= 0) {
                $item['quantidade_baixa'] = $qtdDict[$codigo] ?? null;
            }

            $modelo[] = $item;
        }

        try {
            DB::beginTransaction();

            //Atualiza tabela Transferencia Pneus Itens com o pneu recebido
            foreach ($pneuSelecionados as $item) {
                preg_match('/^\d+/', $item['id_pneu'], $matches);
                $id_pneu = intval($matches[0]);
                $transferenciaPneuItens = TransferenciaPneuItens::where('id_transferencia_modelo', $item['id_transferencia_modelo'])
                    ->where('id_pneu', $id_pneu)
                    ->first();
                $transferenciaPneuItens->data_alteracao = now();
                $transferenciaPneuItens->recebido = $item['selecionado'];
                $transferenciaPneuItens->update();
            }

            //Primeiro vamos atualizar a tabela Transferencia Pneus Modelos com a quantidade da baixa
            foreach ($modelo as $modItem) {
                $transferenciaPneuModelos = TransferenciaPneusModelos::findOrFail($modItem['id_transferencia_pneus_modelos']);
                $transferenciaPneuModelos->data_alteracao = now();
                $transferenciaPneuModelos->quantidade_baixa = $modItem['quantidade_baixa'];
                $transferenciaPneuModelos->save();
            }

            // Inicializa $recebido como true (assumindo que todas são iguais)
            $situacao = 'APROVADO';

            foreach ($modelo as $modItem) {
                // Verifica se quantidade_baixa é diferente de quantidade (incluindo casos de null)
                if ($modItem['quantidade_baixa'] != $modItem['quantidade']) {
                    $situacao = 'BAIXADO PARCIALMENTE';
                    break; // Interrompe o loop ao encontrar a primeira divergência
                }
            }

            $transfPneus = TransferenciaPneus::findOrFail($id);

            $transfPneus->data_alteracao   = now();
            $transfPneus->usuario_baixa    = Auth::user()->id;
            $transfPneus->filial_baixa     = GetterFilial();
            $transfPneus->observacao_saida = $tranferenciaPneus['observacao_saida'];
            $transfPneus->situacao         = $situacao;

            $transfPneus->save();

            DB::commit();

            return redirect()
                ->route('admin.transferenciapneus.edit', $id)
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Transferencia de pneus recebida com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro no recebimento de Transferencia de pneus:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.transferenciapneus.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => 'Não foi possível atualizar o Transferencia de pneus.'
                ]);
        }
    }


    public function getPneusByModelo($transferenciaId, $modeloId = null)
    {
        // Consulta base para transferência de pneus modelos
        $query = TransferenciaPneusModelos::with('modeloPneu', 'modeloPneu.controleVida')
            ->where('id_transferencia_pneu', $transferenciaId);

        // Se um ID de modelo específico for fornecido, filtre por ele
        if ($modeloId !== null) {
            $query->where('id_modelos_requisitados', $modeloId);
        }

        $transfPneusModelos = $query->get();

        if ($transfPneusModelos->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum modelo de pneu encontrado'
            ]);
        }

        $resultados = [];

        foreach ($transfPneusModelos as $modelo) {
            // Buscar todos os itens relacionados a este modelo
            $pneus = TransferenciaPneuItens::where('id_transferencia_modelo', $modelo->id_transferencia_pneus_modelos)
                ->get();

            // Se não houver pneus, continue para o próximo modelo
            if ($pneus->isEmpty()) {
                continue;
            }

            // Para cada pneu encontrado, adicione ao array de resultados
            foreach ($pneus as $pneu) {
                $resultados[] = [
                    'id' => $pneu->id_pneu,
                    'codigo' => $modelo->id_modelos_requisitados,
                    'modelo' => [
                        'id' => $modelo->modeloPneu->id_modelo_pneu,
                        'codigo' => $modelo->modeloPneu->id_modelo_pneu,
                        'descricao' => $modelo->modeloPneu->descricao_modelo
                    ],
                    'vida' => $modelo->modeloPneu->controleVida->descricao_vida_pneu,
                    'id_transferencia_modelo' => $modelo->id_transferencia_pneus_modelos,
                    'selecionado' => $pneu->recebido
                ];
            }
        }

        // Verificar se encontramos algum resultado após o processamento
        if (empty($resultados)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum pneu encontrado para os modelos'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pneus encontrados com sucesso',
            'data' => $resultados
        ]);
    }

    function contarSelecionadosPorCodigoModelo(array $itens)
    {
        $contagem = [];
        foreach ($itens as $item) {
            if (
                isset($item['codigo_modelo'], $item['selecionado']) &&
                $item['selecionado'] === true
            ) {
                $codigo = $item['codigo_modelo'];
                if (!isset($contagem[$codigo])) {
                    $contagem[$codigo] = 0;
                }
                $contagem[$codigo]++;
            }
        }

        // Monta o array no formato desejado
        $resultado = [];
        foreach ($contagem as $codigo => $qtd) {
            $resultado[] = [
                'codigo_modelo' => $codigo,
                'qtd' => $qtd
            ];
        }
        return $resultado;
    }


    public static function onYesFinalizarBaixaPneu(String $id)
    {
        try {
            DB::beginTransaction();

            $idFilial = Auth::user()->filial_id ?? GetterFilial();
            // Busca a transferência corretamente (find já retorna o modelo ou null)
            $transferenciaObj = TransferenciaPneus::find($id);
            $requisicao = $transferenciaObj->id_saida_pneu ?? null;

            // Inicializa arrays
            $qtd_baixa = [];
            $qtd_solicitada = [];
            $modelos = [];
            $pneus = [];

            // Busca os modelos da transferência
            $objects = TransferenciaPneusModelos::where('id_transferencia_pneu', $id)->get();

            foreach ($objects as $object) {
                $qtd_baixa[] = $object->quantidade_baixa;
                $qtd_solicitada[] = $object->quantidade;
                $modelos[] = $object->id_transferencia_pneus_modelos; // corrigido
            }

            $qtd_baixa_ = array_sum($qtd_baixa);
            $qtd_solicitada_ = array_sum($qtd_solicitada);

            // Busca os pneus vinculados aos modelos
            if (!empty($modelos)) {
                $objectos = TransferenciaPneuItens::whereIn('id_transferencia_modelo', $modelos)->get();

                foreach ($objectos as $objecto) {
                    $pneus[] = $objecto->id_pneu;
                }
            }

            if (empty($qtd_baixa_)) {
                throw new \Exception('Salve a transferência antes de receber.');
            }

            if ($qtd_baixa_ < $qtd_solicitada_) {
                throw new \Exception("Atenção: Não foi possível baixar a quantidade total solicitada.<br>Quantidade baixada: $qtd_baixa_<br>Quantidade solicitada: $qtd_solicitada_");
            }

            // Processa os pneus
            foreach ($pneus as $pneuId) {
                $pneu = Pneu::find($pneuId);
                if ($pneu) {
                    if ($pneu->status_pneu == 'TRANSFERENCIA') {
                        $pneu->status_pneu = 'ESTOQUE';
                    } else {
                        // Lança exceção para que o catch realize o rollback e gere a resposta de erro
                        throw new \Exception('Pneu ' . $pneu->id_pneu . ' não possui o status TRANSFERÊNCIA, processo cancelado.');
                    }

                    $pneu->data_alteracao = now();
                    $pneu->id_filial = $idFilial;
                    $pneu->save();

                    // Grava histórico
                    $historico = new Historicopneu();

                    $historico->data_inclusao        = now();
                    $historico->id_pneu              = $pneu->id_pneu;
                    $historico->id_modelo            = $pneu->id_modelo_pneu;
                    $historico->id_vida_pneu         = $pneu->id_controle_vida_pneu;
                    $historico->status_movimentacao  = 'TRANSFERENCIA PNEU';
                    $historico->origem_operacao      = 'ENTRADA';
                    $historico->observacoes_operacao = 'Recebimento de transferência de pneus entre filiais. Requisição: ' . $requisicao;
                    $historico->id_usuario           = Auth::user()->id;

                    $historico->save();
                }
            }

            // Atualiza situação da transferência
            $transferencia = TransferenciaPneus::find($id);
            if ($transferencia) {
                $transferencia->situacao = 'FINALIZADO';
                $transferencia->recebido = true;
                $transferencia->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Baixa de pneus finalizada com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info(['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao finalizar baixa de pneus: ' . $e->getMessage()]);
        }
    }
}
