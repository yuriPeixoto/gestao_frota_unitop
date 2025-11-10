<!-- Aba dos Itens Transferidos (ProdutosSolicitacoes) -->
<div class="relative overflow-x-auto">
    @if (isset($itensTransferidos) && $itensTransferidos->count() > 0)
        <div class="mb-4 text-sm text-gray-600">
            Mostrando {{ $itensTransferidos->count() }} item(ns) transferido(s)
        </div>
    @endif

    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Produto</x-tables.head-cell>
            <x-tables.head-cell>Data da Transferência</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Requisição</x-tables.head-cell>
            <x-tables.head-cell>Usuário da Transferência</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Filial Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Quantidade de <br> Transferência</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($itensTransferidos ?? [] as $item)
                <x-tables.row>
                    <x-tables.cell>{{ $item->id_protudos }}</x-tables.cell>
                    <x-tables.cell>{{ $item->data_inclusao->format('d/m/Y') }}</x-tables.cell>
                    <x-tables.cell>{{ $item->produto->descricao_produto ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->relacaoSolicitacoesPecas->id_solicitacao_pecas ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->user->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->relacaoSolicitacoesPecas->filial->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->filialTransferencia->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ number_format($item->quantidade_transferencia ?? 0, 2, ',', '.') }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.row>
                    <x-tables.cell colspan="7">
                        <div class="text-center text-gray-500">
                            Nenhum item transferido encontrado.
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @endforelse
        </x-tables.body>
    </x-tables.table>
</div>

@if (isset($itensTransferidos))
    <div class="mt-4">
        {{ $itensTransferidos->links() }}
    </div>
@endif
