<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form id="manutencaoImobilizado" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif


                    <!-- Cabeçalho -->
                    <div class=" p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Produto Imobilizado</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                {{-- Código Ordem de Serviço Imobilizado --}}
                                <label for="id_manutencao_imobilizado"
                                    class="block text-sm font-medium text-gray-700">Cód. OS. Imobilizados</label>
                                <input type="text" id="id_manutencao_imobilizado" name="id_manutencao_imobilizado"
                                    readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoImobilizado->id_manutencao_imobilizado ?? '' }}">
                            </div>

                            <div>
                                {{-- Situação --}}
                                <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
                                <input type="text" id="situacao" name="situacao" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoImobilizado->situacao ?? 'MANUTENÇÃO INICIADA' }}">
                            </div>

                            <div>
                                {{-- Filial --}}
                                <x-forms.smart-select name="id_filial" label="Filial"
                                    placeholder="Selecione o Tipo de Imobilizado..." :options="$filial"
                                    :selected="old('id_filial', $manutencaoImobilizado->id_filial ?? '')"
                                    asyncSearch="true" />
                            </div>

                            <div>
                                {{-- Fornecedor --}}
                                <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                    placeholder="Selecione o fornecedor..." :options="$fornecedor"
                                    :searchUrl="route('admin.api.fornecedor.search')" asyncSearch="true"
                                    :selected="old('id_fornecedor', $manutencaoImobilizado->id_fornecedor ?? '')" />
                            </div>
                        </div>

                        <div class="p-4 rounded-lg mb-6">

                            <h3 class="text-lg font-medium mb-4 text-gray-800">Imobilizados</h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">

                                <div>
                                    {{-- Código Imobilizado --}}
                                    <x-forms.smart-select name="id_produtos_imobilizados" label="Código Imobilizado"
                                        placeholder="Selecione o imobilizado..." :options="$produtosImobilizados"
                                        :searchUrl="route('admin.api.produtosimobilizados.search')"
                                        asyncSearch="true" />
                                </div>

                                <div>
                                    {{-- Tipo Manutenção Imobilizado --}}
                                    <x-forms.smart-select name="id_tipo_manutencao_imobilizado"
                                        label="Tipo Manutenção Imobilizado"
                                        placeholder="Selecione o tipo de manutenção imobilizado..."
                                        :options="$tipoManutencaoImobilizado" asyncSearch="false" />
                                </div>

                                <div class="flex justify-start items-center mt-6">
                                    {{-- Botão Adicionar Manutenção --}}
                                    <button type="button" onclick="adicionarImobilizado()"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        Adicionar Manutenção
                                    </button>
                                </div>
                            </div>
                        </div>


                        <!-- Campo hidden para armazenar os históricos -->
                        <input type="hidden" name="imobilizados" id="imobilizados_json">

                        <div class="col-span-full">
                            <table class="min-w-full divide-y divide-gray-200 tabelaImobilizado">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data Inclusão
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tipo Manutenção Imobilizado
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produto Imobilizado
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaImobilizadoBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <hr class="mt-10 mb-10">

                        <div class="p-4 rounded-lg mb-6">

                            <h3 class="text-lg font-medium mb-4 text-gray-800">Produtos</h3>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">

                                <div>
                                    {{-- Produto --}}
                                    <x-forms.smart-select name="id_produtos" label="Produto"
                                        placeholder="Selecione o produto..." :options="$produtos"
                                        :searchUrl="route('admin.api.produtos.search')" asyncSearch="true" />
                                </div>

                                <div>
                                    {{-- Código Ordem de Serviço Imobilizado --}}
                                    <label for="quantidade"
                                        class="block text-sm font-medium text-gray-700">Quantidade</label>
                                    <input type="text" id="quantidade" name="quantidade" min="1" step="1" value="1"
                                        class="text-right mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        inputmode="numeric" maxlength="15">
                                </div>

                                <div class="flex justify-start items-center mt-6">
                                    {{-- Botão Adicionar Manutenção --}}
                                    <button type="button" onclick="adicionarProduto()"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        Adicionar Produto
                                    </button>
                                </div>
                            </div>
                        </div>


                        <!-- Campo hidden para armazenar os produtos -->
                        <input type="hidden" name="produto" id="produto_json">

                        <div class="col-span-full">
                            <table class="min-w-full divide-y divide-gray-200 tabelaProduto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data Inclusão
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Produto
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantidade
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Já Solicitada
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Data Solicitação
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Situação Peças
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaProdutoBody" class="bg-white divide-y divide-gray-200">
                                    <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.ordemservicoimobilizado.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>
                            <a href="#"
                                onclick="onSolicitar({{ $manutencaoImobilizado->id_manutencao_imobilizado ?? '' }}); return false;"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                Solicitar Peças
                            </a>
                            <a href="#"
                                onclick="onFinalizar({{ $manutencaoImobilizado->id_manutencao_imobilizado ?? '' }}); return false;"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Finalizar O.S.
                            </a>

                            <button type="submit" id="submit-button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const ordemServicoPecasImobilizados = @json($ordemServicoPecasImobilizados ?? []);

    const produtosDescricao = @json($produtosDescricao ?? []);

    document.addEventListener('DOMContentLoaded', () => {
        popularProdutoTabela();
    });

    function popularProdutoTabela() {
        const tbody = document.getElementById('tabelaProdutoBody');
        tbody.innerHTML = ''; // Limpa antes de adicionar

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            return `${day}/${month}/${year}`;
        }

        function formatBoolean(value) {
            return value ? 'Sim' : 'Não';
        }

        ordemServicoPecasImobilizados.forEach((item, index) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_inclusao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${produtosDescricao[item.id_produto]}</td>
                <td class="px-6 py-4 whitespace-nowrap">${parseInt(item.quantidade)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatBoolean(item.ja_solicitada)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${formatDate(item.data_solicitacao)}</td>
                <td class="px-6 py-4 whitespace-nowrap">${item.situacao_pecas || '-'}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        ${!item.ja_solicitada ? `
                        <div class="cursor-pointer solicitar-produto" data-index="${index}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                            </svg>
                        </div>
                        ` : ''}
                        <div class="cursor-pointer delete-produto" data-index="${index}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </div>
                    </div>
                </td>
            `;

            tr.querySelector(".delete-produto")?.addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                removerProduto(index);
            });

            tr.querySelector(".solicitar-produto")?.addEventListener("click", (event) => {
                const index = parseInt(event.currentTarget.getAttribute("data-index"));
                solicitarProduto(index);
            });

            tbody.appendChild(tr);
        });

        document.getElementById('produto_json').value = JSON.stringify(ordemServicoPecasImobilizados); 
    }

    function solicitarProduto(index) {
        ordemServicoPecasImobilizados[index].ja_solicitada = true;
        ordemServicoPecasImobilizados[index].data_solicitacao = new Date();
        ordemServicoPecasImobilizados[index].situacao_pecas = 'BAIXADA';
        popularProdutoTabela();
    }

    function removerProduto(index) {
        ordemServicoPecasImobilizados.splice(index, 1);
        popularProdutoTabela();
    }

    function adicionarProduto() {
        const id_produto = document.querySelector('[name="id_produtos"]').value;
        const quantidadeInput = document.querySelector('[name="quantidade"]');
        let quantidade = parseInt(quantidadeInput.value);

        // Garante que a quantidade seja no mínimo 1
        if (isNaN(quantidade) || quantidade < 1) {
            quantidade = 1;
            quantidadeInput.value = 1; // Atualiza o campo visualmente
        }

        if (!id_produto) {
            alert('Selecione um produto para adicionar.');
            return;
        }

        const novoItem = {
            id_produto: id_produto,
            quantidade: quantidade, // Já está como inteiro
            data_inclusao: new Date(),
            ja_solicitada: false,
            data_solicitacao: null,
            situacao_pecas: null
        };

        ordemServicoPecasImobilizados.push(novoItem);
        popularProdutoTabela();
        
        // Limpa os campos após adicionar
        document.querySelector('[name="id_produtos"]').value = '';
        quantidadeInput.value = 1; // Reseta para 1
    }
</script>

@include('admin.ordemservicoimobilizado._scripts')
@endpush