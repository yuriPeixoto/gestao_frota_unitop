<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo de Manutenção</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-1">
            {{-- Nome --}}
            <div>
                <x-bladewind::input label="Descrição" name="descricao" error_message="descricao"
                    selected_value="{{ old('descricao', $tipomanutencaoimobilizado->descricao ?? '') }}" />
                @error('descricao')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3">
                <x-bladewind::button tag="a" href="{{ route('admin.tipomanutencaoimobilizados.index') }}" outline>
                    Cancelar
                </x-bladewind::button>
                   <!-- Botão Enviar -->
                   <button 
                   type="submit" 
                   :disabled="isSubmitting"
                   :class="{ 'opacity-50': isSubmitting }"
                   class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                   <template x-if="!isSubmitting">
                       <span>{{ isset($tipomanutencaoimobilizado) ? 'Atualizar' : 'Salvar' }}</span>
                   </template>
                   <template x-if="isSubmitting">
                       <span>{{ isset($tipomanutencaoimobilizado) ? 'Atualizando...' : 'Salvando...'  }}</span>
                   </template>
                   </button>
            </div>
        </div>