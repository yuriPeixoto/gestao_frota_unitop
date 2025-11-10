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
<div x-data="lancamentoForm()">
    <div class="space-y-6">
        <div class="rounded-lg bg-gray-50 p-4">
            <h3 class="mb-4 text-lg font-medium text-gray-900">Novo Lançamento</h3>

            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'licenciamento'" type="button"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
                        :class="activeTab === 'licenciamento' ? 'border-indigo-500 text-indigo-600' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                        Licenciamento
                    </button>
                    <button @click="activeTab = 'ipva'" type="button"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
                        :class="activeTab === 'ipva' ? 'border-indigo-500 text-indigo-600' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                        IPVA
                    </button>
                    <button @click="activeTab = 'seguro'" type="button"
                        class="whitespace-nowrap border-b-2 px-1 py-4 text-sm font-medium"
                        :class="activeTab === 'seguro' ? 'border-indigo-500 text-indigo-600' :
                            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                        Seguro Obrigatório
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="mt-6">
                <!-- Licenciamento Tab -->
                <div x-show="activeTab === 'licenciamento'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <div class="col-span-2">
                            <label for="ano_licenciamento" class="block text-sm font-medium text-gray-700">
                                Ano de Licenciamento <span class="text-red-500">*</span>
                            </label>
                            <select id="ano_licenciamento" name="ano_licenciamento"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($anos as $ano)
                                    <option value="{{ $ano['value'] }}"
                                        {{ old('ano_licenciamento', $licenciamentoveiculos->ano_licenciamento ?? '') == $ano['value'] ? 'selected' : '' }}>
                                        {{ $ano['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label for="uf_licenciamento" class="block text-sm font-medium text-gray-700">
                                UF <span class="text-red-500">*</span>
                            </label>
                            <select id="uf_licenciamento" name="uf_licenciamento"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                                <option value="">Selecione...</option>
                                @foreach ($ufData as $uf)
                                    <option value="{{ $uf['value'] }}"
                                        {{ old('uf_licenciamento', $licenciamentoveiculos->uf ?? '') == $uf['value'] ? 'selected' : '' }}>
                                        {{ $uf['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label for="final_placa" class="block text-sm font-medium text-gray-700">
                                Dezena final da placa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="final_placa" name="final_placa" required
                                value="{{ old('final_placa', $licenciamentoveiculos->final_placa ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="data_vencimento" class="block text-sm font-medium text-gray-700">
                                Data Limite de Vencimento <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="data_vencimento" name="data_vencimento" required
                                value="{{ old('data_vencimento', $licenciamentoveiculos->data_vencimento ?? '') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="valor_taxa" class="block text-sm font-medium text-gray-700">
                                Taxa Licenciamento Anual (R$) <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="valor_taxa" name="valor_taxa" required
                                value="{{ old('valor_taxa', $licenciamentoveiculos->valor_previsto_valor ?? '') }}"
                                placeholder="0,00"
                                class="valor-moeda mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Buttons for Licenciamento -->
                    <div class="justify-left mt-4 flex space-x-3">
                        <button type="button" id="btn-licenciamento" @click="lancarLicenciamento"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                            Lançar Licenciamento
                        </button>
                        <a href="{{ route('admin.veiculos.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                            Cancelar
                        </a>
                    </div>
                </div>

                <!-- IPVA Tab -->
                <div x-show="activeTab === 'ipva'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="ano_validade_ipva" class="block text-sm font-medium text-gray-700">
                                Ano de Validade <span class="text-red-500">*</span>
                            </label>
                            <select id="ano_validade_ipva" name="ano_validade_ipva" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($anos as $ano)
                                    <option value="{{ $ano['value'] }}">{{ $ano['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="uf_ipva" class="block text-sm font-medium text-gray-700">
                                UF <span class="text-red-500">*</span>
                            </label>
                            <select id="uf_ipva" name="uf_ipva" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($ufData as $uf)
                                    <option value="{{ $uf['value'] }}">{{ $uf['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="final_placa_ipva" class="block text-sm font-medium text-gray-700">
                                Dezena final da placa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="final_placa_ipva" name="final_placa_ipva" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="data_limite_ipva" class="block text-sm font-medium text-gray-700">
                                Data Limite de Vencimento <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="data_limite_ipva" name="data_limite_ipva" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Buttons for IPVA -->
                    <div class="justify-left mt-4 flex space-x-3">
                        <button type="button" id="btn-ipva" @click="lancarIpva"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                            Lançar IPVA
                        </button>
                        <a href="{{ route('admin.veiculos.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                            Cancelar
                        </a>
                    </div>
                </div>

                <!-- Seguro Tab -->
                <div x-show="activeTab === 'seguro'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label for="ano_validade_seguro" class="block text-sm font-medium text-gray-700">
                                Ano de Validade <span class="text-red-500">*</span>
                            </label>
                            <select id="ano_validade_seguro" name="ano_validade_seguro" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($anos as $ano)
                                    <option value="{{ $ano['value'] }}">{{ $ano['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="uf_seguro" class="block text-sm font-medium text-gray-700">
                                UF <span class="text-red-500">*</span>
                            </label>
                            <select id="uf_seguro" name="uf_seguro" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($ufData as $uf)
                                    <option value="{{ $uf['value'] }}">{{ $uf['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="dezena_placa_seguro" class="block text-sm font-medium text-gray-700">
                                Dezena final da placa <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="dezena_placa_seguro" name="dezena_placa_seguro" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="data_limite_seguro" class="block text-sm font-medium text-gray-700">
                                Data Limite de Vencimento <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="data_limite_seguro" name="data_limite_seguro" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Buttons for Seguro -->
                    <div class="justify-left mt-4 flex space-x-3">
                        <button type="button" id="btn-seguro" @click="lancarSeguro"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                            Lançar Seguro Obrigatório
                        </button>
                        <a href="{{ route('admin.veiculos.index') }}"
                            class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function lancamentoForm() {
            return {
                activeTab: 'licenciamento',

                validateForm(formType) {
                    let isValid = true;
                    let fields = [];

                    switch (formType) {
                        case 'licenciamento':
                            fields = [{
                                    id: 'ano_licenciamento',
                                    name: 'Ano de Licenciamento'
                                },
                                {
                                    id: 'uf_licenciamento',
                                    name: 'UF'
                                },
                                {
                                    id: 'final_placa',
                                    name: 'Dezena final da placa'
                                },
                                {
                                    id: 'data_vencimento',
                                    name: 'Data Limite de Vencimento'
                                },
                                {
                                    id: 'valor_taxa',
                                    name: 'Taxa Licenciamento Anual'
                                }
                            ];
                            break;
                        case 'ipva':
                            fields = [{
                                    id: 'ano_validade_ipva',
                                    name: 'Ano de Validade'
                                },
                                {
                                    id: 'uf_ipva',
                                    name: 'UF'
                                },
                                {
                                    id: 'final_placa_ipva',
                                    name: 'Dezena final da placa'
                                },
                                {
                                    id: 'data_limite_ipva',
                                    name: 'Data Limite de Vencimento'
                                }
                            ];
                            break;
                        case 'seguro':
                            fields = [{
                                    id: 'ano_validade_seguro',
                                    name: 'Ano de Validade'
                                },
                                {
                                    id: 'uf_seguro',
                                    name: 'UF'
                                },
                                {
                                    id: 'dezena_placa_seguro',
                                    name: 'Dezena final da placa'
                                },
                                {
                                    id: 'data_limite_seguro',
                                    name: 'Data Limite de Vencimento'
                                }
                            ];
                            break;
                    }

                    fields.forEach(field => {
                        const element = document.getElementById(field.id);
                        if (!element.value.trim()) {
                            isValid = false;
                            element.classList.add('border-red-500');

                            // Adiciona mensagem de erro se ainda não existir
                            const errorId = `${field.id}-error`;
                            if (!document.getElementById(errorId)) {
                                const errorMsg = document.createElement('p');
                                errorMsg.id = errorId;
                                errorMsg.className = 'mt-1 text-sm text-red-600';
                                errorMsg.innerText = `O campo ${field.name} é obrigatório`;
                                element.parentNode.appendChild(errorMsg);
                            }
                        } else {
                            element.classList.remove('border-red-500');

                            // Remove mensagem de erro se existir
                            const errorMsg = document.getElementById(`${field.id}-error`);
                            if (errorMsg) errorMsg.remove();
                        }
                    });

                    return isValid;
                },

                showLoading() {
                    // Implementar lógica para mostrar loading
                },

                hideLoading() {
                    // Implementar lógica para esconder loading
                },

                showNotification(title, message, type = 'success') {
                    Swal.fire({
                        title: title,
                        text: message,
                        icon: type,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#4F46E5'
                    });
                },

                lancarLicenciamento() {
                    if (!this.validateForm('licenciamento')) return;

                    this.showLoading();

                    let valorBruto = document.getElementById('valor_taxa').value;

                    // Remove pontos de milhares e substitui vírgula por ponto
                    valorBruto = valorBruto.replace(/\./g, "").replace(",", ".");

                    // Converte para float
                    let valorConvertido = parseFloat(valorBruto) || 0;

                    const data = {
                        ano_validade_licenciamento: document.getElementById('ano_licenciamento').value,
                        data_vencimento: document.getElementById('data_vencimento').value,
                        uf: document.getElementById('uf_licenciamento').value,
                        final_placa: document.getElementById('final_placa').value,
                        valor_taxa: valorConvertido
                    };

                    fetch("{{ route('admin.lancipvalicenciamentoseguros.lancar-licenciamento') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.hideLoading();
                            this.showNotification(data.title || 'Resultado', data.message, data.type || 'success');
                        })
                        .catch(error => {
                            this.hideLoading();
                            this.showNotification('Erro', 'Ocorreu um erro ao processar a requisição', 'error');
                            console.error(error);
                        });
                },

                lancarIpva() {
                    if (!this.validateForm('ipva')) return;

                    this.showLoading();

                    const data = {
                        ano_validade_ipva: document.getElementById('ano_validade_ipva').value,
                        uf_ipva: document.getElementById('uf_ipva').value,
                        final_placa: document.getElementById('final_placa_ipva').value,
                        data_limite_ipva: document.getElementById('data_limite_ipva').value
                    };

                    fetch("{{ route('admin.lancipvalicenciamentoseguros.lancar-ipva') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.hideLoading();
                            this.showNotification(data.title || 'Resultado', data.message, data.type || 'success');
                        })
                        .catch(error => {
                            this.hideLoading();
                            this.showNotification('Erro', 'Ocorreu um erro ao processar a requisição', 'error');
                            console.error(error);
                        });
                },

                lancarSeguro() {
                    if (!this.validateForm('seguro')) return;

                    this.showLoading();

                    const data = {
                        ano_validade_seguro: document.getElementById('ano_validade_seguro').value,
                        uf_seguro: document.getElementById('uf_seguro').value,
                        dezena_placa_seguro: document.getElementById('dezena_placa_seguro').value,
                        data_limite_seguro: document.getElementById('data_limite_seguro').value
                    };

                    fetch("{{ route('admin.lancipvalicenciamentoseguros.lancar-seguro') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.hideLoading();
                            this.showNotification(data.title || 'Resultado', data.message, data.type || 'success');
                        })
                        .catch(error => {
                            this.hideLoading();
                            this.showNotification('Erro', 'Ocorreu um erro ao processar a requisição', 'error');
                            console.error(error);
                        });
                }
            };
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Função para formatar como valor decimal brasileiro (sem R$)
            function formatarValor(valor) {
                // Remove tudo que não é número
                let numero = valor.replace(/\D/g, '');

                // Se não há números, retorna valor padrão
                if (!numero || numero === '') {
                    return '0,00';
                }

                // Converte para centavos (divide por 100)
                numero = parseInt(numero) / 100;

                // Formata como número brasileiro (sem símbolo de moeda)
                return numero.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Aplica a formatação aos campos com a classe 'valor-moeda'
            document.querySelectorAll('.valor-moeda').forEach(function(campo) {
                // Formata o valor inicial se houver
                if (campo.value && campo.value !== '' && campo.value !== '0') {
                    // Se o valor já contém vírgula, assume que já está formatado
                    if (!campo.value.includes(',')) {
                        // Multiplica por 100 para converter para centavos antes de formatar
                        let valorInicial = parseFloat(campo.value) * 100;
                        campo.value = formatarValor(valorInicial.toString());
                    }
                } else {
                    campo.value = '0,00';
                }

                // Formata enquanto digita
                campo.addEventListener('input', function(e) {
                    let cursorPosition = e.target.selectionStart;
                    let valorAnterior = e.target.value;
                    let valorFormatado = formatarValor(e.target.value);

                    e.target.value = valorFormatado;

                    // Ajusta a posição do cursor de forma mais inteligente
                    if (valorFormatado.length >= valorAnterior.length) {
                        // Se o texto ficou maior ou igual, mantém posição relativa
                        let novaPosicao = Math.min(cursorPosition + (valorFormatado.length -
                            valorAnterior.length), valorFormatado.length);
                        e.target.setSelectionRange(novaPosicao, novaPosicao);
                    } else {
                        // Se o texto ficou menor, ajusta para não ficar em posição inválida
                        let novaPosicao = Math.min(cursorPosition, valorFormatado.length);
                        e.target.setSelectionRange(novaPosicao, novaPosicao);
                    }
                });

                // Seleciona todo o texto ao focar para facilitar edição
                campo.addEventListener('focus', function(e) {
                    setTimeout(() => {
                        e.target.select();
                    }, 10);
                });

                // Valida se o campo não está vazio ao sair do foco
                campo.addEventListener('blur', function(e) {
                    if (!e.target.value || e.target.value === '0,00') {
                        e.target.value = '0,00';
                    }
                });
            });
        });
    </script>
@endpush
