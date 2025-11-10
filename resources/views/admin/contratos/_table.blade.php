<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>contrato</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>CNPJ/CPF - Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Valido</x-tables.head-cell>
            <x-tables.head-cell>Saldo Contrato</x-tables.head-cell>
            <x-tables.head-cell>Valor Contrato</x-tables.head-cell>
            <x-tables.head-cell>Total Valor Contrato</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($contratos as $index => $contrato)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $contrato->id_contrato_forn }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $contrato->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $contrato->data_alteracao?->format('d/m/Y H:i') ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ ($contrato->fornecedor?->cpf_fornecedor ?? $contrato->fornecedor?->cnpj_fornecedor) . ' - ' . $contrato->fornecedor?->nome_fornecedor ?? '-' }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $contrato->is_valido ? 'Ativo' : 'Inativo' }}</x-tables.cell>
                    <x-tables.cell
                        nowrap>{{ $contrato->saldo_contrato ? 'R$ ' . number_format($contrato->saldo_contrato, 2, ',', '.') : 'R$ 0,00' }}</x-tables.cell>
                    <x-tables.cell
                        nowrap>{{ $contrato->valor_contrato ? 'R$ ' . number_format($contrato->valor_contrato, 2, ',', '.') : '-' }}</x-tables.cell>
                    <x-tables.cell nowrap>
                        {{ !is_null($contrato->valor_contrato)
                            ? 'R$ ' .
                                number_format(
                                    $contrato->saldo_contrato !== null
                                        ? $contrato->valor_contrato - $contrato->saldo_contrato
                                        : $contrato->valor_contrato,
                                    2,
                                    ',',
                                    '.',
                                )
                            : '-' }}
                    </x-tables.cell> <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.contratos.edit', $contrato->id_contrato_forn) }}"
                                class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1.5 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <x-icons.pencil class="h-3.5 w-3.5" />
                            </a>
                        </div>
                    </x-tables.cell>

                    <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                        type="error" title="Confirmar exclusão">
                        Tem certeza que deseja excluir esse Cadastro contrato <b class="title"></b>? <br>
                        Esta ação não pode ser desfeita. <br>
                        <x-bladewind::button name="botao-delete" type="button" color="red"
                            onclick="excluirContratos({{ $contrato->id_contrato_forn }})" class="mt-3 text-white">
                            Excluir
                        </x-bladewind::button>
                    </x-bladewind.modal>

                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $contratos->links() }}
    </div>
</div>
