<x-app-layout>
    @if ($errors->any())
        <div class="bg-red-100 text-red-800 p-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <x-slot name="header">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Criar perguntas') }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.checklist.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    Voltar
                </x-button-link>
                <x-help-icon title="Ajuda - Criação de Tipo de Categoria"
                    content="Nesta tela você pode cadastrar um novo tipo de Categoria. Preencha todos os campos obrigatórios com as informações do tipo. Após o preenchimento, clique em 'Salvar' para adicionar o novo registro ou 'Voltar' para retornar à lista de dimensões." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white p-6 shadow-sm sm:rounded-lg">
        <form action="{{ route('admin.checklist.storeQuestion', $id) }}" method="POST"
            x-data="{ isSubmitting: false }"
            @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
            @csrf

            <x-bladewind::input label="Descrição" name="descricao"
            error_message="descricao"
            selected_value="{{ old('descricao', $checklist->descricao ?? '') }}" 
        />

            <x-bladewind::select
                name="tipo"
                selected_value="{{ old('tipo_checklist_id', $checklist->tipo_checklist_id ?? '') }}"
                error_message="tipo_checklist_id"
                label="Tipo Pergunta"
                required
                :data="$tipoPergunta"  
            />

            <div class="flex justify-end space-x-3 col-span-full">
                <x-bladewind::button tag="a" href="{{ route('admin.checklist.index') }}" outline>
                    Voltar
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
        </form>

        <table class="min-w-full divide-y mt-6 divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Código
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        descrição
                    </th>

                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Ações</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($colunaCheckLists as $result)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $result->id}}
                    </td>
                    <td class="px-6 py-4  text-sm text-gray-500">
                        {{ $result->descricao}}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $result->tipo}}
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
        </table>
        <div class="mt-4">
            {{ $colunaCheckLists->links() }}
        </div>
    </div>
</x-app-layout>