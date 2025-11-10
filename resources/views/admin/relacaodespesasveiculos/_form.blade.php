<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        @if (session('notification'))
        <x-notification :notification="session('notification')" />
        @endif
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            <div>
                                <label for="id_despesas_veiculos" class="block text-sm font-medium text-gray-700">Cód.
                                    Despesas</label>
                                <input type="text" id="id_despesas_veiculos" name="id_despesas_veiculos" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->id_despesas_veiculos ?? '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select id="id_veiculo" name="id_veiculo" :options="$veiculosFrequentes"
                                    label="Placa" :searchUrl="route('admin.veiculos.search')"
                                    :selected="old('id_veiculo', $manutencaoConfig->id_veiculo ?? '')" required />
                            </div>

                            <div>
                                <x-forms.smart-select name="id_departamento" label="Departamento"
                                    :options="$departamentos"
                                    :selected="old('id_departamento', $manutencaoConfig->id_departamento ?? '')"
                                    required />
                            </div>

                            <div>
                                <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
                                <select id="id_filial" name="id_filial" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($filiais as $filial)
                                    <option value="{{ $filial->id }}" {{ old('id_filial', $manutencaoConfig->id_filial
                                        ?? '') == $filial->id ? 'selected' : '' }}>
                                        {{ $filial->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <div>
                                <label for="valor_despesa" class="block text-sm font-medium text-gray-700">Valor
                                    Despesa</label>
                                <input type="text" id="valor_despesa" name="valor_despesa"
                                    class="valor-moeda mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('valor_despesa', $manutencaoConfig->valor_despesa ?? 0) }}">
                            </div>

                            <div>
                                <label for="valor_frete" class="block text-sm font-medium text-gray-700">Valor
                                    Frete</label>
                                <input type="text" id="valor_frete" name="valor_frete"
                                    class="valor-moeda mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('valor_frete', $manutencaoConfig->valor_frete ?? 0) }}">
                            </div>

                            <div>
                                <label for="valor_pago" class="block text-sm font-medium text-gray-700">Valor
                                    Pago</label>
                                <input type="text" id="valor_pago" name="valor_pago"
                                    class="valor-moeda mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ old('valor_pago', $manutencaoConfig->valor_pago ?? 0) }}">
                            </div>

                            <div>
                                <label for="numero_nf" class="block text-sm font-medium text-gray-700">N da NF</label>
                                <input type="number" id="numero_nf" name="numero_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->numero_nf ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                            <div>
                                <label for="serie_nf" class="block text-sm font-medium text-gray-700">Série NF</label>
                                <input type="number" id="serie_nf" name="serie_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutencaoConfig->serie_nf ?? '' }}">
                            </div>

                            <div>
                                <x-forms.smart-select id="id_fornecedor" name="id_fornecedor"
                                    :options="$fornecedoresFrequentes" label="Fornecedor"
                                    :searchUrl="route('admin.fornecedores.search')"
                                    :selected="old('id_fornecedor', $manutencaoConfig->id_fornecedor ?? '')" required />
                            </div>

                            <div>
                                <label for="id_tipo_despesas" class="block text-sm font-medium text-gray-700">Tipo
                                    Despesas</label>
                                <select id="id_tipo_despesas" name="id_tipo_despesas"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($tipoDespesas as $tipoDespesa)
                                    <option value="{{ $tipoDespesa->id_tipo_despesas }}" {{ old('id_tipo_despesas',
                                        $manutencaoConfig->id_tipo_despesas ?? '') == $tipoDespesa->id_tipo_despesas ?
                                        'selected' : '' }}>
                                        {{ $tipoDespesa->descricao_despesas }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Aplicar Rateio</label>
                                <div class="mt-1 inline-flex border border-gray-300 rounded-lg overflow-hidden">
                                    <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                        <input type="radio" name="aplicar_rateio" value="1" class="hidden peer" {{
                                            old('aplicar_rateio', $manutencaoConfig->aplicar_rateio ?? '0') == '1' ?
                                        'checked' : '' }}>
                                        <span
                                            class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Sim</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer text-gray-700 bg-white">
                                        <input type="radio" name="aplicar_rateio" value="0" class="hidden peer" {{
                                            old('aplicar_rateio', $manutencaoConfig->aplicar_rateio ?? '0') == '0' ?
                                        'checked' : '' }}>
                                        <span
                                            class="px-4 py-2 font-bold peer-checked:text-white peer-checked:bg-indigo-600">Não</span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                            <div>
                                <label for="observacao"
                                    class="block text-sm font-medium text-gray-700">Observação</label>
                                <textarea id="observacao" name="observacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $manutencaoConfig->observacao ?? '') }}</textarea>
                            </div>

                        </div>

                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <button type="button" x-on:click="limparFormulario"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Limpar Formulário
                        </button>

                        <a href="{{ route('admin.relacaodespesasveiculos.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

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