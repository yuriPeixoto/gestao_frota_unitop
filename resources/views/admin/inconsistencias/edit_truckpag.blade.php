<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Inconsistência TruckPag') }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.inconsistencias.index') }}?tab=truckpag"
                    class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Coluna 1: Detalhes da Inconsistência -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Detalhes da Inconsistência</h3>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Código:</p>
                                <p class="font-medium">{{ $inconsistencia->id_abastecimento_integracao }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Data:</p>
                                <p class="font-medium">{{ $inconsistencia->data_inclusao?->format('d/m/Y H:i') }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Placa:</p>
                                <p class="font-medium">{{ $inconsistencia->placa }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Posto:</p>
                                <p class="font-medium">{{ $inconsistencia->descricao_bomba }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Motivo da Inconsistência:</p>
                                <p class="font-medium text-red-600">{{ $inconsistencia->mensagem }}</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Volume:</p>
                                <p class="font-medium">{{ number_format($inconsistencia->volume, 2, ',', '.') }} L</p>
                            </div>

                            <div class="mb-4">
                                <p class="text-sm text-gray-600">Tipo de Serviço:</p>
                                <p class="font-medium">{{ $inconsistencia->tipo_servico }}</p>
                            </div>
                        </div>

                        <!-- Coluna 2: Formulário de Correção -->
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Corrigir Inconsistência</h3>

                            <form
                                action="{{ route('admin.inconsistencias.truckpag.update', $inconsistencia->id_abastecimento_integracao) }}"
                                method="POST" id="formEditTruckpag">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <x-input-label for="km_abastecimento" value="Km de Abastecimento" />
                                    <div class="flex items-center">
                                        <x-text-input id="km_abastecimento" type="number" name="km_abastecimento"
                                            :value="old('km_abastecimento', $inconsistencia->km_abastecimento)"
                                            class="mt-1 block w-full" required />
                                        <button type="button" id="btnAtualizarKm"
                                            class="mt-1 ml-2 inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
                                            onclick="atualizarKM()">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Atualizar KM
                                        </button>
                                    </div>
                                    <x-input-error :messages="$errors->get('km_abastecimento')" class="mt-2" />
                                    <p class="text-xs text-gray-500 mt-1">Informe o hodômetro correto no momento do
                                        abastecimento.</p>
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="km_anterior" value="KM Anterior" />
                                    <x-text-input id="km_anterior" type="number" name="km_anterior"
                                        :value="old('km_anterior', $inconsistencia->km_anterior)"
                                        class="mt-1 block w-full bg-gray-100" readonly />
                                    <p class="text-xs text-gray-500 mt-1">Último KM registrado para o veículo.</p>
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="id_veiculo" value="Veículo" />
                                    <select id="id_veiculo" name="id_veiculo"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required {{ $isPlacaNaoEncontrada ? '' : 'disabled' }}>
                                        <option value="">Selecione um veículo...</option>
                                        @foreach($veiculos as $veiculo)
                                        <option value="{{ $veiculo->id_veiculo }}" {{ old('id_veiculo',
                                            $inconsistencia->id_veiculo) == $veiculo->id_veiculo ? 'selected' : '' }}>
                                            {{ $veiculo->placa }} - {{ $veiculo->descricao_veiculo }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_veiculo')" class="mt-2" />
                                    @if(!$isPlacaNaoEncontrada)
                                    <input type="hidden" name="id_veiculo" value="{{ $inconsistencia->id_veiculo }}" />
                                    @endif
                                </div>

                                <div class="mb-4">
                                    <x-input-label for="id_departamento" value="Departamento" />
                                    <select id="id_departamento" name="id_departamento"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required disabled>
                                        <option value="">Selecione um departamento...</option>
                                        @foreach($departamentos as $departamento)
                                        <option value="{{ $departamento->id_departamento }}" {{ old('id_departamento',
                                            $inconsistencia->id_departamento) == $departamento->id_departamento ?
                                            'selected' : '' }}>
                                            {{ $departamento->descricao_departamento }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_departamento')" class="mt-2" />
                                    <input type="hidden" name="id_departamento"
                                        value="{{ $inconsistencia->id_departamento }}" />
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="id_filial" value="Filial" />
                                    <select id="id_filial" name="id_filial"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required disabled>
                                        <option value="">Selecione uma filial...</option>
                                        @foreach($filiais as $filial)
                                        <option value="{{ $filial->id }}" {{ old('id_filial', $inconsistencia->
                                            id_filial) == $filial->id ? 'selected' : '' }}>
                                            {{ $filial->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_filial')" class="mt-2" />
                                    <input type="hidden" name="id_filial" value="{{ $inconsistencia->id_filial }}" />
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <a href="{{ route('admin.inconsistencias.index') }}?tab=truckpag"
                                        class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-300 focus:outline-none focus:border-gray-300 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150 mr-3">
                                        Cancelar
                                    </a>

                                    <x-primary-button id="btnSalvar">
                                        {{ __('Salvar') }}
                                    </x-primary-button>
                                </div>

                                <div id="msgSuccess" class="hidden mt-4 p-3 bg-green-100 text-green-800 rounded">
                                    <p class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        KM atualizado com sucesso! Redirecionando...
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function atualizarKM() {
            // Mostrar um indicador de carregamento
            const button = document.getElementById('btnAtualizarKm');
            button.disabled = true;
            button.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Atualizando...
            `;

            // Obter valores necessários
            const idVeiculo = document.getElementById('id_veiculo').value;
            const dataAbastecimento = "{{ $inconsistencia->data_inclusao?->format('Y-m-d H:i:s') }}";

            // Realizar a requisição para obter o KM atualizado
            fetch('{{ route('admin.inconsistencias.getKmInfo') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_veiculo: idVeiculo,
                    data_abastecimento: dataAbastecimento
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Atualizar os campos com os valores recebidos
                    const kmField = document.getElementById('km_abastecimento');
                    const kmAnteriorField = document.getElementById('km_anterior');
                    
                    // Atualizar o km de abastecimento
                    if (data.data.hodometro) {
                        kmField.value = data.data.hodometro;
                    }
                    
                    // Atualizar o km anterior
                    if (data.data.km_anterior) {
                        kmAnteriorField.value = data.data.km_anterior;
                    }
                    
                    if (data.data.hodometro) {
                        // Verificar se o KM é válido 
                        if (parseInt(data.data.hodometro) > parseInt(data.data.km_anterior || 0)) {
                            // Exibir mensagem de sucesso
                            document.getElementById('msgSuccess').classList.remove('hidden');
                            
                            // Aplicar o KM automaticamente após 1 segundo
                            setTimeout(() => {
                                // Enviar o formulário automaticamente
                                document.getElementById('formEditTruckpag').submit();
                            }, 1000);
                            return;
                        }
                    }
                } else {
                    console.error('Erro ao buscar KM:', data.message);
                }
                
                // Restaurar o botão se não redirecionou
                button.disabled = false;
                button.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Atualizar KM
                `;
            })
            .catch(error => {
                console.error('Erro ao buscar KM:', error);
                
                // Restaurar o botão
                button.disabled = false;
                button.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Atualizar KM
                `;
            });
        }
    </script>
    <script>
        // Função para carregar departamento e filial ao selecionar um veículo
        document.addEventListener('DOMContentLoaded', function() {
            // Referência ao select de veículo
            const veiculoSelect = document.getElementById('id_veiculo');
            
            // Adiciona evento de mudança
            if (veiculoSelect) {
                veiculoSelect.addEventListener('change', function() {
                    const veiculoId = this.value;
                    if (veiculoId) {
                        carregarDadosVeiculo(veiculoId);
                    }
                });
            }
        });
    
        // Função para carregar dados do veículo
        function carregarDadosVeiculo(veiculoId) {
            // Mostrar indicador de carregamento ou desabilitar selects
            const departamentoSelect = document.getElementById('id_departamento');
            const filialSelect = document.getElementById('id_filial');
            
            // Desabilitar selects durante o carregamento
            departamentoSelect.disabled = true;
            filialSelect.disabled = true;
            
            // Fazer requisição AJAX para obter informações do veículo
            fetch(`/admin/inconsistencias/getVeiculoInfo/${veiculoId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Preencher campos de departamento e filial
                    if (data.data.id_departamento) {
                        // Atualizar o select de departamento visualmente
                        for (let i = 0; i < departamentoSelect.options.length; i++) {
                            if (departamentoSelect.options[i].value == data.data.id_departamento) {
                                departamentoSelect.selectedIndex = i;
                                break;
                            }
                        }
                        
                        // Atualizar campo hidden
                        const departamentoHidden = document.querySelector('input[name="id_departamento"]');
                        if (departamentoHidden) {
                            departamentoHidden.value = data.data.id_departamento;
                        }
                    }
                    
                    if (data.data.id_filial) {
                        // Atualizar o select de filial visualmente
                        for (let i = 0; i < filialSelect.options.length; i++) {
                            if (filialSelect.options[i].value == data.data.id_filial) {
                                filialSelect.selectedIndex = i;
                                break;
                            }
                        }
                        
                        // Atualizar campo hidden
                        const filialHidden = document.querySelector('input[name="id_filial"]');
                        if (filialHidden) {
                            filialHidden.value = data.data.id_filial;
                        }
                    }
                } else {
                    console.error('Erro ao carregar dados do veículo:', data.message);
                }
            })
            .catch(error => {
                console.error('Erro ao buscar informações do veículo:', error);
            })
            .finally(() => {
                // Manter selects desabilitados conforme regra do sistema
                departamentoSelect.disabled = true;
                filialSelect.disabled = true;
            });
        }
    </script>
    @endpush
</x-app-layout>