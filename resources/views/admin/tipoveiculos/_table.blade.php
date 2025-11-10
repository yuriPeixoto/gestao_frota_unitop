<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Tipo Veículo</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoveiculos as $index => $tipoveiculo)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <x-forms.button type="secondary" variant="outlined" size="sm"
                                href="{{ route('admin.tipoveiculos.edit', $tipoveiculo->id) }}">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </x-forms.button>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                <x-forms.button type="danger" variant="outlined" size="sm"
                                    onclick="confirmarExclusao({{ $tipoveiculo->id }})">
                                    <x-icons.trash class="w-4 h-4 text-red-600" />
                                </x-forms.button>
                            @endif
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $tipoveiculo->id }}</x-tables.cell>
                    <x-tables.cell>{{ $tipoveiculo->descricao }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($tipoveiculo->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ format_date($tipoveiculo->data_alteracao) }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoveiculos->links() }}
    </div>
</div>
