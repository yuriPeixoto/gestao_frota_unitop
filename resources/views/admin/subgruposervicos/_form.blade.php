<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Subgrupo</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Nome --}}
            <div>
                <label for="descricao_subgrupo" class="block text-sm font-medium text-gray-700">Descrição
                    Subgrupo</label>
                <input type="text" name="descricao_subgrupo" id="descricao_subgrupo"
                    value="{{ old('descricao_subgrupo', $subgruposervico->descricao_subgrupo ?? '') }}"
                    class=" mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('descricao_subgrupo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="id_grupo_servico" class="block text-sm font-medium text-gray-700">Grupo</label>
                <select id="id_grupo_servico" name="id_grupo_servico"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione um grupo</option>
                    @foreach($grupos as $grupo)
                    <option value="{{ $grupo->id_grupo }}" {{ old('id_grupo_servico', $subgruposervico->id_grupo_servico
                        ?? '') == $grupo->id_grupo ? 'selected' : '' }}>
                        {{ $grupo->descricao_grupo }}
                    </option>
                    @endforeach
                </select>
                @error('id_grupo_servico')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>



            <!-- Botões -->
            <div class="col-span-1 sm:col-span-3 flex justify-end space-x-3 w-full">
                <a href="{{ route('admin.subgruposervicos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($subgruposervico) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>