<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($errors->any())
                        <div class="mb-4 bg-red-50 p-4 rounded">
                            <ul class="list-disc list-inside text-red-600">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex justify-between items-center mb-4">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            {{ __('Nova Filial') }}
                        </h2>
                        <div class="flex items-center space-x-2">
                            <x-button-link href="{{ route('admin.filiais.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-150">
                                Voltar
                            </x-button-link>
                            <x-help-icon
                                title="Ajuda - Criação de Filial"
                                content="Nesta tela você pode cadastrar uma nova filial. Preencha todos os campos obrigatórios com as informações da filial. Após o preenchimento, clique em 'Salvar' para adicionar a nova filial ou 'Voltar' para retornar à lista de filiais."
                            />
                        </div>
                    </div>

                    <form action="{{ route('admin.filiais.store') }}" method="POST">
                        @csrf
                        @include('admin.filiais._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
