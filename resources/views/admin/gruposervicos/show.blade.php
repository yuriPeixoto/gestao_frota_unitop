<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes do Grupo de Serviço') }}
            </h2>
            <div class="flex items-center space-x-2">
                @can('editar_gruposervico')
                <a href="{{ route('admin.gruposervicos.edit', $grupoServico) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Editar
                </a>
                @endcan
                <a href="{{ route('admin.gruposervicos.index') }}"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Informações do Grupo</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Código</p>
                            <p class="font-medium text-lg">{{ $grupoServico->id_grupo }}</p>
                        </div>
                        <div class="md:col-span-1">
                            <p class="text-sm text-gray-500">Descrição</p>
                            <p class="font-medium text-lg">{{ $grupoServico->descricao_grupo }}</p>
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
                                {{ $grupoServico->data_inclusao ? $grupoServico->data_inclusao->format('d/m/Y H:i:s') : 'Não informado' }}
                            </p>
                        </div>
                        @if($grupoServico->data_alteracao)
                        <div>
                            <p class="text-sm text-gray-500">Data de Alteração</p>
                            <p class="font-medium font-mono">
                                {{ $grupoServico->data_alteracao->format('d/m/Y H:i:s') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Seção: Subgrupos Relacionados (se existirem) --}}
                @if($grupoServico->subgrupoServico && $grupoServico->subgrupoServico->count() > 0)
                <div class="bg-gradient-to-br from-white to-green-50 rounded-xl p-6 shadow-lg border border-green-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Subgrupos Relacionados</h3>
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $grupoServico->subgrupoServico->count() }} {{ $grupoServico->subgrupoServico->count() === 1 ? 'item' : 'itens' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($grupoServico->subgrupoServico as $subgrupo)
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                            <p class="font-medium text-gray-900">{{ $subgrupo->descricao_subgrupo ?? 'Sem descrição' }}</p>
                            <p class="text-sm text-gray-500">ID: {{ $subgrupo->id_subgrupo }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>