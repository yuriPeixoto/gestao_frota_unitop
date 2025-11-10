<div class="space-y-6">
    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
        <h3 class="text-lg font-medium text-gray-900 mb-5 pb-2 border-b">Cadastro de Tanque</h3>

        <div class="grid md:grid-cols-2 gap-6 sm:grid-cols-1 mb-6">
            <!-- Linha 1: Tanque e Capacidade -->
            <!-- Nome do Tanque -->
            <div>
                <label for="tanque" class="block text-sm font-medium text-gray-700 mb-1">Tanque:</label>
                <input type="text" name="tanque" id="tanque" value="{{ old('tanque', $tanque->tanque ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('tanque')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Capacidade -->
            <div>
                <label for="capacidade" class="block text-sm font-medium text-gray-700 mb-1">Capacidade:</label>
                <input type="text" name="capacidade" id="capacidade"
                    value="{{ old('capacidade', $tanque->capacidade ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('capacidade')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-6 sm:grid-cols-1 mb-6">
            <!-- Linha 2: Posto (Fornecedor) e Combustível -->
            <!-- Posto (Fornecedor) -->
            <div>
                <x-forms.smart-select name="id_fornecedor" label="Posto:" placeholder="Selecione um posto..."
                    :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
                    :selected="old('id_fornecedor', $tanque->id_fornecedor ?? null)" asyncSearch="true"
                    minSearchLength="3" />
                @error('id_fornecedor')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Combustível -->
            <div>
                <label for="combustivel" class="block text-sm font-medium text-gray-700 mb-1">Combustível:</label>
                <select name="combustivel" id="combustivel"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Selecione um combustível</option>
                    @foreach ($formOptions['tipocombustivel'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" {{ old('combustivel', $tanque->combustivel ?? '') ==
                        $option['value'] ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                    @endforeach
                </select>
                @error('combustivel')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 sm:grid-cols-1 mb-6">
            <!-- Linha 3: Estoque Mínimo, Estoque Máximo e Filial -->
            <!-- Estoque Mínimo -->
            <div>
                <label for="estoque_minimo" class="block text-sm font-medium text-gray-700 mb-1">Estoque Mínimo:</label>
                <input type="text" name="estoque_minimo" id="estoque_minimo"
                    value="{{ old('estoque_minimo', $tanque->estoque_minimo ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('estoque_minimo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Estoque Máximo -->
            <div>
                <label for="estoque_maximo" class="block text-sm font-medium text-gray-700 mb-1">Estoque Máximo:</label>
                <input type="text" name="estoque_maximo" id="estoque_maximo"
                    value="{{ old('estoque_maximo', $tanque->estoque_maximo ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('estoque_maximo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Filial (Unidade) -->
            <div>
                <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-1">Filial / Unidade:</label>
                <select name="id_filial" id="id_filial"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Selecione uma filial</option>
                    @foreach ($formOptions['filiais'] ?? [] as $option)
                    <option value="{{ $option['value'] }}" {{ old('id_filial', $tanque->id_filial ?? '') ==
                        $option['value'] ? 'selected' : '' }}>
                        {{ $option['label'] }}
                    </option>
                    @endforeach
                </select>
                @error('id_filial')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Linha 4: Descrição Complementar (se existir) -->
        <div class="grid md:grid-cols-1 gap-6 sm:grid-cols-1">
            <!-- Descrição ATS -->
            <div>
                <label for="descricao_ats" class="block text-sm font-medium text-gray-700 mb-1">Descrição
                    Complementar:</label>
                <input type="text" name="descricao_ats" id="descricao_ats"
                    value="{{ old('descricao_ats', $tanque->descricao_ats ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('descricao_ats')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end space-x-3 pt-5 mt-5 border-t">
            <button type="button" onclick="window.location.href='{{ route('admin.tanques.index') }}'"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Cancelar
            </button>

            <!-- Botão Enviar -->
            <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <template x-if="!isSubmitting">
                    <span>{{ isset($tanque) ? 'Atualizar' : 'Salvar' }}</span>
                </template>
                <template x-if="isSubmitting">
                    <div class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <span>{{ isset($tanque) ? 'Atualizando...' : 'Salvando...' }}</span>
                    </div>
                </template>
            </button>
        </div>
    </div>
</div>