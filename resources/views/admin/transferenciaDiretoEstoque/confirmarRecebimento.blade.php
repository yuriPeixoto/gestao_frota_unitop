<x-app-layout>
    <form
        action="{{ route('admin.transferenciaDiretoEstoque.confirmar', $transferencia->id_transferencia_direta_estoque) }}"
        method="POST">
        @csrf

        <div class="space-y-6 bg-white">
            <div class="py-6 px-4 sm:px-6 lg:px-8 w-full space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl text-black font-bold">Confirmar Recebimento Transferência </h2>
                            <a href="{{ route('admin.transferenciaDiretoEstoque.index') }}"
                                class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                                Voltar
                            </a>
                        </div>
                    </div>
                </div>
                {{-- CRUD - Cadastro --}}
                <div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Código da Transferência (apenas visualização) --}}
                        <x-forms.input name="id_transferencia" label="Cód. Transferência"
                            value="{{ $transferencia->id_transferencia_direta_estoque ?? '' }}" readonly />

                        <input type="hidden" id="id_filial_atual" value="{{ auth()->user()->filial->id }}">

                        {{-- Filial --}}
                        <x-forms.input name="filial" label="Filial" value="{{ auth()->user()->filial->name ?? '' }}"
                            disabled />

                        {{-- Departamento --}}
                        <x-forms.input name="id_departamento" label="Departamento"
                            value="{{ auth()->user()->departamento->descricao_departamento ?? '' }}" readonly />

                        {{-- Usuário --}}
                        <x-forms.input name="usuario" label="Usuário" value="{{ auth()->user()->name }}" disabled />

                    </div>
                </div>
                <h3>Inserir Produtos</h3>
                {{-- --}}

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-forms.input name="id_produto" id="id_produto" label="Produto" placeholder="Produto selecionado"
                        readonly />


                    {{-- Aqui recebe o UF da filial --}}
                    <x-forms.input name="id_uf" label="UND:" :value="auth()->user()->filial->estado->uf ?? ''"
                        disabled />


                    {{-- Aqui recebe a quantidade do produto que tem na filial ( ao selecioanr produto esse campo é para
                    atualizar
                    dinamicamente apresentando a quantidade existente do estoque()) --}}
                    <x-forms.input id="quantidade_produto" name="quantidade_produto" label="Estoque Filial:" value=""
                        readonly />

                    {{-- Quantiade que ira ser enviada para a transferencia--}}
                    <x-forms.input name="qtde_pedido" label="Qtde pedido:"
                        value="{{ old('qtde_produto', $transferencia->qtde_produto ?? '') }}  " readonly />


                </div>



                {{-- Inserir Produtos --}}
                <div>
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell></x-tables.head-cell>
                            <x-tables.head-cell>Produto</x-tables.head-cell>
                            <x-tables.head-cell>Qtde Produto</x-tables.head-cell>
                            <x-tables.head-cell>Qtd Recebida</x-tables.head-cell>
                        </x-tables.header>

                        <x-tables.body id="tbody-produtos">
                            @foreach ($produtosSelecionados ?? [] as $index => $item)
                            <x-tables.row :index="$index">
                                {{-- Produto --}}
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                    <input type="checkbox" class="select-produto-checkbox" data-index="{{ $index }}"
                                        data-id="{{ $item->id_produto }}"
                                        data-nome="{{ $item->produto->descricao_produto }}"
                                        data-qtde-pedido="{{ $item->qtde_produto }}" />


                                    <input type="hidden"
                                        name="produtos[{{ $index }}][id_transferencia_direta_estoque_itens]"
                                        value="{{ $item->id_transferencia_direta_estoque_itens }}">

                                    {{ $item->id_produto }}
                                </td>

                                <x-tables.cell>
                                    {{ $item->produto->descricao_produto ?? 'Produto não encontrado' }}
                                </x-tables.cell>
                                {{-- Qtde Pedido --}}
                                <x-tables.cell>{{ $item->qtde_produto }}</x-tables.cell>
                                {{-- Qtde Baixa --}}
                                <x-tables.cell>
                                    <input type="number" name="produtos[{{ $index }}][qtd_baixa]"
                                        value="{{ $item->qtd_baixa ?? 0 }}" min="0" step="0.01">

                                </x-tables.cell>
                            </x-tables.row>
                            @endforeach
                        </x-tables.body>
                    </x-tables.table>

                    {{-- Observações --}}
                    <div class="w-full mt-4">
                        <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
                        <textarea name="observacao" id="observacao" rows="6"
                            class="w-full h-40 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 resize-none">{{ old('observacao', $transferencia->observacao ?? '') }}</textarea>
                    </div>

                </div>

            </div>
            <div class="flex justify-end space-x-3 col-span-full">
                {{-- Cancelar --}}
                <a href="{{ route('admin.transferenciaDiretoEstoque.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                {{-- Novo botão: salvar parcialmente --}}
                <button type="submit" name="action" value="salvar"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition ease-in-out duration-150">
                    Salvar Baixa
                </button>

                {{-- Botão já existente: finalizar --}}
                <button type="submit" name="action" value="finalizar"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Confirmar Recebimento
                </button>
            </div>
        </div>
    </form>
</x-app-layout>





<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.select-produto-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const idProduto = this.dataset.id;
                const nomeProduto = this.dataset.nome;
                const qtdePedido = this.dataset.qtdePedido;
                const idFilial = document.getElementById('id_filial_atual')?.value;

                if (this.checked) {
                    // Atualiza nome do produto
                    const nomeProdutoInput = document.querySelector('input[name="id_produto"], #id_produto');
                    if (nomeProdutoInput) {
                        nomeProdutoInput.value = nomeProduto;
                        console.log('[DEBUG] Nome do produto preenchido:', nomeProduto);
                    } else {
                        console.warn('[AVISO] Campo "Produto" não encontrado.');
                    }

                    // Atualiza qtde_pedido
                    const qtdePedidoInput = document.querySelector('input[name="qtde_pedido"]');
                    if (qtdePedidoInput) qtdePedidoInput.value = qtdePedido;

                    // Busca estoque
                    if (!idProduto || !idFilial) {
                        console.warn('Produto ou filial não definidos.');
                        document.querySelector('input[name="quantidade_produto"]').value = 0;
                        return;
                    }

                    const url = `${window.location.origin}/admin/transferenciaDiretoEstoque/produtos-por-filial?id_produto=${idProduto}&id_filial=${idFilial}`;
                    console.log('[DEBUG] Fetch URL:', url);

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            const estoqueInput = document.querySelector('input[name="quantidade_produto"]');
                            if (estoqueInput) estoqueInput.value = data.quantidade ?? 0;
                        })
                        .catch(err => {
                            console.error('[ERRO] ao buscar estoque:', err);
                            document.querySelector('input[name="quantidade_produto"]').value = 0;
                        });

                } else {
                    // Desmarcado: limpa os campos
                    ['id_produto', 'quantidade_produto', 'qtde_pedido'].forEach(nome => {
                        const input = document.querySelector(`input[name="${nome}"]`);
                        if (input) input.value = '';
                    });
                }
            });
        });
    });
</script>






</div>