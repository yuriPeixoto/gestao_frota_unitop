<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova Entrada por Aferição') }}
            </h2>
            <div class="flex items-center space-x-4">
                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">Ajuda - Nova Entrada por
                                    Aferição</p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Nesta tela você pode criar uma nova entrada por aferição.
                                    @if(isset($abastecimento))
                                    Apenas o campo Volume de Entrada pode ser modificado.
                                    @else
                                    Selecione o tanque e indique o volume de entrada.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <form action="{{ route('admin.afericaobombas.store') }}" method="POST" class="py-4" id="afericaoForm">
        @csrf

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-4 bg-white border-b border-gray-200">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div x-data="afericaoForm()" x-init="document.querySelector('#afericaoForm').addEventListener('submit', function(e) {
                            console.log('Formulário submetido');
                         })">
                        <!-- Cabeçalho -->
                        <div class="bg-gray-50 p-4 rounded-lg mb-6">
                            <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Aferição</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @if(isset($abastecimento))
                                <div>
                                    <label for="id_abastecimento_integracao"
                                        class="block text-sm font-medium text-gray-700">Código Aferição</label>
                                    <input type="text" id="id_abastecimento_integracao"
                                        name="id_abastecimento_integracao" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $abastecimento->id_abastecimento_integracao }}">
                                    <input type="hidden" name="id_abastecimento_int"
                                        value="{{ $abastecimento->id_abastecimento_integracao }}">
                                </div>

                                <div>
                                    <label for="descricao_bomba"
                                        class="block text-sm font-medium text-gray-700">Descrição da Bomba</label>
                                    <input type="text" id="descricao_bomba" name="descricao_bomba" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $abastecimento->descricao_bomba }}">
                                </div>

                                <div>
                                    <label for="placa" class="block text-sm font-medium text-gray-700">Placa</label>
                                    <input type="text" id="placa" name="placa" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ $abastecimento->placa }}">
                                </div>
                                @endif

                                @if(isset($abastecimento))
                                <div>
                                    <label for="volume_abastecimento"
                                        class="block text-sm font-medium text-gray-700">Volume Abastecimento</label>
                                    <input type="text" id="volume_abastecimento" name="volume_abastecimento" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ number_format($abastecimento->volume, 2, ',', '.') }}">
                                </div>

                                <div>
                                    <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data do
                                        Abastecimento</label>
                                    <input type="text" id="data_inicio" name="data_inicio" readonly
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ is_object($abastecimento->data_inicio) ? $abastecimento->data_inicio->format('d/m/Y H:i') : (is_string($abastecimento->data_inicio) && $abastecimento->data_inicio ? date('d/m/Y H:i', strtotime($abastecimento->data_inicio)) : '') }}">
                                </div>
                                @endif

                                <div>
                                    @if(isset($tanqueSelecionado))
                                    <label for="id_tanque"
                                        class="block text-sm font-medium text-gray-700">Tanque</label>
                                    <select id="id_tanque" name="id_tanque"
                                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        disabled>
                                        @foreach ($tanques as $tanque)
                                        @if($tanque->value == $tanqueSelecionado)
                                        <option value="{{ $tanque->value }}" selected>{{ $tanque->label }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="id_tanque" value="{{ $tanqueSelecionado }}">
                                    @else
                                    <label for="id_tanque"
                                        class="block text-sm font-medium text-gray-700">Tanque</label>
                                    <select id="id_tanque" name="id_tanque" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        <option value="">Selecione...</option>
                                        @foreach ($tanques as $tanque)
                                        <option value="{{ $tanque->value }}" {{ old('id_tanque')==$tanque->value ?
                                            'selected' : '' }}>
                                            {{ $tanque->label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @endif
                                </div>

                                <div>
                                    <label for="volume_entrada" class="block text-sm font-medium text-gray-700">Volume
                                        de Entrada</label>
                                    <input type="number" id="volume_entrada" name="volume_entrada" step="0.01" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        value="{{ old('volume_entrada', isset($abastecimento) ? $abastecimento->volume : '') }}"
                                        x-on:input="validarVolume" x-ref="volumeEntrada">
                                    <div x-show="volumeError" class="mt-1 text-sm text-red-600" x-text="volumeError">
                                    </div>
                                    @if(isset($abastecimento))
                                    <div x-show="showWarning"
                                        class="mt-1 text-sm text-yellow-600 bg-yellow-50 p-2 rounded border border-yellow-200">
                                        <span class="font-medium">Aviso: </span><span x-text="warningMessage"></span>
                                    </div>
                                    @endif
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
                                {{--
                                <x-icons.save class="h-4 w-4 mr-2 text-white" /> --}}
                                Salvar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
    <script>
        function afericaoForm() {
                return {
                    volumeError: '',
                    showWarning: false,
                    warningMessage: '',
                    
                    validarVolume() {
                        const volumeEntrada = parseFloat(this.$refs.volumeEntrada.value);
                        @if(isset($abastecimento))
                        const volumeAbastecimento = parseFloat('{{ $abastecimento->volume }}');
                        
                        if (isNaN(volumeEntrada) || volumeEntrada <= 0) {
                            this.volumeError = 'O volume de entrada deve ser maior que zero';
                            this.showWarning = false;
                        } else if (volumeEntrada > volumeAbastecimento) {
                            this.volumeError = 'O volume de entrada não pode ser maior que o volume de abastecimento';
                            this.showWarning = false;
                        } else if (volumeEntrada < volumeAbastecimento) {
                            this.volumeError = '';
                            this.showWarning = true;
                            this.warningMessage = 'Atenção: O volume de entrada é menor que o volume abastecido e pode causar inconsistências no saldo do tanque.';
                        } else {
                            this.volumeError = '';
                            this.showWarning = false;
                        }
                        @else
                        if (isNaN(volumeEntrada) || volumeEntrada <= 0) {
                            this.volumeError = 'O volume de entrada deve ser maior que zero';
                        } else {
                            this.volumeError = '';
                        }
                        @endif
                    },

                    init() {
                        // Validação inicial
                        setTimeout(() => {
                            this.validarVolume();
                        }, 100);
                    }
                };
            }
    </script>
    @endpush
</x-app-layout>