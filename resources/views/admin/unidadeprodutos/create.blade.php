<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova Unidade de Produto') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.unidadeprodutos.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    <x-icons.arrow-back class="h-4 w-4 mr-2" />
                    Voltar
                </a>

                {{-- Help Icon --}}
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm font-medium text-gray-900">Ajuda - Nova Unidade</p>
                                <p class="mt-1 text-xs text-gray-500">
                                    Preencha a descrição da nova unidade de produto. A descrição deve ser única no
                                    sistema.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <form action="{{ route('admin.unidadeprodutos.store') }}" method="POST" x-data="{ isSubmitting: false }"
                @submit.prevent="if (!isSubmitting) { isSubmitting = true; $el.submit(); }">
                @csrf

                <div class="space-y-6">
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Dados da Unidade</h3>

                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="descricao_unidade" class="block text-sm font-medium text-gray-700">
                                    Descrição da Unidade *
                                </label>
                                <input type="text" id="descricao_unidade" name="descricao_unidade" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('descricao_unidade') border-red-300 @enderror"
                                    value="{{ old('descricao_unidade') }}"
                                    placeholder="Ex: Litro, Quilograma, Metro...">

                                @error('descricao_unidade')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="flex justify-end space-x-3 mt-6">
                            <a href="{{ route('admin.unidadeprodutos.index') }}"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancelar
                            </a>

                            <button type="submit" :disabled="isSubmitting"
                                :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <template x-if="!isSubmitting">
                                    <span>Salvar</span>
                                </template>
                                <template x-if="isSubmitting">
                                    <span>Salvando...</span>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>