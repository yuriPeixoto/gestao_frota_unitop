<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo Combustível</h3>
        <div class="grid md:grid-cols-3 gap-4 sm:grid-cols-1">
            <div>
                <x-forms.input name="descricao" label="Descrição"
                    value="{{ old('descricao', $tipocombustivel->descricao ?? '') }}" />
            </div>
            <div>
                <x-forms.input name="unidade_medida" label="Unidade Medida"
                    value="{{ old('unidade_medida', $tipocombustivel->unidade_medida ?? '') }}" />
            </div>
            <div>
                <x-forms.input name="ncm" label="NCM" value="{{ old('ncm', $tipocombustivel->ncm ?? '') }}" />
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3 col-span-full">
                <x-bladewind::button tag="a" href="{{ route('admin.tipocombustiveis.index') }}" outline>
                    Cancelar
                </x-bladewind::button>
                <!-- Botão Enviar -->
                <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                    <template x-if="!isSubmitting">
                        <span>{{ isset($tipocombustivel) ? 'Atualizar' : 'Salvar' }}</span>
                    </template>
                    <template x-if="isSubmitting">
                        <span>{{ isset($tipocombustivel) ? 'Atualizando...' : 'Salvando...' }}</span>
                    </template>
                </button>
            </div>
        </div>
