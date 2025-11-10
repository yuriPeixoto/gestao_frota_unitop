<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Departamento</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Departamento</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Sigla</x-tables.head-cell>
            <x-tables.head-cell>Ativo</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoDepartamento as $index => $departamento)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <div>
                            <x-forms.button type="secondary" variant="outlined" size="sm"
                                href="{{ route('admin.departamentos.edit', $departamento->id_departamento) }}">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </x-forms.button>
                        </div>
                        <div>
                            <x-forms.button type="danger" variant="outlined" size="sm"
                                onclick="confirmarExclusao({{ $departamento->id_departamento }})">
                                <x-icons.trash class="w-4 h-4 text-red-600" />
                            </x-forms.button>
                        </div>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $departamento->id_departamento }}</x-tables.cell>
                <x-tables.cell>{{ format_date($departamento->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($departamento->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $departamento->descricao_departamento ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $departamento->filial->name ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $departamento->sigla ?? '' }}</x-tables.cell>
                <x-tables.cell>{{ $departamento->ativo ? 'Sim' : 'Não' }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoDepartamento->links() }}
    </div>
    @push('scripts')
    @include('admin.departamentos._scripts')
    @endpush
</div>