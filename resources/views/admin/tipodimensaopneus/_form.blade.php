<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Descrição</h3>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-1">
            <div>
                <x-bladewind::input label="Descrição" name="descricao_pneu"
                    error_message="descricao_pneu"
                    selected_value="{{ old('descricao_pneu', $tipodimensaopneus->descricao_pneu ?? '') }}" />
                @error('descricao_pneu')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3 col-span-2">
                <x-bladewind::button tag="a" href="{{ route('admin.tipodimensaopneus.index') }}" outline>
                    Cancelar
                </x-bladewind::button>
          <!-- Botão Enviar -->
                <button 
                type="submit" 
                :disabled="isSubmitting"
                :class="{ 'opacity-50': isSubmitting }"
                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                <template x-if="!isSubmitting">
                    <span>{{ isset($tipodimensaopneus) ? 'Atualizar' : 'Salvar' }}</span>
                </template>
                <template x-if="isSubmitting">
                    <span>{{ isset($tipodimensaopneus) ? 'Atualizando...' : 'Salvando...' }}</span>
                </template>
                </button>
            </div>
        </div>

  