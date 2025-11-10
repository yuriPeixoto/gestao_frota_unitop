<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Tipo Ocorrência</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Tipo Ocorrência</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipoOcorrencia as $index => $Ocorrencia)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <div>
                                <x-forms.button type="primary" variant="outlined" size="sm"
                                    href="{{ route('admin.tipoocorrencias.edit', $Ocorrencia->id_tipo_ocorrencia) }}">
                                    <x-icons.edit class="w-4 h-4 text-blue-600" />
                                </x-forms.button>
                            </div>
                            <div>
                                <x-forms.button type="danger" variant="outlined" size="sm"
                                    onclick="confirmarExclusao({{ $Ocorrencia->id_tipo_ocorrencia }})">
                                    <x-icons.trash class="w-4 h-4 text-red-600" />
                                </x-forms.button>
                            </div>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $Ocorrencia->id_tipo_ocorrencia }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($Ocorrencia->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($Ocorrencia->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $Ocorrencia->descricao_ocorrencia }}</x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipoOcorrencia->links() }}
    </div>
    @push('scripts')
        @include('admin.tipoocorrencias._scripts')
    @endpush
</div>
