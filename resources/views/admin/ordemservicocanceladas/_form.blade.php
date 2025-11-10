<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="abastecimentoForm()">
                <form id="abastecimentoForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
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
                                <label for="id_veiculo" class="block text-sm font-medium text-gray-700">Placa</label>
                                <select id="id_veiculo" name="id_veiculo" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-on:change="atualizarDadosVeiculo($event.target.value)">
                                    <option value="">Selecione...</option>
                                    @foreach ($veiculos as $veiculo)
                                        <option value="{{ $veiculo->id_veiculo }}"
                                            {{ old('id_veiculo', $abastecimento->id_veiculo ?? '') == $veiculo->id_veiculo ? 'selected' : '' }}>
                                            {{ $veiculo->placa }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="capacidade_tanque"
                                    class="block text-sm font-medium text-gray-700">Capacidade do Tanque</label>
                                <input type="text" id="capacidade_tanque" name="capacidade_tanque" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="capacidadeTanque">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="id_fornecedor"
                                    class="block text-sm font-medium text-gray-700">Fornecedor</label>
                                <select id="id_fornecedor" name="id_fornecedor" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id_fornecedor }}"
                                            {{ old('id_fornecedor', $abastecimento->id_fornecedor ?? '') == $fornecedor->id_fornecedor ? 'selected' : '' }}>
                                            {{ $fornecedor->nome_fornecedor }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
                                <select id="id_filial" name="id_filial" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($filiais as $filial)
                                        <option value="{{ $filial->id }}"
                                            {{ old('id_filial', $abastecimento->id_filial ?? '') == $filial->id ? 'selected' : '' }}>
                                            {{ $filial->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_departamento"
                                    class="block text-sm font-medium text-gray-700">Departamento</label>
                                <select id="id_departamento" name="id_departamento" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($departamentos as $departamento)
                                        <option value="{{ $departamento->id_departamento }}"
                                            {{ old('id_departamento', $abastecimento->id_departamento ?? '') == $departamento->id_departamento ? 'selected' : '' }}>
                                            {{ $departamento->descricao_departamento }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="numero_nota_fiscal" class="block text-sm font-medium text-gray-700">Nº Nota
                                    Fiscal</label>
                                <input type="text" id="numero_nota_fiscal" name="numero_nota_fiscal" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('numero_nota_fiscal', $abastecimento->numero_nota_fiscal ?? '') }}">
                            </div>

                            <div>
                                <label for="chave_nf" class="block text-sm font-medium text-gray-700">Chave NF</label>
                                <input type="text" id="chave_nf" name="chave_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('chave_nf', $abastecimento->chave_nf ?? '') }}">
                            </div>

                            <div>
                                <label for="id_motorista"
                                    class="block text-sm font-medium text-gray-700">Motorista</label>
                                <select id="id_motorista" name="id_motorista"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($motoristas as $motorista)
                                        <option value="{{ $motorista->idobtermotorista }}"
                                            {{ old('id_motorista', $abastecimento->id_motorista ?? '') == $motorista->idobtermotorista ? 'selected' : '' }}>
                                            {{ $motorista->nome }}
                                        </option>
                                    @endforeach
                                </select>
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
                                <input type="datetime-local" id="data_abastecimento" name="data_abastecimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.data_abastecimento" required>
                            </div>

                            <div>
                                <label for="id_tipo_combustivel" class="block text-sm font-medium text-gray-700">Tipo
                                    Combustível</label>
                                <select id="id_tipo_combustivel" name="id_tipo_combustivel"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.id_tipo_combustivel" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($tiposCombustivel as $tipo)
                                        <option value="{{ $tipo->id_tipo_combustivel }}">{{ $tipo->descricao }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="id_bomba" class="block text-sm font-medium text-gray-700">Bomba
                                    (Bico)</label>
                                <select id="id_bomba" name="id_bomba"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.id_bomba" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($bombas as $bomba)
                                        <option value="{{ $bomba->id_bomba }}">{{ $bomba->descricao_bomba }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="litros" class="block text-sm font-medium text-gray-700">Litros
                                    (m³)</label>
                                <input type="number" id="litros" name="litros" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.litros" x-on:input="calcularValorTotal" required>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                            <div>
                                <label for="km_veiculo" class="block text-sm font-medium text-gray-700">Km
                                    Veículo</label>
                                <input type="number" id="km_veiculo" name="km_veiculo" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.km_veiculo" required>
                            </div>

                            <div>
                                <label for="km_anterior" class="block text-sm font-medium text-gray-700">Km
                                    Anterior</label>
                                <input type="text" id="km_anterior" name="km_anterior" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="kmAnterior">
                            </div>

                            <div>
                                <label for="valor_unitario" class="block text-sm font-medium text-gray-700">Valor
                                    Unitário</label>
                                <input type="number" id="valor_unitario" name="valor_unitario" step="0.01"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.valor_unitario" x-on:input="calcularValorTotal" required>
                            </div>

                            <div>
                                <label for="valor_total" class="block text-sm font-medium text-gray-700">Valor
                                    Total</label>
                                <input type="number" id="valor_total" name="valor_total" step="0.01" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    x-model="novoItem.valor_total">
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="button" x-on:click="adicionarItem"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Adicionar
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
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="bg-white border-b hover:bg-gray-50">
                                            <td class="py-3 px-6" x-text="formatarData(item.data_abastecimento)"></td>
                                            <td class="py-3 px-6"
                                                x-text="getTipoCombustivel(item.id_tipo_combustivel)"></td>
                                            <td class="py-3 px-6" x-text="getBomba(item.id_bomba)"></td>
                                            <td class="py-3 px-6" x-text="formatarNumero(item.litros)"></td>
                                            <td class="py-3 px-6" x-text="formatarNumero(item.km_veiculo)"></td>
                                            <td class="py-3 px-6" x-text="formatarMoeda(item.valor_unitario)"></td>
                                            <td class="py-3 px-6" x-text="formatarMoeda(item.valor_total)"></td>
                                            <td class="py-3 px-6">
                                                <div class="flex space-x-2">
                                                    <button type="button" x-on:click="editarItem(index)"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                        </svg>
                                                    </button>
                                                    <button type="button" x-on:click="removerItem(index)"
                                                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="items.length === 0" class="bg-white border-b">
                                        <td colspan="8" class="py-3 px-6 text-center text-gray-500">Nenhum item
                                            adicionado</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" x-on:click="limparFormulario"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar Formulário
                        </button>

                        <a href="{{ route('admin.abastecimentomanual.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>

                    <!-- Campo Hidden para Itens -->
                    <input type="hidden" name="items" x-model="JSON.stringify(items)" />
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function abastecimentoForm() {
            return {
                capacidadeTanque: '{{ $abastecimento->veiculo->capacidade_tanque ?? '' }}',
                kmAnterior: '{{ $abastecimento->veiculo->km_atual ?? '' }}',
                items: @json($abastecimento->itens ?? []),
                novoItem: {
                    data_abastecimento: '',
                    id_tipo_combustivel: '',
                    id_bomba: '',
                    litros: '',
                    km_veiculo: '',
                    valor_unitario: '',
                    valor_total: ''
                },

                async atualizarDadosVeiculo(idVeiculo) {
                    if (!idVeiculo) return;

                    try {
                        const response = await fetch(`/api/veiculos/${idVeiculo}`);
                        const data = await response.json();
                        this.capacidadeTanque = data.capacidade_tanque;
                        this.kmAnterior = data.km_atual || 'N/A';
                    } catch (error) {
                        console.error('Erro ao buscar dados do veículo:', error);
                    }
                },

                calcularValorTotal() {
                    if (this.novoItem.litros && this.novoItem.valor_unitario) {
                        this.novoItem.valor_total = (
                            parseFloat(this.novoItem.litros) *
                            parseFloat(this.novoItem.valor_unitario)
                        ).toFixed(2);
                    }
                },

                adicionarItem() {
                    if (!this.validarItem()) return;

                    this.items.push({
                        ...this.novoItem
                    });
                    this.limparNovoItem();
                },

                editarItem(index) {
                    this.novoItem = {
                        ...this.items[index]
                    };
                    this.items.splice(index, 1);
                },

                removerItem(index) {
                    this.items.splice(index, 1);
                },

                limparNovoItem() {
                    this.novoItem = {
                        data_abastecimento: '',
                        id_tipo_combustivel: '',
                        id_bomba: '',
                        litros: '',
                        km_veiculo: '',
                        valor_unitario: '',
                        valor_total: ''
                    };
                },

                limparFormulario() {
                    if (!confirm('Deseja realmente limpar todos os dados do formulário?')) return;

                    this.items = [];
                    this.limparNovoItem();
                    this.capacidadeTanque = '';
                    this.kmAnterior = '';
                    document.getElementById('abastecimentoForm').reset();
                },

                validarItem() {
                    const campos = [
                        'data_abastecimento',
                        'id_tipo_combustivel',
                        'id_bomba',
                        'litros',
                        'km_veiculo',
                        'valor_unitario'
                    ];

                    for (const campo of campos) {
                        if (!this.novoItem[campo]) {
                            alert(`O campo ${campo.replace('_', ' ')} é obrigatório`);
                            return false;
                        }
                    }

                    return true;
                },

                formatarData(data) {
                    return new Date(data).toLocaleString('pt-BR');
                },

                formatarNumero(numero) {
                    return parseFloat(numero).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                formatarMoeda(valor) {
                    return parseFloat(valor).toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                },

                getTipoCombustivel(id) {
                    const tipos = @json($tiposCombustivel);
                    const tipo = tipos.find(t => t.id_tipo_combustivel == id);
                    return tipo ? tipo.descricao : '';
                },

                getBomba(id) {
                    const bombas = @json($bombas);
                    const bomba = bombas.find(b => b.id_bomba == id);
                    return bomba ? bomba.descricao_bomba : '';
                }
            }
        }
    </script>
@endpush
