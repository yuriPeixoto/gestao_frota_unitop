<div class="space-y-6">
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-forms.smart-select name="id_filial" label="Filial" :options="$filiais" :selected="old('id_filial', $estoque->id_filial ?? '')" />

        <x-forms.input label="Descrição do estoque" name="descricao_estoque"
            value="{{ old('descricao_estoque', $estoque->descricao_estoque ?? '') }}" />

        <div class="flex justify-end space-x-3 col-span-full">
            <x-forms.button href="{{ route('admin.estoque.index') }}" variant="outlined">
                Cancelar
            </x-forms.button>
            <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                <template x-if="!isSubmitting">
                    <span>{{ isset($estoque) ? 'Atualizar' : 'Salvar' }}</span>
                </template>
                <template x-if="isSubmitting">
                    <span>{{ isset($checklist) ? 'Atualizando...' : 'Salvando...' }}</span>
                </template>
            </button>
        </div>
    </div>
</div>
