<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Código </x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Telefone</x-tables.head-cell>
            <x-tables.head-cell>Nome</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($telefoneTransferencia as $index => $telefone)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <div>
                            <x-forms.button type="secondary" variant="outlined" size="sm"
                                href="{{ route('admin.telefonetransferencia.edit', $telefone->id_telefone_transferencia) }}">
                                <x-icons.edit class="w-4 h-4 text-blue-600" />
                            </x-forms.button>
                        </div>
                        <div>
                            <x-forms.button type="danger" variant="outlined" size="sm"
                                onclick="destroyOrdemServico({{ $telefone->id_telefone_transferencia }})">
                                <x-icons.trash class="w-4 h-4 text-red-600" />
                            </x-forms.button>
                        </div>
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $telefone->id_telefone_transferencia }}</x-tables.cell>
                <x-tables.cell>{{ format_date($telefone->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ format_date($telefone->data_alteracao) }}</x-tables.cell>
                <x-tables.cell> {{ $telefone->telefone_formatado }}</x-tables.cell>
                <x-tables.cell>{{ $telefone->nome }}</x-tables.cell>
                <x-tables.cell>{{ $telefone->departamentoTransferencia->departamento }}</x-tables.cell>

                <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                    type="error" title="Confirmar exclusão">
                    Tem certeza que deseja excluir esse telefone <b class="title"></b>? <br>
                    Esta ação não pode ser desfeita. <br>
                    <x-bladewind::button name="botao-delete" type="button" color="red"
                        onclick="confirmarExclusao({{ $telefone->id_telefone_transferencia }})" class="mt-3 text-white">
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
        {{ $telefoneTransferencia->links() }}
    </div>
    @push('scripts')
    @include('admin.telefonetransferencia._scripts')
    @endpush
</div>