@php
    use Illuminate\Support\Facades\Storage;

    $columns = [
        ['field' => 'id_produtos_solicitacoes', 'label' => 'Código Produto Solicitação'],
        ['field' => 'descricao_produto', 'label' => 'Descrição do Produto'],
        ['field' => 'descricao_unidade', 'label' => 'Descrição Unidade'],
        ['field' => 'quantidade', 'label' => 'quantidade', 'class' => 'text-center'],
    ];
@endphp

<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Código Requisição</x-tables.head-cell>
            <x-tables.head-cell>Data Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data Alteração</x-tables.head-cell>
            <x-tables.head-cell>Departamento</x-tables.head-cell>
            <x-tables.head-cell>Usuário Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Requisição de Pneu</x-tables.head-cell>
            <x-tables.head-cell>Requisição TI</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($SolicitacaoPecas as $index => $item)
                <x-tables.row :index="$index">
                    <x-tables.cell>{{ $item->id_solicitacao_pecas }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($item->data_inclusao) }}</x-tables.cell>
                    <x-tables.cell>{{ format_date($item->data_alteracao) }}</x-tables.cell>
                    <x-tables.cell>{{ $item->departamentoPecas->descricao_departamento ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->usuario->name ?? '-' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->situacao }}</x-tables.cell>
                    <x-tables.cell>{{ $item->requisicao_pneu ? 'Sim' : 'Nao' }}</x-tables.cell>
                    <x-tables.cell>{{ $item->requisicao_ti ? 'Sim' : 'Nao' }}</x-tables.cell>
                    <x-tables.cell>
                        <div class="flex items-center space-x-2">
                            @if ($item->podeSerEditado())
                                <a href="{{ route('admin.requisicaoMaterial.edit', $item->id_solicitacao_pecas) }}"
                                    title="Editar"
                                    class="inline-flex items-center rounded-full border border-transparent bg-indigo-600 p-1 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <x-icons.pencil class="h-3 w-3" />
                                </a>
                            @endif

                            <a href="#" title="Ver Produtos"
                                class="modal-trigger-produtos-{{ $item->id_solicitacao_pecas }} inline-flex items-center rounded-full border border-transparent bg-purple-600 p-1 text-white shadow-sm hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <x-icons.document-text class="h-3 w-3" />
                            </a>

                            <a href="{{ route('admin.requisicaoMaterial.show', $item->id_solicitacao_pecas) }}"
                                title="Visualizar"
                                class="dinline-flex items-center rounded-full border border-transparent bg-green-600 p-1 text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <x-icons.eye class="h-3 w-3" />
                            </a>

                            @if (auth()->user()->is_superuser)
                                <button type="button" onclick="confirmarExclusao({{ $item->id_solicitacao_pecas }})"
                                    title="Excluir"
                                    class="inline-flex items-center rounded-full border border-transparent bg-red-600 p-1 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    <x-icons.trash class="h-3 w-3" />
                                </button>
                            @endif
                        </div>

                        <x-table-modal modal-id="produtos-{{ $item->id_solicitacao_pecas }}" title="Lista de Produtos"
                            :columns="$columns"
                            fetch-url="/admin/requisicaoMaterial/getProdutosPorRequisicao/{{ $item->id_solicitacao_pecas }}/dados"
                            :items-per-page="5" max-width="7xl" />
                    </x-tables.cell>

                </x-tables.row>
            @empty
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $SolicitacaoPecas->links() }}
    </div>
</div>
