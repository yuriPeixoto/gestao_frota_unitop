<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Filial Veiculo</x-tables.head-cell>
            <x-tables.head-cell>RENAVAM</x-tables.head-cell>
            <x-tables.head-cell>Ano<br>Validade</x-tables.head-cell>
            <x-tables.head-cell>Status de<br>Pagamento</x-tables.head-cell>
            <x-tables.head-cell>Qtde.<br>Parcelas</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Pagamento</x-tables.head-cell>
            <x-tables.head-cell>Valor<br>Previsto</x-tables.head-cell>
            <x-tables.head-cell>Valor<br>Pago</x-tables.head-cell>
            <x-tables.head-cell>Ativo/Inativo</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($ipvaveiculos as $index => $ipvaveiculo)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $ipvaveiculo->id_ipva_veiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $ipvaveiculo->veiculo->placa ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $ipvaveiculo->veiculo->filial->name ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $ipvaveiculo->veiculo->renavam ?? 'N/A' }}</x-tables.cell>
                    <x-tables.cell>{{ $ipvaveiculo->ano_validade }}</x-tables.cell>
                    <x-tables.cell class="text-center">
                        @statusBadge($ipvaveiculo->status_ipva)
                    </x-tables.cell>
                    <x-tables.cell>{{ $ipvaveiculo->quantidade_parcelas }}</x-tables.cell>
                    <x-tables.cell
                        nowrap>{{ $ipvaveiculo->data_pagamento_ipva ? date('d/m/Y', strtotime($ipvaveiculo->data_pagamento_ipva)) : 'N/A' }}</x-tables.cell>
                    <x-tables.cell nowrap>R$
                        {{ number_format($ipvaveiculo->valor_previsto_ipva ?? 0, 2, ',', '.') }}
                    </x-tables.cell>
                    <x-tables.cell nowrap>R$
                        {{ number_format($ipvaveiculo->valor_pago_ipva ?? 0, 2, ',', '.') }}
                    </x-tables.cell>
                    <x-tables.cell>
                        <span id="status-badge-{{ $ipvaveiculo->id_ipva_veiculo }}"
                            class="px-2 py-1 text-xs font-medium inline-flex items-center rounded-full {{ $ipvaveiculo->is_ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            @if ($ipvaveiculo->is_ativo)
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Ativo
                            @else
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Inativo
                            @endif
                        </span>
                    </x-tables.cell>
                    <x-tables.cell nowrap>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.ipvaveiculos.edit', $ipvaveiculo->id_ipva_veiculo) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                <button type="button" onclick="excluirIpva({{ $ipvaveiculo->id_ipva_veiculo }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.disable class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>
                </x-tables.row>
            @empty
                <x-tables.empty cols="10" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $ipvaveiculos->links() }}
    </div>
</div>
