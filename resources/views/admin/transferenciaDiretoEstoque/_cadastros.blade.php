<div class="py-6 px-4 sm:px-6 lg:px-8 w-full space-y-6">

    {{-- CRUD - Cadastro --}}
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Código da Transferência (apenas visualização) --}}
            <x-forms.input name="id_transferencia" label="Cód. Transferência"
                value="{{ $transferencia->id_transferencia_direta_estoque ?? '' }}" readonly />

            <input type="hidden" id="id_filial_atual" value="{{ auth()->user()->filial->id }}">

            {{-- Filial --}}
            <x-forms.input name="filial" label="Filial" value="{{ auth()->user()->filial->name ?? '' }}" disabled />

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
        <x-forms.smart-select name="id_produto" id="id_produto" label="Produto" placeholder="Selecionar"
            :options="$produtos" :searchUrl="route('admin.api.produto.search')" asyncSearch="false"
            minSearchLength="2" />


        {{-- Aqui recebe o UF da filial --}}
        <x-forms.input name="id_uf" label="UND:" :value="auth()->user()->filial->estado->uf ?? ''" disabled />
        {{-- Aqui recebe a quantidade do produto que tem na filial ( ao selecioanr produto esse campo é para atualizar
        dinamicamente apresentando a quantidade existente do estoque()) --}}
        <x-forms.input id="quantidade_produto" name="quantidade_produto" label="Estoque Filial:" value="" readonly />

        {{-- Quantiade que ira ser enviada para a transferencia--}}
        <x-forms.input name="qtd_baixa" label="Qtde Pedido:" value="" />
        <input type="hidden" name="qtde_produto" value="">

    </div>
    <button type="button"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700"
        onclick="adicionarProduto()">
        <x-icons.plus class="h-4 w-4 mr-1" />
        Adicionar
    </button>

    {{-- Inserir Produtos --}}
    <div>
        <x-tables.table>
            <x-tables.header>
                <x-tables.head-cell>Cod:</x-tables.head-cell>
                <x-tables.head-cell>Produto</x-tables.head-cell>
                <x-tables.head-cell>Qtd Produto</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tbody-produtos">
                @foreach ($produtosSelecionados ?? [] as $index => $produto)
                <x-tables.row :index="$index">
                    {{-- Produto --}}
                    <x-tables.cell>
                        <input type="hidden" name="produtos[{{ $index }}][id_produto]"
                            value="{{ $produto->id_produto }}">
                        {{ $produto->id_produto }}
                    </x-tables.cell>
                    <x-tables.cell>
                        <input type="hidden" name="produtos[{{ $index }}][id_produto]"
                            value="{{ $produto->id_produto }}">
                        {{ $produto->descricao_produto }}
                    </x-tables.cell>
                    {{-- Qtde Baixa --}}
                    <x-tables.cell>
                        <input type="number" name="produtos[{{ $index }}][quantidade]" min="1" class="..."
                            value="{{ old('qtde_produto', $produto->qtde_produto ?? '') }}">
                    </x-tables.cell>

                    {{-- Remover x
                    <x-tables.cell>
                        <button type="button" class="text-red-600 hover:text-red-800"
                            onclick="removerProduto({{ $index }})">
                            <x-icons.trash class="w-4 h-4" />
                        </button>
                    </x-tables.cell>
                    --}}
                </x-tables.row>
                @endforeach
            </x-tables.body>
        </x-tables.table>


    </div>

    {{-- Observações --}}
    <div class="w-full mt-4">
        <label for="observacao" class="block text-sm font-medium text-gray-700">Observação</label>
        <textarea name="observacao" id="observacao" rows="6"
            class="w-full h-40 border-gray-300 rounded-md shadow-sm focus:ring-indig o-500 focus:border-indigo-500 resize-none">{{ old('observacao', $transferencia->observacao ?? '') }}</textarea>
    </div>


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

</div>