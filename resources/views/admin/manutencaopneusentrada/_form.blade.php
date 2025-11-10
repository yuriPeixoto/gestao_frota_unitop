<div class="space-y-6">
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-forms.input name="id_manutencao_entrada" label="Código Entrada Pneus" readonly
            value="{{ old('id_manutencao_entrada', $manutencaoPneusEntrada->id_manutencao_entrada ?? '') }}" />

        @if (!isset($manutencaoPneusEntrada))
        <x-forms.input name="id_manutencao" label="Código Manutenção Pneus"
            value="{{ old('id_manutencao', $manutencaoPneusEntrada->id_manutencao ?? '') }}" />

        <div class="flex items-center mt-4">
            <x-forms.button onclick="onCarregarManutencaoPneus()">
                <x-icons.download class="text-blue-500 h-4 w-4 mr-2" />
                Carregar dados Manutenção Pneus
            </x-forms.button>
        </div>
        @else
        <div></div>
        <div></div>
        @endif

        <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
            :options="$formOptions['filiais']" :selected="old('id_filial', $manutencaoPneusEntrada->id_filial ?? '')"
            asyncSearch="false" />

        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
            :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
            :selected="old('id_fornecedor', $manutencaoPneusEntrada->id_fornecedor ?? '')" asyncSearch="true" />

        <div>
            <x-forms.input name="chave_nf_entrada" label="Chave Acesso NF Envio"
                value="{{ old('chave_nf_entrada', $manutencaoPneus->chave_nf_entrada ?? '') }}" id="chave_nf_entrada"
                maxlength="44" oninput="atualizarContagem()" />

            <small id="contador-chave" class="text-gray-500">
                0 / 44 dígitos
            </small>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">

        <x-forms.input type="number" name="numero_nf" label="Número NF"
            value="{{ old('numero_nf', $manutencaoPneusEntrada->numero_nf ?? '') }}" />

        <x-forms.input type="number" name="serie_nf" label="Série NF"
            value="{{ old('serie_nf', $manutencaoPneusEntrada->serie_nf ?? '') }}" />

        <x-forms.input name="valor_total_nf" label="Valor Total NF" data-mask="valor"
            value="{{ old('valor_total_nf', $manutencaoPneusEntrada->valor_total_nf ?? '') }}" />

        <x-forms.input name="valor_total_desconto" label="Valor Total NF com Desconto" data-mask="valor"
            value="{{ old('valor_total_desconto', $manutencaoPneusEntrada->valor_total_desconto ?? '') }}" />

        <x-forms.input type="date" name="data_recebimento" label="Data Recebimento"
            value="{{ old('data_recebimento', $manutencaoPneusEntrada->data_recebimento ?? '') }}" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-5">
        <x-forms.smart-select id="id_pneu" name="id_pneu" label="Número de Fogo"
            placeholder="Selecione o número de fogo..." :options="$formOptions['pneus']" :multiple="true"
            :selected="old('id_pneu', $manutencaoPneusEntrada->id_pneu ?? '')" asyncSearch="true"
            :searchUrl="route('admin.manutencaopneusentrada.api.pneus-search')" minSearchLength="1" />

        <x-forms.smart-select name="id_tipo_reforma" label="Tipo Reforma" placeholder="Selecione o tipo de reforma..."
            :options="$formOptions['tipoReforma']"
            :selected="old('id_tipo_reforma', $manutencaoPneusEntrada->id_tipo_reforma ?? '')" asyncSearch="true" />

        <x-forms.smart-select name="id_desenho_pneu" label="Desenho do Pneu"
            placeholder="Selecione o desenho do pneu..." :options="$formOptions['desenhopneu']"
            :selected="old('id_desenho_pneu', $manutencaoPneusEntrada->id_desenho_pneu ?? '')" asyncSearch="true" />

        <x-forms.smart-select name="tipo_borracha" label="Tipo Borracha" placeholder="Selecione o tipo de borracha..."
            :options="$formOptions['tipoborracha']"
            :selected="old('tipo_borracha', $manutencaoPneusEntrada->tipo_borracha ?? '')" asyncSearch="true" />

        <x-forms.smart-select name="id_servico" label="Serviço" placeholder="Selecione o tipo de borracha..."
            :options="$formOptions['servico']" :searchUrl="route('admin.api.servicos.search')"
            :selected="old('id_servico', $manutencaoPneusEntrada->servico->descricao_servico ?? '')"
            asyncSearch="true" />

        <x-forms.input name="valor_pneu" label="Valor Pneu" data-mask="valor" />

        <x-forms.input name="valor_pneu_total" label="Valor Total Pneu" data-mask="valor" readonly />

    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">


        <div>
            <span class="block text-sm font-medium text-gray-900 ml-6">Descarte</span>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="descarte" name="descarte" value="1" {{ isset($manutencaoPneusEntrada) &&
                        $manutencaoPneusEntrada->descarte == 1 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="descarte" class="ml-2">Sim</label>
            </fieldset>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="descarte_nao" name="descarte" value="0" checked {{
                        isset($manutencaoPneusEntrada) && $manutencaoPneusEntrada->descarte == 0 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="descarte" class="ml-2">Não</label>
            </fieldset>
        </div>
        <div id="is_feito_div" class="hidden">
            <span class="block text-sm font-medium text-gray-900 ml-6">Marcado</span>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="is_feito" name="is_feito" value="1" {{ isset($manutencaoPneusEntrada) &&
                        $manutencaoPneusEntrada->is_feito == 1 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="is_feito" class="ml-2">Sim</label>
            </fieldset>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="is_feito_nao" name="is_feito" value="0" checked {{
                        isset($manutencaoPneusEntrada) && $manutencaoPneusEntrada->is_feito == 0 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="is_feito" class="ml-2">Não</label>
            </fieldset>
        </div>
        <div id="is_conferido_div" class="hidden">
            <span class="block text-sm font-medium text-gray-900 ml-6">Conferido</span>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="is_conferido" name="is_conferido" value="1" {{
                        isset($manutencaoPneusEntrada) && $manutencaoPneusEntrada->is_conferido == 1 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="is_conferido" class="ml-2">Sim</label>
            </fieldset>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="is_conferido_nao" name="is_conferido" value="0" checked {{
                        isset($manutencaoPneusEntrada) && $manutencaoPneusEntrada->is_conferido == 0 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="is_conferido" class="ml-2">Não</label>
            </fieldset>
        </div>
    </div>
    <button type="button" onclick="adicionarpneu()"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        Adicionar Entrada da Manutenção
    </button>

    <input type="hidden" id="pneus" name="pneus" value="{{ isset($pneus) ? json_encode($pneus) : '[]' }}">

    <table class="min-w-full divide-y divide-gray-200 tabelaEntradaPneu">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data Inclusão
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data Alteração
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nº de Fogo
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo Reforma
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Desenho Pneu
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo borracha
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Valor Pneu
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Descarte
                </th>
            </tr>
        </thead>
        <tbody id="tabelaEntradaPneuBody" class="bg-white divide-y divide-gray-200">
            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
        </tbody>
    </table>

    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">
        <a href="{{ route('admin.envioerecebimentopneus.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit" id="submit-form"
            onclick="if (confirm('Atenção: você deseja concluir a inclusão dos pneus? Ao confirmar será realizada e entrada do pneu no estoque e atualizado o histórico da vida do pneu com as informações preenchidas. Deseja continuar?')) { return true; } else { return false; }"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($manutencaoPneusEntrada) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>



@push('scripts')
<script src="{{ asset('js\pneus\manutencaopneusentrada\entrada-manutencao-pneus.js') }}"></script>
<script>
    function onCarregarManutencaoPneus() {
        const idManutencaoPneu = document.querySelector('input[name="id_manutencao"]')?.value;

        if (!idManutencaoPneu || idManutencaoPneu.trim() === '') {
            alert('Insira um código de Manutenção de Pneu válido');
            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
            document.querySelector('input[name="_token"]')?.value;

        if (!csrfToken) {
            console.error('CSRF token não encontrado');
            alert('Erro de segurança. Recarregue a página.');
            return;
        }

        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        console.log('🔄 Carregando dados da manutenção...');

        fetch(`/admin/manutencaopneusentrada/api/${encodeURIComponent(idManutencaoPneu)}`, {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin'
        })
        .then(async response => {
            // Se for 422, pega o JSON e retorna pra ser tratado
            if (response.status === 422) {
                const errorData = await response.json();
                return errorData;
            }

            if (!response.ok) {
                if (response.status === 404) {
                    throw new Error('Manutenção não encontrada');
                } else if (response.status === 403) {
                    throw new Error('Sem permissão para acessar este recurso');
                } else if (response.status >= 500) {
                    throw new Error('Erro interno do servidor');
                } else {
                    throw new Error(`Erro na API: ${response.status} - ${response.statusText}`);
                }
            }

            return response.json();
        })
        .then(data => {
            if (data.error) {
                alert(data.message); // aqui aparece "⚠️ Manutenção de pneu já lançada!"
                return;
            }

            if (data && Object.keys(data).length > 0) {
                processarDadosManutencao(data);
            } else {
                console.warn('⚠️ Nenhum dado encontrado para esta manutenção');
                alert('Nenhum dado encontrado para o ID informado');
            }
        })
        .catch(error => {
            if (error.message.includes('Failed to fetch')) {
                alert('Erro de conexão. Verifique sua internet e tente novamente.');
            } else {
                alert(`Erro: ${error.message}`);
            }
        });
    }

    function processarDadosManutencao(data) {
        try {
            setSmartSelectValue('id_filial', data.id_filial);
            setSmartSelectValue('id_fornecedor', data.id_fornecedor, {
                triggerEvents: true,
                createIfNotFound: true
            });

            //document.querySelector('input[name="chave_nf_entrada"]').value = data.chave_nf_envio || '';
            //document.querySelector('input[name="numero_nf"]').value = data.nf_envio || '';

            console.log('📋 Pneus recebidos:', data.manutencaopneusitens);

            if (Array.isArray(data.manutencaopneusitens) && data.manutencaopneusitens.length > 0) {
                preencherSelectPneus(
                    data.manutencaopneusitens.map(item => ({
                        id_pneu: String(item.id_pneu),
                        numero_fogo: item.pneu?.numero_fogo || `${item.id_pneu}`
                    }))
                );

                console.log('✅ Select de fogos atualizado via preencherSelectPneus');
            } else {
                alert('Nenhum pneu encontrado para essa manutenção.');
            }

        } catch (error) {
            console.error('❌ Erro ao processar dados da manutenção:', error);
            alert('Erro ao carregar dados da manutenção.');
        }
    }

    // Variável global para guardar os pneus do envio
    let pneusDaManutencaoEnvio = [];

    function preencherSelectPneus(fogos) {
        pneusDaManutencaoEnvio = fogos.map(fogo => String(fogo.id_pneu));

        const selectContainer =
            document.querySelector('#id_pneu-button')?.closest('[x-data]') ||
            document.querySelector('[name="id_pneu[]"]')?.closest('[x-data]');

        if (!selectContainer) {
            console.warn('⚠️ Não encontrei o componente SmartSelect para id_pneu');
            return;
        }

        const smartSelect = Alpine.$data(selectContainer);

        // Reset
        smartSelect.options = [];
        smartSelect.selectedValues = [];
        smartSelect.selectedLabels = [];

        // Adiciona todos os pneus ao SmartSelect
        fogos.forEach(fogo => {
            const opt = {
                value: String(fogo.id_pneu), // sempre id_pneu como value
                label: fogo.numero_fogo      // label amigável (fogo)
            };

            smartSelect.options.push(opt);
            smartSelect.selectedValues.push(opt.value);
            smartSelect.selectedLabels.push(opt.label);
        });

        smartSelect.filteredOptions = [...smartSelect.options];

        if (typeof smartSelect.updateDisplay === 'function') {
            smartSelect.updateDisplay();
        }

        console.log("✅ Select atualizado com pneus:", smartSelect.selectedValues);

        // Destacar já adicionados
        setTimeout(() => {
            const opcoes = selectContainer.querySelectorAll('[data-value]');
            opcoes.forEach(option => {
                const valor = option.getAttribute('data-value');
                const jaAdicionado = pneuManutencao.some(p => String(p.id_pneu) === valor);

                if (jaAdicionado) {
                    option.style.color = 'red';
                    option.style.textDecoration = 'line-through';
                } else {
                    option.style.color = '';
                    option.style.textDecoration = '';
                }
            });
        }, 50);
    }

    document.getElementById('submit-form').addEventListener('click', function(e) {
        const pneusAdicionados = pneuManutencao.map(p => String(p.id_pneu));

        const faltando = pneusDaManutencaoEnvio.filter(id => !pneusAdicionados.includes(id));

        if (faltando.length > 0) {
            e.preventDefault();
            alert(`⚠️ Faltam ${faltando.length} pneu(s) para serem adicionados antes de salvar!`);
            return false;
        }

        if (!confirm('Atenção: você deseja concluir a inclusão dos pneus? Ao confirmar será realizada a entrada no estoque e atualizado o histórico da vida do pneu. Deseja continuar?')) {
            e.preventDefault();
            return false;
        }
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            onSmartSelectChange('id_pneu', function(data) {

                // Obter CSRF token de forma mais segura
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                        'content') ||
                    document.querySelector('input[name="_token"]')?.value;

                if (!csrfToken) {
                    console.error('CSRF token não encontrado');
                    alert('Erro de segurança. Recarregue a página.');
                    return;
                }

                // Headers configurados corretamente
                const headers = {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                };

                fetch(`/admin/pneus/api/${data.value}`, {
                        method: 'GET',
                        headers: headers,
                        credentials: 'same-origin'
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Erro na resposta da API: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Verificar se o status do pneu é "BORRACHARIA"
                        if (data.data && data.data.status_pneu === "BORRACHARIA") {
                            // Selecionar as duas divs que você quer tornar visíveis
                            const div1 = document.getElementById(
                                'is_feito_div'); // Substitua pelo ID real da primeira div
                            const div2 = document.getElementById(
                                'is_conferido_div'); // Substitua pelo ID real da segunda div

                            // Verificar se as divs existem antes de alterar
                            if (div1) {
                                div1.classList.remove('hidden');
                                div1.classList.add('visible');
                            }

                            if (div2) {
                                div2.classList.remove('hidden');
                                div2.classList.add('visible');
                            }
                        } else {
                            // Opcional: ocultar as divs se o status não for "BORRACHARIA"
                            const div1 = document.getElementById('is_feito_div');
                            const div2 = document.getElementById('is_conferido_div');

                            if (div1) {
                                div1.classList.remove('visible');
                                div1.classList.add('hidden');
                            }

                            if (div2) {
                                div2.classList.remove('visible');
                                div2.classList.add('hidden');
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao buscar dados do pneu:', err);
                    });
            });
        });

      
    function atualizarContagem() {
        const input = document.getElementById("chave_nf_entrada");
        const contador = document.getElementById("contador-chave");
        contador.textContent = input.value.length + " / 44 dígitos";
    }

    // inicializa ao carregar a página
    document.addEventListener("DOMContentLoaded", atualizarContagem);

</script>
@endpush