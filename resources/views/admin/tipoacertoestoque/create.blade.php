<!-- Drawer lateral -->
<div id="drawer"
    class="fixed top-0 right-0 w-96 h-full bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out z-50">
    <div class="p-6 overflow-y-auto h-full flex flex-col">

        <!-- Título -->
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Novo Tipo Acerto</h2>

        <!-- Conteúdo -->
        <div id="drawer-content" class="flex-1">
            <form action="{{ route('admin.tipoacertoestoque.store') }}" method="POST" class="flex flex-col h-full">
                @csrf
                @method('POST')

                <!-- Campo -->
                <div class="mb-6">
                    <label for="descricao_tipo_acerto" class="block text-sm font-medium text-gray-700">Descrição</label>
                    <input type="text" name="descricao_tipo_acerto" id="descricao_tipo_acerto"
                        value="{{ old('descricao_tipo_acerto', $tipoacerto->descricao_tipo_acerto ?? '') }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('descricao_tipo_acerto')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="mt-auto flex justify-end space-x-3">
                    <button type="button" onclick="closeDrawer()"
                        class="px-4 py-2 border border-gray-300 rounded-md font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ isset($tipoacerto) ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>