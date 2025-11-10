<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="bg-white p-4 rounded-lg">
        <div x-data="licenciamentoVeiculosForm()">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Cadastro de Licencimento de Veículos</h3>
            <div class="grid grid-cols-2 gap-2">
                @if ($errors->any())
                <div class="bg-red-100 text-red-800 p-4 rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                {{-- placa --}}
                <div>
                    <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione o veículo..."
                        :options="$placasData"
                        :selected="isset($licenciamentoveiculos) ? $licenciamentoveiculos->id_veiculo : null"
                        required="true" asyncSearch="true" searchUrl="{{ route('admin.veiculos.search') }}"
                        minSearchLength="2" onSelectCallback="atualizarDadosVeiculo" />

                </div>

                <div>
                    <label for="filial" class="block text-sm font-medium text-gray-700">Filial</label>
                    <input type="text" id="filial" readonly
                        class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        x-bind:value="filial">
                    <input type="hidden" id="hidden_filial" name="id_filial">
                </div>

                {{-- ano_licenciamento --}}
                <div>
                    <x-input-label for="ano_licenciamento" value="Ano Licenciamento" />
                    <x-text-input id="ano_licenciamento" type="number" name="ano_licenciamento"
                        value="{{ old('ano_licenciamento', $licenciamentoveiculos->ano_licenciamento ?? '') }}"
                        class="mt-1 block w-full" required />
                </div>

                {{-- crlv --}}
                <div>
                    <x-input-label for="crlv" value="CRLV" />
                    <x-text-input id="crlv" type="number" name="crlv"
                        value="{{ old('crlv', $licenciamentoveiculos->crlv ?? '') }}" class="mt-1 block w-full"
                        required />
                </div>

                {{-- data_emissao_crlv --}}
                <div>
                    <x-input-label for="data_emissao_crlv" value="Data Emissão CRLV" />
                    <x-text-input id="data_emissao_crlv" type="date" name="data_emissao_crlv"
                        value="{{ old('data_emissao_crlv') ?? (optional($licenciamentoveiculos->data_emissao_crlv ?? null)->format('Y-m-d') ?? '') }}"
                        class="mt-1 block w-full" required />
                </div>

                {{-- data_vencimento --}}
                <div>
                    <x-input-label for="data_vencimento" value="Data Vencimento" />
                    <x-text-input id="data_vencimento" type="date" name="data_vencimento"
                        value="{{ old('data_vencimento') ?? (optional($licenciamentoveiculos->data_vencimento ?? null)->format('Y-m-d') ?? '') }}"
                        class="mt-1 block w-full" required />
                </div>

                {{-- valor_previsto --}}
                <div class="mb-4">
                    <x-input-label for="valor_previsto_valor" value="Valor Previsto" />
                    <input type="text" id="valor_previsto_valor" name="valor_previsto_valor" step="0.00" required
                        class="text-right mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        value="{{ old('valor_previsto_valor', isset($licenciamentoveiculos->valor_previsto_valor) ? number_format($licenciamentoveiculos->valor_previsto_valor, 2, ',', '') : '') }}">
                </div>

                {{-- valor_pago --}}
                <div class="mb-4">
                    <x-input-label for="valor_pago_licenciamento" value="Valor Pago" />
                    <input type="text" id="valor_pago_licenciamento" name="valor_pago_licenciamento" step="0.00" required
                        class="text-right mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        value="{{ old('valor_pago_licenciamento', isset($licenciamentoveiculos->valor_pago_licenciamento) ? number_format($licenciamentoveiculos->valor_pago_licenciamento, 2, ',', '') : '') }}">
                </div>

                {{-- situacao --}}
                {{-- <div class="mb-4">
                    <x-input-label for="situacao" value="Situação" />
                    <select id="situacao" name="situacao" class="w-full border border-gray-300 rounded-md">
                        <option value="">Selecione...</option>
                        <option value="Cancelados" {{ old('situacao', $licenciamentoveiculos->situacao ?? '') ==
                            'Cancelados' ? 'selected' : '' }}>Cancelados</option>
                        <option value="A vencer" {{ old('situacao', $licenciamentoveiculos->situacao ?? '') == 'A
                            vencer' ?
                            'selected' : '' }}>A vencer</option>
                        <option value="Vencido" {{ old('situacao', $licenciamentoveiculos->situacao ?? '') == 'Vencido'
                            ?
                            'selected' : '' }}>Vencido</option>
                        <option value="Quitado" {{ old('situacao', $licenciamentoveiculos->situacao ?? '') == 'Quitado'
                            ?
                            'selected' : '' }}>Quitado</option>
                    </select>
                </div> --}}

            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.licenciamentoveiculos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit" id="save-button"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($licenciamentoveiculos) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const valorPrevisto = document.getElementById('valor_previsto_valor');
        const valorPago = document.getElementById('valor_pago_licenciamento');

        if (valorPrevisto.value) {
            valorPrevisto.value = formataReal(valorPrevisto.value)
        }

        if (valorPago.value) {
            valorPago.value = formataReal(valorPago.value)
        }

        function formataReal(valor) {
            const formatador = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            });
            valor = valor.replace(/\D/g, '');
            let valorNumerico = parseFloat(valor) / 100;
            if (!isNaN(valorNumerico)) {
                return formatador.format(valorNumerico);
            } else {
                return '';
            }
        }

        valorPrevisto.addEventListener('input', function() {
            valorPrevisto.value = formataReal(valorPrevisto.value)
        })

        valorPago.addEventListener('input', function() {
            valorPago.value = formataReal(valorPago.value)
        })
    });

    document.getElementById('valor_previsto_valor').addEventListener('change', function() {
            // Atualiza o atributo data-exact-value com o novo valor
            this.setAttribute('data-exact-value', this.value);
        });

    // Antes de enviar o formulário, substitui o valor pelo exato
    document.querySelector('form').addEventListener('submit', function() {
        var input = document.getElementById('valor_previsto_valor');
        input.value = input.getAttribute('data-exact-value');
    });

    function licenciamentoVeiculosForm() {
        return {
            filial: "{{ isset($licenciamentoveiculos) && $licenciamentoveiculos->veiculo && $licenciamentoveiculos->veiculo->filial ? $licenciamentoveiculos->veiculo->filial->name : 'Selecionar uma placa...' }}",

            init() {
                // Listener para atualizar a filial quando um veículo for selecionado
                window.addEventListener('id_veiculo:selected', (event) => {
                    if (event.detail && event.detail.value) {
                        this.atualizarDadosVeiculo(event.detail.value);
                    }
                });
            },

            atualizarDadosVeiculo(id) {
                this.filial = 'Carregando...'; // Mensagem temporária

                fetch('/admin/licenciamentoveiculos/get-vehicle-data', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        },
                        body: JSON.stringify({
                            placa: id
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.error) {
                            this.filial = String(data.filial || 'Selecionar uma placa...');
                        } else {
                            console.error('Erro ao buscar dados do veículo:', data.error);
                            this.filial = 'Erro ao carregar';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar dados do veículo:', error);
                        this.filial = 'Erro ao carregar';
                    });
            }
        };
    }
</script>
@endpush