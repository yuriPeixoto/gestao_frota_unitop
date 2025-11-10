<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Borracha</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Tipo Borracha</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoBorrachaPneus as $index => $tipoBorrachaPneu)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <div>
                            <a href="javascript:void(0)"
                                onclick="openDrawerEdit({{ $tipoBorrachaPneu->id_tipo_borracha }})" title="Editar">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </a>
                        </div>
                        <div>
                            <button type="button"
                                onclick="confirmarExclusao({{ $tipoBorrachaPneu->id_tipo_borracha }})">
                                <x-icons.trash class="w-4 h-4 mr-2 text-red-600" />
                            </button>
                        </div>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $tipoBorrachaPneu->id_tipo_borracha }}</x-tables.cell>
                <x-tables.cell>{{ format_date($tipoBorrachaPneu->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($tipoBorrachaPneu->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $tipoBorrachaPneu->descricao_tipo_borracha }}</x-tables.cell>

            </x-tables.row>
            @empty
            <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoBorrachaPneus->links() }}
    </div>
    @push('scripts')
    @include('admin.tipoborrachapneus._scripts')
    @endpush
</div>

<div id="drawerEdit"
    class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6 overflow-y-auto h-full flex flex-col">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Editar Tipo de Acerto</h2>

        <div id="drawerEdit-content">
            <!-- Aqui o formulário será carregado -->
        </div>
    </div>
</div>