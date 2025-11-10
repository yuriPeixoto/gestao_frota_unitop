<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código<br>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Prioridade</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Tipo<br>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($solicitacoes as $index => $solicitacao)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $solicitacao->id_solicitacoes_compras }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->departamento->descricao_departamento }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->filial->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->prioridade }}</x-tables.cell>
                    <x-tables.cell nowrap>
                        <span
                            class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold leading-5 text-blue-800">
                            {{ $solicitacao->situacao_compra }}
                        </span>
                    </x-tables.cell>

                    <x-tables.cell>
                        @if ($solicitacao->tipo_solicitacao == 1)
                            Solicitação de Produto
                        @elseif ($solicitacao->tipo_solicitacao == 2)
                            Solicitação de Serviço
                        @endif
                    </x-tables.cell>
                    <x-tables.cell>

                        <a href="{{ route('admin.compras.validarcotacoes.edit', $solicitacao->id_solicitacoes_compras) }}"
                            class="text-green-600 hover:text-green-900" title="Liberar">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </a>
                    </x-tables.cell>

                    <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                        type="error" title="Confirmar exclusão">
                        Tem certeza que deseja excluir esse Cadastro solicitacao <b class="title"></b>? <br>
                        Esta ação não pode ser desfeita. <br>
                        <x-bladewind::button name="botao-delete" type="button" color="red"
                            onclick="excluirsolicitacoes({{ $solicitacao->id_solicitacoes_compras }})"
                            class="mt-3 text-white">
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
        {{ $solicitacoes->links() }}
    </div>
</div>
