<form action="{{ route('admin.tipoborrachapneus.update', $tipoborrachapneus->id_tipo_borracha ) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-6">
        <label for="descricao_tipo_borracha" class="block text-sm font-medium text-gray-700">Descrição</label>
        <input type="text" name="descricao_tipo_borracha" id="descricao_tipo_borracha"
            value="{{ old('descricao_tipo_borracha', $tipoborrachapneus->descricao_tipo_borracha) }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="flex justify-end space-x-3">
        <button type="button" onclick="closeDrawerEdit()" class="px-4 py-2 border rounded-md">Cancelar</button>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Atualizar</button>
    </div>
</form>