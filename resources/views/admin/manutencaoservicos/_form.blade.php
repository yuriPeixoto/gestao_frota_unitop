<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <!-- Abas -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <!-- Aba Cadastro Serviço (ativa por padrão) -->
                        <button onclick="openTab(event, 'cadastro')"
                            class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-indigo-500 text-indigo-600">
                            Serviço
                        </button>
                        <!-- Aba Peças Relacionadas -->
                        <button onclick="openTab(event, 'servicos')"
                            class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Peças Relacionadas
                        </button>
                    </nav>
                </div>
            </div>

            <!-- Conteúdo das Abas -->
            <div x-data="manutencaoGeral()">
                <!-- Formulário Principal -->
                <form method="POST" action="{{ $action }}" class="space-y-4" id="formularioPrincipal">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif

                    <input type="hidden" name="servicos" x-model="JSON.stringify(servicosItems)">
                    <input type="hidden" name="pecas" x-model="JSON.stringify(pecasItems)">

                    <!-- Conteúdo da Aba Serviço -->
                    <div id="cadastro" class="tab-content">
                        <!-- Campos do Serviço -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="id_servico" class="block text-sm font-medium text-gray-700">Cód.
                                        Serviço</label>
                                    <input type="number" id="id_servico" name="id_servico" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required value="{{ old('id_servico', $manutencaoConfig->id_servico ?? '') }}"
                                        readonly>
                                </div>

                                <div>
                                    <label for="id_filial"
                                        class="block text-sm font-medium text-gray-700">Filial</label>
                                    <select id="id_filial" name="id_filial" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Selecione...</option>
                                        @foreach ($filiais as $filial)
                                        <option value="{{ $filial->id }}" {{ old('id_filial', $manutencaoConfig->
                                            id_filial ?? '') == $filial->id ? 'selected' : '' }}>
                                            {{ $filial->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <div>
                                    <label for="id_grupo" class="block text-sm font-medium text-gray-700">Grupo
                                        Serviço</label>
                                    <select id="id_grupo" name="id_grupo" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Selecione...</option>
                                        @foreach ($grupoServico as $grupo)
                                        <option value="{{ $grupo->id_grupo }}" {{ old('id_grupo', $manutencaoConfig->
                                            id_grupo ?? '') == $grupo->id_grupo ? 'selected' : '' }}>
                                            {{ $grupo->descricao_grupo }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="descricao_servico"
                                        class="block text-sm font-medium text-gray-700">Descrição do Serviço</label>
                                    <input type="text" id="descricao_servico" name="descricao_servico" step="0.01"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        required
                                        value="{{ old('descricao_servico', $manutencaoConfig->descricao_servico ?? '') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Serviço Ativo</label>
                                    <div class="mt-1 inline-flex border border-gray-300 rounded-lg overflow-hidden">
                                        <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                            <input type="radio" name="ativo_servico" value="true" class="hidden peer" {{
                                                old('ativo_servico', $manutencaoConfig->ativo_servico ?? '') == 'true' ?
                                            'checked' : '' }}>
                                            <span
                                                class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Sim</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                            <input type="radio" name="ativo_servico" value="false" class="hidden peer"
                                                {{ old('ativo_servico', $manutencaoConfig->ativo_servico ?? '') ==
                                            'false' ? 'checked' : '' }}>
                                            <span
                                                class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Não</span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                <div>
                                    <label for="id_manutencao"
                                        class="block text-sm font-medium text-gray-700">Manutenção</label>
                                    <select id="id_manutencao" name="id_manutencao"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Selecione...</option>
                                        @foreach ($manutencoes as $manutencao)
                                        <option value="{{ $manutencao->id_manutencao }}" {{ old('id_manutencao',
                                            $manutencaoConfig->id_manutencao ?? '') == $manutencao->id_manutencao ?
                                            'selected' : '' }}>
                                            {{ $manutencao->descricao_manutencao }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="hora_servico" class="block text-sm font-medium text-gray-700">Tempo do
                                        Serviço</label>
                                    <input type="time" id="hora_servico" name="hora_servico"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Axiliar</label>
                                    <div class="mt-1 inline-flex border border-gray-300 rounded-lg overflow-hidden">
                                        <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                            <input type="radio" name="auxiliar" value="true" class="hidden peer" {{
                                                old('auxiliar', $manutencaoConfig->auxiliar ?? '') == 'true' ? 'checked'
                                            : '' }}>
                                            <span
                                                class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Sim</span>
                                        </label>
                                        <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                            <input type="radio" name="auxiliar" value="false" class="hidden peer" {{
                                                old('auxiliar', $manutencaoConfig->auxiliar ?? '') == 'false' ?
                                            'checked' : '' }}>
                                            <span
                                                class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Não</span>
                                        </label>
                                    </div>
                                </div>

                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                <div>
                                    <x-forms.smart-select name="id_categoria" label="Categoria Serviço"
                                        placeholder="Selecione a categoria..." :options="$tipoCategoria" :selected="''"
                                        asyncSearch="false" value="{{request('id_categoria')}}" />
                                </div>

                                <div class="flex justify-start mt-4">
                                    <button type="button" x-on:click="adicionarItem"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4" />
                                        </svg>
                                        Adicionar
                                    </button>
                                </div>

                            </div>
                        </div>

                        <!-- Tabela de Itens do Serviço -->
                        <div class="mt-6">
                            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-700">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th scope="col" class="py-3 px-6">Data Inclusão</th>
                                            <th scope="col" class="py-3 px-6">Data Alteração</th>
                                            <th scope="col" class="py-3 px-6">Cód. Categoria</th>
                                            <th scope="col" class="py-3 px-6">Categoria</th>
                                            <th scope="col" class="py-3 px-6">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in servicosItems" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="py-3 px-6" x-text="item.data_inclusao"></td>
                                                <td class="py-3 px-6" x-text="item.data_alteracao"></td>
                                                <td class="py-3 px-6" x-text="item.id_categoria"></td>
                                                <td class="py-3 px-6" x-text="item.descricao_categoria"></td>
                                                <td class="py-3 px-6">
                                                    <div class="flex space-x-2">
                                                        <button type="button" x-on:click="editarItem(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" x-on:click="removerItem(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="servicosItems.length === 0" class="bg-white border-b">
                                            <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhum item
                                                adicionado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Conteúdo da Aba Peças Relacionadas -->
                    <div id="servicos" class="tab-content" style="display: none;">
                        <!-- Campos das Peças -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <div>
                                <x-forms.smart-select name="id_produto" label="Produto"
                                    placeholder="Selecione o produto..." :options="$produtos" :selected="''"
                                    asyncSearch="false" value="{{request('id_produto')}}"
                                    :searchUrl="route('admin.api.produtos.search')" />
                            </div>
                            <div class="flex justify-start mt-4">
                                <button type="button" x-on:click="adicionarItemProduto"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4" />
                                    </svg>
                                    Adicionar
                                </button>
                            </div>
                        </div>

                        <!-- Tabela de Itens das Peças -->
                        <div class="mt-6">
                            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-700">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th scope="col" class="py-3 px-6">Data Inclusão</th>
                                            <th scope="col" class="py-3 px-6">Data Alteração</th>
                                            <th scope="col" class="py-3 px-6">Cód. Produto</th>
                                            <th scope="col" class="py-3 px-6">Descrição</th>
                                            <th scope="col" class="py-3 px-6">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in pecasItems" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="py-3 px-6" x-text="item.data_inclusao"></td>
                                                <td class="py-3 px-6" x-text="item.data_alteracao"></td>
                                                <td class="py-3 px-6" x-text="item.id_produto"></td>
                                                <td class="py-3 px-6" x-text="item.descricao_produto"></td>
                                                <td class="py-3 px-6">
                                                    <div class="flex space-x-2">
                                                        <button type="button" x-on:click="editarItemProduto(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button" x-on:click="removerItemProduto(index)"
                                                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="pecasItems.length === 0" class="bg-white border-b">
                                            <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhum item
                                                adicionado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('admin.manutencaoservicos.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para controle das abas -->
<script>
    function openTab(event, tabName) {
        // Oculta todos os conteúdos das abas
        const tabContents = document.querySelectorAll('.tab-content');
        tabContents.forEach(content => {
            content.style.display = 'none';
        });

        // Remove a classe ativa de todas as abas
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach(link => {
            link.classList.remove('border-indigo-500', 'text-indigo-600');
            link.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });

        // Exibe o conteúdo da aba clicada
        document.getElementById(tabName).style.display = 'block';

        // Adiciona a classe ativa à aba clicada
        event.currentTarget.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        event.currentTarget.classList.add('border-indigo-500', 'text-indigo-600');
    }

    // Ativa a aba "Cadastro Serviço" ao carregar a página
    document.addEventListener('DOMContentLoaded', function () {
        const defaultTab = document.querySelector('.tab-link'); // Seleciona a primeira aba
        const defaultTabContent = document.getElementById('cadastro'); // Seleciona o conteúdo da primeira aba

        if (defaultTab && defaultTabContent) {
            defaultTab.classList.add('border-indigo-500', 'text-indigo-600');
            defaultTabContent.style.display = 'block';
        }
    });

</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('manutencaoGeral', () => ({
            servicosItems: @json($manutencaoConfig->categorias ?? []),

            pecasItems: @json($pecasFormatadas ?? []),

            novoItem: { id_categoria: '', descricao_categoria: '', data_inclusao: '', data_alteracao: '' },
            novoItemProduto: { id_produto: '', descricao_produto: '', data_inclusao: '', data_alteracao: '' },

            adicionarItem() {
                const select = document.querySelector('[name="id_categoria"]');
                const idCategoria = select?.value;

                const descricao =
                    select?.closest('div')?.querySelector('[x-text="selectedLabels[0]"]')?.textContent?.trim() ||
                    select?.closest('div')?.querySelector('span.block.truncate.text-gray-900')?.textContent?.trim() ||
                    '';

                if (!idCategoria) {
                    alert('Selecione uma categoria.');
                    return;
                }

                this.servicosItems.push({
                    id_categoria: idCategoria,
                    descricao_categoria: descricao,
                    data_inclusao: new Date().toLocaleString(),
                    data_alteracao: new Date().toLocaleString(),
                });
            },

            adicionarItemProduto() {
                const select = document.querySelector('[name="id_produto"]');
                const idProduto = select?.value;

                const descricao =
                    select?.closest('div')?.querySelector('[x-text="selectedLabels[0]"]')?.textContent?.trim() ||
                    select?.closest('div')?.querySelector('span.block.truncate.text-gray-900')?.textContent?.trim() ||
                    '';

                if (!idProduto) {
                    alert('Selecione um produto.');
                    return;
                }

                this.pecasItems.push({
                    id_produto: idProduto,
                    descricao_produto: descricao,
                    data_inclusao: new Date().toLocaleString(),
                    data_alteracao: new Date().toLocaleString(),
                });
            },

            editarItem(index) {
                this.novoItem = { ...this.servicosItems[index] };
                this.servicosItems.splice(index, 1);
            },

            editarItemProduto(index) {
                this.novoItemProduto = { ...this.pecasItems[index] };
                this.pecasItems.splice(index, 1);
            },

            removerItem(index) {
                this.servicosItems.splice(index, 1);
            },

            removerItemProduto(index) {
                this.pecasItems.splice(index, 1);
            }
        }))
    })
</script>