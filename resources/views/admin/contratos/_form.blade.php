@php
    use Illuminate\Support\Facades\Storage;
@endphp
{{-- Formulário de Contratos --}}
<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
    {{-- Mensagens de Feedback --}}
    @if (session('error'))
        <div class="alert-danger alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded bg-red-50 p-4">
            <ul class="list-inside list-disc text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Conteúdo do Formulário --}}
    <div class="bg-white p-6">
        <form id="contratosForm" method="POST" action="{{ $action }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @if ($method === 'PUT')
                @method('PUT')
            @endif

            {{-- Cabeçalho --}}
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    {{ $method === 'PUT' ? 'Editar Contrato' : 'Novo Contrato' }}
                </h3>
                <p class="mt-1 text-sm text-gray-600">
                    Preencha as informações do contrato abaixo.
                </p>
            </div>

            {{-- Seção: Informações Básicas --}}
            <div class="space-y-4">
                <h4 class="text-md font-medium text-gray-800">Informações Básicas</h4>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    {{-- Fornecedor --}}
                    <div>
                        <x-forms.smart-select name="id_fornecedor" label="Fornecedor"
                            placeholder="Selecione o fornecedor..." :options="$fornecedores" required="true" :searchUrl="route('admin.api.fornecedores.search')"
                            asyncSearch="true" :selected="old('id_fornecedor', $contrato->id_fornecedor ?? '')" />
                    </div>

                    {{-- Status --}}
                    <div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ativo</label>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <input id="is_valido_sim" name="is_valido" type="radio" value="1"
                                    {{ old('is_valido', isset($contrato) ? $contrato->is_valido : 1) == 1 ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="is_valido_sim" class="ml-2 block text-sm text-gray-700">Sim</label>
                            </div>
                            <div class="flex items-center">
                                <input id="is_valido_nao" name="is_valido" type="radio" value="0"
                                    {{ old('is_valido', isset($contrato) ? $contrato->is_valido : '') == 0 ? 'checked' : '' }}
                                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="is_valido_nao" class="ml-2 block text-sm text-gray-700">Não</label>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Seção: Datas --}}
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-800">Período de Vigência</h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- Data Início --}}
                        <div>
                            <x-forms.input name="data_inicial" type="date" label="Data de Início" required="true"
                                value="{{ old('data_inicial', isset($contrato) ? $contrato->data_inicial?->format('Y-m-d') : '') }}" />
                        </div>

                        {{-- Data Fim --}}
                        <div>
                            <x-forms.input name="data_final" type="date" label="Data de Término" required="true"
                                value="{{ old('data_final', isset($contrato) ? $contrato->data_final?->format('Y-m-d') : '') }}" />
                        </div>
                    </div>
                </div>

                {{-- Seção: Valores Financeiros --}}
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-800">Informações Financeiras</h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        {{-- Valor Total --}}
                        <div>
                            <label for="valor_contrato" class="block text-sm font-medium text-gray-700">
                                Valor Total <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="valor_contrato" name="valor_contrato" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                oninput="formatarMoedaBrasileira(this)" placeholder="R$ 0,00"
                                value="{{ old('valor_contrato', isset($contrato->valor_contrato) ? 'R$ ' . number_format($contrato->valor_contrato, 2, ',', '.') : '') }}">
                        </div>

                        {{-- Saldo do Contrato --}}
                        <div>
                            <label for="saldo_contrato" class="block text-sm font-medium text-gray-700">
                                Saldo Disponível
                            </label>
                            <input type="text" id="saldo_contrato" name="saldo_contrato" disabled
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                oninput="formatarMoedaBrasileira(this)" placeholder="R$ 0,00"
                                value="{{ old('saldo_contrato', isset($contrato->saldo_contrato) ? 'R$ ' . number_format($contrato->saldo_contrato, 2, ',', '.') : '') }}">
                        </div>
                    </div>
                </div>

                {{-- Seção: Documentação --}}
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-800">Documentação</h4>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        {{-- Arquivo do Contrato --}}
                        <div>
                            <label for="arquivo" class="block text-sm font-medium text-gray-700">
                                Arquivo do Contrato
                            </label>
                            <input type="file" id="arquivo" name="arquivo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">

                            <p class="mt-1 text-xs text-gray-500">
                                Formatos aceitos: PDF, DOC, DOCX, JPG, JPEG, PNG
                            </p>

                            @if (!empty($contrato->doc_contrato))
                                <div class="mt-2 flex items-center space-x-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <span class="text-sm text-gray-600">Arquivo atual:</span>
                                    <a href="{{ Storage::url($contrato->doc_contrato) }}" target="_blank"
                                        class="text-sm text-blue-600 underline hover:text-blue-800">
                                        {{ basename($contrato->doc_contrato) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação --}}
                <div class="flex justify-end space-x-3 border-t border-gray-200 pt-6">
                    <a href="{{ route('admin.contratos.index') }}"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar
                    </a>

                    <button type="submit" id="submit-button"
                        class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        {{ $method === 'PUT' ? 'Atualizar Contrato' : 'Salvar Contrato' }}
                    </button>
                </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        function formatarMoedaBrasileira(input) {
            // Remove tudo que não é dígito
            let valor = input.value.replace(/\D/g, '');

            // Se estiver vazio, retorna vazio
            if (valor === '') {
                input.value = '';
                return;
            }

            // Converte para número e divide por 100 para obter os centavos
            const valorNumerico = parseInt(valor, 10) / 100;

            // Formata para o padrão brasileiro
            input.value = valorNumerico.toLocaleString('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                minimumFractionDigits: 2
            });

            // Mantém o cursor na posição correta
            const length = input.value.length;
            input.setSelectionRange(length, length);
        }

        // Formatar valores que já estão preenchidos ao carregar a página
        document.addEventListener('DOMContentLoaded', function() {
            const valorContrato = document.getElementById('valor_contrato');
            const saldoContrato = document.getElementById('saldo_contrato');

            // Se o valor_contrato já tem conteúdo mas não está formatado como moeda
            if (valorContrato && valorContrato.value && !valorContrato.value.includes('R$')) {
                const valor = parseFloat(valorContrato.value);
                if (!isNaN(valor)) {
                    valorContrato.value = valor.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: 2
                    });
                }
            }

            // Se o saldo_contrato já tem conteúdo mas não está formatado como moeda
            if (saldoContrato && saldoContrato.value && !saldoContrato.value.includes('R$')) {
                const valor = parseFloat(saldoContrato.value);
                if (!isNaN(valor)) {
                    saldoContrato.value = valor.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL',
                        minimumFractionDigits: 2
                    });
                }
            }
        });
    </script>
@endpush
