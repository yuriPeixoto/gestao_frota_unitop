<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastrar Ajuste KM Abastecimento') }}
            </h2>
            <div class="flex items-center space-x-4">
                <x-help-icon title="Ajuda - Cadastro de Ajuste KM"
                    content="Nesta tela você pode cadastrar um novo ajuste de KM para abastecimentos. Preencha todos os campos obrigatórios para concluir o cadastro." />
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('admin.ajustekm.store') }}" class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Data do Ajuste -->
                        <div>
                            <x-forms.input type="date" name="data_abastecimento" label="Data Abastecimento *"
                                value="{{ old('data_abastecimento', date('Y-m-d')) }}" required />
                        </div>

                        <!-- Veículo -->
                        <div>
                            <x-forms.smart-select name="id_veiculo" label="Placa *" placeholder="Selecione o veículo..."
                                :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                                :selected="old('id_veiculo')" asyncSearch="true" required="true" />
                            @error('id_veiculo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Km Atual (campo oculto para JavaScript) -->
                        <input type="hidden" id="km_atual">

                        @push('scripts')
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Monitorar mudanças no select de veículos
                                const veiculoSelect = document.querySelector('[name="id_veiculo"]');
                                
                                if (veiculoSelect) {
                                    veiculoSelect.addEventListener('change', function() {
                                        const veiculoId = this.value;
                                        if (veiculoId) {
                                            fetch(`/admin/api/veiculos/${veiculoId}/dados`)
                                                .then(res => res.json())
                                                .then(data => {
                                                    document.getElementById('km_atual').value = data.km_atual || '';
                                                })
                                                .catch(err => console.error('Erro ao buscar dados do veículo:', err));
                                        }
                                    });
                                }
                            });
                        </script>
                        @endpush

                        <!-- Km Abastecimento -->
                        <div>
                            <x-forms.input type="number" name="km_abastecimento" label="KM *"
                                value="{{ old('km_abastecimento') }}" min="0" step="1" required />
                        </div>

                        <!-- Diesel e Arla (Sim/Não) -->
                        <div>
                            <label for="arla" class="block text-sm font-medium text-gray-700">Diesel e Arla *</label>
                            <select id="arla" name="arla" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione...</option>
                                <option value="1" {{ old('arla')=='1' ? 'selected' : '' }}>
                                    Sim</option>
                                <option value="2" {{ old('arla')=='2' ? 'selected' : '' }}>Não
                                </option>
                            </select>
                            @error('arla')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                        </div>
                    </div>

                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('admin.ajustekm.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Salvar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>