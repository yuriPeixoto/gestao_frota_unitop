<div class="results-table" id="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Códido</x-tables.head-cell>
            <x-tables.head-cell>Data Incluasão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Usuário Solicitante</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Usuário Estoque</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($requisicaoPneus as $index => $pneu)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <!-- Na sua view principal -->
                        <x-forms.button @click="carregarDados({{ $pneu->id_requisicao_pneu }})" class="m-2">
                            <x-icons.eye class="w-4 h-4 mr-2" />
                            Visualizar
                        </x-forms.button>
                    </x-tables.cell>
                    <x-tables.cell>{{ $pneu->id_requisicao_pneu }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($pneu->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($pneu->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->usuarioSolicitante->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->situacao }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->usuarioVendas->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->filial->name ?? '-' }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $requisicaoPneus->links() }}
    </div>
</div>
