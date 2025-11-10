<!-- Aba dos Itens Recebidos (TransferenciaEstoqueItens) -->
<div class="relative overflow-x-auto">
    @if (isset($itensRecebidos) && $itensRecebidos->count() > 0)
        <div class="mb-4 text-sm text-gray-600">
            Mostrando {{ $itensRecebidos->count() }} item(ns) recebido(s)
        </div>
    @endif

    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>ID Item</x-tables.head-cell>
            <x-tables.head-cell>ID Transferência</x-tables.head-cell>
            <x-tables.head-cell>Data da Transferência</x-tables.head-cell>
            <x-tables.head-cell>Produto</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Qtd. Solicitada</x-tables.head-cell>
            <x-tables.head-cell>Qtd. Recebida</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($itensRecebidos ?? [] as $item)
                <x-tables.row class="@if ($item->tem_inconsistencia ?? false) bg-red-50 @endif">
                    <x-tables.cell>{{ $item->id_transferencia_itens }}</x-tables.cell>
                    <x-tables.cell>
                        <span
                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                            {{ $item->id_transferencia }}
                        </span>
                    </x-tables.cell>
                    <x-tables.cell>{{ $item->data_inclusao->format('d/m/Y') }}</x-tables.cell>
                    <x-tables.cell>{{ $item->id_produto }}</x-tables.cell>
                    <x-tables.cell>{{ $item->produto->descricao_produto ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->transferencia->usuario->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->transferencia->filial->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ number_format($item->quantidade ?? 0, 2, ',', '.') }}</x-tables.cell>
                    <x-tables.cell>{{ number_format($item->quantidade_baixa ?? 0, 2, ',', '.') }}</x-tables.cell>
                    <x-tables.cell>
                        @if ($item->tem_inconsistencia ?? false)
                            <span
                                class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                Inconsistência
                            </span>
                        @else
                            <span
                                class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                Normal
                            </span>
                        @endif
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.row>
                    <x-tables.cell colspan="10">
                        <div class="text-center text-gray-500">
                            Nenhum item recebido encontrado.
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @endforelse
        </x-tables.body>
    </x-tables.table>
</div>

@if (isset($itensRecebidos))
    <div class="mt-4">
        {{ $itensRecebidos->links() }}
    </div>
@endif

@if (isset($itensRecebidos) && $itensRecebidos->count() > 0)
    <!-- Legenda -->
    <div class="mt-4 rounded-lg bg-gray-50 p-4">
        <h4 class="mb-2 text-sm font-medium text-gray-900">Legenda:</h4>
        <div class="flex flex-wrap gap-4 text-sm">
            <div class="flex items-center">
                <div class="mr-2 h-4 w-4 rounded border border-red-200 bg-red-50"></div>
                <span class="text-gray-700">Linha destacada: Item com inconsistência (quantidade recebida menor que a
                    solicitada)</span>
            </div>
            <div class="flex items-center">
                <span
                    class="mr-2 inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Inconsistência
                </span>
                <span class="text-gray-700">Quantidade recebida inferior à solicitada</span>
            </div>
            <div class="flex items-center">
                <span
                    class="mr-2 inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    Normal
                </span>
                <span class="text-gray-700">Recebimento conforme solicitado</span>
            </div>
        </div>
    </div>
@endif
