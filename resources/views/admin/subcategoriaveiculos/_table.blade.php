<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. SubCategoria</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição SubCategoria</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($subCategoria as $index => $categoria)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <div>
                            <a href="javascript:void(0)" onclick="openDrawerEdit({{ $categoria->id_subcategoria }})"
                                title="Editar">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </a>

                        </div>
                        <div>
                            <form
                                action="{{ route('admin.subcategoriaveiculos.destroy', $categoria->id_subcategoria) }}"
                                method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('Tem certeza que deseja excluir este Tipo SubCategoria ?')"
                                    title="Excluir" class="p-1 hover:bg-red-100 rounded">
                                    <x-icons.trash class="w-4 h-4 text-red-600" />
                                </button>
                            </form>
                        </div>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $categoria->id_subcategoria }}</x-tables.cell>
                <x-tables.cell>{{ format_date($categoria->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($categoria->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $categoria->descricao_subcategoria }}</x-tables.cell>

            </x-tables.row>
            @empty
            <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $subCategoria->links() }}
    </div>
    @push('scripts')
    @include('admin.subcategoriaveiculos._scripts')
    @endpush
</div>
<div id="drawerEdit"
    class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6 overflow-y-auto h-full flex flex-col">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Editar SubCategoria</h2>

        <div id="drawerEdit-content">
            <!-- Aqui o formulário será carregado -->
        </div>
    </div>
</div>