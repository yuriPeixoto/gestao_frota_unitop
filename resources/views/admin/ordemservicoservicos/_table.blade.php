<div class="results-table">
    <form id="lancar-nf-form" action="{{ route('admin.ordemservicoservicos.lancar-nf') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <button type="submit" id="btn-lancar-nf" disabled
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Gravar NF
            </button>
        </div>
        
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell class="w-10">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </x-tables.head-cell>
                <x-tables.head-cell>O.S.</x-tables.head-cell>
                <x-tables.head-cell>Fornecedor</x-tables.head-cell>
                <x-tables.head-cell>Serviço</x-tables.head-cell>
                <x-tables.head-cell>Valor Serviço</x-tables.head-cell>
                <x-tables.head-cell>Valor c/ Desconto</x-tables.head-cell>
                <x-tables.head-cell>Valor Total</x-tables.head-cell>
                <x-tables.head-cell>Status</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body>
                @forelse ($servicos as $index => $servico)
                    <x-tables.row :index="$index">
                        <x-tables.cell>
                            <input type="checkbox" name="servicos[]" value="{{ $servico->id_ordem_servico_serv }}" 
                                class="servico-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                {{ $servico->numero_nota_fiscal_servicos ? 'disabled' : '' }}>
                        </x-tables.cell>
                        <x-tables.cell>{{ $servico->id_ordem_servico }}</x-tables.cell>
                        <x-tables.cell>{{ $servico->fornecedor->nome_fornecedor ?? 'N/A' }}</x-tables.cell>
                        <x-tables.cell>{{ $servico->servicos->descricao_servico ?? 'N/A' }}</x-tables.cell>
                        <x-tables.cell>{{ 'R$ ' . number_format($servico->valor_servico ?? 0, 2, ',', '.') }}</x-tables.cell>
                        <x-tables.cell>{{ 'R$ ' . number_format($servico->valor_descontoservico ?? 0, 2, ',', '.') }}</x-tables.cell>
                        <x-tables.cell>{{ 'R$ ' . number_format($servico->valor_total_com_desconto ?? 0, 2, ',', '.') }}</x-tables.cell>
                        <x-tables.cell>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $servico->status_servico === 'FATURADO' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $servico->status_servico ?? 'PENDENTE' }}
                            </span>
                        </x-tables.cell>
                    </x-tables.row>
                @empty
                    <x-tables.empty cols="8" message="Nenhum registro encontrado" />
                @endforelse
            </x-tables.body>
        </x-tables.table>
    </form>

    <div class="mt-4">
        {{ $servicos->links() }}
    </div>
</div>