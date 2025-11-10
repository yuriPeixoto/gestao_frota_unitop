<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Tipo Veículo') }}: {{ $tipoveiculos->descricao }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.tipoveiculos.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                    Voltar
                </x-button-link>
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Edição de Tipo
                                    Veículo</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode editar as informações de um tipo de veículo existente.
                                    Modifique o campo de descrição conforme necessário e clique em 'Atualizar' para
                                    salvar as alterações ou 'Cancelar' para retornar sem salvar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    {{--<div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">--}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Notificações de sessão -->
                    @if(session('notification'))
                    <div
                        class="mb-4 p-4 {{ session('notification')['type'] == 'success' ? 'bg-green-100 text-green-800 border-green-400' : 'bg-red-100 text-red-800 border-red-400' }} rounded-md">
                        <h3 class="font-medium">{{ session('notification')['title'] }}</h3>
                        <p>{{ session('notification')['message'] }}</p>
                    </div>
                    @endif

                    <!-- Erros de validação -->
                    @if($errors->any())
                    <div class="mb-4 p-4 bg-red-100 text-red-800 border-l-4 border-red-400 rounded-md">
                        <h3 class="font-medium">Erro de validação</h3>
                        <ul class="mt-2 list-disc list-inside">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.tipoveiculos.update', $tipoveiculos->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Tipo Veículo</h3>
                                <div class="grid md:grid-cols-1 gap-4 sm:grid-cols-1">
                                    <div>
                                        <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição
                                            <span class="text-red-500">*</span></label>
                                        <input type="text" name="descricao" id="descricao"
                                            value="{{ old('descricao', $tipoveiculos->descricao) }}"
                                            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                                            required>
                                        @error('descricao')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Botões -->
                                    <div class="flex justify-end space-x-3">
                                        <a href="{{ route('admin.tipoveiculos.index') }}"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Cancelar
                                        </a>

                                        <!-- Botão Enviar (sem Alpine.js) -->
                                        <button type="submit" id="submitButton"
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            <span id="submitText">Atualizar</span>
                                            <span id="submitSpinner" class="hidden ml-2">
                                                <svg class="animate-spin h-4 w-4 text-white"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
       {{-- </div>
    </div>--}}

    <!-- Script para mostrar spinner durante o envio -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = document.getElementById('submitButton');
            const submitSpinner = document.getElementById('submitSpinner');

            if (form && submitButton) {
                form.addEventListener('submit', function() {
                    // Mostrar o spinner
                    submitSpinner.classList.remove('hidden');
                    return true;
                });
            }
        });
    </script>
</x-app-layout>
