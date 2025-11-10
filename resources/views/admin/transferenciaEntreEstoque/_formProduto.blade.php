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
    <div class="flex gap-4">
        <div class="w-6/12">
            <x-bladewind::select
                name="id_produto"
                selected_value="{{ old('id_produto', $transferenciaItem->id_produto ?? '') }}"
                error_message="id_produto"
                label="Produto"
                :data="$produtos"  
            />
        </div>
        <x-bladewind::input 
            label="Quantidade Solicitada" 
            name="quantidade_solicitada"
            required
            type="number"
            error_message="quantidade_solicitada"
            selected_value="{{ old('quantidade_solicitada', $transferenciaItem->quantidade_solicitada ?? '') }}" 
        />
        <x-bladewind::input 
            label="Quantidade Recebida" 
            name="quantidade"
            required
            type="number"
            error_message="quantidade"
            selected_value="{{ old('quantidade', $transferenciaItem->quantidade ?? '') }}" 
        />
        <x-bladewind::input 
            label="Data Recebimento" 
            name="data_inclusao"
            type="date"
            error_message="data_inclusao"
            selected_value="{{ old('data_inclusao', $transferenciaItem->data_inclusao ?? '') }}" 
        />
    </div>
    <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
            <template x-if="!isSubmitting">
                <span>{{ isset($transferenciaItem) ? 'Atualizar' : 'Salvar' }}</span>
            </template>
            <template x-if="isSubmitting">
                <span>{{ isset($transferenciaItem) ? 'Atualizando...' : 'Salvando...' }}</span>
            </template>
        </button>
</div>

