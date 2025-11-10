<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód. Franquia RV</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
            <x-tables.head-cell>Tipo Equipamento</x-tables.head-cell>
            <x-tables.head-cell>Peso</x-tables.head-cell>
            <x-tables.head-cell>Sub Categoria</x-tables.head-cell>
            <x-tables.head-cell>Categoria Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Operação</x-tables.head-cell>
            <x-tables.head-cell>Step</x-tables.head-cell>
            <x-tables.head-cell>Média</x-tables.head-cell>
            <x-tables.head-cell>Valor</x-tables.head-cell>
            <x-tables.head-cell>Usuário</x-tables.head-cell>
            <x-tables.head-cell>Clonado</x-tables.head-cell>

        </x-tables.header>

        <x-tables.body>
            @forelse ($listagem as $franquia)
            <x-tables.row>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <x-tooltip content="Desativar">
                            <form
                                action="{{ route('admin.franquiapremiorv.desativar', $franquia->id_franquia_premio_rv) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja desativar este registro?');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    <x-icons.ban class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Editar">
                            <a href="{{ route('admin.franquiapremiorv.edit', $franquia->id_franquia_premio_rv) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        <x-tooltip content="Excluir">
                            <form
                                action="{{ route('admin.franquiapremiorv.delete', $franquia->id_franquia_premio_rv) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja excluir este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            </form>
                        </x-tooltip>
                        <x-tooltip content="Clonar">
                            <form
                                action="{{ route('admin.franquiapremiorv.clonar', $franquia->id_franquia_premio_rv) }}"
                                method="POST" class="inline-block"
                                onsubmit="return confirm('Tem certeza que deseja clonar este registro?');">
                                @csrf
                                <button type="submit">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                        class="bi bi-copy" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                            d="M4 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1v-1h1v1a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h1v1z" />
                                    </svg>
                                </button>
                            </form>
                        </x-tooltip>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $franquia->id_franquia_premio_rv }}</x-tables.cell>
                <x-tables.cell>{{ format_date($franquia->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>
                    <span class="{{ $franquia->ativo ? 'text-green-600' : 'text-red-600'}}">
                        {{ $franquia->ativo ? 'Sim' : 'Não'}}
                    </span>
                </x-tables.cell>
                <x-tables.cell>{{ $franquia->equipamento->descricao_tipo ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->pesobruto}}</x-tables.cell>
                <x-tables.cell>{{ $franquia->subcategoria->descricao_subcategoria ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->categoria->descricao_categoria ?? ''}}</x-tables.cell>
                <x-tables.cell>{{ $franquia->operacao->descricao_tipo_operacao ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->step}}</x-tables.cell>
                <x-tables.cell>{{ $franquia->media}}</x-tables.cell>
                <x-tables.cell>R$ {{ number_format($franquia->valor, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ $franquia->users->name ?? ''}}</x-tables.cell>
                <x-tables.cell>
                    <span class="{{ $franquia->clonado ? 'text-green-600' : 'text-red-600'}}">
                        {{ $franquia->clonado ? 'Sim' : 'Não'}}
                    </span>
                </x-tables.cell>


            </x-tables.row>
            @empty
            <x-tables.empty cols="14" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>
    {{ $listagem->links() }}
</div>
</div>