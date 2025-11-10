<x-app-layout>
    <form
        action="{{ route('admin.transferenciaDiretoEstoque.baixar', $transferencia->id_transferencia_direta_estoque) }}"
        method="POST">
        @csrf

        <div class="space-y-6 bg-white">
            <div class="py-6 px-4 sm:px-6 lg:px-8 w-full space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">

                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl text-black font-bold">Baixar Transferência </h2>
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
                    <x-forms.input name="id_produto" id="id_produto" label="Produto" placeholder="" readonly />


                    {{-- Aqui recebe o UF da filial --}}
                    <x-forms.input name="id_uf" label="UND:" :value="auth()->user()->filial->estado->uf ?? ''"
                        disabled />


                    {{-- Aqui recebe a quantidade do produto que tem na filial ( ao selecioanr produto esse campo é para
                    atualizar
                    dinamicamente apresentando a quantidade existente do estoque()) --}}
                    <x-forms.input id="estoque_filial" name="estoque_filial" label="Estoque Filial:" value=""
                        readonly />

                    {{-- Aqui recebe a quantidade do produto que tem na filial Matriz( ao selecioanar produto esse campo
                    é
                    para
                    atualizar
                    dinamicamente apresentando a quantidade existente do estoque da Matriz()) --}}
                    <x-forms.input id="estoque_matriz" name="estoque_matriz" label="Estoque Matriz:" value=""
                        readonly />

                    {{-- Quantiade que ira ser enviada para a transferencia--}}
                    <x-forms.input name="qtde_pedido" label="Qtde pedido:"
                        value="{{ old('qtde_produto', $transferencia->qtde_produto ?? '') }}  " readonly />

                    {{-- Quantiade que ira ser enviada para a transferencia--}}
                    <x-forms.input id="qtd_baixa_input" name="qtd_baixa" label="Baixa:" value="" />

                </div>



                {{-- Inserir Produtos --}}
                <div>
                    <x-tables.table>
                        <x-tables.header>
                            <x-tables.head-cell></x-tables.head-cell>
                            <x-tables.head-cell>Produto</x-tables.head-cell>
                            <x-tables.head-cell>Qtde Produto</x-tables.head-cell>
                            <x-tables.head-cell>Qtd Baixa</x-tables.head-cell>
                        </x-tables.header>

                        <x-tables.body id="tbody-produtos">
                            @foreach ($produtosSelecionados ?? [] as $index => $item)
                            <x-tables.row :index="$index">
                                {{-- Produto --}}
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">
                                    <input type="checkbox" id="select-produto-checkbox" class="select-produto-checkbox"
                                        data-index="{{ $index }}">
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
                                    <input type="hidden" name="produtos[{{ $index }}][id_produto]"
                                        value="{{ $item->id_produto }}">
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
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                {{-- Novo botão: salvar parcialmente --}}
                <button type="submit" name="action" value="salvar"
                    class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 transition ease-in-out duration-150">
                    Salvar Baixa
                </button>

                {{-- Botão já existente: finalizar --}}
                <button type="submit" name="action" value="finalizar"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Finalizar Processo
                </button>
            </div>
        </div>
    </form>
</x-app-layout>



<script>
    document.addEventListener('DOMContentLoaded', function () {
    let contadorProdutos = 0;
    let produtoSelecionado = { id: null, nome: null };

    // Escuta mudanças no smart-select
    onSmartSelectChange('id_produto', function (data) {
        console.log('[DEBUG] Produto selecionado:', data.value);
        console.log('[DEBUG] Label do produto:', data.label);

        produtoSelecionado.id = data.value;
        produtoSelecionado.nome = data.label;

        const estoqueInput = document.querySelector('input[name="quantidade_produto"]');
        const idFilial = document.getElementById('id_filial_atual')?.value;

        if (!data.value || !idFilial) {
            console.warn('[AVISO] Produto ou filial indefinido');
            if (estoqueInput) estoqueInput.value = 0;
            return;
        }

        const url = `${window.location.origin}/admin/transferenciaDiretoEstoque/produtos-por-filial?id_produto=${data.value}&id_filial=${idFilial}`;
        console.log('[DEBUG] Requisição para URL:', url);

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Resposta não OK da API');
                return response.json();
            })
            .then(data => {
                if (estoqueInput) {
                    estoqueInput.value = data.quantidade ?? 0;
                    console.log('[DEBUG] Campo estoque atualizado para:', estoqueInput.value);
                }
            })
            .catch(err => {
                console.error('[ERRO] Erro ao buscar estoque:', err);
                if (estoqueInput) estoqueInput.value = 0;
            });

        // Limpa campo de quantidade ao trocar produto
        limparCamposProduto();
    });

    function limparCamposProduto() {
        const qtdBaixaInput = document.querySelector('[name="qtd_baixa"]');
        if (qtdBaixaInput) qtdBaixaInput.value = '';
    }

    window.adicionarProduto = function () {
        const tabela = document.querySelector('#tbody-produtos');
        const qtdBaixaInput = document.querySelector('[name="qtd_baixa"]');

        const idProduto = produtoSelecionado.id;
        const nomeProduto = produtoSelecionado.nome;
        const quantidade = qtdBaixaInput.value;

        if (!idProduto || !quantidade || quantidade <= 0) {
            alert("Selecione um produto e informe uma quantidade válida.");
            return;
        }

        const novaLinha = document.createElement('tr');
        novaLinha.innerHTML = `
            <td>
                <input type="hidden" name="produtos[${contadorProdutos}][id_produto]" value="${idProduto}">
                ${idProduto}
            </td>
            <td>
                <input type="hidden" name="produtos[${contadorProdutos}][id_produto]" value="${idProduto}">
                ${nomeProduto}
            </td>
            <td>
                <input type="number" name="produtos[${contadorProdutos}][quantidade]" value="${quantidade}" min="1"
                    class="w-full border-gray-300 rounded-md shadow-sm text-sm" />
            </td>
            <td>
                <button type="button" class="text-red-600 hover:text-red-800" onclick="removerProduto(this)">
                    🗑️
                </button>
            </td>
        `;

        tabela.appendChild(novaLinha);

        produtoSelecionado = { id: null, nome: null };
        document.querySelector('[name="qtd_baixa"]').value = '';
        document.querySelector('input[name="quantidade_produto"]').value = 0;

        contadorProdutos++;
    }

    window.removerProduto = function (botao) {
        const linha = botao.closest('tr');
        linha.remove();
    }
});
</script>

