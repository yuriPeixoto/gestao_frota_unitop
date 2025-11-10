<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <form id="metaTipoEquipamentoForm" method="POST" action="{{ $action }}" class="space-y-4">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <!-- Cabeçalho -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Meta por Tipo de Equipamento</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if(isset($metaTipoEquipamentos) && $metaTipoEquipamentos->id_meta)
                        <div>
                            <label for="id_meta" class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" id="id_meta" name="id_meta" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $metaTipoEquipamentos->id_meta ?? '' }}">
                        </div>
                        @endif

                        <div>
                            <label for="data_inicial" class="block text-sm font-medium text-gray-700">Data Inicial</label>
                            <input type="date" id="data_inicial" name="data_inicial"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('data_inicial', $metaTipoEquipamentos->data_inicial ?? '') }}">
                        </div>

                        <div>
                            <label for="data_final" class="block text-sm font-medium text-gray-700">Data Final</label>
                            <input type="date" id="data_final" name="data_final"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('data_final', $metaTipoEquipamentos->data_final ?? '') }}">
                        </div>

                        <div>
                            <x-forms.smart-select
                                name="id_filial"
                                label="Filial"
                                placeholder="Selecione a filial..."
                                :options="$filiais"
                                :selected="old('id_filial', $metaTipoEquipamentos->id_filial ?? '')"
                                asyncSearch="false"
                                required="true"
                            />
                        </div>

                        <div>
                            <x-forms.smart-select
                                name="id_equipamento"
                                label="Tipo de Equipamento"
                                placeholder="Selecione o tipo de equipamento..."
                                :options="$tiposEquipamento"
                                :selected="old('id_equipamento', $metaTipoEquipamentos->id_equipamento ?? '')"
                                asyncSearch="false"
                                required="true"
                            />
                        </div>

                        <div>
                            <label for="vlr_meta" class="block text-sm font-medium text-gray-700">Valor Meta</label>
                            <input type="number" id="vlr_meta" name="vlr_meta" step="0.01" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('vlr_meta', $metaTipoEquipamentos->vlr_meta ?? '') }}">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Ativo</label>
                        <div class="mt-2 flex items-center space-x-4">
                            <div class="flex items-center">
                                <input id="ativo-sim" name="ativo" type="radio" value="1" 
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{ old('ativo', $metaTipoEquipamentos->ativo ?? 1) == 1 ? 'checked' : '' }}>
                                <label for="ativo-sim" class="ml-2 block text-sm text-gray-700">Sim</label>
                            </div>
                            <div class="flex items-center">
                                <input id="ativo-nao" name="ativo" type="radio" value="0" 
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    {{ old('ativo', $metaTipoEquipamentos->ativo ?? 1) == 0 ? 'checked' : '' }}>
                                <label for="ativo-nao" class="ml-2 block text-sm text-gray-700">Não</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('admin.metatipoequipamentos.index') }}"
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