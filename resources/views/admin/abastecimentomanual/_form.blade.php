<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    {{-- Mensagens de erro --}}
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div id="tiposCombustivel" data-tipos='@json($tiposCombustivel)' class="hidden"></div>
            <div id="bombas" data-bombas='@json($bombas)' class="hidden"></div>

            <div id="abastecimentoFormContainer">
                @if (isset($abastecimento) && isset($abastecimento->itens))
                    <!-- Adicionar um elemento script com os dados dos itens para inicialização -->
                    <script id="items-data" type="application/json">
                    @json($abastecimento->itens)
                </script>
                @endif

                <!-- Painel de Lote -->
                <div id="panelLote" class="mb-6 bg-blue-50 p-4 rounded-lg border border-blue-200"
                    style="display: none;">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-lg font-medium text-blue-800">
                            Lote de Abastecimentos (<span id="abastecimentosCount">0</span>)
                        </h3>
                        <div class="flex space-x-2">
                            <button type="button" id="btnProcessarLote"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Processar Lote
                            </button>
                        </div>
                    </div>

                    <!-- Lista de abastecimentos no lote -->
                    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left text-gray-700">
                            <thead class="text-xs text-gray-700 uppercase bg-blue-100">
                                <tr>
                                    <th scope="col" class="py-2 px-3">NF</th>
                                    <th scope="col" class="py-2 px-3">Veículo</th>
                                    <th scope="col" class="py-2 px-3">Fornecedor</th>
                                    <th scope="col" class="py-2 px-3">Itens</th>
                                    <th scope="col" class="py-2 px-3">Valor Total</th>
                                    <th scope="col" class="py-2 px-3">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="loteTableBody">
                                <!-- Conteúdo preenchido por JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <form id="abastecimentoForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif
                    @if ($errors->any())
                        <div class="bg-red-100 text-red-800 p-4 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Abastecimento</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="id_abastecimento"
                                    class="block text-sm font-medium text-gray-700">Código</label>
                                <input type="text" id="id_abastecimento" name="id_abastecimento" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $abastecimento->id_abastecimento ?? '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Placa"
                                    placeholder="Selecione o veículo..." :options="$veiculos" :searchUrl="route('admin.api.abastecimentomanual.getVeiculo')"
                                    :selected="old('id_veiculo', $abastecimento->veiculo->placa ?? '')" asyncSearch="true" required="true"
                                    onSelectCallback="atualizarDadosVeiculoCallback" />
                            </div>

                            <div>
                                <label for="capacidade_tanque"
                                    class="block text-sm font-medium text-gray-700">Capacidade do Tanque</label>
                                <input type="text" id="capacidade_tanque" name="capacidade_tanque" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $abastecimento->veiculo->capacidade_tanque_principal ?? '' }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                                    placeholder="Selecione o fornecedor..." :options="$fornecedores" :searchUrl="route('admin.api.fornecedores.search')"
                                    :selected="old('id_fornecedor', $abastecimento->id_fornecedor ?? '')" asyncSearch="true" required="true" />
                            </div>

                            <div>
                                <label for="filial_display"
                                    class="block text-sm font-medium text-gray-700">Filial</label>
                                <input type="text" id="filial_display" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Será preenchido automaticamente">
                                <!-- Campo hidden que armazenará o valor real -->
                                <input type="hidden" id="id_filial" name="id_filial"
                                    value="{{ old('id_filial', $abastecimento->id_filial ?? auth()->user()->filial_id) }}">
                            </div>

                            <div>
                                <label for="departamento_display"
                                    class="block text-sm font-medium text-gray-700">Departamento</label>
                                <input type="text" id="departamento_display" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Será preenchido automaticamente">
                                <!-- Campo hidden que armazenará o valor real -->
                                <input type="hidden" id="id_departamento" name="id_departamento"
                                    value="{{ old('id_departamento', $abastecimento->id_departamento ?? '') }}">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="numero_nota_fiscal" class="block text-sm font-medium text-gray-700">Nº
                                    Nota
                                    Fiscal</label>
                                <input type="text" id="numero_nota_fiscal" name="numero_nota_fiscal" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('numero_nota_fiscal', $abastecimento->numero_nota_fiscal ?? '') }}">
                                @error('numero_nota_fiscal')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="chave_nf" class="block text-sm font-medium text-gray-700">Chave NF</label>
                                <input type="text" id="chave_nf" name="chave_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('chave_nf', $abastecimento->chave_nf ?? '') }}">
                            </div>

                            <div>
                                <x-forms.smart-select name="id_motorista" label="Motorista"
                                    placeholder="Selecione o motorista..." :options="$motoristas" :selected="old('id_motorista', $abastecimento->id_motorista ?? '')"
                                    asyncSearch="false" />
                            </div>
                        </div>
                    </div>

                    <!-- Form de Itens -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Abastecimentos</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label for="data_abastecimento" class="block text-sm font-medium text-gray-700">Data
                                    Abastecimento</label>
                                <input type="date" id="data_abastecimento" max="{{ date('Y-m-d') }}"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">A hora será adicionada automaticamente ao salvar
                                </p>
                            </div>

                            <div>
                                <label for="id_combustivel" class="block text-sm font-medium text-gray-700">Tipo
                                    Combustível</label>
                                <select id="id_combustivel"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($tiposCombustivel as $tipo)
                                        <option value="{{ $tipo->value }}">{{ $tipo->label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_bomba" class="block text-sm font-medium text-gray-700">Bomba
                                    (Bico)</label>
                                <select id="id_bomba" name="id_bomba"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    <!-- As opções serão preenchidas dinamicamente via JavaScript -->
                                </select>
                            </div>

                            <div>
                                <label for="litros" class="block text-sm font-medium text-gray-700">Litros
                                    (m³)</label>
                                <input type="number" id="litros" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <label for="km_veiculo" class="block text-sm font-medium text-gray-700">Km
                                    Veículo</label>
                                <input type="number" id="km_veiculo" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="km_anterior" class="block text-sm font-medium text-gray-700">Km
                                    Anterior</label>
                                <input type="text" id="km_anterior" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="valor_unitario" class="block text-sm font-medium text-gray-700">Valor
                                    Unitário</label>
                                <input type="number" id="valor_unitario" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="valor_total" class="block text-sm font-medium text-gray-700">Valor
                                    Total</label>
                                <input type="number" id="valor_total" step="0.01" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" id="adicionarItem"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar
                            </button>
                            <button type="button" id="atualizarItem"
                                class="hidden inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                Atualizar
                            </button>
                        </div>
                    </div>

                    <!-- Tabela de Itens -->
                    <div class="mt-6">
                        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left text-gray-700">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">Data Abast.</th>
                                        <th scope="col" class="py-3 px-6">Tipo Combustível</th>
                                        <th scope="col" class="py-3 px-6">Bomba</th>
                                        <th scope="col" class="py-3 px-6">Litros</th>
                                        <th scope="col" class="py-3 px-6">Km</th>
                                        <th scope="col" class="py-3 px-6">Valor Unit.</th>
                                        <th scope="col" class="py-3 px-6">Valor Total</th>
                                        <th scope="col" class="py-3 px-6">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Conteúdo preenchido por JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex flex-col space-y-4 mt-6">
                        <div id="alertNoItems" class="p-3 bg-yellow-100 text-yellow-800 rounded-md"
                            style="display: none;">
                            <p class="text-sm font-medium">É necessário adicionar ao menos um item de abastecimento
                                antes de adicionar ao lote.</p>
                        </div>

                        <div class="flex justify-end space-x-4">
                            <button type="button" id="btnLimpar"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Limpar
                            </button>

                            <a href="{{ route('admin.abastecimentomanual.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Voltar
                            </a>

                            <button type="button" id="btnAddToLote"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Salvar
                            </button>
                        </div>
                    </div>

                    <!-- Campo Hidden para Itens -->
                    <input type="hidden" name="items" id="itemsHidden" value="[]" />
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Selects ocultos para referência -->
<select id="id_departamento_original" class="hidden">
    <option value="">Selecione...</option>
    @foreach ($departamentos as $departamento)
        <option value="{{ $departamento['value'] }}">{{ $departamento['label'] }}</option>
    @endforeach
</select>

<select id="id_filial_original" class="hidden">
    <option value="">Selecione...</option>
    @foreach ($filiais as $filial)
        <option value="{{ $filial['value'] }}">{{ $filial['label'] }}</option>
    @endforeach
</select>

<!-- Carregar o JavaScript consolidado -->
<script src="{{ asset('js/abastecimentos/abastecimentoForm.js') }}?v={{ config('app.version', time()) }}"></script>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('[DEBUG] Inicializando formulário de abastecimento...');

            // Verificar qual objeto de inicialização está disponível
            if (window.AbastecimentoController && typeof window.AbastecimentoController.init === 'function') {
                window.AbastecimentoController.init();
            } else if (window.AbastecimentoForm && typeof window.AbastecimentoForm.init === 'function') {
                window.AbastecimentoForm.init();
            } else {
                console.error('[ERRO] Controlador de abastecimento não encontrado!');
            }
        });
    </script>
@endpush
