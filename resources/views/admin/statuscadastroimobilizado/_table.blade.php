<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Cód. Tipo<br>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição Status Cadastro</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($statusCadastroImobilizado as $index => $status)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <div>
                            <x-forms.button type="secondary" variant="outlined" size="sm"
                                href="{{ route('admin.statuscadastroimobilizado.edit', $status->id) }}">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </x-forms.button>
                        </div>
                        <div>
                            <x-forms.button type="danger" variant="outlined" size="sm"
                                onclick="destroyOrdemServico({{ $status->id }})">
                                <x-icons.trash class="w-4 h-4 text-red-600" />
                            </x-forms.button>
                        </div>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $status->id }}</x-tables.cell>
                <x-tables.cell>{{ format_date($status->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($status->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $status->descricao }}</x-tables.cell>

                <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                    type="error" title="Confirmar exclusão">
                    Tem certeza que deseja excluir esse Produto Imobilizado <b class="title"></b>? <br>
                    Esta ação não pode ser desfeita. <br>
                    <x-bladewind::button name="botao-delete" type="button" color="red"
                        onclick="confirmarExclusao({{ $status->id }})" class="mt-3 text-white">
                        Excluir
                    </x-bladewind::button>
                </x-bladewind.modal>

            </x-tables.row>
            @empty
            <x-tables.empty cols="5" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $statusCadastroImobilizado->links() }}
    </div>
    @push('scripts')
    @include('admin.statuscadastroimobilizado._scripts')
    @endpush
</div>