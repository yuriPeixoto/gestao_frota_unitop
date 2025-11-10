<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Histórico de Vida dos Pneus') }}
            </h2>
        </div>
    </x-slot>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Histórico de Vida dos Pneus</h1>
            <p class="text-gray-600 mt-1">Visualize o histórico completo de movimentações de cada pneu</p>
        </div>
    </div>

    <!-- Filtros de Busca -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <form method="GET" action="{{ route('admin.pneuhistorico.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="id_pneu" class="block text-sm font-medium text-gray-700 mb-2">N° de Fogo</label>
                    <input type="number" name="id_pneu" id="id_pneu"
                           value="{{ request('id_pneu') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="N° de Fogo">
                </div>

                <div>
                    <label for="cod_antigo" class="block text-sm font-medium text-gray-700 mb-2">Código Antigo</label>
                    <input type="text" name="cod_antigo" id="cod_antigo"
                           value="{{ request('cod_antigo') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="Código do Pneu">
                </div>

                <div>
                    <label for="id_filial" class="block text-sm font-medium text-gray-700 mb-2">Filial</label>
                    <select name="id_filial" id="id_filial"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Todas as Filiais</option>
                        @foreach(\App\Models\VFilial::orderBy('name')->get() as $filial)
                            <option value="{{ $filial->id }}" {{ request('id_filial') == $filial->id ? 'selected' : '' }}>
                                {{ $filial->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="id_departamento" class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
                    <select name="id_departamento" id="id_departamento"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">Todos os Departamentos</option>
                        @foreach(\App\Models\Departamento::orderBy('descricao_departamento')->get() as $dept)
                            <option value="{{ $dept->id_departamento }}" {{ request('id_departamento') == $dept->id_departamento ? 'selected' : '' }}>
                                {{ $dept->descricao_departamento }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="data_inicial" class="block text-sm font-medium text-gray-700 mb-2">Data Inicial</label>
                    <input type="date" name="data_inicial" id="data_inicial"
                           value="{{ request('data_inicial') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>

                <div>
                    <label for="data_final" class="block text-sm font-medium text-gray-700 mb-2">Data Final</label>
                    <input type="date" name="data_final" id="data_final"
                           value="{{ request('data_final') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.pneuhistorico.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Limpar Filtros
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="w-4 h-4 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Buscar
                </button>
            </div>
        </form>
    </div>

    <!-- Lista de Pneus -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Pneus Encontrados</h3>
            <p class="text-sm text-gray-600 mt-1">{{ $pneus->total() }} pneu(s) encontrado(s)</p>
        </div>

        @if($pneus->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                N° de Fogo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Modelo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Localização
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Inclusão
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pneus as $pneu)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">N° de Fogo: {{ $pneu->id_pneu }}</div>
                                    @if($pneu->cod_antigo)
                                        <div class="text-sm text-gray-600">{{ $pneu->cod_antigo }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $pneu->getModeloPneuFromHistorico()?->descricao_modelo ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $pneu->filialPneu->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-600">{{ $pneu->departamentoPneu->descricao_departamento ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'APLICADO' => 'bg-green-100 text-green-800',
                                        'ESTOQUE' => 'bg-blue-100 text-blue-800',
                                        'MANUTENCAO' => 'bg-orange-100 text-orange-800',
                                        'DESCARTE' => 'bg-red-100 text-red-800',
                                        'VENDA' => 'bg-purple-100 text-purple-800'
                                    ];
                                    $statusColor = $statusColors[$pneu->status_pneu] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ $pneu->status_pneu }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($pneu->data_inclusao)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('admin.pneuhistorico.show', $pneu->id_pneu) }}"
                                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-colors">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Ver Histórico
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            @if($pneus->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $pneus->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 34l8-8m0 0V14a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 002 2h16m8-8l-8-8m0 8h8m-8 0v8"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum pneu encontrado</h3>
                <p class="mt-1 text-sm text-gray-500">Tente ajustar os filtros de busca ou verifique os dados.</p>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
