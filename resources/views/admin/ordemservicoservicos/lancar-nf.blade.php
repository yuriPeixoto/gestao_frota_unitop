<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Cadastro NF Serviço') }}
            </h2>
            <div>
                <a href="{{ route('admin.ordemservicoservicos.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-icons.arrow-back class="mr-2 h-4 w-4" />
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="border-b border-gray-200 bg-white p-4">
            <div class="border-b border-gray-200 bg-white p-6">
                <form action="{{ route('admin.ordemservicoservicos.gravar-nf') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Informações da NF -->
                    <div class="mb-6 rounded-lg bg-gray-50 p-4">
                        <!-- Linha 1 -->
                        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-5">
                            <div>
                                <label for="id_nf_ordem" class="block text-sm font-medium text-gray-700">Cód.
                                    NF:</label>
                                <input type="text" id="id_nf_ordem" name="id_nf_ordem" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="numero_nf" class="block text-sm font-medium text-gray-700">Nº da Nota
                                    Fiscal:</label>
                                <input type="text" id="numero_nf" name="numero_nf" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    maxlength="9" pattern="[0-9]*">
                            </div>

                            <div>
                                <label for="serie" class="block text-sm font-medium text-gray-700">Série:</label>
                                <input type="text" id="serie" name="serie" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div>
                                <label for="data_emissao_nf" class="block text-sm font-medium text-gray-700">Data
                                    Emissão NF:</label>
                                <input type="datetime-local" id="data_emissao_nf" name="data_emissao_nf" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ date('Y-m-d\TH:i') }}">
                            </div>

                            <div>
                                <label for="chave_nf" class="block text-sm font-medium text-gray-700">Chave NF:</label>
                                <input type="text" id="chave_nf" name="chave_nf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    maxlength="44">
                            </div>
                        </div>

                        <!-- Linha 2 -->
                        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-5">
                            <div>
                                <label for="id_ordem_servico" class="block text-sm font-medium text-gray-700">Cód.
                                    O.S:</label>
                                <input type="text" id="id_ordem_servico" name="id_ordem_servico" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $ordemServicoId }}">
                            </div>

                            <div class="md:col-span-2">
                                <label for="id_fornecedor"
                                    class="block text-sm font-medium text-gray-700">Fornecedor:</label>
                                <select id="id_fornecedor" name="id_fornecedor" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor['value'] }}"
                                            {{ $fornecedorId == $fornecedor['value'] ? 'selected' : '' }}>
                                            {{ $fornecedor['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="valor_bruto_nf" class="block text-sm font-medium text-gray-700">Valor
                                    Bruto:</label>
                                <input type="text" id="valor_bruto_nf" name="valor_bruto_nf" required
                                    class="money-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ number_format($valorTotal, 2, ',', '.') }}">
                            </div>

                            <div>
                                <label for="valor_descontonf" class="block text-sm font-medium text-gray-700">Valor
                                    Desconto:</label>
                                <input type="text" id="valor_descontonf" name="valor_descontonf"
                                    class="money-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="0,00">
                            </div>
                        </div>

                        <!-- Linha 3 -->
                        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-5">
                            <div>
                                <label for="valor_liquido_nf" class="block text-sm font-medium text-gray-700">Valor
                                    Líquido:</label>
                                <input type="text" id="valor_liquido_nf" name="valor_liquido_nf" required
                                    class="money-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ number_format($valorTotal, 2, ',', '.') }}">
                            </div>

                            <div class="md:col-span-4">
                                <label for="observacao"
                                    class="block text-sm font-medium text-gray-700">Observação:</label>
                                <textarea id="observacao" name="observacao" rows="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Serviços Selecionados -->
                    <div class="rounded-lg bg-gray-50 p-4">
                        <h3 class="mb-4 text-lg font-medium text-gray-800">Serviços Selecionados</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            O.S.
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Serviço
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Valor
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    @php $totalServicos = 0; @endphp

                                    @foreach ($servicos as $servico)
                                        <tr>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                                {{ $servico->id_ordem_servico }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ $servico->servicos->descricao_servico ?? 'N/A' }}
                                            </td>
                                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                {{ 'R$ ' . number_format($servico->valor_total_com_desconto ?? 0, 2, ',', '.') }}
                                            </td>
                                        </tr>

                                        @php $totalServicos += ($servico->valor_total_com_desconto ?? 0); @endphp
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-bold text-gray-900">
                                            Total:
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-4 text-sm font-bold text-gray-900">
                                            {{ 'R$ ' . number_format($totalServicos, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-4">
                            <div class="border-l-4 border-yellow-400 bg-yellow-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-yellow-700">
                                            Confira se o valor total dos serviços
                                            ({{ 'R$ ' . number_format($totalServicos, 2, ',', '.') }}) corresponde ao
                                            valor da NF que está sendo lançada.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-between space-x-4">
                        <button type="button" id="btn-limpar"
                            class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Limpar Formulário
                        </button>

                        <div class="flex space-x-4">
                            <a href="{{ route('admin.ordemservicoservicos.index') }}"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                Cancelar
                            </a>

                            <button type="submit"
                                class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                Salvar Registro
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const valorBrutoInput = document.getElementById('valor_bruto_nf');
                const valorDescontoInput = document.getElementById('valor_descontonf');
                const valorLiquidoInput = document.getElementById('valor_liquido_nf');
                const btnLimpar = document.getElementById('btn-limpar');

                // Função para formatar valores monetários
                function formatMoney(value) {
                    if (typeof value === 'string') {
                        value = value.replace(/\./g, '').replace(',', '.');
                    }
                    return parseFloat(value).toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }

                // Função para converter string para número
                function parseMoneyValue(value) {
                    if (typeof value === 'string') {
                        return parseFloat(value.replace(/\./g, '').replace(',', '.'));
                    }
                    return value;
                }

                // Função para calcular o valor líquido
                function calcularValorLiquido() {
                    const valorBruto = parseMoneyValue(valorBrutoInput.value) || 0;
                    const valorDesconto = parseMoneyValue(valorDescontoInput.value) || 0;
                    const valorLiquido = valorBruto - valorDesconto;

                    valorLiquidoInput.value = formatMoney(valorLiquido);
                }

                // Event listeners para os campos de valor
                if (valorBrutoInput && valorDescontoInput) {
                    valorBrutoInput.addEventListener('input', function() {
                        this.value = formatMoney(this.value);
                        calcularValorLiquido();
                    });

                    valorDescontoInput.addEventListener('input', function() {
                        this.value = formatMoney(this.value);
                        calcularValorLiquido();
                    });
                }

                // Event listener para o botão de limpar
                if (btnLimpar) {
                    btnLimpar.addEventListener('click', function() {
                        if (confirm('Deseja realmente limpar o formulário?')) {
                            const form = document.querySelector('form');

                            // Manter apenas os valores fixos (O.S., Fornecedor)
                            const osValue = document.getElementById('id_ordem_servico').value;
                            const fornecedorValue = document.getElementById('id_fornecedor').value;

                            form.reset();

                            // Restaurar os valores fixos
                            document.getElementById('id_ordem_servico').value = osValue;
                            document.getElementById('id_fornecedor').value = fornecedorValue;

                            // Resetar campos monetários
                            valorBrutoInput.value = formatMoney({{ $valorTotal }});
                            valorDescontoInput.value = formatMoney(0);
                            valorLiquidoInput.value = formatMoney({{ $valorTotal }});

                            // Restaurar a data atual
                            document.getElementById('data_emissao_nf').value = new Date().toISOString().slice(0,
                                16);
                        }
                    });
                }

                // Validar o formulário antes do envio
                const form = document.querySelector('form');
                form.addEventListener('submit', function(e) {
                    const numeroNF = document.getElementById('numero_nf').value;
                    const serie = document.getElementById('serie').value;
                    const dataEmissao = document.getElementById('data_emissao_nf').value;
                    const fornecedor = document.getElementById('id_fornecedor').value;
                    const valorBruto = parseMoneyValue(valorBrutoInput.value);
                    const valorLiquido = parseMoneyValue(valorLiquidoInput.value);

                    if (!numeroNF || !serie || !dataEmissao || !fornecedor || !valorBruto || !valorLiquido) {
                        e.preventDefault();
                        alert('Por favor, preencha todos os campos obrigatórios.');
                        return false;
                    }

                    if (valorBruto <= 0 || valorLiquido <= 0) {
                        e.preventDefault();
                        alert('Os valores bruto e líquido devem ser maiores que zero.');
                        return false;
                    }

                    return true;
                });

                // Formatação inicial dos campos monetários
                document.querySelectorAll('.money-input').forEach(input => {
                    input.addEventListener('blur', function() {
                        this.value = formatMoney(this.value);
                    });
                });
            });
        </script>
    @endpush
</x-app-layout>
