<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="afericaoForm()">
                <form id="afericaoForm" method="POST" action="{{ $action ?? route('admin.afericaobombas.store') }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Aferição</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if(isset($afericao))
                                <div>
                                    <label for="id_abastecimento_integracao" class="block text-sm font-medium text-gray-700">Código Aferição</label>
                                    <input type="text" id="id_abastecimento_integracao" name="id_abastecimento_integracao" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $afericao->id_abastecimento_integracao }}">
                                </div>

                                <div>
                                    <label for="descricao_bomba" class="block text-sm font-medium text-gray-700">Descrição da Bomba</label>
                                    <input type="text" id="descricao_bomba" name="descricao_bomba" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $afericao->descricao_bomba }}">
                                </div>

                                <div>
                                    <label for="placa" class="block text-sm font-medium text-gray-700">Placa</label>
                                    <input type="text" id="placa" name="placa" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $afericao->placa }}">
                                </div>
                            @endif

                            <div>
                                <label for="volume" class="block text-sm font-medium text-gray-700">Volume Abastecimento</label>
                                <input type="text" id="volume" name="volume" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($afericao) ? number_format($afericao->volume, 2, ',', '.') : '' }}">
                            </div>

                            <div>
                                <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data do Abastecimento</label>
                                <input type="text" id="data_inicio" name="data_inicio" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ isset($afericao) && $afericao->data_inicio ? (is_object($afericao->data_inicio) ? $afericao->data_inicio->format('d/m/Y H:i') : (is_string($afericao->data_inicio) ? date('d/m/Y H:i', strtotime($afericao->data_inicio)) : '')) : '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select
                                    name="id_tanque"
                                    label="Tanque"
                                    placeholder="Selecione o tanque..."
                                    :options="$tanques"
                                    :selected="old('id_tanque', $tanqueSelecionado ?? null)"
                                    asyncSearch="false"
                                    required="true"
                                />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label for="volume_entrada" class="block text-sm font-medium text-gray-700">Volume de Entrada</label>
                                <input type="number" id="volume_entrada" name="volume_entrada" step="0.01" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('volume_entrada') }}"
                                    x-on:input="validarVolume"
                                    x-ref="volumeEntrada">
                                <div x-show="volumeError" class="mt-1 text-sm text-red-600" x-text="volumeError"></div>
                            </div>

                            <div>
                                <label for="usuario" class="block text-sm font-medium text-gray-700">Usuário</label>
                                <input type="text" id="usuario" name="usuario" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ Auth::user()->name }}">
                            </div>
                        </div>
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <a href="{{ route('admin.afericaobombas.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            x-bind:disabled="!!volumeError">
                            {{-- <x-icons.save class="h-4 w-4 mr-2 text-white" /> --}}
                            {{ $method === 'PUT' ? 'Gerar Entrada' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function afericaoForm() {
            return {
                volumeError: '',
                
                validarVolume() {
                    const volumeEntrada = parseFloat(this.$refs.volumeEntrada.value);
                    const volumeAbastecimento = parseFloat('{{ isset($afericao) ? $afericao->volume : 0 }}');
                    
                    if (isNaN(volumeEntrada) || volumeEntrada <= 0) {
                        this.volumeError = 'O volume de entrada deve ser maior que zero';
                    } else if (volumeEntrada > volumeAbastecimento) {
                        this.volumeError = 'O volume de entrada não pode ser maior que o volume de abastecimento';
                    } else {
                        this.volumeError = '';
                    }
                },

                init() {
                    // Validação inicial
                    this.validarVolume();
                }
            };
        }
    </script>
@endpush