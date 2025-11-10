<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo Acerto</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Nome --}}
            <div>
                <label for="descricao_tipo_acerto" class="block text-sm font-medium text-gray-700">Descrição</label>
                <input type="text" name="descricao_tipo_acerto" id="descricao_tipo_acerto"
                    value="{{ old('descricao_tipo_acerto', $tipoacerto->descricao_tipo_acerto ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('descricao_tipo_acerto')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <!-- Botões -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.tipoacertoestoque.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($tipoacerto) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>