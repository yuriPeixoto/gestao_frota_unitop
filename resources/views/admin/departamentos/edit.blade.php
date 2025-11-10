<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Departamento') }}: {{ $departamento->descricao_departamento }}
            </h2>
            <div class="flex items-center space-x-2">
                <x-button-link href="{{ route('admin.departamentos.index') }}"
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
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Edição de
                                    Departamento</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode editar as informações de um departamento existente. Modifique
                                    os campos conforme necessário e clique em 'Atualizar' para salvar as alterações ou
                                    'Cancelar' para retornar sem salvar.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

   {{-- <div class="py-6">
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

                    <form action="{{ route('admin.departamentos.update', $departamento->id_departamento) }}"
                        method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.departamentos._form')
                    </form>
                </div>
            </div>
       {{-- </div>
    </div>--}}
</x-app-layout>
