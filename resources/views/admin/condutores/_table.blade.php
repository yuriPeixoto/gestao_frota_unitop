<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>CNH</x-tables.head-cell>
            <x-tables.head-cell>Nome</x-tables.head-cell>
            <x-tables.head-cell>CPF</x-tables.head-cell>
            <x-tables.head-cell>Pontuação</x-tables.head-cell>
            <x-tables.head-cell>Portaria</x-tables.head-cell>
            <x-tables.head-cell>Impedimento</x-tables.head-cell>
            <x-tables.head-cell>Data Vencimento</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($smartecCnh as $index => $smartecCnhs)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $smartecCnhs->cnh }}</x-tables.cell>
                <x-tables.cell>{{ $smartecCnhs->nome }}</x-tables.cell>
                <x-tables.cell>{{ $smartecCnhs->cpf }}</x-tables.cell>
                <x-tables.cell>{{ $smartecCnhs->pontuacao ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $smartecCnhs->portaria ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $smartecCnhs->impedimento ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $smartecCnhs->vencimento?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.condutores.edit', $smartecCnhs->id_smartec_cnh) }}"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3.5 w-3.5" />
                        </a>


                        <button type="button"
                            onclick="destroySmartecCnhs({{ $smartecCnhs->id_smartec_cnh }}, '{{ $smartecCnhs->cnh }}')"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.trash class="h-3.5 w-3.5" />
                        </button>

                    </div>
                </x-tables.cell>


                <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                    type="error" title="Confirmar exclusão">
                    Tem certeza que deseja excluir essa CNH <b class="title"></b>? <br>
                    Esta ação não pode ser desfeita. <br>
                    <x-bladewind::button name="botao-delete" type="button" color="red"
                        onclick="excluirSmartecCnhs({{ $smartecCnhs->id_smartec_cnh }})" class="mt-3 text-white">
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
        {{ $smartecCnh->links() }}
    </div>
</div>