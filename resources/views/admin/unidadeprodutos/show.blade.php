<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes da Unidade de Produto') }}
            </h2>
            <div class="flex items-center space-x-2">
                @can('editar_unidadeproduto')
                <a href="{{ route('admin.unidadeprodutos.edit', $unidadeProduto) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                @endcan
                <a href="{{ route('admin.unidadeprodutos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="max-w-7xl mx-auto space-y-6">

                {{-- Seção: Informações Básicas --}}
                <div class="bg-gradient-to-br from-white to-blue-50 rounded-xl p-6 shadow-lg border border-blue-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Informações da Unidade</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Código</p>
                            <p class="font-medium text-lg">{{ $unidadeProduto->id_unidade_produto }}</p>
                        </div>
                        <div class="md:col-span-1">
                            <p class="text-sm text-gray-500">Descrição</p>
                            <p class="font-medium text-lg">{{ $unidadeProduto->descricao_unidade }}</p>
                        </div>
                    </div>
                </div>

                {{-- Seção: Controle de Datas --}}
                <div class="bg-gradient-to-br from-white to-amber-50 rounded-xl p-6 shadow-lg border border-amber-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Controle de Datas</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Data de Inclusão</p>
                            <p class="font-medium font-mono">
                                {{ $unidadeProduto->data_inclusao ? $unidadeProduto->data_inclusao->format('d/m/Y H:i:s') : 'Não informado' }}
                            </p>
                        </div>
                        @if($unidadeProduto->data_alteracao)
                        <div>
                            <p class="text-sm text-gray-500">Data de Alteração</p>
                            <p class="font-medium font-mono">
                                {{ $unidadeProduto->data_alteracao->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>