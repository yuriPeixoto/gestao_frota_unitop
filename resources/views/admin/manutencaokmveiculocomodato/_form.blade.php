<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Cadastro Km de Comodato</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="id_km_comotado" class="block text-sm font-medium text-gray-700">Cód.</label>
                                <input type="text" id="id_km_comotado" name="id_km_comotado" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('id_km_comotado', $veiculoKmComodato->id_km_comotado ?? '') }}">
                            </div>

                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Placa"
                                    placeholder="Selecione o veículo..." :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                                    :selected="old('id_veiculo', $veiculoKmComodato->id_veiculo ?? '')" asyncSearch="true" />
                            </div>

                            <div>
                                <label for="data_realizacao" class="block text-sm font-medium text-gray-700">Data
                                    Realização</label>
                                <input type="datetime-local" id="data_realizacao" name="data_realizacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('data_realizacao', $veiculoKmComodato->data_realizacao ?? '') }}">
                            </div>
                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="km_realizacao" class="block text-sm font-medium text-gray-700">Km
                                    Realização</label>
                                <input type="number" id="km_realizacao" name="km_realizacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('km_realizacao', $veiculoKmComodato->km_realizacao ?? '') }}">
                            </div>

                            <div>
                                <label for="horimetro" class="block text-sm font-medium text-gray-700">Horímetro</label>
                                <input type="number" id="horimetro" name="horimetro"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('horimetro', $veiculoKmComodato->horimetro ?? '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">

                        <a href="{{ route('admin.manutencaokmveiculocomodato.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
