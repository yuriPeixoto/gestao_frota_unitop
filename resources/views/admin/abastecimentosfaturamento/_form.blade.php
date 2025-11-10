<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form id="abastecimentoFaturamentoForm" method="POST" action="{{ $action }}" class="space-y-4">
            @csrf
            @if ($method === 'PUT')
            @method('PUT')
            @endif

            @foreach ($abastecimentosSelecionados as $abastecimento)
            <input type="hidden" name="ids[]" value="{{ $abastecimento->cod_transacao }}">
            @endforeach

            <div class="flex justify-between items-center mb-6">
                <button type="button" onclick="confirmarFaturamento()"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 inline-flex items-center">
                    <x-icons.check class="w-4 h-4 mr-2" />
                    Confirmar Faturamento
                </button>
                <a href="{{ route('admin.abastecimentosfaturamento.index') }}"
                    onclick="localStorage.removeItem('selectedRows')"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Voltar
                </a>
            </div>

            @php
            $abastecimentosAgrupados = $abastecimentosSelecionados->groupBy('posto_abastecimento');
            @endphp

            <x-tables.table>
                <x-tables.header>
                    <x-tables.head-cell>Cód. Transação</x-tables.head-cell>
                    <x-tables.head-cell>Placa</x-tables.head-cell>
                    <x-tables.head-cell>Tipo Combustível</x-tables.head-cell>
                    <x-tables.head-cell>Número da NF</x-tables.head-cell>
                    <x-tables.head-cell>Chave da NF</x-tables.head-cell>
                    <x-tables.head-cell>Data Vencimento</x-tables.head-cell>
                    <x-tables.head-cell>Posto do Abastecimento</x-tables.head-cell>
                    <x-tables.head-cell>CNPJ</x-tables.head-cell>
                    <x-tables.head-cell>Valor da NF</x-tables.head-cell>
                </x-tables.header>

                <x-tables.body>
                    @forelse ($abastecimentosAgrupados as $posto => $abastecimentos)
                    <tr class="bg-gray-100">
                        <td colspan="9" class="text-center font-bold px-4 py-2">{{ $posto }}</td>
                    </tr>
                    @foreach ($abastecimentos as $index => $abastecimento)
                    <x-tables.row :index="$index">
                        <x-tables.cell>{{ $abastecimento->cod_transacao }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->placa }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->tipo_combustivel }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->numero_nf }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->chave_nf }}</x-tables.cell>
                        <x-tables.cell>{{ format_date($abastecimento->data_vencimento_nf) }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->posto_abastecimento }}</x-tables.cell>
                        <x-tables.cell>{{ $abastecimento->cnpj }}</x-tables.cell>
                        <x-tables.cell>R$ {{ number_format($abastecimento->valor_nf, 2, ',', '.') }}</x-tables.cell>
                    </x-tables.row>
                    @endforeach
                    <tr class="bg-gray-200 font-bold">
                        <td colspan="8" class="text-right px-4 py-2">Total do Posto:</td>
                        <td class="px-4 py-2">R$ {{ number_format($abastecimentos->sum('valor_nf'), 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <x-tables.empty cols="9" message="Nenhum registro encontrado" />
                    @endforelse

                    <tr class="bg-gray-300 font-bold">
                        <td colspan="8" class="text-right px-4 py-2">Total Geral:</td>
                        <td class="px-4 py-2">R$ {{ number_format($abastecimentosSelecionados->sum('valor_nf'), 2, ',',
                            '.') }}</td>
                    </tr>
                </x-tables.body>
            </x-tables.table>
        </form>
    </div>
</div>

@push('scripts')
<script>
        function confirmarFaturamento() {
            if (confirm('Confirma o faturamento dos abastecimentos selecionados?')) {
                document.getElementById('abastecimentoFaturamentoForm').submit();
            }
        }
</script>   

@endpush