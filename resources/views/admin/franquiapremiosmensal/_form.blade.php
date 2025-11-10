@if (session('error'))
<div class="alert-danger alert">{{ session('error') }}</div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form action="{{ $action ?? route('admin.franquiapremiosmensal.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input label="Cód. Franquia:" name="id_franquia_premio_mensal"
                        value="{{ old('id_franquia_premio_mensal', $franquia->id_franquia_premio_mensal ?? '') }}"
                        readonly />
                </div>
                <div>
                    <x-forms.input type="datetime-local" label="Data Inclusão:" name="data_inclusao" value="{{ isset($franquia) && $franquia->data_inclusao
    ? \Carbon\Carbon::parse($franquia->data_inclusao)->format('Y-m-d\TH:i')
    : now()->format('Y-m-d\TH:i') }}" readonly />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.smart-select label="Operação:" name="id_operacao"
                        value="{{ old('id_operacao', $franquia->id_operacao ?? '') }}" :options="$operador" />
                </div>
                <div>
                    <x-forms.smart-select label="Tipo Equipamento:" name="id_tipoequipamento"
                        value="{{ old('id_tipoequipamento', $franquia->id_tipoequipamento ?? '') }}"
                        :options="$equipamento" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.smart-select label="SubCategoria:" name="id_subcategoria"
                        value="{{ old('id_subcategoria', $franquia->id_subcategoria ?? '') }}"
                        :options="$subcategoria" />
                </div>
                <div>
                    <x-forms.smart-select label="Step:" name="step" value="{{ old('step', $franquia->step ?? '') }}"
                        :options="$step" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="Media:" name="media"
                        value="{{ old('media', $franquia->media ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="0 a 1000:" name="_0_1000"
                        value="{{ old('_0_1000', $franquia->_0_1000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="1000:" name="_1000"
                        value="{{ old('_1000', $franquia->_1000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="2000:" name="_2000"
                        value="{{ old('_2000', $franquia->_2000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="3000:" name="_3000"
                        value="{{ old('_3000', $franquia->_3000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="4000:" name="_4000"
                        value="{{ old('_4000', $franquia->_4000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="5000:" name="_5000"
                        value="{{ old('_5000', $franquia->_5000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="6000:" name="_6000"
                        value="{{ old('_6000', $franquia->_6000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="7000:" name="_7000"
                        value="{{ old('_7000', $franquia->_7000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="8000:" name="_8000"
                        value="{{ old('_8000', $franquia->_8000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="9000:" name="_9000"
                        value="{{ old('_9000', $franquia->_9000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="10000:" name="_10000"
                        value="{{ old('_10000', $franquia->_10000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="11000:" name="_11000"
                        value="{{ old('_11000', $franquia->_11000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="12000:" name="_12000"
                        value="{{ old('_12000', $franquia->_12000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="13000:" name="_13000"
                        value="{{ old('_13000', $franquia->_13000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="14000:" name="_14000"
                        value="{{ old('_14000', $franquia->_14000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <x-forms.input class="valor-moeda" label="15000:" name="_15000"
                        value="{{ old('_15000', $franquia->_15000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="16000:" name="_16000"
                        value="{{ old('_16000', $franquia->_16000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="17000:" name="_17000"
                        value="{{ old('_17000', $franquia->_17000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div>
                    <x-forms.input class="valor-moeda" label="18000:" name="_18000"
                        value="{{ old('_18000', $franquia->_18000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="19000:" name="_19000"
                        value="{{ old('_19000', $franquia->_19000 ?? '') }}" />
                </div>
                <div>
                    <x-forms.input class="valor-moeda" label="20000:" name="_20000"
                        value="{{ old('_20000', $franquia->_20000 ?? '') }}" />
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <input type="hidden" name="id_filial" value="{{ auth()->user()->filial_id ?? '' }}" />
                    <x-forms.input label="Filial:" name="filial" value="{{ auth()->user()->filial->name ?? '' }}"
                        readonly />
                </div>
                <div>
                    <input type="hidden" name="usuario_inclusao" value="{{ auth()->user()->id ?? '' }}" />
                    <x-forms.input label="Usuário:" name="usuario" value="{{ auth()->user()->name ?? ''}}" readonly />
                </div>

            </div>
            <!------------------------------------------------------------------------------>
            <br />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="flex items-center space-x-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ativo:</label>
                    <label class="flex items-center space-x-1">
                        <input type="radio" name="ativo" value="1" {{ old('ativo', $franquia->ativo ?? 1) == 1 ?
                        'checked' : '' }}
                        class="text-green-600 border-gray-300 focus:ring-green-500">
                        <span>Sim</span>
                    </label>

                    <label class="flex items-center space-x-1">
                        <input type="radio" name="ativo" value="0" {{ old('ativo', $franquia->ativo ?? 1) == 0 ?
                        'checked' : '' }}
                        class="text-red-600 border-gray-300 focus:ring-red-500">
                        <span>Não</span>
                    </label>
                </div>
            </div>
            <!------------------------------------------------------------------------------>
            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.franquiapremiosmensal.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($franquia) && $franquia?->id ? 'Atualizar' : 'Salvar' }}

                </button>
            </div>

        </form>
    </div>
</div>
@push('scripts')
@include('admin.franquiapremiosmensal._scripts')
@endpush

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para formatar como moeda
        function formatarMoeda(valor) {
            // Remove tudo que não é número
            let numero = valor.replace(/\D/g, '');
            
            // Converte para float e divide por 100 para ter decimais
            numero = (numero / 100).toFixed(2);
            
            // Formata como moeda brasileira
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(numero);
        }

        // Aplica a formatação aos campos com a classe 'valor-moeda'
        document.querySelectorAll('.valor-moeda').forEach(function(campo) {
            // Formata o valor inicial
            if (campo.value && campo.value !== '0') {
                campo.value = formatarMoeda(campo.value.toString());
            } else {
                campo.value = '0.00';
            }

            // Formata enquanto digita
            campo.addEventListener('input', function(e) {
                let valor = e.target.value;
                e.target.value = formatarMoeda(valor);
            });
        });
    });

</script>