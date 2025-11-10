<form method="GET" action="{{ route('admin.ipvaveiculos.index') }}" class="space-y-4"
    hx-get="{{ route('admin.ipvaveiculos.index') }}" hx-target="#results-table" hx-select="#results-table"
    hx-trigger="change delay:500ms, search">

    {{-- Exibir mensagens de erro/confirmação --}}
    @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm leading-5 text-green-700">
                        {{ session('success') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm leading-5 text-red-700">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="id_ipva_veiculo" label="Código IPVA" value="{{ request('id_ipva_veiculo') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Veículo (Placa)" placeholder="Selecione o veículo..."
                :options="$veiculosFrequentes ?? []" :searchUrl="route('admin.api.veiculos.search')" :selected="request('id_veiculo')" asyncSearch="true" />
        </div>

        <div>
            <label for="filial_veiculo" class="block text-sm font-medium text-gray-700 mb-1">Filial do Veiculo</label>
            <select name="filial_veiculo" id="filial_veiculo"
                class="mt-1 block w-full rounded-md shadow-sm sm:text-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Selecione a filial...</option>
                @foreach ($filiais as $filial)
                    <option value="{{ $filial['value'] }}">{{ $filial['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <x-forms.input name="renavam" label="RENAVAM" value="{{ request('renavam') }}" />
        </div>

        <div>
            <x-forms.input type="number" name="ano_validade" label="Ano de Validade"
                value="{{ request('ano_validade') }}" min="2000" max="{{ date('Y') + 1 }}" />
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.select name="status_ipva" label="Status" :options="[
                '' => 'Todos',
                'QUITADO' => 'Quitado',
                'PARCIAL' => 'Parcial',
                'PENDENTE' => 'Pendente',
            ]" :selected="request('status_ipva')" />
        </div>

        <div>
            <x-forms.input type="date" name="data_inicial" label="Data Pagamento (Início)"
                value="{{ request('data_inicial') }}" />
        </div>

        <div>
            <x-forms.input type="date" name="data_final" label="Data Pagamento (Fim)"
                value="{{ request('data_final') }}" />
        </div>

        <div>
            <x-forms.input type="text" name="valor_pago" label="Valor Pago (Acima de)"
                value="{{ request('valor_pago') }}" class="money-mask" />
        </div>

        <div>
            <x-forms.smart-select name="status" label="Ativo/Inativo" placeholder="Selecione..." :options="[['value' => 'ativo', 'label' => 'Ativo'], ['value' => 'inativo', 'label' => 'Inativo']]"
                :selected="request('status')" />
        </div>
    </div>

    <div class="flex justify-between mt-4">
        <div class="flex space-x-2">
            <a href="{{ route('admin.ipvaveiculos.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
        <div>
            {{-- Usar o novo componente de botões de exportação --}}
            <x-ui.export-buttons route="admin.ipvaveiculos" :formats="['pdf', 'csv', 'xls', 'xml']" />
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar máscaras monetárias
            document.querySelectorAll('.money-mask').forEach((element) => {
                element.addEventListener('focus', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = (parseFloat(value) / 100).toFixed(2).replace('.', ',');
                    e.target.value = value;
                });

                element.addEventListener('blur', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = (parseFloat(value) / 100);
                    if (isNaN(value)) value = 0.0;
                    e.target.value = value.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                });

                element.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^\d,]/g, '');
                });
            });
        });
    </script>
@endpush
