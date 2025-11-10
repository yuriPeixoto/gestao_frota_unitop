<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Transação</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Transação</x-tables.head-cell>
            <x-tables.head-cell>Hodometro</x-tables.head-cell>
            <x-tables.head-cell>Litragem</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Nome<br>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Serviço</x-tables.head-cell>
            <x-tables.head-cell>Tipo Abastecimento</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Tanque</x-tables.head-cell>
            <x-tables.head-cell>Nome<br>Tanque</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Bomba</x-tables.head-cell>
            <x-tables.head-cell>Nome<br>Bomba</x-tables.head-cell>
            <x-tables.head-cell>Razao<br>Social Posto</x-tables.head-cell>
            <x-tables.head-cell>Nome<br>Fantasia Posto</x-tables.head-cell>
            <x-tables.head-cell>CNPJ Posto</x-tables.head-cell>
            <x-tables.head-cell>Cidade Posto</x-tables.head-cell>
            <x-tables.head-cell>UF Posto</x-tables.head-cell>
            <x-tables.head-cell>Cartão Mascarado</x-tables.head-cell>
            <x-tables.head-cell>Motorista</x-tables.head-cell>
            <x-tables.head-cell>Mat. Motorista</x-tables.head-cell>
            <x-tables.head-cell>CPF Motorista</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Modelo Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Ano Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Matricula Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Marca Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Cor Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Transação Estornada</x-tables.head-cell>
            <x-tables.head-cell>CNPJ Cliente</x-tables.head-cell>

        </x-tables.header>

        <x-tables.body>
            @forelse ($integracoes as $index => $integracao)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.abastecimentomanual.edit', $integracao->transacao) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 25]))
                                <button type="button" onclick="confirmarExclusao({{ $integracao->transacao }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>
                    </x-tables.cell>

                    <x-tables.cell>{{ $integracao->transacao }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($integracao->datatransacao, 'd/m/Y') }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->hodometro }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->litragem }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->codcombustivel }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->nomecombustivel }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->servico }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->tipoabastecimento }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->codigotanque }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->nometanque }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->codigobomba }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->nomebomba }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->razaosocialposto }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->nomefantasiaposto }}</x-tables.cell>
                    <x-tables.cell>{{ FormatDocs($integracao->cnpjposto) }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->cidadeposto }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->ufposto }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->cartaomascarado }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->motorista }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->matriculamotorista }}</x-tables.cell>
                    <x-tables.cell>{{ FormatDocs($integracao->cpfmotorista, 'CPF') }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->placa }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->modeloveiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->anoveiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->matriculaveiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->marcaveiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->corveiculo }}</x-tables.cell>
                    <x-tables.cell>{{ $integracao->transacaoestornada }}</x-tables.cell>
                    <x-tables.cell>{{ FormatDocs($integracao->cnpjcliente) }}</x-tables.cell>

                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $integracoes->links() }}
    </div>
</div>
