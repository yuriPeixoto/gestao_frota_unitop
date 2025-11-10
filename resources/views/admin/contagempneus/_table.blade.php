<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Contagem Pneu</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Modelo Pneu</x-tables.head-cell>
            <x-tables.head-cell>Valor Contado pelo Usuário</x-tables.head-cell>
            <x-tables.head-cell>Contagem de Acordo com o Estoque</x-tables.head-cell>
        </x-tables.header>
        <x-tables.body>
            @forelse ($contagemPneus as $index => $contagem)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            @if(auth()->user()->is_superuser)
                            <div>
                                <a href="{{ route('admin.contagempneus.edit', $contagem->id_contagem_pneu) }}"
                                    alt="Editar">
                                    <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                </a>
                            </div>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $contagem->id_contagem_pneu }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($contagem->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($contagem->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $contagem->modelopneu->descricao_modelo ?? 'Não Informado' }}</x-tables.cell>
                    <x-tables.cell>{{ $contagem->contagem_usuario }}</x-tables.cell>
                    <x-tables.cell>{{ $contagem->is_igual ? 'Sim' : 'Nao' }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="7" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $contagemPneus->links() }}
    </div>
</div>
