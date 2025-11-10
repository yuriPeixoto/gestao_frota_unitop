<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód. <br> Dimensão Pneu</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($tipodimensaopneus as $index => $tipodimensaopneu)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $tipodimensaopneu->id_dimensao_pneu }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $tipodimensaopneu->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell nowrap>{{ $tipodimensaopneu->data_alteracao?->format('d/m/Y H:i') }}</x-tables.cell>
                    <x-tables.cell>{{ $tipodimensaopneu->descricao_pneu ?? '' }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            {{-- <a href="{{ route('admin.tipodimensaopneus.show', $tipodimensaopneu->id_dimensao_pneu) }}"
                            title="Visualizar"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <x-icons.eye class="h-3 w-3" />
                        </a> --}}
                            <a href="{{ route('admin.tipodimensaopneus.edit', $tipodimensaopneu->id_dimensao_pneu) }}"
                                title="Editar"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>

                            {{-- @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 17, 25, 1001])) --}}
                            <button type="button" onclick="destroy({{ $tipodimensaopneu->id_dimensao_pneu }})"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                            {{-- @endif --}}
                        </div>
                    </x-tables.cell>

                    <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                        type="error" title="Confirmar exclusão">
                        Tem certeza que deseja excluir esse tipo de dimensão <b class="title"></b>? <br>
                        Esta ação não pode ser desfeita. <br>
                        <x-bladewind::button name="botao-delete" type="button" color="red"
                            onclick="confirmarExclusao({{ $tipodimensaopneu->id_dimensao_pneu }})"
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
        {{ $tipodimensaopneus->links() }}
    </div>
</div>
