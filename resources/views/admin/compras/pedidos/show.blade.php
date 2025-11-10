<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Detalhes do Pedido') }} #{{ $pedido->numero }}
            </h2>
            <div class="flex items-center space-x-4">
                @if (
                    $pedido->situacaoPedido->descricao_situacao_pedido != 'CANCELADO' &&
                        $pedido->situacaoPedido->descricao_situacao_pedido != 'REJEITADO')

                    <div class="relative inline-block text-left" x-data="{ actionsOpen: false }">
                        <button @click="actionsOpen = !actionsOpen" type="button"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                            </svg>
                            Ações
                        </button>

                        <div x-show="actionsOpen" @click.away="actionsOpen = false"
                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                            <div class="py-1" role="menu" aria-orientation="vertical">
                                @if ($pedido->podeSerEditado() && auth()->user()->can('update', $pedido))
                                    <a href="{{ route('admin.compras.pedidos.edit', $pedido->id_pedido_compras) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">
                                        Editar Pedido
                                    </a>
                                @endif

                                @if ($pedido->podeSerAprovado() && auth()->user()->can('approve', $pedido))
                                    <a href="#" onclick="document.getElementById('aprovar-form').submit();"
                                        class="block px-4 py-2 text-sm text-green-700 hover:bg-gray-100"
                                        role="menuitem">
                                        Aprovar Pedido
                                    </a>
                                    <form id="aprovar-form"
                                        action="{{ route('admin.compras.pedidos.aprovar', $pedido->id_pedido_compras) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endif

                                @if ($pedido->podeSerEnviado() && auth()->user()->can('send', $pedido))
                                    <a href="#" onclick="document.getElementById('enviar-form').submit();"
                                        class="block px-4 py-2 text-sm text-blue-700 hover:bg-gray-100" role="menuitem">
                                        Enviar ao Fornecedor
                                    </a>
                                    <form id="enviar-form"
                                        action="{{ route('admin.compras.pedidos.enviar', $pedido->id_pedido_compras) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endif

                                @if ($pedido->podeSerFinalizado() && auth()->user()->can('update', $pedido))
                                    <a href="#" onclick="document.getElementById('finalizar-form').submit();"
                                        class="block px-4 py-2 text-sm text-purple-700 hover:bg-gray-100"
                                        role="menuitem">
                                        Finalizar Pedido
                                    </a>
                                    <form id="finalizar-form"
                                        action="{{ route('admin.compras.pedidos.finalizar', $pedido->id_pedido_compras) }}"
                                        method="POST" class="hidden">
                                        @csrf
                                    </form>
                                @endif

                                @if ($pedido->podeSerCancelado() && auth()->user()->can('cancel', $pedido))
                                    <a href="#" onclick="confirmarCancelamento()"
                                        class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100" role="menuitem">
                                        Cancelar Pedido
                                    </a>
                                @endif

                                <a href="{{ route('admin.compras.pedidos.imprimir', $pedido->id_pedido_compras) }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem"
                                    target="_blank">
                                    Imprimir Pedido
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <a href="{{ route('admin.compras.pedidos.index') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Status do Pedido -->
            <div class="mb-6 overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <span
                            class="{{ $pedido->statusClass }} inline-flex rounded-full px-3 py-1 text-sm font-semibold leading-5">
                            {{ $pedido->status }}
                        </span>

                        <span class="ml-4 text-sm text-gray-600">
                            Criado em {{ $pedido->data_inclusao->format('d/m/Y H:i') }}
                        </span>

                        @if ($pedido->data_alteracao)
                            <span class="ml-4 text-sm text-gray-600">
                                Atualizado em {{ $pedido->data_alteracao->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>

                    <div class="text-lg font-bold text-gray-900">
                        Valor Total: R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Informações do Pedido -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                        Informações do Pedido
                    </h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Número do Pedido</h4>
                            <p class="mt-1 text-sm text-gray-900">{{ $pedido->numero }}</p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Solicitação</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                @if ($pedido->solicitacaoCompra)
                                    <a href="{{ route('admin.compras.solicitacoes.show', $pedido->solicitacaoCompra->id_solicitacoes_compras) }}"
                                        class="text-indigo-600 hover:text-indigo-900">
                                        {{ $pedido->solicitacaoCompra->id_solicitacoes_compras }}
                                    </a>
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Tipo</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->isProduto() ? 'Produto' : 'Serviço' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Fornecedor</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->fornecedor->nome_fornecedor ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">CNPJ</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->fornecedor->cnpj_fornecedor ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Contato</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->fornecedor->email ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Filial</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->filial->name ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Filial de Entrega</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->filialEntrega->name ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Filial de Faturamento</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->filialFaturamento->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border-b border-gray-200 p-6">
                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                        Responsáveis
                    </h3>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Solicitante</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->solicitacaoCompra->solicitante->name ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Comprador</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->comprador->name ?? 'N/A' }}
                            </p>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-500">Aprovador</h4>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pedido->aprovador->name ?? 'Pendente' }}
                            </p>
                        </div>
                    </div>
                </div>

                @if ($pedido->observacao_pedido)
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                            Observações
                        </h3>

                        <div class="whitespace-pre-line text-sm text-gray-900">
                            {{ $pedido->observacao_pedido }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Itens do Pedido -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 p-6">
                    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                        Itens do Pedido
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Item</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Descrição</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Qtd. Solicitada</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Unidade</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Valor Unit.</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Valor Total</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Valor Total com Desconto</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                        Observação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($pedido->itens as $item)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            {{ $item->produtos->descricao_produto }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ $item ? number_format($item->quantidade_produtos, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ $item->unidade_produto }}</td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ $item ? number_format($item->valor_produto, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ $item ? number_format($item->valor_total, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">
                                            {{ $item ? number_format($item->valor_total_desconto, 2, ',', '.') : '-' }}
                                        </td>
                                        <td class="max-w-xs px-6 py-4 text-sm text-gray-500">
                                            @if ($item->observacao_edicao)
                                                <div class="group relative">
                                                    <div class="cursor-help truncate"
                                                        title="{{ $item->observacao_edicao }}">
                                                        {{ $item->observacao_edicao }}
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Nenhum item encontrado
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-right text-sm font-medium text-gray-500">
                                        Total:</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">R$
                                        {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Histórico de Aprovações e Rejeições -->
            @if ($pedido->aprovador || $pedido->justificativa)
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                            Histórico de Aprovação
                        </h3>

                        <div class="border-l-2 border-indigo-500 pl-4">
                            @if ($pedido->aprovador)
                                <div class="mb-4">
                                    <div class="flex items-center">
                                        <div class="rounded-full bg-green-100 p-1">
                                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="ml-2 text-sm font-medium text-gray-900">Aprovado por
                                            {{ $pedido->aprovador->name }}</span>
                                    </div>
                                    <div class="ml-7 mt-1 text-sm text-gray-500">
                                        {{ $pedido->data_alteracao
                                            ? $pedido->data_alteracao->format('d/m/Y H:i')
                                            : 'Data não
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        disponível' }}
                                    </div>
                                </div>
                            @endif

                            @if ($pedido->justificativa)
                                <div>
                                    <div class="flex items-center">
                                        <div class="rounded-full bg-gray-100 p-1">
                                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <span class="ml-2 text-sm font-medium text-gray-900">
                                            @if ($pedido->situacaoPedido->descricao_situacao_pedido === 'CANCELADO')
                                                Justificativa de Cancelamento/Rejeição
                                            @else
                                                Justificativa de Aprovação
                                            @endif
                                        </span>
                                    </div>
                                    <div class="ml-7 mt-1 whitespace-pre-line text-sm text-gray-500">
                                        {{ $pedido->justificativa }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Orçamentos Vinculados -->
            @if ($pedido->orcamentos->isNotEmpty())
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                            Orçamentos Vinculados
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Código</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Fornecedor</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Data</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Valor Total</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Selecionado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($pedido->orcamentos as $orcamento)
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $orcamento->id_orcamento }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $orcamento->fornecedor->nome_fornecedor ?? 'N/A' }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $orcamento->data_orcamento->format('d/m/Y') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">R$
                                                {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-center text-sm text-gray-900">
                                                @if ($orcamento->selecionado)
                                                    <span
                                                        class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                                                        Sim
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800">
                                                        Não
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notas Fiscais Vinculadas -->
            {{-- @if ($pedido->notasFiscais->isNotEmpty())
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">
                            Notas Fiscais Vinculadas
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Número</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Série</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Fornecedor</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Data Emissão</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Valor Total</th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @foreach ($pedido->notasFiscais as $nota)
                                        <tr>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-indigo-600 hover:text-indigo-900">
                                                <a href="#">{{ $nota->numero_nota }}</a>
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $nota->serie }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $nota->fornecedor->nome_fornecedor ?? 'N/A' }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $nota->data_emissao->format('d/m/Y') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900">R$
                                                {{ number_format($nota->valor_total, 2, ',', '.') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $nota->status }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif --}}
        </div>
    </div>

    <!-- Modal de Cancelamento -->
    <form id="cancelar-form" action="{{ route('admin.compras.pedidos.cancelar', $pedido->id_pedido_compras) }}"
        method="POST" class="hidden">
        @csrf
        <input type="hidden" name="motivo_cancelamento" id="motivo_cancelamento">
    </form>

    @push('scripts')
        <script>
            function confirmarCancelamento() {
                const motivo = prompt('Informe o motivo do cancelamento:');
                if (motivo) {
                    document.getElementById('motivo_cancelamento').value = motivo;
                    document.getElementById('cancelar-form').submit();
                }
            }
        </script>
    @endpush
</x-app-layout>
