<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo Manutenção</h3>
        <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
            <div>
                <x-forms.input label="Descrição" name="tipo_manutencao_descricao"
                    value="{{ old('tipo_manutencao_descricao', $tipomanutencoes->tipo_manutencao_descricao ?? '') }}" />
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.tipomanutencoes.index') }}"
                    class="px-4 py-2 text-blue-500 border border-blue-500 rounded hover:bg-blue-600">
                    Cancelar
                </a>
                <!-- Botão Enviar -->
                <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                    <template x-if="!isSubmitting">
                        <span>{{ isset($tipomanutencoes) ? 'Atualizar' : 'Salvar' }}</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>{{ isset($tipomanutencoes) ? 'Atualizando...' : 'Salvando...' }}</span>
                    </template>
                </button>
            </div>
        </div>
