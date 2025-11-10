<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.</x-tables.head-cell>
            <x-tables.head-cell>Veículo</x-tables.head-cell>
            <x-tables.head-cell>Filial do Veículo</x-tables.head-cell>
            <x-tables.head-cell>Nº Bilhete</x-tables.head-cell>
            <x-tables.head-cell>Ano<br>Validade</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Vencimento</x-tables.head-cell>
            <x-tables.head-cell>Valor<br>Previsto</x-tables.head-cell>
            <x-tables.head-cell>Valor<br>Pago</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Pagamento</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Ativo/Inativo</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($segurosObrigatorios as $index => $seguro)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $seguro->id_seguro_obrigatorio_veiculo }}</x-tables.cell>
                <x-tables.cell>{{ $seguro->veiculo->placa ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ $seguro->veiculo->filial->name ?? 'N/A' }}</x-tables.cell>
                <x-tables.cell>{{ $seguro->numero_bilhete }}</x-tables.cell>
                <x-tables.cell>{{ $seguro->ano_validade }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $seguro->data_vencimento ? date('d/m/Y', strtotime($seguro->data_vencimento)) :
                    'N/A' }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $seguro->valor_seguro_previsto }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $seguro->valor_seguro_pago }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $seguro->data_pagamento ? date('d/m/Y', strtotime($seguro->data_pagamento)) :
                    'N/A' }}</x-tables.cell>
                <x-tables.cell>
                    @statusBadge($seguro->situacao ?? 'Não informado')
                </x-tables.cell>
                <x-tables.cell>
                    <span id="status-badge-{{ $seguro->id_seguro_obrigatorio_veiculo }}"
                        class="px-2 py-1 text-xs font-medium inline-flex items-center rounded-full {{ $seguro->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        @if ($seguro->is_ativo)
                        <x-icons.check class="w-3 h-3 mr-1" />
                        Ativo
                        @else
                        <x-icons.check class="w-3 h-3 mr-1" />
                        Inativo
                        @endif
                    </span>
                </x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        {{-- <a
                            href="{{ route('admin.seguroobrigatorio.show', $seguro->id_seguro_obrigatorio_veiculo) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            title="Visualizar">
                            <x-icons.eye class="h-3 w-3" />
                        </a> --}}
                        <a href="{{ route('admin.seguroobrigatorio.edit', $seguro->id_seguro_obrigatorio_veiculo) }}"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            title="Editar">
                            <x-icons.pencil class="h-3 w-3" />
                        </a>

                        @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                        <button type="button" onclick="confirmarExclusao({{ $seguro->id_seguro_obrigatorio_veiculo }})"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.disable class="h-3 w-3" />
                        </button>
                        @endif
                    </div>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="12" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $segurosObrigatorios->links() }}
    </div>
</div>