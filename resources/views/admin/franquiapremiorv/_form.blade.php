@if (session('error'))
<div class="alert-danger alert">{{ session('error') }}</div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form action="{{ $action ?? route('admin.franquiapremiorv.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input label="Cód. Franquia:" name="id_franquia_premio_rv"
                        value="{{ old('id_franquia_premio_rv', $franquia->id_franquia_premio_rv ?? '') }}" readonly />
                </div>
                <div>
                    <x-forms.input type="datetime-local" label="Data Inclusão:" name="data_inclusao" value="{{ isset($franquia) && $franquia->data_inclusao
    ? \Carbon\Carbon::parse($franquia->data_inclusao)->format('Y-m-d\TH:i')
    : now()->format('Y-m-d\TH:i') }}" readonly />
                </div>


            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input class="valor-moeda" label="Valor:" name="valor"
                        value="{{ old('valor', $franquia->valor ?? '') }}" />
                </div>
                <div>
                    <x-forms.smart-select label="Operação:" name="id_operacao"
                        value="{{ old('id_operacao', $franquia->id_operacao ?? '') }}" :options="$operador" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input label="Media:" name="media" value="{{ old('media', $franquia->media ?? '') }}" />
                </div>
                <div>
                    <input type="hidden" name="id_filial" value="{{ auth()->user()->filial_id ?? '' }}" />
                    <x-forms.input label="Filial:" name="filial" value="{{ auth()->user()->filial->name ?? '' }}"
                        readonly />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.smart-select label="Categoria:" name="id_categoria"
                        value="{{ old('id_categoria', $franquia->id_categoria ?? '') }}" :options="$categoria" />
                </div>
                <div>
                    <x-forms.smart-select label="Tipo Equipamento:" name="id_tipoequipamento"
                        value="{{ old('id_tipoequipamento', $franquia->id_tipoequipamento ?? '') }}"
                        :options="$equipamento" />
                </div>
            </div>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <input type="hidden" name="usuario_inclusao" value="{{ auth()->user()->id ?? '' }}" />
                    <x-forms.input label="Usuário:" name="usuario" value="{{ auth()->user()->name ?? ''}}" readonly />
                </div>
                <div>
                    <x-forms.input label="Pesobruto:" name="pesobruto"
                        value="{{ old('pesobruto', $franquia->pesobruto ?? '') }}" />
                </div>
            </div>
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

            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.franquiapremiorv.index') }}"
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
@include('admin.franquiapremiorv._scripts')
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
                campo.value = 'R$ 0,00';
            }

            // Formata enquanto digita
            campo.addEventListener('input', function(e) {
                let valor = e.target.value;
                e.target.value = formatarMoeda(valor);
            });
        });
    });

</script>