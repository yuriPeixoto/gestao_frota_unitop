<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Tipo Imobilizado</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Numero nota fiscal</x-tables.head-cell>
            <x-tables.head-cell>Usuario</x-tables.head-cell>
            <x-tables.head-cell>Imobilizado ativo</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($cadastroImobilizado as $index => $imobilizado)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $imobilizado->id_cadastro_imobilizado }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $imobilizado->data_inclusao?->format('d/m/Y H:i') }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $imobilizado->data_alteracao?->format('d/m/Y H:i') ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $imobilizado->tipoImobilizado->descricao_tipo_imobilizados ?? 'Não informada'}}
                </x-tables.cell>
                <x-tables.cell>
                    <span id="status-badge-{{ $imobilizado->status }}" class="px-2 py-1 text-xs font-medium rounded-full 
                        @if ($imobilizado->status->descricao == 'Finalizado') bg-green-100 text-green-800
                        @elseif($imobilizado->status->descricao == 'Pendente') bg-red-100 text-red-800 @endif">
                        {{ $imobilizado->status->descricao }}
                    </span>
                </x-tables.cell>
                <x-tables.cell nowrap>{{ $imobilizado->numero_nota_fiscal }}</x-tables.cell>
                <x-tables.cell>{{ $imobilizado->user->name ?? $imobilizado->id_usuario }}</x-tables.cell>
                <x-tables.cell>{{ $imobilizado->is_ativo ? 'Sim' : 'Não' }}</x-tables.cell>
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('admin.cadastroimobilizado.edit', $imobilizado->id_cadastro_imobilizado) }}"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.pencil class="h-3.5 w-3.5" />
                        </a>

                        @isset($imobilizado->id_veiculo)
                        <a href="{{ route('admin.veiculos.edit', $imobilizado->id_veiculo) }}" class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm  focus:outline-none focus:ring-2 focus:ring-offset-2
                        @if ($imobilizado->status->descricao == 'Finalizado') bg-green-100 text-green-800
                        @elseif($imobilizado->status->descricao == 'Pendente') bg-red-100 text-red-800 @endif">
                            <x-icons.eye class="h-3.5 w-3.5" />
                        </a>
                        @endisset

                        @if (auth()->user()->id == 1 || in_array(auth()->user()->id, [3, 4, 17, 25]))
                        <button type="button"
                            onclick="destroyCadastroImobilizado({{ $imobilizado->id_cadastro_imobilizado }})"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-icons.trash class="h-3.5 w-3.5" />
                        </button>
                        @endif
                    </div>
                </x-tables.cell>


                <x-bladewind.modal name="delete-autorizacao" cancel_button_label="Cancelar" ok_button_label=""
                    type="error" title="Confirmar exclusão">
                    Tem certeza que deseja excluir esse Cadastro Imobilizado <b class="title"></b>? <br>
                    Esta ação não pode ser desfeita. <br>
                    <x-bladewind::button name="botao-delete" type="button" color="red"
                        onclick="excluirCadastroImobilizado({{ $imobilizado->id_cadastro_imobilizado }})"
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
        {{ $cadastroImobilizado->links() }}
    </div>
</div>