<div class="results-table">
    <!-- Botão para criar solicitação -->
    <div class="mb-4 flex items-center justify-between">
        <div>
            <button id="btn-criar-solicitacao"
                class="rounded bg-blue-600 px-4 py-2 font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                disabled>
                <i class="fas fa-plus mr-2"></i>
                Criar Solicitação de Compra
            </button>
            <span id="items-count" class="ml-4 text-sm text-gray-600">0 itens selecionados</span>
        </div>
    </div>

    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>
                <input type="checkbox" id="select-all"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            </x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Item<br>Compra</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Produto</x-tables.head-cell>
            <x-tables.head-cell>Descrição</x-tables.head-cell>
            <x-tables.head-cell>Grupo</x-tables.head-cell>
            <x-tables.head-cell>Subgrupo</x-tables.head-cell>
            <x-tables.head-cell>Cód.<br>Solicitação</x-tables.head-cell>
            <x-tables.head-cell>Situação</x-tables.head-cell>
            <x-tables.head-cell>Quantidade<br>para<br>Compra</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @if ($itensAgrupados->isEmpty())
                <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @else
                @foreach ($itensAgrupados as $grupoNome => $itensDoGrupo)
                    {{-- Cabeçalho do Grupo --}}
                    <tr class="group-header">
                        <td colspan="9" class="px-4 py-3 text-left text-sm font-semibold text-gray-700">
                            <i class="fas fa-folder mr-2"></i>
                            {{ $grupoNome }} ({{ $itensDoGrupo->count() }}
                            item{{ $itensDoGrupo->count() > 1 ? 's' : '' }})
                        </td>
                    </tr>

                    {{-- Itens do Grupo --}}
                    @foreach ($itensDoGrupo as $index => $itensParaCompras)
                        <x-tables.row :index="$index">
                            <x-tables.cell>
                                <input type="checkbox" name="item_selected"
                                    value="{{ $itensParaCompras->id_item_compra }}"
                                    class="item-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </x-tables.cell>
                            <x-tables.cell>{{ $itensParaCompras->id_item_compra }}</x-tables.cell>
                            <x-tables.cell>{{ $itensParaCompras->produto->id_produto ?? 'N/A' }}</x-tables.cell>
                            <x-tables.cell>{{ $itensParaCompras->produto->descricao_completa ?? ($itensParaCompras->produto->descricao_produto ?? 'N/A') }}</x-tables.cell>
                            <x-tables.cell>
                                <span
                                    class="inline-flex items-center rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">
                                    {{ $itensParaCompras->produto->grupoServico->descricao_grupo ?? 'Sem Grupo' }}
                                </span>
                            </x-tables.cell>
                            <x-tables.cell>
                                <span
                                    class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">
                                    {{ $itensParaCompras->produto->subgrupoServico->descricao_subgrupo ?? 'Sem Subgrupo' }}
                                </span>
                            </x-tables.cell>
                            <x-tables.cell>{{ $itensParaCompras->id_relacaosolicitacoespecas }}</x-tables.cell>
                            <x-tables.cell>{{ $itensParaCompras->situacao }}</x-tables.cell>
                            <x-tables.cell>{{ $itensParaCompras->quantidade_compra }}</x-tables.cell>
                        </x-tables.row>
                    @endforeach
                @endforeach
            @endif

        </x-tables.body>

    </x-tables.table>

    <div class="mt-4">
        {{ $itensParaCompra->links() }}
    </div>

</div>

<!-- Modal para criar solicitação -->
<div id="modal-criar-solicitacao"
    class="fixed inset-0 z-50 hidden h-full w-full overflow-y-auto bg-gray-600 bg-opacity-50">
    <div class="relative top-20 mx-auto w-11/12 max-w-md rounded-md border bg-white p-5 shadow-lg">
        <div class="mt-3">
            <!-- Header do Modal -->
            <div class="flex items-center justify-between border-b pb-3">
                <h3 class="text-lg font-medium text-gray-900">
                    Criar Solicitação de Compra
                </h3>
                <button id="btn-fechar-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Corpo do Modal -->
            <form id="form-criar-solicitacao" class="mt-4">
                @csrf
                <input type="hidden" id="itens-selecionados" name="itens_selecionados">

                <!-- Filial da Solicitação (readonly - filial do usuário) -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Filial da Solicitação
                    </label>
                    <input type="text" value="{{ auth()->user()->filial->name ?? 'Não definido' }}"
                        class="w-full rounded-md border border-gray-300 bg-gray-100 px-3 py-2" readonly>
                </div>

                <!-- Prioridade -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Prioridade <span class="text-red-500">*</span>
                    </label>
                    <select name="prioridade" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione a prioridade</option>
                        <option value="BAIXA">Baixa</option>
                        <option value="MEDIA">Média</option>
                        <option value="ALTA">Alta</option>
                    </select>
                </div>

                <!-- Departamento -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Departamento <span class="text-red-500">*</span>
                    </label>
                    <select name="id_departamento" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione o departamento</option>
                        @foreach ($departamentos as $departamento)
                            <option value="{{ $departamento->id_departamento }}">
                                {{ $departamento->descricao_departamento }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filial de Entrega -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Filial de Entrega <span class="text-red-500">*</span>
                    </label>
                    <select name="filial_entrega" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione a filial de entrega</option>
                        @foreach ($filiais as $filial)
                            <option value="{{ $filial->id }}">{{ $filial->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filial de Faturamento -->
                <div class="mb-4">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Filial de Faturamento <span class="text-red-500">*</span>
                    </label>
                    <select name="filial_faturamento" required
                        class="w-full rounded-md border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Selecione a filial de faturamento</option>
                        @foreach ($filiais as $filial)
                            <option value="{{ $filial->id }}">{{ $filial->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3">
                    <button type="button" id="btn-cancelar"
                        class="rounded-md bg-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-400">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <i class="fas fa-spinner fa-spin hidden" id="spinner"></i>
                        Criar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
