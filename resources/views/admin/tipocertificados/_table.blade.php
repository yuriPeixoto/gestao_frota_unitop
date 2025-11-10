<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Órgão Certificador</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipocertificados as $index => $certificado)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $certificado->id_tipo_certificado }}</x-tables.cell>
                    <x-tables.cell>{{ $certificado->descricao_certificado }}</x-tables.cell>
                    <x-tables.cell>{{ $certificado->orgao_certificado }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $certificado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $certificado->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.tipocertificados.edit', $certificado->id_tipo_certificado) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                <button type="button" onclick="confirmarExclusao({{ $certificado->id_tipo_certificado }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="6" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $tipocertificados->links() }}
    </div>
</div>