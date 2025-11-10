<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    @if ($errors->any())
    <div class="bg-red-100 text-red-800 p-4 rounded">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <form id="valorCombustivelForm" method="POST" action="{{ $action }}" class="space-y-4">
                @csrf
                @if ($method === 'PUT')
                @method('PUT')
                @endif

                <!-- Cabeçalho -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Valor de Combustível</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            {{-- Código --}}
                            <label for="id_valor_combustivel_terceiro"
                                class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" id="id_valor_combustivel_terceiro" name="id_valor_combustivel_terceiro"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $valorCombustiveis->id_valor_combustivel_terceiro ?? '' }}">
                        </div>

                        <div>
                            {{-- Filial --}}
                            <div>
                                <label for="id_filial">Filial</label>
                            </div>
                            <select
                                class="rounded-md w-full border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                name="id_filial" id="id_filial" {{ $habilitarEdicaoUsuario==='true' ? 'disabled' : ''
                                }}>
                                @foreach ($formOptions['filiais'] as $filial)
                                <option value="{{ $filial['value'] }}" {{ (old('id_filial')==$filial['value'] ||
                                    (isset($valorCombustiveis) && $valorCombustiveis->id_filial == $filial['value'])) ?
                                    'selected' : '' }}>
                                    {{ $filial['label'] }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>

                            {{-- Usuário --}}
                            <label for="id_usuario" class="block text-sm font-medium text-gray-700">Usuário</label>
                            <input type="text" id="id_usuario" name="id_usuario" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ auth()->user()->name }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            {{-- Bomba (Bico) --}}
                            <x-forms.smart-select name="boma_combustivel" label="Bomba (Bico)"
                                onchange="ValidarIdBomba()" placeholder="Selecione a bomba..."
                                :options="$formOptions['bombas']"
                                :selected="old('boma_combustivel', isset($valorCombustiveis->boma_combustivel) ? $valorCombustiveis->boma_combustivel : '')"
                                :disabled="$habilitarEdicaoUsuario === 'true'" asyncSearch="false" required="true"
                                onSelectCallback="atualizarDadosBombaCallback" />
                        </div>

                        <div>
                            {{-- Tipo de Combustível --}}
                            <label for="tipoCombustivel" class="block text-sm font-medium text-gray-700">Tipo de
                                Combustível</label>
                            <input type="text" id="tipoCombustivel" name="tipoCombustivel" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('tipoCombustivel', isset($valorCombustiveis->tipoCombustivel->descricao) ?? '') }}">
                        </div>

                        <div>

                            {{-- Valor do Combustível --}}
                            <label for="valor_diesel" class="block text-sm font-medium text-gray-700">Valor do
                                Combustível</label>
                            <input type="number" id="valor_diesel" name="valor_diesel" step="0.01" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('valor_diesel', isset($valorCombustiveis->valor_diesel) ?? '') }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                        <div>

                            {{-- Valor Interno --}}
                            <label for="valor_acrescimo" class="block text-sm font-medium text-gray-700">Valor
                                Interno</label>
                            <input type="number" id="valor_acrescimo" name="valor_acrescimo" step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('valor_acrescimo', isset($valorCombustiveis) ? $valorCombustiveis->valor_acrescimo : '') }}"
                                {{ $habilitarEdicaoUsuario==='true' ? 'readonly' : '' }} required>
                        </div>

                        <div>
                            {{-- Valor Terceiro --}}
                            <label for="valor_terceiro" class="block text-sm font-medium text-gray-700">Valor
                                Terceiro</label>
                            <input type="number" id="valor_terceiro" name="valor_terceiro" step="0.01"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('valor_terceiro', isset($valorCombustiveis) ? $valorCombustiveis->valor_terceiro : '') }}"
                                {{ $habilitarEdicaoUsuario==='true' ? 'readonly' : '' }} required>
                        </div>

                        <div>

                            {{-- Data Início --}}
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data Início</label>
                            <input type="date" id="data_inicio" name="data_inicio"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('data_inicio', isset($valorCombustiveis->data_inicio) ? date('Y-m-d', strtotime($valorCombustiveis->data_inicio)) : '') }}"
                                {{ $habilitarEdicaoUsuario==='true' ? 'readonly' : '' }} required>
                        </div>

                        @php
                        $modo = session('modo'); // Obtém o modo da sessão
                        @endphp

                        @if ($modo === 'editar')
                        <div>

                            {{-- Data Fim --}}
                            <label for="data_fim" class="block text-sm font-medium text-gray-700">Data
                                Fim</label>
                            <input type="date" id="data_fim" name="data_fim"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('data_fim', isset($valorCombustiveis->data_fim) ? date('Y-m-d', strtotime($valorCombustiveis->data_fim)) : '') }}"
                                {{ $habilitarEdicaoUsuario==='true' ? 'readonly' : '' }}>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end space-x-4 mt-6">
                    <button type="button" onclick="limparFormulario()"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Limpar Formulário
                    </button>

                    <a href="{{ route('admin.valorcombustiveis.index') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Voltar
                    </a>

                    @if($method !== 'GET')
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>


<x-bladewind.modal name="validarBombaBico" cancel_button_label="Cancelar" ok_button_label="" type="error"
    title="Não encontrado">
    <h2 class="title"></h2>
</x-bladewind.modal>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para atualizar dados da bomba
        function atualizarDadosBomba(idBomba) {
            if (!idBomba) return;
            
            fetch("{{ route('admin.valorcombustiveis.get-Valor-Bomba') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ idBomba: idBomba })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.error) {
                    document.getElementById('tipoCombustivel').value = data.tipo_combustivel;
                    document.getElementById('valor_diesel').value = data.vlrunitario_interno;
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Erro ao buscar dados da bomba:', error);
            });
        }
        
        // Callback global para atualização de dados da bomba
        window.atualizarDadosBombaCallback = function(idBomba) {
            atualizarDadosBomba(idBomba);
        };
        
        // Monitorar mudanças na seleção da bomba
        const bombaSelect = document.querySelector('[name="boma_combustivel"]');

        if (bombaSelect) {
            bombaSelect.addEventListener("change", function () {
                ValidarIdBomba();
            });

            // Se já houver um valor selecionado ao carregar a página, buscar os dados
            if (bombaSelect.value) {
                atualizarDadosBomba(bombaSelect.value);
            }
        }
    });

    function limparFormulario() {
        if (!confirm('Deseja realmente limpar todos os dados do formulário?')) return;
        
        document.getElementById('valorCombustivelForm').reset();
        document.getElementById('tipoCombustivel').value = '';
        document.getElementById('valor_diesel').value = '';
    }

</script>
@endpush