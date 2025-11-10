<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        @if (session('notification'))
            <x-notification :notification="session('notification')" />
        @endif
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="seguroObrigatorioForm()">
                <form id="seguroObrigatorioForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Seguro Obrigatório</h3>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label for="id_seguro_obrigatorio_veiculo"
                                    class="block text-sm font-medium text-gray-700">Código</label>
                                <input type="text" id="id_seguro_obrigatorio_veiculo"
                                    name="id_seguro_obrigatorio_veiculo" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $seguroObrigatorio->id_seguro_obrigatorio_veiculo ?? '' }}">
                            </div>

                            <div>
                                <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
                                <select id="situacao" name="situacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    <option value="Cancelados"
                                        {{ old('situacao', $seguroObrigatorio->situacao ?? '') == 'Cancelados' ? 'selected' : '' }}>
                                        Cancelados</option>
                                    <option value="A vencer"
                                        {{ old('situacao', $seguroObrigatorio->situacao ?? '') == 'A vencer' ? 'selected' : '' }}>
                                        A vencer</option>
                                    <option value="Vencido"
                                        {{ old('situacao', $seguroObrigatorio->situacao ?? '') == 'Vencido' ? 'selected' : '' }}>
                                        Vencido</option>
                                    <option value="Quitado"
                                        {{ old('situacao', $seguroObrigatorio->situacao ?? '') == 'Quitado' ? 'selected' : '' }}>
                                        Quitado</option>
                                </select>
                            </div>

                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Placa"
                                    placeholder="Selecione o placa..." :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')"
                                    :selected="old('id_veiculo', $seguroObrigatorio->id_veiculo ?? '')" asyncSearch="true" required="true"
                                    onSelectCallback="atualizarDadosVeiculoCallback" />
                            </div>

                            <div>
                                <label for="chassi_veiculo"
                                    class="block text-sm font-medium text-gray-700">Chassi</label>
                                <input type="text" id="chassi_veiculo" name="chassi_veiculo" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="chassiVeiculo">
                            </div>

                            <div>
                                <label for="filial_veiculo" class="block text-sm font-medium text-gray-700">Filial do
                                    Veículo</label>
                                <input type="text" id="filial_veiculo" name="filial_veiculo" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="filialVeiculo">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="numero_bilhete" class="block text-sm font-medium text-gray-700">Número do
                                    Bilhete</label>
                                <input type="number" id="numero_bilhete" name="numero_bilhete" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('numero_bilhete', $seguroObrigatorio->numero_bilhete ?? '') }}">
                            </div>

                            <div>
                                <label for="ano_validade" class="block text-sm font-medium text-gray-700">Ano de
                                    Validade</label>
                                <input type="number" id="ano_validade" name="ano_validade" required min="2000"
                                    max="2099"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('ano_validade', $seguroObrigatorio->ano_validade ?? date('Y')) }}">
                            </div>

                            <div>
                                <label for="data_vencimento" class="block text-sm font-medium text-gray-700">Data de
                                    Vencimento</label>
                                <input type="date" id="data_vencimento" name="data_vencimento" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    {{-- value="{{ $ordemServico->data_abertura ?? '' }}" --}} value="{{ $seguroObrigatorio->data_vencimento ?? '' }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="valor_seguro_previsto">Valor Previsto</label>
                                <input type="text" data-mask="valor" id="valor_seguro_previsto"
                                    name="valor_seguro_previsto" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    step="0.01" min="0"
                                    value="{{ old('valor_seguro_previsto', $seguroObrigatorio->valor_seguro_previsto ?? '') }}">
                            </div>

                            <div>
                                <label for="valor_seguro_pago" class="block text-sm font-medium text-gray-700">Valor
                                    Pago</label>
                                <input type="text" id="valor_seguro_pago" name="valor_seguro_pago" required
                                    step="0.01" min="0" data-mask="valor"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('valor_seguro_pago', $seguroObrigatorio->valor_seguro_pago ?? '') }}">
                            </div>

                            <div>
                                <label for="data_pagamento" class="block text-sm font-medium text-gray-700">Data de
                                    Pagamento</label>
                                <input type="date" id="data_pagamento" name="data_pagamento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    {{-- value="{{ $ordemServico->data_abertura ?? '' }}" /> --}} value="{{ $seguroObrigatorio->data_pagamento ?? '' }}">
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" onclick="document.getElementById('seguroObrigatorioForm').reset()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar
                        </button>

                        <a href="{{ route('admin.seguroobrigatorio.index') }}"
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

@push('scripts')
    @include('admin.seguroobrigatorio._scripts')
@endpush
