<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Id.</x-tables.head-cell>
            <x-tables.head-cell>Nome</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Permissões</x-tables.head-cell>
            <x-tables.head-cell>Data de Criação</x-tables.head-cell>
            <x-tables.head-cell>Data de Alteração</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($roles as $index => $result)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $result->id }}</x-tables.cell>
                    <x-tables.cell>{{ $result->name }}</x-tables.cell>
                    <x-tables.cell>{{ $result->description }}</x-tables.cell>
                    <x-tables.cell>{{ $result->permissions_list }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($result->created_at) }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($result->updated_at) }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $result->is_ativo == 1 ? 'Ativo' : 'Inativo' }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.cargos.show', $result->id) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-icons.eye class="h-3 w-3" />
                            </a>
                            <a href="{{ route('admin.cargos.edit', $result->id) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $roles->links() }}
    </div>
</div>
