<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalhes do Veículo') }}
            </h2>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.veiculos.edit', $veiculo) }}"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Editar
                </a>
                <a href="{{ route('admin.veiculos.index') }}"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Identificação</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Placa</p>
                            <p class="font-medium font-mono text-lg">{{ $veiculo->placa ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Renavam</p>
                            <p class="font-medium font-mono">{{ $veiculo->renavam ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Chassi</p>
                            <p class="font-medium font-mono">{{ $veiculo->chassi ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Marca</p>
                            <p class="font-medium">{{ $veiculo->marca_veiculo ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Modelo</p>
                            <p class="font-medium">
                                {{ $veiculo->modeloVeiculo->descricao_modelo_veiculo ??
                                    'Não
                                                                informado' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Cor</p>
                            <p class="font-medium">{{ $veiculo->cor_veiculo ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ano Modelo</p>
                            <p class="font-medium">{{ $veiculo->ano_modelo ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Ano Fabricação</p>
                            <p class="font-medium">{{ $veiculo->ano_fabricacao ?? 'Não informado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Seção: Combustível e Capacidades --}}
                <div class="bg-gradient-to-br from-white to-green-50 rounded-xl p-6 shadow-lg border border-green-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Combustível e Capacidades</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Combustível</p>
                            <p class="font-medium">{{ $veiculo->combustivelVeiculo->descricao ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Capacidade Tanque Principal (L)</p>
                            <p class="font-medium">{{ $veiculo->capacidade_tanque_principal ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Capacidade Tanque Secundário (L)</p>
                            <p class="font-medium">{{ $veiculo->capacidade_tanque_secundario ?? 'Não informado' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Seção: Localização e Filial --}}
                <div
                    class="bg-gradient-to-br from-white to-purple-50 rounded-xl p-6 shadow-lg border border-purple-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-purple-100 rounded-lg">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Localização</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Filial</p>
                            <p class="font-medium">{{ $filial->name ?? 'Não informado' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Município</p>
                            <p class="font-medium">
                                {{ $municipio->nome_municipio ?? 'Não informado' }}{{ $municipio->uf ? ' - ' . $municipio->uf : '' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Seção: Informações Financeiras e Datas --}}
                <div class="bg-gradient-to-br from-white to-amber-50 rounded-xl p-6 shadow-lg border border-amber-100">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="p-2 bg-amber-100 rounded-lg">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900">Informações Financeiras e Datas</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Valor Venal</p>
                            <p class="font-medium">
                                @if ($veiculo->valor_venal)
                                    R$ {{ number_format($veiculo->valor_venal, 2, ',', '.') }}
                                @else
                                    Não informado
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Data de Compra</p>
                            <p class="font-medium font-mono">
                                {{ $veiculo->data_compra
                                    ? date('d/m/Y H:i', strtotime($veiculo->data_compra))
                                    : 'Não
                                                                informado' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Data de Venda</p>
                            <p class="font-medium font-mono">
                                {{ $registroCompra && $registroCompra->data_venda
                                    ? date('d/m/Y H:i', strtotime($registroCompra->data_venda))
                                    : 'Não
                                                                informado' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Data de Inclusão</p>
                            <p class="font-medium font-mono">
                                {{ $veiculo->data_inclusao ? date('d/m/Y H:i', strtotime($veiculo->data_inclusao)) : 'Não informado' }}
                            </p>
                        </div>
                        @if ($veiculo->data_alteracao)
                            <div>
                                <p class="text-sm text-gray-500">Data de Alteração</p>
                                <p class="font-medium font-mono">
                                    {{ $veiculo->data_alteracao }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
