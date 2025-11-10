<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="manutencaoServico(@json($manutencaoConfig->servicosPlanejamento ?? []))">
                <form id="manutencaoServico" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif

                    <!-- Campo oculto para os serviços -->
                    <input type="hidden" name="servicos" x-model="JSON.stringify(items)">

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="id_planejamento_manutencao"
                                    class="block text-sm font-medium text-gray-700">Cód. categoria planejamento
                                    manutenção</label>
                                <input type="text" id="id_planejamento_manutencao" name="id_planejamento_manutencao"
                                    readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->id_planejamento_manutencao ?? '' }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <x-forms.smart-select name="id_manutencao" label="Planejamento"
                                    placeholder="Selecione o tipo da Manutenção..." 
                                    :searchUrl="route('admin.api.manutencao.search')"
                                    :options="$planejamentos" asyncSearch="false"
                                    value="{{ request('id_manutencao') }}" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Planejamento Ativo</label>
                                <div class="mt-1 inline-flex border border-gray-300 rounded-lg overflow-hidden">
                                    <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                        <input type="radio" name="status_planejamento" value="true" class="hidden peer"
                                            {{ old('status_planejamento', $manutencaoConfig->status_planejamento ?? '')
                                        == 'true' ? 'checked' : '' }}>
                                        <span
                                            class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Sim</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                        <input type="radio" name="status_planejamento" value="false" class="hidden peer"
                                            {{ old('status_planejamento', $manutencaoConfig->status_planejamento ?? '')
                                        == 'false' ? 'checked' : '' }}>
                                        <span
                                            class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Não</span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-forms.smart-select id="id_servico" name="id_servico" label="Serviços"
                                    placeholder="Selecione o serviço..." 
                                    :searchUrl="route('admin.api.servico.search')"
                                    :options="$servicos" asyncSearch="false"
                                    value="{{ request('id_servico') }}" />
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

                        <!-- Tabela de Itens -->
                        <div class="mt-6">
                            <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                                <table class="w-full text-sm text-left text-gray-700">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th class="py-3 px-6">Data inclusão</th>
                                            <th class="py-3 px-6">Data alteração</th>
                                            <th class="py-3 px-6">Serviço</th>
                                            <th class="py-3 px-6">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="py-3 px-6" x-text="item.data_inclusao"></td>
                                                <td class="py-3 px-6" x-text="item.data_alteracao"></td>
                                                <td class="py-3 px-6" x-text="item.descricao_servico"></td>
                                                <td class="py-3 px-6">
                                                    <div class="flex space-x-2">
                                                        <button type="button" x-on:click="editarItem(index)"
                                                            class="p-1 bg-indigo-600 text-white rounded-full hover:bg-indigo-700">
                                                            Editar
                                                        </button>
                                                        <button type="button" x-on:click="removerItem(index)"
                                                            class="p-1 bg-red-600 text-white rounded-full hover:bg-red-700">
                                                            Remover
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>

                                        <tr x-show="items.length === 0" class="bg-white border-b">
                                            <td colspan="4" class="py-3 px-6 text-center text-gray-500">Nenhum item
                                                adicionado</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" x-on:click="limparFormulario"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar Formulário
                        </button>

                        <a href="{{ route('admin.manutencaoservico.index') }}"
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

@push('scripts')
<script>
    function manutencaoServico(existingItems = []) {
        return {
            items: existingItems.map(item => ({
                id_servico: item.id_servico,
                descricao_servico: item.servico?.descricao_servico ?? '',
                data_inclusao: item.data_inclusao,
                data_alteracao: item.data_alteracao,
            })),
            novoItem: {
                data_inclusao: new Date().toLocaleString(),
                data_alteracao: new Date().toLocaleString(),
                descricao_servico: '',
                id_servico: ''
            },

            adicionarItem() {
                const select = document.querySelector('[name="id_servico"]');

                // Pode precisar ajustar dependendo do HTML interno do componente
                const idServico = select?.value;
                const descricaoServico =
                    select?.closest('div')?.querySelector('[x-text="selectedLabels[0]"]')?.textContent?.trim() 
                    select?.closest('div')?.querySelector('span.block.truncate.text-gray-900')?.textContent?.trim() 
                    '';

                if (!idServico) {
                    alert('Por favor, selecione um serviço.');
                    return;
                }

                this.items.push({
                    id_servico: idServico,
                    descricao_servico: descricaoServico,
                    data_inclusao: new Date().toLocaleString(),
                    data_alteracao: new Date().toLocaleString(),
                });

                this.limparNovoItem();
            },

            editarItem(index) {
                this.novoItem = { ...this.items[index] };
                this.items.splice(index, 1);
            },

            removerItem(index) {
                this.items.splice(index, 1);
            },

            limparNovoItem() {
                this.novoItem = {
                    data_inclusao: new Date().toLocaleString(),
                    data_alteracao: new Date().toLocaleString(),
                    descricao_servico: '',
                    id_servico: ''
                };
            }
        }
    }
</script>
@endpush