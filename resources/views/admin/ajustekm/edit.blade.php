<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Ajuste KM Abastecimento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Edição de Ajuste KM"
                    content="Nesta tela você pode editar os dados de um ajuste de KM existente. Altere os campos necessários e clique em Atualizar para salvar as modificações." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('admin.ajustekm.update', $ajusteKm->id_ajuste_km_abastecimento) }}"
                    class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Código (Somente leitura) -->
                        <div>
                            <label for="id_ajuste_km_abastecimento"
                                class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" id="id_ajuste_km_abastecimento"
                                value="{{ $ajusteKm->id_ajuste_km_abastecimento }}" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Data Inclusão (Somente leitura) -->
                        <div>
                            <label for="data_inclusao" class="block text-sm font-medium text-gray-700">Data
                                Inclusão</label>
                            <input type="text" id="data_inclusao"
                                value="{{ $ajusteKm->data_inclusao?->format('d/m/Y H:i') }}" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Data Abastecimento -->
                        <div>
                            <x-forms.input type="date" name="data_abastecimento" label="Data Abastecimento *"
                                value="{{ old('data_abastecimento', $ajusteKm->data_abastecimento?->format('Y-m-d')) }}"
                                required />
                        </div>

                        <!-- Veículo -->
                        <div>
                            <label for="id_veiculo" class="block text-sm font-medium text-gray-700">Veículo *</label>
                            <select id="id_veiculo" name="id_veiculo" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                x-data="{ kmAtual: '{{ $ajusteKm->veiculo->km_atual ?? '' }}' }" x-init="$watch('$el.value', value => {
                                        if (value) {
                                            fetch(`/api/veiculos/${value}/dados`)
                                                .then(res => res.json())
                                                .then(data => {
                                                    kmAtual = data.km_atual || '';
                                                    document.getElementById('km_atual').value = kmAtual;
                                                })
                                                .catch(err => console.error('Erro ao buscar dados do veículo:', err));
                                        }
                                    })">
                                <option value="">Selecione...</option>
                                @foreach ($veiculos as $veiculo)
                                <option value="{{ $veiculo->id_veiculo }}" {{ old('id_veiculo', $ajusteKm->id_veiculo)
                                    == $veiculo->id_veiculo ? 'selected' : '' }}>
                                    {{ $veiculo->placa }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_veiculo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Km Atual (Somente leitura) -->
                        <div>
                            <label for="km_atual" class="block text-sm font-medium text-gray-700">KM Atual do
                                Veículo</label>
                            <input type="text" id="km_atual" value="{{ $ajusteKm->veiculo->km_atual ?? '' }}" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <!-- Km Abastecimento -->
                        <div>
                            <x-forms.input type="number" name="km_abastecimento" label="KM Abastecimento *"
                                value="{{ old('km_abastecimento', $ajusteKm->km_abastecimento) }}" min="0" required />
                        </div>

                        <!-- Tipo Combustível -->
                        <div>
                            <label for="tipo_combustivel" class="block text-sm font-medium text-gray-700">Tipo de
                                Combustível *</label>
                            <select id="tipo_combustivel" name="tipo_combustivel" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($tiposCombustivel as $tipo)
                                <option value="{{ $tipo->descricao }}" {{ old('tipo_combustivel', $ajusteKm->
                                    tipo_combustivel) == $tipo->descricao ? 'selected' : '' }}>
                                    {{ $tipo->descricao }}
                                </option>
                                @endforeach
                            </select>
                            @error('tipo_combustivel')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- ID Permissão KM Manual -->
                        <div>
                            <x-forms.input type="number" name="id_permissao_km_manual" label="Cód. Permissão KM Manual"
                                value="{{ old('id_permissao_km_manual', $ajusteKm->id_permissao_km_manual) }}" />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('admin.ajustekm.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Atualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>