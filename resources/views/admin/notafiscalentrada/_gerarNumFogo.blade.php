<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gerar Número de Fogo') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-help-icon title="Ajuda - Gerar Número de Fogo"
                    content="Nesta tela você pode gerar um novo número de fogo para os modelos pré-carregados. 
                    Preencha todos os campos obrigatórios com as informações. Após o preenchimento, clique em 'Gerar Número de fogo' para adicionar o novos registros." />
            </div>
        </div>
    </x-slot>

    <div>
        <div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('admin.notafiscalentrada.lancarNumFogo', ['id' => $id]) }}" method="POST"
                        x-data="{ isSubmitting: false }"
                        @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-4 sm:grid-cols-1">
                            <div>
                                <x-forms.input label="Código Produto Nota Fiscal" name="id_nota_fiscal_produtos"
                                    readonly value="{{ old('id_nota_fiscal_produtos', $id) }}" />
                            </div>

                            <div>
                                <label for="modelo_pneu" class="block text-sm font-medium text-gray-700 mb-1">
                                    Informe Modelo do Pneu <span class="text-red-500">*</span>
                                </label>
                                <select x-ref="select" name="modelo_pneu" id="modelo_pneu" required
                                    @change="$refs.hiddenInput.value = $event.target.selectedOptions[0].dataset.idProduto"
                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Selecione um modelo...</option>
                                    @foreach ($nfProdutos as $produto)
                                        <option value="{{ $produto['value'] }}"
                                            data-id-produto="{{ $produto['id_nota_fiscal_produtos'] }}">
                                            {{ $produto['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Campo hidden -->
                            <input type="hidden" name="id_nota_fiscal_produtos" x-ref="hiddenInput" value="">

                        </div>

                        <div class="flex justify-right mt-4">
                            <x-forms.button button-type="submit">
                                <x-icons.tire class="w-6 mr-2" />
                                Gerar Número de Fogo
                            </x-forms.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
