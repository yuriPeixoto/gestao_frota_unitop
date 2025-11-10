<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código<br>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Prioridade</x-tables.head-cell>
            <x-tables.head-cell>Comprador</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Solicitante</x-tables.head-cell>
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
                    <x-tables.cell>{{ $solicitacao->comprador->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->situacao_compra }}</x-tables.cell>
                    <x-tables.cell>{{ $solicitacao->solicitante->name ?? '' }}</x-tables.cell>
                    <x-tables.cell>
                        @if ($solicitacao->tipo_solicitacao == 1)
                            Solicitação de Produto
                        @elseif ($solicitacao->tipo_solicitacao == 2)
                            Solicitação de Serviço
                        @endif
                    </x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('admin.compras.aprovarpedido.edit', $solicitacao->id_solicitacoes_compras) }}"
                                class="text-green-600 hover:text-green-900" title="Aprovar pedido">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7">
                                    </path>
                                </svg>
                            </a>

                            <a onclick="cancelarCotacao({{ $solicitacao->id_solicitacoes_compras }})"
                                class="cursor-pointer text-red-600 hover:text-red-900" title="Retornar solicitação">
                                <x-icons.arrow-path-rounded-square />
                            </a>

                            <a href="{{ route('admin.compras.aprovarpedido.show', $solicitacao->id_solicitacoes_compras) }}"
                                class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </a>
                        </div>
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
                <x-tables.empty cols="11" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $solicitacoes->links() }}
    </div>
</div>
