<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. Tanque</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Tanque</x-tables.head-cell>
            <x-tables.head-cell>Capacidade</x-tables.head-cell>
            <x-tables.head-cell>Posto</x-tables.head-cell>
            <x-tables.head-cell>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>

            {{-- Coluna Ações - Só aparece se usuário tem pelo menos uma permissão de ação --}}
            @if(auth()->user()->hasAnyPermission(['editar_tanque', 'excluir_tanque']))
            <x-tables.head-cell>Ações</x-tables.head-cell>
            @endif
        </x-tables.header>

        <x-tables.body>
            @forelse ($tanque as $index => $tanques)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $tanques->id_tanque }}</x-tables.cell>
                <x-tables.cell>
                    @if($tanques->data_inclusao)
                    <span title="{{ \Carbon\Carbon::parse($tanques->data_inclusao)->format('d/m/Y H:i:s') }}">
                        {{ \Carbon\Carbon::parse($tanques->data_inclusao)->format('d/m/Y') }}
                    </span>
                    @else
                    -
                    @endif
                </x-tables.cell>
                <x-tables.cell>
                    @if($tanques->data_alteracao)
                    <span title="{{ \Carbon\Carbon::parse($tanques->data_alteracao)->format('d/m/Y H:i:s') }}">
                        {{ \Carbon\Carbon::parse($tanques->data_alteracao)->format('d/m/Y') }}
                    </span>
                    @else
                    -
                    @endif
                </x-tables.cell>
                <x-tables.cell>{{ $tanques->tanque }}</x-tables.cell>
                <x-tables.cell>{{ $tanques->capacidade }}</x-tables.cell>
                <x-tables.cell>{{ $tanques->filial->name ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $tanques->tipoCombustivel->descricao ?? '-' }}</x-tables.cell>
                <x-tables.cell>
                    <span id="status-badge-{{ $tanques->id_tanque }}"
                        class="px-2 py-1 text-xs font-medium rounded-full {{ $tanques->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $tanques->is_ativo ? 'Ativo' : 'Inativo' }}
                    </span>
                </x-tables.cell>

                @if(auth()->user()->hasAnyPermission(['editar_tanque', 'excluir_tanque']))
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @can('editar_tanque')
                        <a href="{{ route('admin.tanques.edit', $tanques->id_tanque) }}"
                            title="Editar {{ $tanques->tanque }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>
                        @endcan

                        <button type="button" id="toggle-button-{{ $tanques->id_tanque }}"
                            onclick="toggleStatusTanque({{ $tanques->id_tanque }}, '{{ $tanques->tanque }}')"
                            title="{{ $tanques->is_ativo ? 'Desativar' : 'Ativar' }} {{ $tanques->tanque }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white {{ $tanques->is_ativo ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $tanques->is_ativo ? 'focus:ring-red-500' : 'focus:ring-green-500' }}">
                            @if($tanques->is_ativo)
                                <x-icons.ban class="h-3 w-3" />
                            @else
                                <x-icons.check class="h-3 w-3" />
                            @endif
                        </button>
                    </div>
                </x-tables.cell>
                @endif
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tanque->links() }}
    </div>
</div>