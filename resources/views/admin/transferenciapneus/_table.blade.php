<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Código Transferência</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Aprovado</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Usuário Baixa</x-tables.head-cell>
            <x-tables.head-cell>Observação Baixa</x-tables.head-cell>
            <x-tables.head-cell>Observação Saída</x-tables.head-cell>
            <x-tables.head-cell>Recebido</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($transfPneus as $index => $transferencia)
                <x-tables.row :index="$index">
                    <x-tables.cell nowrap>
                        <div class="flex justify-center space-x-2">
                            @if ($transferencia->recebido == false)
                                <a href="{{ route('admin.transferenciapneus.edit', $transferencia->id_transferencia_pneus) }}"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-icons.check class="h-3 w-3" />
                                    <span class="text-[10px]">Confirmar Recibimento</span>
                                </a>
                            @endif
                            @if (auth()->user()->is_superuser || in_array(auth()->user()->id, [3, 4, 25]))
                                <button type="button"
                                    onclick="confirmarExclusao({{ $transferencia->id_transferencia_pneus }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $transferencia->id_transferencia_pneus }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($transferencia->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($transferencia->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->filial->name }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->usuario->name ?? 'Não Cadastrado' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->aprovado ? 'Sim' : 'Não' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->situacao }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->usuarioBaixa->name ?? 'Não Cadadstrado' }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->observacao_baixa }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->observacao_saida }}</x-tables.cell>
                    <x-tables.cell>{{ $transferencia->recebido ? 'Sim' : 'Não' }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="12" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $transfPneus->links() }}
    </div>
</div>
