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
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Checklist</h3>
    <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
    <div>
        <x-bladewind::input label="Nome" name="nome"
            error_message="nome"
            selected_value="{{ old('nome', $checklist->nome ?? '') }}" 
        />

        <x-bladewind::input label="Descrição" name="descricao"
            error_message="descricao"
            selected_value="{{ old('descricao', $checklist->descricao ?? '') }}"
        />

        <x-bladewind::select
            name="tipo_checklist_id"
            selected_value="{{ old('tipo_checklist_id', $checklist->tipo_checklist_id ?? '') }}"
            error_message="tipo_checklist_id"
            label="Tipo de checklist"
            :data="$tipoChecklist"  
        />

        @error('descricao_tipo_borracha')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">
        <x-bladewind::button tag="a" href="{{ route('admin.checklist.index') }}" outline>
            Cancelar
        </x-bladewind::button>
        <button type="submit" :disabled="isSubmitting" :class="{ 'opacity-50': isSubmitting }"
            class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
            <template x-if="!isSubmitting">
                <span>{{ isset($checklist) ? 'Atualizar' : 'Salvar' }}</span>
            </template>
            <template x-if="isSubmitting">
                <span>{{ isset($checklist) ? 'Atualizando...' : 'Salvando...' }}</span>
            </template>
        </button>
    </div>

    <div class="bg-gray-50 p-4 bg-white rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Selecione as perguntas</h3>
    <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Id
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Descrição
                </th>

                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Selecionar
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Ações</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($perguntas as $result)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $result->id}}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $result->descricao}}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        {{ $result->tipo}}
                    </td>
                    <td class="px-8 py-4 whitespace-nowrap text-sm text-gray-500">
                       <input type="checkbox" name="perguntas[]" value="{{$result->id}}" {{ isset($pergunta) && in_array($result->id, $pergunta->pluck('id')->toArray()) ? 'checked' : '' }}/>
                    </td>
               </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Nenhum checklist cadastrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </tbody>
    </table>
    <div class="mt-4">
        {{ $perguntas->links() }}
    </div>
</div>
<script>


</script>
