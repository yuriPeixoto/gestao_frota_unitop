<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>{{-- Ações --}}</x-tables.head-cell>
            <x-tables.head-cell>Número de Fogo</x-tables.head-cell>
            <x-tables.head-cell>Status Pneu</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>N° de Fogo Antigo</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($pneus as $index => $pneu)
                <x-tables.row :index="$index">
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            <div>
                                <a href="{{ route('admin.pneus.edit', $pneu->id_pneu) }}" alt="Editar">
                                    <x-icons.edit class="w-4 h-4 mr-2 text-blue-600" />
                                </a>
                            </div>
                        </div>
                    </x-tables.cell>
                    <x-tables.cell>{{ $pneu->id_pneu }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->status_pneu }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($pneu->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($pneu->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->departamentoPneu->descricao_departamento ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->filialPneu->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $pneu->cod_antigo }}</x-tables.cell>

                    <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                        type="error" title="Confirmar exclusão">
                        Tem certeza que deseja excluir a ordem de serviço <b class="title"></b>? <br>
                        Esta ação não pode ser desfeita. <br>
                        <x-bladewind::button name="botao-delete" type="button" color="red"
                            onclick="confirmarExclusao({{ $pneu->id_pneu }})" class="mt-3 text-white">
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
        {{ $pneus->links() }}
    </div>
</div>
