<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Tipo Reforma</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Tipo Reforma</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoReforma as $index => $ReformaPneu)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <div>
                                <x-forms.button type="secondary" variant="outlined" size="sm"
                                    href="{{ route('admin.tiporeformapneus.edit', $ReformaPneu->id_tipo_reforma) }}"><x-icons.edit
                                        class="w-4 h-4 text-blue-600" />
                                </x-forms.button>
                            </div>
                            <div>
                                <x-forms.button type="danger" variant="outlined" size="sm"
                                    onclick="confirmarExclusao({{ $ReformaPneu->id_tipo_reforma }})">
                                    <x-icons.trash class="w-4 h-4 text-red-600" />
                                </x-forms.button>
                            </div>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $ReformaPneu->id_tipo_reforma }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($ReformaPneu->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($ReformaPneu->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $ReformaPneu->descricao_tipo_reforma }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoReforma->links() }}
    </div>
    @push('scripts')
        @include('admin.tiporeformapneus._scripts')
    @endpush
</div>