<script>
    function adicionarProduto() {
        // Valor do input de baixa
        const qtdBaixaInput = parseFloat(document.getElementById('qtd_baixa_input').value);
        if (isNaN(qtdBaixaInput) || qtdBaixaInput <= 0) {
            alert('Informe uma quantidade válida para a baixa.');
            return;
        }

        // Pegar os checkboxes selecionados
        const checkboxes = document.querySelectorAll('.select-produto-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Selecione pelo menos um produto para aplicar a baixa.');
            return;
        }

        checkboxes.forEach(checkbox => {
            const index = checkbox.dataset.index;

            // Atualiza a quantidade de baixa visualmente
            const display = document.getElementById(`qtd_baixa_display_${index}`);
            const hiddenInput = document.getElementById(`qtd_baixa_hidden_${index}`);

            let qtdAtual = parseFloat(hiddenInput.value || 0);
            let novaQtd = qtdAtual + qtdBaixaInput;

            display.innerText = novaQtd;
            hiddenInput.value = novaQtd;

            // Deseleciona o checkbox após atualizar
            checkbox.checked = false;
        });

        // Limpar o campo de baixa após uso
        document.getElementById('qtd_baixa_input').value = '';
        function validarBaixa() {
            const checkboxes = document.querySelectorAll('input[name="id_transferencia_direta_estoque_itens[]"]:checked');
            let valid = false;

            checkboxes.forEach(checkbox => {
                const id = checkbox.value;
                const qtdInput = document.querySelector(`input[name="qtd_baixa[${id}]"]`);
                
                if (qtdInput && parseFloat(qtdInput.value) > 0) {
                    valid = true;
                }
            });

            if (!valid) {
                alert('Selecione um produto e informe uma quantidade válida.');
                return false; // impede o envio do formulário
            }

            return true; // permite envio
        }

    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.select-produto-checkbox');

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', async function () {
                if (this.checked) {
                    // Desmarca os outros
                    checkboxes.forEach(cb => {
                        if (cb !== this) cb.checked = false;
                    });

                    const row = this.closest('tr');

                    // Coleta de dados
                    const idProduto = row.querySelector('td:nth-child(1)').textContent.trim();
                    const nomeProduto = row.querySelector('td:nth-child(2)').textContent.trim();
                    const quantidadeSolicitada = row.querySelector('td:nth-child(3)').textContent.trim();
                    const inputBaixa = row.querySelector('input[name^="produtos"][name$="[qtd_baixa]"]');
                    const quantidadeBaixa = inputBaixa ? inputBaixa.value : '';

                    // Consulta estoque da MATRIZ (id_filial = 1)
                    const urlMatriz = `${window.location.origin}/admin/transferenciaDiretoEstoque/produtos-por-filial?id_produto=${idProduto}&id_filial=1`;
                    const responseMatriz = await fetch(urlMatriz);
                    const dataMatriz = await responseMatriz.json();

                    // Consulta estoque da FILIAL ATUAL
                    const idFilialAtual = document.getElementById('id_filial_atual').value;
                    const urlFilial = `${window.location.origin}/admin/transferenciaDiretoEstoque/produtos-por-filial?id_produto=${idProduto}&id_filial=${idFilialAtual}`;
                    const responseFilial = await fetch(urlFilial);
                    const dataFilial = await responseFilial.json();

                    // Atualiza campos
                    document.getElementById('estoque_matriz').value = dataMatriz.quantidade ?? 0;
                    document.getElementById('estoque_filial').value = dataFilial.quantidade ?? 0;
                    document.getElementById('id_produto').value = idProduto + ' - ' + nomeProduto;
                    document.querySelector('input[name="qtde_pedido"]').value = quantidadeSolicitada;
                    document.querySelector('input[name="qtd_baixa"]').value = quantidadeBaixa;

                } else {
                    // Limpa os campos ao desmarcar
                    document.getElementById('id_produto').value = '';
                    document.getElementById('estoque_matriz').value = '';
                    document.getElementById('estoque_filial').value = '';
                    document.querySelector('input[name="qtde_pedido"]').value = '';
                    document.querySelector('input[name="qtd_baixa"]').value = '';
                }
            });
        });
    });
</script>






</div>