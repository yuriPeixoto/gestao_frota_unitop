{{-- Conteúdo da aba de Contrato x Modelo --}}
<div class="bg-gray-50 p-4 rounded-lg">
    <div id="contrato-modelo-form" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="modelo[0][id_contrato_modelo]" class="block text-sm font-medium text-gray-700">Cód.:</label>
                <input type="text" id="modelo[0][id_contrato_modelo]_display" value="" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <input type="hidden" id="modelo[0][id_contrato_modelo]" name="modelo[0][id_contrato_modelo]" value="">
            </div>

            <div>
                <label for="modelo[0][id_modelo]" class="block text-sm font-medium text-gray-700">Modelo:</label>
                <x-forms.smart-select id="modelo[0][id_modelo]" name="modelo[0][id_modelo]"
                    placeholder="Selecione um modelo" :options="$modelosVeiculos" :selected="old('modelo.0.id_modelo')"
                    minSearchLength="2" display-class="select-display" />
            </div>

            <div>
                <label for="modelo[0][id_contrato]" class="block text-sm font-medium text-gray-700">Contrato:</label>
                <x-forms.smart-select id="modelo[0][id_contrato]" name="modelo[0][id_contrato]"
                    placeholder="Selecione um contrato" :options="$modeloContrato"
                    :selected="old('modelo.0.id_contrato')" minSearchLength="2" display-class="select-display" />
            </div>

            <div>
                <label for="modelo[0][ativo]" class="block text-sm font-medium text-gray-700">Ativo:</label>
                <div class="mt-1 space-x-4">
                    <div class="inline-flex items-center">
                        <input type="radio" id="ativo_sim" name="modelo[0][ativo]" value="1" checked
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="ativo_sim" class="ml-2 block text-sm text-gray-700">Sim</label>
                    </div>
                    <div class="inline-flex items-center">
                        <input type="radio" id="ativo_nao" name="modelo[0][ativo]" value="0"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="ativo_nao" class="ml-2 block text-sm text-gray-700">Não</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" id="btn_adicionar_contrato_modelo"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Adicionar
            </button>
        </div>
    </div>

    {{-- Lista de Contratos x Modelos --}}
    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Cód.</th>
                        <th scope="col" class="py-3 px-6">Modelo</th>
                        <th scope="col" class="py-3 px-6">Contrato</th>
                        <th scope="col" class="py-3 px-6">Ativo</th>
                        <th scope="col" class="py-3 px-6">Data Inclusão</th>
                        <th scope="col" class="py-3 px-6">Data Alteração</th>
                        <th scope="col" class="py-3 px-6">Ações</th>
                    </tr>
                </thead>
                <tbody id="contratos-modelo-lista">
                    @if(isset($contratosModelo) && count($contratosModelo) > 0)
                    @foreach($contratosModelo as $contratoModelo)
                    <tr class="bg-white border-b hover:bg-gray-50"
                        id="linha-contrato-modelo-{{ $contratoModelo->id_contrato_modelo ?? '-' }}"
                        data-id="{{ $contratoModelo->id_contrato_modelo ?? '-' }}">
                        <td class="py-3 px-6">{{ $contratoModelo->id_contrato_modelo ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $contratoModelo->modelo->id_modelo_veiculo ?? '-' }} - {{
                            $contratoModelo->modelo->descricao_modelo_veiculo ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $contratoModelo->contrato->id_contrato_forn ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $contratoModelo->ativo ? 'Sim' : 'Não' }}</td>
                        <td class="py-3 px-6">{{ $contratoModelo->data_inclusao ?
                            $contratoModelo->data_inclusao->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3 px-6">{{ $contratoModelo->data_alteracao ?
                            $contratoModelo->data_alteracao->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3 px-6">
                            <div class="flex space-x-2">
                                <button type="button"
                                    onclick="editarContratoModelo('{{ $contratoModelo->id_contrato_modelo }}')"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>

                                <button type="button"
                                    onclick="excluirContratoModelo({{ $contratoModelo->id_contrato_modelo }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr class="bg-white border-b">
                        <td colspan="7" class="py-3 px-6 text-center text-gray-500">Nenhum vínculo contrato-modelo
                            cadastrado</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let contratoModeloTemporarios = [];
    let contratoModeloEditando = null;
    let tempIdCounter = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Botão para adicionar contrato
        const btn_adicionar_contrato_modelo = document.getElementById('btn_adicionar_contrato_modelo');
        if(btn_adicionar_contrato_modelo) {
            btn_adicionar_contrato_modelo.addEventListener('click', function(){
                adicionarContratoModelo();
            });
        }

        const form = document.querySelector('#form-fornecedor');
        if (form) {
            form.addEventListener('submit', function(e) {
                prepararDadosContratoModeloParaEnvio();

                // DEBUG: mostrar todos os inputs que vão ser enviados
                console.log('Inputs para envio:', document.querySelectorAll('input[name^="modelo["]'));
            });
        }

        @if(isset($contratosModelo) && count($contratosModelo) > 0)
            @foreach($contratosModelo as $contratoModelo)
           contratoModeloTemporarios.push({
                id_contrato_modelo: {{ $contratoModelo->id_contrato_modelo ?? 'null' }},
                id_modelo: {{ $contratoModelo->id_modelo ?? 'null' }}, // id real
                descricao_modelo: '{{ $contratoModelo->modelo->descricao_modelo_veiculo ?? '' }}', // descrição
                id_contrato: {{ $contratoModelo->id_contrato ?? 'null' }},
                ativo: {{ $contratoModelo->ativo ?? 1 }},
                data_inclusao: '{{ $contratoModelo->data_inclusao ? $contratoModelo->data_inclusao->format("d/m/Y H:i") : "" }}',
                data_alteracao: '{{ $contratoModelo->data_alteracao ? $contratoModelo->data_alteracao->format("d/m/Y H:i") : "" }}',
                temp_id: null
            });

            @endforeach
            console.log('Dados carregados do PHP:', contratoModeloTemporarios);
        @else
            console.log('Nenhum dado carregado do PHP');
        @endif

        atualizarTabelaContratoModelo();
    });

    function prepararDadosContratoModeloParaEnvio() {
        const form = document.querySelector('#form-fornecedor');
        document.querySelectorAll('input[name^="modelo["]').forEach(f => f.remove());

        console.log('Dados temporários antes de preparar:', contratoModeloTemporarios);

        contratoModeloTemporarios.forEach((modelo, index) => {
            const campos = ['id_contrato_modelo', 'id_modelo', 'id_contrato', 'ativo'];
            campos.forEach(campo => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `modelo[${index}][${campo}]`;
                input.value = modelo[campo] ?? '';
                form.appendChild(input);
                
                // DEBUG: verifique o valor sendo setado
                console.log(`Campo modelo[${index}][${campo}]:`, modelo[campo]);
            });
        });

        console.log('Dados preparados para envio:', contratoModeloTemporarios);
    }

    function encontrarInputSmartSelect(name) {
        // Tentar encontrar de várias formas
        const selectors = [
            `[name="${name}"]`,
            `input[name="${name}"]`,
            `[data-name="${name}"]`,
            `#${name}`,
            `#${name.replace(/\[|\]/g, '-')}`
        ];
        
        for (const selector of selectors) {
            const element = document.querySelector(selector);
            if (element) {
                console.log(`Input encontrado com selector: ${selector}`, element);
                return element;
            }
        }
        
        console.log(`Input não encontrado: ${name}`);
        return null;
    }

    function preencherSmartSelectAlpineCorrigido(inputElement, value, tipo) {
        if (!inputElement) return;
        
        console.log(`Preenchendo ${tipo} com valor:`, value);
        
        // 1. Primeiro definir o valor no input hidden
        inputElement.value = value;
        
        // 2. Encontrar o componente Alpine pai
        const component = inputElement.closest('[x-data]');
        if (component) {
            try {
                const alpineData = Alpine.$data(component);
                if (alpineData) {
                    console.log('Dados Alpine encontrados:', alpineData);
                    
                    // **SOLUÇÃO PRINCIPAL** - Usar reactividade do Alpine
                    if (alpineData.selectedValues) {
                        // Usar Alpine.reactive para garantir reactividade
                        Alpine.reactive(alpineData).selectedValues = [value];
                        console.log('selectedValues definido com reactividade');
                    }
                    
                    // **ALTERNATIVA 1** - Chamar método de update se existir
                    if (typeof alpineData.updateSelected === 'function') {
                        alpineData.updateSelected();
                        console.log('Método updateSelected chamado');
                    }
                    
                    // **ALTERNATIVA 2** - Forçar renderização
                    if (typeof alpineData.$refresh === 'function') {
                        alpineData.$refresh();
                        console.log('Refresh do Alpine chamado');
                    }
                }
            } catch (e) {
                console.log('Erro ao acessar Alpine:', e);
            }
        }
        
        // 3. Disparar eventos importantes
        const eventos = ['input', 'change'];
        eventos.forEach(evento => {
            inputElement.dispatchEvent(new Event(evento, { bubbles: true }));
        });
        
        // 4. Disparar evento customizado que o componente pode estar ouvindo
        inputElement.dispatchEvent(new CustomEvent('selected-changed', {
            detail: { value: value },
            bubbles: true
        }));
        
        // 5. **FORÇAR ATUALIZAÇÃO VISUAL** - Abrir e fechar dropdown
        //forcarAtualizacaoVisual(inputElement, value);
    }

    function excluirContratoModelo(id) {
        const isTemp = id.toString().startsWith("temp-");

        // Se for um registro temporário (não salvo no banco)
        if (isTemp || id === 'novo') {
            // Remover do array em memória
            contratoModeloTemporarios = contratoModeloTemporarios.filter(item =>
            String(item.temp_id) !== String(id) &&
            String(item.id_contrato_modelo) !== String(id)
            );
            removerLinhaVisual(id);
            if (contratoModeloTemporarios.length === 0) atualizarTabelaContratoModelo();

            // persistido (no .then)
            contratoModeloTemporarios = contratoModeloTemporarios.filter(item =>
            String(item.id_contrato_modelo) !== String(id)
            );
            removerLinhaVisual(id);
            if (contratoModeloTemporarios.length === 0) atualizarTabelaContratoModelo();

            // Remover linha da tabela
            const linha = document.querySelector(`tr[data-id="${id}"]`);
            if (linha) {
                linha.style.transition = 'opacity 0.3s';
                linha.style.opacity = '0';
                setTimeout(() => linha.remove(), 300);
            }

            showFeedback('message', 'Contrato não persistido removido com sucesso');
            return;
        }

        // Caso seja um registro salvo no banco
        if (!confirm('Tem certeza que deseja excluir este contrato permanentemente?')) {
            return;
        }

        fetch(`/admin/fornecedores/contrato/modelo/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(async response => {
            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.message || 'Erro ao excluir contrato');
            }
            return response.json();
        })
        .then(data => {
            // Remover do array em memória
            contratoModeloTemporarios = contratoModeloTemporarios.filter(item =>
                String(item.id_contrato_modelo) !== String(id)
            );

            // Remover da tabela
            const linha = document.querySelector(`tr[data-id="${id}"]`);
            if (linha) {
                linha.style.transition = 'opacity 0.3s';
                linha.style.opacity = '0';
                setTimeout(() => linha.remove(), 300);
            }

            showFeedback('success', data.message || 'Contrato excluído com sucesso');
        })
        .catch(error => {
            console.error('Erro na exclusão:', error);
            showFeedback('error', error.message || 'Falha ao excluir contrato');
        });
    }

    function debugEstruturaCompleta() {
        console.log('=== DEBUG DA ESTRUTURA ===');
        
        // Verificar todos os inputs com name contendo "modelo"
        const allModeloInputs = document.querySelectorAll('[name*="modelo"]');
        console.log('Todos os inputs com "modelo":', allModeloInputs);
        
        // Verificar especificamente os que precisamos
        const idModeloInput = document.querySelector('[name="modelo[0][id_modelo]"]');
        const idContratoInput = document.querySelector('[name="modelo[0][id_contrato]"]');
        const idContratoModeloInput = document.querySelector('[name="modelo[0][id_contrato_modelo]"]');
        
        console.log('Input id_modelo:', idModeloInput);
        console.log('Input id_contrato:', idContratoInput);
        console.log('Input id_contrato_modelo:', idContratoModeloInput);
        
        // Verificar componentes Alpine
        const alpineComponents = document.querySelectorAll('[x-data]');
        console.log('Componentes Alpine encontrados:', alpineComponents.length);
        
        alpineComponents.forEach((comp, index) => {
            console.log(`Componente ${index + 1}:`, comp);
            const inputs = comp.querySelectorAll('input');
            console.log(`Inputs no componente ${index + 1}:`, inputs);
        });
    }

    // Execute esta função no console primeiro
    debugEstruturaCompleta();

    function salvarEdicaoContratoModelo(id) {
        const modelo = contratoModeloTemporarios.find(item =>
            item.id_contrato_modelo == id || item.temp_id == id
        );

        if (modeloIndex !== -1) {
            contratoModeloTemporarios[modeloIndex].id_modelo = document.querySelector('#id_modelo').value;
            contratoModeloTemporarios[modeloIndex].modelo_nome = document.querySelector('#id_modelo option:checked').text;
            contratoModeloTemporarios[modeloIndex].ativo = document.querySelector('#ativo').value;
            contratoModeloTemporarios[modeloIndex].data_inclusao = document.querySelector('#data_inclusao').value;
        }


        atualizarLinhaContratoModelo(id);
    }


    function limparFormularioContratoModelo() {
        contratoModeloEditando = null;
        document.getElementById('btn_adicionar_contrato_modelo').textContent = 'Adicionar';
    }

    function editarContratoModelo(id) {
        const modelo = contratoModeloTemporarios.find(item =>
            String(item.id_contrato_modelo) === String(id) ||
            String(item.temp_id) === String(id)
        );

        if (!modelo) {
            alert('Registro não encontrado para edição');
            return;
        }

        document.querySelector('[name="modelo[0][id_modelo]"]').value = modelo.id_modelo;
        document.querySelector('[name="modelo[0][id_contrato]"]').value = modelo.id_contrato;
        document.querySelectorAll('input[name="modelo[0][ativo]"]').forEach(r => r.checked = (r.value == modelo.ativo));

        contratoModeloEditando = modelo;
        document.getElementById('btn_adicionar_contrato_modelo').textContent = 'Atualizar';
}

    

    function preencherFormularioCorrigido(modelo) {
        try {
            // 1. Primeiro encontrar os inputs CORRETAMENTE
            const idModeloInput = encontrarInputSmartSelect('modelo[0][id_modelo]');
            const idContratoInput = encontrarInputSmartSelect('modelo[0][id_contrato]');
            const idContratoModeloDisplay = document.getElementById('modelo[0][id_contrato_modelo]_display');
            const idContratoModeloHidden = document.getElementById('modelo[0][id_contrato_modelo]');

            console.log('Inputs encontrados:', {
                idModeloInput,
                idContratoInput,
                idContratoModeloDisplay,
                idContratoModeloHidden
            });

            // 2. Preencher campos de código
            if (idContratoModeloDisplay && idContratoModeloHidden) {
                idContratoModeloDisplay.value = modelo.id_contrato_modelo || 'Novo'; // apenas exibição
                idContratoModeloHidden.value = modelo.id_contrato_modelo || '';      // hidden enviado ao backend
                console.log('Campo código preenchido:', modelo.id_contrato_modelo);
            }

            // 3. Preencher smart-selects
            if (idModeloInput) {
                preencherSmartSelectAlpineCorrigido(idModeloInput, modelo.id_modelo, 'modelo');
            }

            if (idContratoInput) {
                preencherSmartSelectAlpineCorrigido(idContratoInput, modelo.id_contrato, 'contrato');
            }

            // 4. Preencher radios
            const radios = document.querySelectorAll('input[name="modelo[0][ativo]"]');
            if (radios.length > 0) {
                radios.forEach(radio => {
                    radio.checked = (radio.value == modelo.ativo);
                });
                console.log('Radio preenchido:', modelo.ativo);
            }

            // 5. Marcar como editando
            contratoModeloEditando = modelo;
            document.getElementById('btn_adicionar_contrato_modelo').textContent = 'Atualizar';

            // 6. Scroll para o formulário
            document.getElementById('contrato-modelo-form').scrollIntoView({
                behavior: 'smooth'
            });

        } catch (error) {
            console.error('Erro ao preencher formulário:', error);
        }
    }

    function adicionarContratoModelo() {
        const idModelo = Number(document.querySelector('[name="modelo[0][id_modelo]"]').value);
        const idContrato = Number(document.querySelector('[name="modelo[0][id_contrato]"]').value);
        const ativo = document.querySelector('input[name="modelo[0][ativo]"]:checked')?.value || 1;

        if (!idModelo || !idContrato) {
            alert('Selecione Modelo e Contrato!');
            return false;
        }

        if (contratoModeloEditando) {
            const index = contratoModeloTemporarios.findIndex(item =>
                String(item.id_contrato_modelo) === String(contratoModeloEditando.id_contrato_modelo) ||
                String(item.temp_id) === String(contratoModeloEditando.temp_id)
            );

            if (index !== -1) {
                contratoModeloTemporarios[index] = {
                    ...contratoModeloTemporarios[index],
                    id_modelo: idModelo,
                    id_contrato: idContrato,
                    ativo: ativo,
                    data_alteracao: new Date().toLocaleString('pt-BR')
                };

                // Atualiza hidden
                document.querySelector('[name="modelo[0][id_contrato_modelo]"]').value = contratoModeloTemporarios[index].id_contrato_modelo;

                atualizarLinhaContratoModelo(contratoModeloTemporarios[index]);
            }
        }else {
            // Novo registro
            const novoRegistro = {
                temp_id: `temp-${++tempIdCounter}`,
                id_contrato_modelo: null,
                id_modelo: idModelo,
                id_contrato: idContrato,
                ativo: ativo,
                data_inclusao: new Date().toLocaleString('pt-BR'),
                data_alteracao: new Date().toLocaleString('pt-BR')
            };
            contratoModeloTemporarios.push(novoRegistro);

            // Adiciona nova linha
            adicionarLinhaContratoModelo(novoRegistro);
        }

        limparFormularioContratoModelo();
    }


    function adicionarLinhaContratoModelo(data) {
        const tabela = document.getElementById('contratos-modelo-lista');
        if (!tabela) return;

        // Remover linha vazia se existir
        const emptyRow = tabela.querySelector('tr td[colspan="7"]');
        if (emptyRow) {
            emptyRow.parentElement.remove();
        }

        const id = data.id_contrato_modelo ? data.id_contrato_modelo.toString() : data.temp_id;
        
        const row = document.createElement('tr');
        row.id = `linha-contrato-modelo-${id}`;
        row.className = 'bg-white border-b hover:bg-gray-50';
        row.setAttribute('data-id', id);
        
        row.innerHTML = `
            <td class="py-3 px-6">${data.id_contrato_modelo || 'Novo'}</td>
            <td class="py-3 px-6">${data.descricao_modelo}</td>
            <td class="py-3 px-6">${data.id_contrato}</td>
            <td class="py-3 px-6">${data.ativo == 1 ? 'Sim' : 'Não'}</td>
            <td class="py-3 px-6">${data.data_inclusao}</td>
            <td class="py-3 px-6">${data.data_alteracao}</td>
            <td class="py-3 px-6">
                <div class="flex space-x-2">
                    <button type="button" onclick="editarContratoModelo('${id}')"
                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button type="button" onclick="excluirContratoModelo('${id}')"
                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        `;
        
        tabela.appendChild(row);
    }

    function atualizarLinhaContratoModelo(modelo) {
        const id = modelo.id_contrato_modelo || modelo.temp_id;
        const linha = document.getElementById(`linha-contrato-modelo-${id}`);
        
        if (linha) {
            const cells = linha.querySelectorAll('td');
            
            // Atualizar células
            cells[0].textContent = modelo.id_contrato_modelo || 'Novo';
            cells[1].textContent = modelo.id_modelo;
            cells[2].textContent = modelo.id_contrato;
            cells[3].textContent = modelo.ativo == '1' ? 'Sim' : 'Não';
            cells[5].textContent = modelo.data_alteracao;
            
            // Atualizar botões de ação
            const btnEditar = cells[6].querySelector('button[onclick*="editarContratoModelo"]');
            const btnExcluir = cells[6].querySelector('button[onclick*="excluirContratoModelo"]');
            
            if (btnEditar) {
                btnEditar.setAttribute('onclick', `editarContratoModelo('${id}')`);
            }
            if (btnExcluir) {
                btnExcluir.setAttribute('onclick', `excluirContratoModelo('${id}')`);
            }
            
            console.log('Linha atualizada visualmente');
        } else {
            console.error('Linha não encontrada para atualização visual');
        }
    }

    

    function encontrarLinhaContratoModelo(id) {
        return document.getElementById(`linha-contrato-modelo-${id}`);
    }


    function atualizarTabelaContratoModelo() {
        const tabela = document.getElementById('contratos-modelo-lista');
        tabela.innerHTML = '';

        if (contratoModeloTemporarios.length === 0) {
            tabela.innerHTML = `<tr><td colspan="7" class="text-center text-gray-500">Nenhum vínculo contrato-modelo cadastrado</td></tr>`;
            return;
        }

        contratoModeloTemporarios.forEach(item => {
            const id = item.id_contrato_modelo ?? item.temp_id;
            const row = document.createElement('tr');
            row.id = `linha-contrato-modelo-${id}`;
            row.setAttribute('data-id', id); // 👈 FALTAVA ISSO
            row.className = 'bg-white border-b hover:bg-gray-50';

            row.innerHTML = `
                <td class="py-3 px-6">${item.id_contrato_modelo || 'Novo'}</td>
                <td class="py-3 px-6">${item.descricao_modelo}</td>
                <td class="py-3 px-6">${item.id_contrato}</td>
                <td class="py-3 px-6">${item.ativo == 1 ? 'Sim' : 'Não'}</td>
                <td class="py-3 px-6">${item.data_inclusao}</td>
                <td class="py-3 px-6">${item.data_alteracao}</td>
                <td class="py-3 px-6">
                    <div class="flex space-x-2">
                        <button type="button" onclick="editarContratoModelo('${id}')"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </button>
                        <button type="button" onclick="excluirContratoModelo('${id}')"
                            class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </td>
            `;

        tabela.appendChild(row);
    });
    }

    function removerLinhaVisual(id) {
        const linha = document.querySelector(`tr[data-id="${id}"]`)
                    || document.getElementById(`linha-contrato-modelo-${id}`);
        if (linha) {
            linha.style.transition = 'opacity 0.3s';
            linha.style.opacity = '0';
            setTimeout(() => linha.remove(), 300);
        }
    }

</script>


@endpush