<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="abastecimentoForm()">
                <form id="abastecimentoForm" method="GET"
                    action="{{ route('admin.abastecimentostruckpag.onProcessarATS') }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Abastecimento</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data
                                    Inicial</label>
                                <input type="date" id="data_inicio" name="data_inicio"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>

                            <div>
                                <label for="data_fim" class="block text-sm font-medium text-gray-700">Data
                                    Final</label>
                                <input type="date" id="data_fim" name="data_fim"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>

                            <div>
                                <label for="id_veiculo" class="block text-sm font-medium text-gray-700">Placa</label>
                                <select id="id_veiculo" name="id_veiculo" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($veiculos as $veiculo)
                                        <option value="{{ $veiculo['value'] }}"
                                            {{ old('id_veiculo', $abastecimento->id_veiculo ?? '') == $veiculo['value'] ? 'selected' : '' }}>
                                            {{ $veiculo['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_bomba" class="block text-sm font-medium text-gray-700">Bomba</label>
                                <select id="id_bomba" name="id_bomba" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($bombas as $bomba)
                                        <option value="{{ $bomba['value'] }}"
                                            {{ old('id_veiculo', $abastecimento->id_bomba ?? '') == $bomba['value'] ? 'selected' : '' }}>
                                            {{ $bomba['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-left space-x-4 mt-6">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.arrow-path-rounded-square />
                            Processar
                        </button>

                        <a href="{{ route('admin.abastecimentostruckpag.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
