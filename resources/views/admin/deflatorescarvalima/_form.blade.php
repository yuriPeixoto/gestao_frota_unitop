@if ($errors->any())
<div class="mb-4 rounded bg-red-50 p-4">
    <ul class="list-inside list-disc text-red-600">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

    <div class="p-4 bg-white border-b border-gray-200">

        <form action="{{ $action ?? route('admin.deflatorescarvalima.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input label="Cód. Deflatores Motoristas:" name="id_deflatores"
                        value="{{ old('id_deflatores', $evento->id_deflatores ?? '') }}" readonly />
                </div>
                <div>
                    <x-forms.input label="Descrição Deflator:" name="descricao_evento"
                        value="{{ old('descricao_evento', $evento->descricao_evento ?? '') }}" />
                </div>
            </div>
            <br />
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <div>
                    <x-forms.input class="valor-moeda" label="Valor:" name="valor"
                        value="{{ old('valor', number_format($evento->valor ?? 0, 2, ',', '')) }}" />
                </div>
                <div>
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}" />
                    <x-forms.input label="Usuario:" name="usuario" value="{{ auth()->user()->name }}" disabled />
                </div>
                <div>
                    <input type="hidden" name="unidade_id" value="{{ auth()->user()->filial_id }}" />
                    <x-forms.smart-select label="Filial:" name="filial" :options="$filial"
                        value="{{ auth()->user()->filial->name }}" />
                </div>

            </div>

            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.deflatorescarvalima.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($evento) && $evento?->id_deflatores ? 'Atualizar' : 'Salvar' }}

                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@include('admin.deflatorescarvalima._scripts')
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