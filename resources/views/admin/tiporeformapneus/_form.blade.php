<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo Reforma</h3>
        <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
            <div>
                <x-forms.input label="Descrição" name="descricao_tipo_reforma"
                    value="{{ old('descricao_tipo_reforma', $tiporeformapneus->descricao_tipo_reforma ?? '') }}" />
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3 col-span-full">
                <x-forms.button type="secondary" href="{{ route('admin.tiporeformapneus.index') }}" variant="outlined">
                    Cancelar
                </x-forms.button>
                <!-- Botão Enviar -->
                <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                    <template x-if="!isSubmitting">
                        <span>{{ isset($tiporeformapneu) ? 'Atualizar' : 'Salvar' }}</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>{{ isset($tiporeformapneu) ? 'Atualizando...' : 'Salvando...' }}</span>
                    </template>
                </button>
            </div>
        </div>
