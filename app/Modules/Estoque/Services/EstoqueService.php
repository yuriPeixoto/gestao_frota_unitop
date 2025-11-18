<?php

namespace App\Modules\Estoque\Services;

use App\Modules\Estoque\Models\EstoqueItem;
use App\Modules\Estoque\Models\EstoqueMovimento;
use App\Models\Produto;
use App\Modules\Compras\Models\SolicitacaoCompra;
use App\Modules\Compras\Models\ItemSolicitacaoCompra;
use App\Modules\Configuracoes\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstoqueService
{
    /**
     * Verifica a disponibilidade de um produto no estoque
     *
     * @param int $idProduto ID do produto
     * @param float $quantidade Quantidade necessária
     * @param int|null $idEstoque ID do estoque específico (opcional)
     * @return array Array com informações de disponibilidade
     */
    public function verificarDisponibilidade(int $idProduto, float $quantidade, ?int $idEstoque = null): array
    {
        try {
            $query = EstoqueItem::where('id_produto', $idProduto)
                ->where('ativo', true);

            if ($idEstoque) {
                $query->where('id_estoque', $idEstoque);
            }

            $estoqueItens = $query->get();

            $quantidadeTotal = $estoqueItens->sum('quantidade_atual');
            $disponivel = $quantidadeTotal >= $quantidade;
            $faltante = $disponivel ? 0 : ($quantidade - $quantidadeTotal);

            // Se houver mais de um estoque com o item, vamos detalhar a distribuição
            $detalhamento = [];
            foreach ($estoqueItens as $item) {
                $detalhamento[] = [
                    'id_estoque' => $item->id_estoque,
                    'descricao_estoque' => $item->estoque->descricao_estoque,
                    'quantidade_disponivel' => $item->quantidade_atual,
                    'localizacao' => $item->localizacao
                ];
            }

            return [
                'disponivel' => $disponivel,
                'quantidade_solicitada' => $quantidade,
                'quantidade_disponivel' => $quantidadeTotal,
                'quantidade_faltante' => $faltante,
                'detalhamento' => $detalhamento
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao verificar disponibilidade em estoque: ' . $e->getMessage());

            return [
                'disponivel' => false,
                'erro' => 'Ocorreu um erro ao verificar a disponibilidade. Tente novamente.'
            ];
        }
    }

    /**
     * Realiza a saída de um produto do estoque para atender uma requisição
     *
     * @param int $idProduto ID do produto
     * @param float $quantidade Quantidade necessária
     * @param string $destino Destino do produto (ex: 'requisicao')
     * @param int $idReferencia ID da requisição ou outro documento de referência
     * @param int|null $idEstoque ID do estoque específico (opcional)
     * @return array Array com informações da operação
     */
    public function realizarSaida(int $idProduto, float $quantidade, string $destino, int $idReferencia, ?int $idEstoque = null): array
    {
        try {
            // Verifica disponibilidade antes de realizar a saída
            $verificacao = $this->verificarDisponibilidade($idProduto, $quantidade, $idEstoque);

            if (!$verificacao['disponivel']) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Quantidade insuficiente em estoque',
                    'detalhes' => $verificacao
                ];
            }

            // Define a estratégia de saída (FIFO, LIFO, etc.)
            // Por padrão, vamos usar o FIFO (primeiro a entrar, primeiro a sair)
            // Podemos priorizar itens com data de validade mais próxima depois

            $quantidadeRestante = $quantidade;
            $saidas = [];

            // Se especificou um estoque, tenta retirar apenas dele
            if ($idEstoque) {
                $item = EstoqueItem::where('id_produto', $idProduto)
                    ->where('id_estoque', $idEstoque)
                    ->where('ativo', true)
                    ->first();

                if ($item && $item->quantidade_atual >= $quantidade) {
                    $resultado = $item->registrarSaida($quantidade, $destino, $idReferencia);

                    if ($resultado) {
                        $saidas[] = [
                            'id_estoque' => $item->id_estoque,
                            'quantidade' => $quantidade
                        ];

                        $quantidadeRestante = 0;
                    }
                }
            }

            // Se ainda não conseguiu atender toda a quantidade, busca em outros estoques
            if ($quantidadeRestante > 0) {
                $itens = EstoqueItem::where('id_produto', $idProduto)
                    ->where('ativo', true)
                    ->orderBy('data_ultima_entrada', 'asc') // FIFO
                    ->get();

                foreach ($itens as $item) {
                    $quantidadeSaida = min($quantidadeRestante, $item->quantidade_atual);

                    if ($quantidadeSaida > 0) {
                        $resultado = $item->registrarSaida($quantidadeSaida, $destino, $idReferencia);

                        if ($resultado) {
                            $saidas[] = [
                                'id_estoque' => $item->id_estoque,
                                'quantidade' => $quantidadeSaida
                            ];

                            $quantidadeRestante -= $quantidadeSaida;

                            if ($quantidadeRestante <= 0) {
                                break;
                            }
                        }
                    }
                }
            }

            // Verifica se saiu tudo
            $sucesso = $quantidadeRestante <= 0;

            return [
                'sucesso' => $sucesso,
                'mensagem' => $sucesso ? 'Saída realizada com sucesso' : 'Saída realizada parcialmente',
                'quantidade_solicitada' => $quantidade,
                'quantidade_atendida' => $quantidade - $quantidadeRestante,
                'saidas' => $saidas
            ];
        } catch (\Exception $e) {
            Log::error('Erro ao realizar saída do estoque: ' . $e->getMessage());

            return [
                'sucesso' => false,
                'mensagem' => 'Ocorreu um erro ao realizar a saída. Tente novamente.',
                'erro' => $e->getMessage()
            ];
        }
    }

    /**
     * Verifica disponibilidade no estoque para uma solicitação de compra
     * e gera automaticamente uma solicitação de compra para itens indisponíveis
     *
     * @param array $itens Array de itens requisitados [id_produto => quantidade]
     * @param int $idSolicitante ID do usuário solicitante
     * @param int $idDepartamento ID do departamento solicitante
     * @param int $idFilial ID da filial solicitante
     * @param string $observacao Observações adicionais
     * @return array Array com o resultado da verificação e ações tomadas
     */
    public function verificarDisponibilidadeEGerarSolicitacao(
        array $itens,
        int $idSolicitante,
        int $idDepartamento,
        int $idFilial,
        string $observacao = ''
    ): array {
        try {
            $resultado = [
                'itens_disponiveis' => [],
                'itens_indisponiveis' => [],
                'solicitacao_gerada' => false,
                'id_solicitacao' => null
            ];

            // Verifica cada item
            foreach ($itens as $idProduto => $quantidade) {
                $verificacao = $this->verificarDisponibilidade($idProduto, $quantidade);

                if ($verificacao['disponivel']) {
                    $resultado['itens_disponiveis'][] = [
                        'id_produto' => $idProduto,
                        'quantidade' => $quantidade,
                        'detalhes' => $verificacao
                    ];
                } else {
                    $resultado['itens_indisponiveis'][] = [
                        'id_produto' => $idProduto,
                        'quantidade' => $quantidade,
                        'quantidade_faltante' => $verificacao['quantidade_faltante'],
                        'detalhes' => $verificacao
                    ];
                }
            }

            // Se houver itens indisponíveis, gera uma solicitação de compra
            if (!empty($resultado['itens_indisponiveis'])) {
                DB::beginTransaction();

                try {
                    // Cria a solicitação de compra
                    $solicitacao = SolicitacaoCompra::create([
                        'id_solicitante' => $idSolicitante,
                        'id_departamento' => $idDepartamento,
                        'id_filial' => $idFilial,
                        'data_solicitacao' => now(),
                        'status' => 'nova',
                        'prioridade' => 'normal',
                        'observacao' => $observacao . ' [Gerada automaticamente por falta em estoque]',
                        'data_inclusao' => now()
                    ]);

                    // Adiciona os itens indisponíveis à solicitação
                    foreach ($resultado['itens_indisponiveis'] as $item) {
                        $produto = Produto::find($item['id_produto']);

                        ItemSolicitacaoCompra::create([
                            'id_solicitacao' => $solicitacao->id_solicitacao,
                            'tipo' => 'produto',
                            'id_produto' => $item['id_produto'],
                            'descricao' => $produto ? $produto->descricao : "Produto ID: {$item['id_produto']}",
                            'quantidade' => $item['quantidade_faltante'] > 0 ? $item['quantidade_faltante'] : $item['quantidade'],
                            'unidade_medida' => $produto ? $produto->unidade_medida : '',
                            'status' => 'pendente',
                            'justificativa' => 'Item em falta no estoque',
                            'data_inclusao' => now()
                        ]);
                    }

                    // Notificar o gestor de estoque e o gestor financeiro (implementar depois)
                    // $this->notificarGestores($solicitacao);

                    DB::commit();

                    $resultado['solicitacao_gerada'] = true;
                    $resultado['id_solicitacao'] = $solicitacao->id_solicitacao;
                    $resultado['mensagem'] = 'Solicitação de compra gerada automaticamente para os itens indisponíveis';
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erro ao gerar solicitação de compra: ' . $e->getMessage());

                    $resultado['erro'] = 'Erro ao gerar solicitação de compra: ' . $e->getMessage();
                }
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Erro ao verificar disponibilidade e gerar solicitação: ' . $e->getMessage());

            return [
                'erro' => 'Ocorreu um erro ao verificar disponibilidade e gerar solicitação. Tente novamente.',
                'detalhes' => $e->getMessage()
            ];
        }
    }

    /**
     * Registra a entrada de produtos no estoque vindo de uma compra
     *
     * @param int $idPedidoCompra ID do pedido de compra
     * @param int $idEstoque ID do estoque onde será armazenado o produto
     * @param array $itens Array de itens recebidos [id_item_pedido => quantidade_recebida]
     * @return array Array com o resultado da operação
     */
    public function registrarEntradaCompra(int $idPedidoCompra, int $idEstoque, array $itens): array
    {
        try {
            $resultado = [
                'sucesso' => true,
                'entradas' => []
            ];

            foreach ($itens as $idItemPedido => $quantidadeRecebida) {
                // Buscar informações do item do pedido
                $itemPedido = DB::connection('pgsql')->table('itens_pedido_compra')
                    ->where('id_item_pedido', $idItemPedido)
                    ->first();

                if (!$itemPedido) {
                    $resultado['entradas'][] = [
                        'id_item_pedido' => $idItemPedido,
                        'sucesso' => false,
                        'mensagem' => 'Item do pedido não encontrado'
                    ];
                    continue;
                }

                // Buscar ou criar o item de estoque
                $estoqueItem = EstoqueItem::firstOrNew([
                    'id_estoque' => $idEstoque,
                    'id_produto' => $itemPedido->id_produto
                ]);

                if (!$estoqueItem->exists) {
                    $estoqueItem->fill([
                        'quantidade_atual' => 0,
                        'quantidade_minima' => 0,
                        'data_inclusao' => now(),
                        'ativo' => true
                    ]);
                    $estoqueItem->save();
                }

                // Registrar a entrada
                $sucesso = $estoqueItem->registrarEntrada(
                    $quantidadeRecebida,
                    'compra',
                    $idPedidoCompra
                );

                $resultado['entradas'][] = [
                    'id_item_pedido' => $idItemPedido,
                    'id_produto' => $itemPedido->id_produto,
                    'quantidade_recebida' => $quantidadeRecebida,
                    'sucesso' => $sucesso,
                    'mensagem' => $sucesso ? 'Entrada registrada com sucesso' : 'Falha ao registrar entrada'
                ];

                if (!$sucesso) {
                    $resultado['sucesso'] = false;
                }
            }

            return $resultado;
        } catch (\Exception $e) {
            Log::error('Erro ao registrar entrada no estoque: ' . $e->getMessage());

            return [
                'sucesso' => false,
                'mensagem' => 'Ocorreu um erro ao registrar entrada no estoque. Tente novamente.',
                'erro' => $e->getMessage()
            ];
        }
    }
}
