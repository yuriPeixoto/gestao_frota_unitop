<div class="results-table mt-4">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ações</x-tables.head-cell>
            <x-tables.head-cell>Cód.OS</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Filial</x-tables.head-cell>
            <x-tables.head-cell>Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>Número Nota Fiscal</x-tables.head-cell>
            <x-tables.head-cell>Data Recebimento</x-tables.head-cell>
            <x-tables.head-cell>Situação Entrada</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($manutencaoPneusEntrada as $index => $res)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @if ($res->situacao_entrada != 'FINALIZADO' )
                        <x-tooltip content="Editar">
                            <a href="{{ route('admin.manutencaopneusentrada.edit', $res->id_manutencao_entrada) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-icons.pencil class="h-3 w-3" />
                            </a>
                        </x-tooltip>
                        @endif
                        @if ($res->situacao_entrada == 'FINALIZADO' )
                        <x-tooltip content="Avaliar Recebimento">
                            @php
                            // Garante que vamos passar algum valor para o nf_entrada
                            $nfEntrada = $res->chave_nf_entrada ?? $res->numero_nf ?? '0';
                            @endphp
                            <a href="{{ route('admin.manutencaopneusentrada.checklist', [$res->id_manutencao_entrada, $nfEntrada]) }}"
                                class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <x-icons.check class="h-3 w-3" />
                            </a>
                        </x-tooltip>

                        @endif
                        @php
                        $usuarios = [1, 25, 318, 92];
                        @endphp

                        @if ((auth()->user()->is_superuser || in_array(auth()->user()->id, $usuarios)) &&
                        $res->situacao_entrada != 'FINALIZADO')
                        <x-tooltip content="Excluir">
                            <button
                                class="bg-red-600 rounded-full p-1 inline-flex items-center shadow-sm text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                show_close_icon="true"
                                onclick="showModal('delete-pneu-entrada'), idSelecionado = {{ $res->id_manutencao_entrada }}">
                                <x-icons.trash class="h-3 w-3" />
                            </button>
                        </x-tooltip>
                        @endif
                    </div>
                </x-tables.cell>
                <x-tables.cell>{{ $res->id_manutencao_entrada }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($res->data_inclusao) }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($res->data_alteracao) }}</x-tables.cell>
                <x-tables.cell>{{ $res->filial->name }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $res->fornecedor->nome_fornecedor }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $res->numero_nf }}</x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($res->data_recebimento) }}</x-tables.cell>
                <x-tables.cell nowrap>{{ $res->situacao_entrada }}</x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $manutencaoPneusEntrada->links() }}
    </div>
</div>