{{-- Conteúdo da aba de Peças --}}
<div class="bg-gray-50 p-4 rounded-lg">
    <form id="pecas-form" class="space-y-4" data-edit-mode="false" data-editing-id="">
        <input type="hidden" id="pecas_index" name="pecas_index" value="0">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="pecas[0][id_pecas_forn]" class="block text-sm font-medium text-gray-700">Cód.:</label>
                <input type="text" id="pecas[0][id_pecas_forn]" name="pecas[0][id_pecas_forn]" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="pecas[0][id_contrato_forn]"
                    class="block text-sm font-medium text-gray-700">Contrato:</label>
                <x-forms.smart-select id="pecas[0][id_contrato_forn]" name="pecas[0][id_contrato_forn]"
                    placeholder="Selecionar" :options="$modeloContrato"
                    :selected="request('pecas[0][id_contrato_forn]')" minSearchLength="2"
                    display-class="select-display" />
            </div>

            <div>
                <label for="pecas[0][id_contrato_modelo]" class="block text-sm font-medium text-gray-700">Contrato x
                    Modelo:</label>
                <x-forms.smart-select id="pecas[0][id_contrato_modelo]" name="pecas[0][id_contrato_modelo]"
                    placeholder="Selecionar" :options="$modeloContratox"
                    :selected="request('pecas[0][id_contrato_modelo]')" minSearchLength="2"
                    display-class="select-display" />
            </div>

            <div x-data="smartSelect({
                    options: {{ json_encode($gruposPecas) }},
                    selected: '{{ request('pecas[0][id_grupo_pecas]') }}',
                    placeholder: 'Selecionar',
                    minSearchLength: 2,
                    onSelectCallback: handleGrupopecasChange
                })">

                <label for="pecas[0][id_grupo_pecas]" class="block text-sm font-medium text-gray-700">Grupo de
                    Serviço:</label>
                <x-forms.smart-select id="pecas[0][id_grupo_pecas]" name="pecas[0][id_grupo_pecas]"
                    placeholder="Selecionar" :options="$gruposPecas" :selected="request('pecas[0][id_grupo_pecas]')"
                    minSearchLength="2" display-class="select-display" :on-select-callback="'handleGrupopecasChange'" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


            <input type="hidden" id="pecas[0][id_produto]" name="pecas[0][id_produto]" placeholder="Selecionar"
                :selected="request('pecas[0][id_produto]')" minSearchLength="2" display-class="select-display" />


            <div>
                <label for="pecas[0][valor_produto]" class="block text-sm font-medium text-gray-700">Valor
                    Peça:</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">R$</span>
                    </div>
                    <input type="text" step="0.01" id="pecas[0][valor_produto]" name="pecas[0][valor_produto]" required
                        class="pl-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="0,00">
                </div>
            </div>

            <div>
                <label for="pecas[0][is_valido]" class="block text-sm font-medium text-gray-700">Ativo:</label>
                <div class="mt-1 space-x-4">
                    <div class="inline-flex items-center">
                        <input type="radio" id="is_valido_sim" name="pecas[0][is_valido]" value="1" checked
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="is_valido_sim" class="ml-2 block text-sm text-gray-700">Sim</label>
                    </div>
                    <div class="inline-flex items-center">
                        <input type="radio" id="is_valido_nao" name="pecas[0][is_valido]" value="0"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="is_valido_nao" class="ml-2 block text-sm text-gray-700">Não</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" id="btn_adicionar_pecas"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Adicionar
            </button>
        </div>
    </form>

    {{-- Lista de Peças --}}
    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Cód.</th>
                        <th scope="col" class="py-3 px-6">Grupo</th>
                        <th scope="col" class="py-3 px-6">Peças</th>
                        <th scope="col" class="py-3 px-6">Contrato</th>
                        <th scope="col" class="py-3 px-6">Modelo</th>
                        <th scope="col" class="py-3 px-6">Valor</th>
                        <th scope="col" class="py-3 px-6">Ativo</th>
                        <th scope="col" class="py-3 px-6">Ações</th>
                    </tr>
                </thead>
                <tbody id="pecas-lista">
                    @if(isset($pecas) && count($pecas) > 0)
                    @foreach($pecas as $peca)
                    <tr class="bg-white border-b hover:bg-gray-50" id="linha-peca-{{ $peca->id_pecas_forn }}"
                        data-id="{{ $peca->id_pecas_forn }}" data-id-grupo="{{ $peca->id_grupo_peca }}"
                        data-id-peca="{{ $peca->id_peca }}" data-id-contrato="{{ $peca->id_contrato_forn }}"
                        data-id-contrato-modelo="{{ $peca->id_contrato_modelo }}" data-id="{{ $peca->id_pecas_forn }}">
                        <td class="py-3 px-6">{{ $peca->id_pecas_forn }}</td>
                        <td class="py-3 px-6">{{ $peca->grupoPecas->descricao_grupo ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $peca->produto->descricao_produto ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $peca->contrato->id_contrato_forn ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $peca->contratoModelo->modelo->descricao_modelo_veiculo ?? '-' }}
                        </td>
                        <td class="py-3 px-6">R$ {{ number_format($peca->valor_produto, 2, ',', '.') }}</td>
                        <td class="py-3 px-6">{{ $peca->is_valido ? 'Sim' : 'Não' }}</td>
                        <td class="py-3 px-6">
                            <div class="flex space-x-2">
                                <button type="button" onclick="editarPecas({{ $peca->id_pecas_forn }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" onclick="excluirPecas({{ $peca->id_pecas_forn }})"
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
                    <tr class="border-b bg-white">
                        <td colspan="9" class="px-6 py-3 text-center text-gray-500">Nenhuma peça cadastrada
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let pecasTemporarias = [];
        //let pecasModeloEditando = null;

        document.addEventListener('DOMContentLoaded', function(){
        const btn_adicionar_pecas = document.getElementById('btn_adicionar_pecas');
        
        // Debug: verifique se os elementos foram encontrados
        console.log('DOM carregado - Iniciando configuração de peças');

        // ação do botão adicionar
        if(btn_adicionar_pecas) {
            btn_adicionar_pecas.addEventListener('click', function() {
                adicionarPecasLocal();
            });
        }

        // capturar form
        const form = document.querySelector('form-fornecedor'); // Corrigido para selecionar form corretamente
        if(form){
            form.addEventListener('submit', function(){
                document.querySelectorAll('[name^="pecas["]').forEach(field => field.remove());

                pecasTemporarias.forEach((pecas, index) => {
                    const idPecaInput = document.createElement('input');
                    idPecaInput.type = 'hidden';
                    idPecaInput.name = `pecas[${index}][id_pecas_forn]`;
                    idPecaInput.value = pecas.id_pecas_forn || '';
                    form.appendChild(idPecaInput);

                    const idContrato = document.createElement('input'); 
                    idContrato.type = 'hidden'; 
                    idContrato.name = `pecas[${index}][id_contrato_forn]`; 
                    idContrato.value = pecas.id_contrato_forn || ''; 
                    form.appendChild(idContrato); 
                    
                    const idContratoModelo = document.createElement('input'); 
                    idContratoModelo.type = 'hidden'; 
                    idContratoModelo.name = `pecas[${index}][id_contrato_modelo]`;
                    idContratoModelo.value = pecas.id_contrato_modelo || ''; 
                    form.appendChild(idContratoModelo); 
                    
                    const idGrupoPecas = document.createElement('input'); 
                    idGrupoPecas.type = 'hidden'; 
                    idGrupoPecas.name = `pecas[${index}][id_grupo_pecas]`;
                    idGrupoPecas.value = pecas.id_grupo_pecas || ''; 
                    form.appendChild(idGrupoPecas); 

                    const idPecas = document.createElement('input'); 
                    idPecas.type = 'hidden'; 
                    idPecas.name = `pecas[${index}][id_produto]`;
                    idPecas.value = pecas.id_produto || ''; 
                    form.appendChild(idPecas); 

                    const idValorProduto = document.createElement('input'); 
                    idValorProduto.type = 'hidden'; 
                    idValorProduto.name = `pecas[${index}][valor_produto]`;

                    // normaliza: remove pontos de milhar e troca vírgula por ponto
                    let valorFormatado = pecas.valor_produto || '';
                    valorFormatado = valorFormatado.replace(/\./g, '').replace(',', '.');

                    idValorProduto.value = valorFormatado;
                    form.appendChild(idValorProduto);
                    
                    const idValidoPeca = document.createElement('input'); 
                    idValidoPeca.type = 'hidden'; 
                    idValidoPeca.name = `pecas[${index}][is_valido]`; 
                    idValidoPeca.value = pecas.is_valido || 1; 
                    form.appendChild(idValidoPeca);
                });
            });
        }

        // Carregar peças existentes do PHP para o array temporário
        @if(isset($pecas) && $pecas->count() > 0)
            @foreach($pecas as $peca)
            pecasTemporarias.push({
                id_pecas_forn: '{{ $peca->id_pecas_forn }}'.trim(),
                id_grupo_pecas: '{{ $peca->id_grupo_pecas }}'.trim(), // Corrigido: usar ID, não descrição
                descricao_grupo: '{{ $peca->grupoPecas->descricao_grupo ?? '' }}'.trim(),
                id_produto: '{{ $peca->id_produto }}'.trim(), // Corrigido: usar ID, não descrição
                descricao: '{{ $peca->produto->descricao ?? '' }}'.trim(),
                id_contrato_forn: '{{ $peca->id_contrato_forn }}'.trim(),
                id_contrato_modelo: '{{ $peca->id_contrato_modelo }}'.trim(), // Corrigido: usar ID
                descricao_contrato_modelo: '{{ $peca->contratoModelo->modelo->descricao_modelo_veiculo ?? '' }}'.trim(),
                valor_produto: '{{ $peca->valor_produto }}',
                is_valido: '{{ $peca->is_valido }}'
            });
            @endforeach
        @endif

        console.log('Array inicial de Peças:', pecasTemporarias);

        // NOVO CÓDIGO PARA INICIALIZAÇÃO AUTOMÁTICA
        setTimeout(() => {
            // Verificar se o smart-select de grupo já tem um valor
            const grupoSmartSelect = document.querySelector('[x-data*="smartSelect"]');
            let grupoId = null;

            if (grupoSmartSelect && grupoSmartSelect.__x) {
                const alpineData = grupoSmartSelect.__x.$data;
                if (alpineData.selectedValues && alpineData.selectedValues.length > 0) {
                    grupoId = alpineData.selectedValues[0];
                    console.log('Valor já selecionado no smart-select:', grupoId);
                }
            }
            
            // Alternative: Verificar pelo input hidden do grupo
            if (!grupoId) {
                const grupoHiddenInput = document.querySelector('input[name="pecas[0][id_grupo_pecas]"]');
                if (grupoHiddenInput && grupoHiddenInput.value) {
                    grupoId = grupoHiddenInput.value;
                    console.log('Valor encontrado no input hidden do grupo:', grupoId);
                }
            }

            // Se encontrou um grupo, carregar as peças
            if (grupoId) {
                carregarPecas(grupoId);
                
                // Se houver peças temporárias, tentar selecionar a peça correspondente
                if (pecasTemporarias.length > 0 && pecasTemporarias[0].id_produto) {
                    setTimeout(() => {
                        const primeiraPeca = pecasTemporarias[0];
                        console.log('Tentando selecionar peça existente:', primeiraPeca.id_produto);
                        selecionarPecaExistente(primeiraPeca.id_produto);
                    }, 800); // Delay menor
                }
            } else {
                console.log('Nenhum grupo selecionado para carregar peças');
            }
        }, 300);

        // Configurar o callback global para o smart-select
        window.handleGrupopecasChange = function(selectedValue, selectedOption) {
            console.log('Grupo selecionado (callback):', selectedValue, 'Opção:', selectedOption);
            
            if (selectedValue) {
                carregarPecas(selectedValue);
            } else {
                limparSelectPecas();
            }
        };
    });

    // Função para selecionar peça existente
    function selecionarPecaExistente(idPeca) {
        const pecaSelect = encontrarSelectPecas();
            if (pecaSelect && pecaSelect.tagName === 'SELECT') {
                pecaSelect.value = idPeca;
                console.log('Peça selecionada no select:', idPeca);
            } else if (pecaSelect && pecaSelect.tagName === 'INPUT') {
                // Para inputs hidden (Alpine)
            pecaSelect.value = idPeca;
            console.log('Input hidden de peça definido:', idPeca);
            
            // Tentar atualizar também o componente Alpine se existir
            const pecaComponent = document.querySelector('[x-data*="smartSelect"]');
            if (pecaComponent && pecaComponent.__x) {
                const alpine = pecaComponent.__x;
                alpine.$data.selectedValues = [idPeca];
                const pecaOption = alpine.$data.initialOptions.find(opt => opt.value == idPeca);
                if (pecaOption) {
                    alpine.$data.selectedLabels = [pecaOption.label];
                }
                console.log('Componente Alpine atualizado com peça selecionada');
            }
        }
    }

    // Função global para handle do grupo - CORRIGIDA
    window.handleGrupopecasChange = function(selectedValue, selectedOption) {
        console.log('Grupo selecionado (callback):', selectedValue, 'Opção:', selectedOption);
        
        if (selectedValue) {
            carregarPecas(selectedValue);
        } else {
            limparSelectPecas();
        }
    };

    // Função para carregar Peças
    function carregarPecas(idGrupo) {
        console.log('Carregando peças para grupo:', idGrupo);
        
        if (!idGrupo || idGrupo === '') {
            limparSelectPecas();
            return;
        }

        // URL completa para debug
        const url = `/admin/fornecedores/pecas/grupo/${idGrupo}`;
        console.log('Fetching URL:', url);
        
        fetch(url)
            .then(response => {
                console.log('Status da resposta:', response.status);
                if (!response.ok) throw new Error('Erro na resposta do servidor: ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('Peças recebidas:', data);
                if (data && data.length > 0) {
                    atualizarSelectPecas(data);
                } else {
                    console.warn('Nenhuma peça retornada para o grupo');
                    limparSelectPecas('Nenhuma peça encontrada');
                }
            })
            .catch(err => {
                console.error('Erro ao carregar Peças:', err);
                limparSelectPecas('Erro ao carregar Peças');
            });
    }

    function limparSelectPecas(mensagem = 'Selecione um grupo primeiro') {
        const pecaSelect = encontrarSelectPecas();
        if (pecaSelect) {
            if (pecaSelect.tagName === 'SELECT') {
                pecaSelect.innerHTML = '<option value="">' + mensagem + '</option>';
            } else if (pecaSelect.tagName === 'INPUT') {
                pecaSelect.value = '';
            }
        }
        
        // Limpar outros campos relacionados
        const valorInput = document.querySelector('input[name="pecas[0][valor_produto]"]');
        if (valorInput) valorInput.value = '';
        
        const radios = document.querySelectorAll('input[name="pecas[0][is_valido]"]');
        radios.forEach(r => r.checked = false);
    }

    function encontrarSelectPecas() {
        // Tenta encontrar o select principal primeiro
        let pecaSelect = document.querySelector('select[name="pecas[0][id_produto]"]');
        
        if (!pecaSelect) {
            // Tenta encontrar input hidden
            pecaSelect = document.querySelector('input[name="pecas[0][id_produto]"]');
        }
        
        if (!pecaSelect) {
            // Busca por elementos Alpine
            const alpineComponents = document.querySelectorAll('[x-data*="smartSelect"]');
            alpineComponents.forEach(component => {
                const input = component.querySelector('input[name*="id_produto"]');
                if (input) pecaSelect = input;
            });
        }
        
        console.log('Select encontrado:', pecaSelect);
        return pecaSelect;
    }

    function atualizarSelectPecas(pecas) {
        const pecaSelect = encontrarSelectPecas();
        
        if (!pecaSelect) {
            console.error('Select de Peças não encontrado após busca ampla!');
            return;
        }

        console.log('Atualizando select:', pecaSelect);
        
        if (pecaSelect.tagName === 'INPUT' && pecaSelect.type === 'hidden') {
            console.log('É um input hidden, atualizando Alpine...');
            atualizarAlpineSelectPecas(pecas);
        } else if (pecaSelect.tagName === 'SELECT') {
            // É um select normal
            pecaSelect.innerHTML = '<option value="">Selecione o serviço</option>';
            
            pecas.forEach(peca => {
                const option = document.createElement('option');
                option.value = peca.value;
                option.textContent = peca.label;
                pecaSelect.appendChild(option);
            });

            pecaSelect.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function atualizarAlpineSelectPecas(pecas, valorSelecionado = '') {
        const pecaComponent = document.querySelector('[id="pecas\\[0\\]\\[id_produto\\]"]');
        
        if (pecaComponent && pecaComponent.__x) {
            const alpine = pecaComponent.__x;
            console.log('Componente Alpine encontrado:', alpine);

            alpine.$data.initialOptions = pecas;
            alpine.$data.filteredOptions = pecas;
            alpine.$data.loading = false;

            if (valorSelecionado) {
                alpine.$data.selectedValues = [valorSelecionado];
                alpine.$data.selectedLabels = [pecas.find(s => s.value == valorSelecionado)?.label || ''];
            }

            console.log('Smart-select de peças atualizado');
        } else {
            console.warn('Componente Alpine não encontrado');
            criarSelectTemporarioPecas(pecas);
        }
    }


    function criarSelectTemporarioPecas(pecas) {
        console.log('Criando select temporário com busca expansível...');

        let container = document.querySelector('#pecas-temp-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'pecas-temp-container';
            
            // Encontra o input hidden que será substituído
            const hiddenInput = document.querySelector('input[name="pecas[0][id_produto]"]');
            
            if (hiddenInput && hiddenInput.parentNode) {
                // Substitui o input hidden pelo container do select
                hiddenInput.parentNode.replaceChild(container, hiddenInput);
            } else {
                // Fallback: adiciona no body se não encontrar
                document.body.appendChild(container);
            }
        }

        // HTML do select personalizado
        container.innerHTML = `
            <div style="margin-bottom: 15px;">
                <label for="pecas[0][id_produto]" class="block text-sm font-medium text-gray-700">Peça:</label>
                <div style="max-width: 400px; position: relative;">
                    <div id="pecas-dropdown-toggle" 
                        style="padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px;
                            cursor: pointer; background: #fff; display: flex; justify-content: space-between; align-items: center;">
                        <span id="pecas-selected" style="flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            Selecione a peça
                        </span>
                        <span style="margin-left: 8px;"></span>
                    </div>
                    <div id="pecas-dropdown" 
                        style="display: none; position: absolute; top: 100%; left: 0; right: 0;
                            border: 1px solid #d1d5db; border-radius: 6px; background: #fff; z-index: 1000;
                            padding: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                        <input type="text" id="pecas-search" placeholder="Pesquisar..." 
                            style="width: 100%; padding: 8px; margin-bottom: 6px; border: 1px solid #d1d5db;
                                border-radius: 4px; font-size: 14px;">
                        <div id="pecas-options" style="max-height: 150px; overflow-y: auto;"></div>
                    </div>
                </div>
            </div>
            <input type="hidden" id="pecas[0][id_produto]" name="pecas[0][id_produto]" value="">
        `;

        const toggle = container.querySelector('#pecas-dropdown-toggle');
        const dropdown = container.querySelector('#pecas-dropdown');
        const searchInput = container.querySelector('#pecas-search');
        const optionsContainer = container.querySelector('#pecas-options');
        const selectedText = container.querySelector('#pecas-selected');
        const hiddenInput = container.querySelector('input[name="pecas[0][id_produto]"]');

        function renderOptions(filtro = '') {
            optionsContainer.innerHTML = pecas
                .filter(s => s.label.toLowerCase().includes(filtro.toLowerCase()))
                .map(s => `<div data-value="${s.value}" style="padding: 6px; cursor: pointer; 
                            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
                            title="${s.label}">${s.label}</div>`)
                .join('');
        }

        renderOptions();

        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
            searchInput.value = '';
            renderOptions();
            searchInput.focus();
        });

        searchInput.addEventListener('input', () => {
            renderOptions(searchInput.value);
        });

        optionsContainer.addEventListener('click', (e) => {
            if (e.target.dataset.value) {
                selectedText.textContent = e.target.textContent;
                selectedText.title = e.target.getAttribute('title');
                dropdown.style.display = 'none';
                hiddenInput.value = e.target.dataset.value;
            }
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        // Fechar dropdown ao pressionar ESC
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdown.style.display = 'none';
            }
        });
    }

    

    console.log('Todos os selects na página:');

    document.querySelectorAll('select').forEach(select => {
        console.log('Select:', select.name, select.id, select);
    });

    console.log('Todos os inputs hidden:');
    document.querySelectorAll('input[type="hidden"]').forEach(input => {
        if (input.name.includes('pecas')) {
            console.log('Input pecas:', input.name, input.value);
        }
    });

    // Verificar a estrutura do smart-select de Peças
    const pecasSmartComponent = document.querySelector('[id="pecas\\[0\\]\\[id_produto\\]"]');
    console.log('Componente smart-select de Peças:', pecasSmartComponent);

    if (pecasSmartComponent) {
        // Procurar o select real dentro do componente
        const selectReal = pecasSmartComponent.querySelector('select');
        console.log('Select real dentro do componente:', selectReal);
    }

    function adicionarPecasLocal() {
        const btn_adicionar_pecas = document.getElementById('btn_adicionar_pecas');
        const isEditMode = btn_adicionar_pecas?.getAttribute('data-edit-mode') === 'true';
        const editingId = btn_adicionar_pecas?.getAttribute('data-editing-id');

        // SEMPRE usar índice 0 porque temos apenas um formulário
        const indexPrefix = 0;
        
        const idContratoEl = document.querySelector(`[name="pecas[${indexPrefix}][id_contrato_forn]"]`);
        const idContrato = idContratoEl ? idContratoEl.value : '';

        const idContratoModeloEl = document.querySelector(`[name="pecas[${indexPrefix}][id_contrato_modelo]"]`);
        const idContratoModelo = idContratoModeloEl ? idContratoModeloEl.value : '';

        const grupoEl = document.querySelector(`[name="pecas[${indexPrefix}][id_grupo_pecas]"]`);
        const idGrupoPecas = grupoEl ? grupoEl.value : '';
        
        // Correção: Verificar se grupoEl existe antes de acessar suas propriedades
        let descricaoGrupo = '';
        const grupoDisplayEl = document.querySelector(`.select-display[name="pecas[${indexPrefix}][id_grupo_pecas]"]`);
        
        if (grupoDisplayEl) {
            descricaoGrupo = grupoDisplayEl.textContent.trim();
        } else if (grupoEl && grupoEl.options && grupoEl.selectedIndex !== -1) {
            descricaoGrupo = grupoEl.options[grupoEl.selectedIndex].text;
        }

        const pecasEl = document.querySelector(`[name="pecas[${indexPrefix}][id_produto]"]`);
        const idPecas = pecasEl ? pecasEl.value : '';
        
        // Correção: Verificar se pecasEl existe antes de acessar suas propriedades
        let descricaoPecas = '';
        const pecasDisplayEl = document.querySelector(`.pecas-display[name="pecas[${indexPrefix}][id_produto]"]`);
        
        if (pecasDisplayEl) {
            descricaoPecas = pecasDisplayEl.textContent.trim();
        } else if (pecasEl && pecasEl.options && pecasEl.selectedIndex !== -1) {
            descricaoPecas = pecasEl.options[pecasEl.selectedIndex].text;
        }

        const idValidoPecas = document.querySelector(`input[name="pecas[${indexPrefix}][is_valido]"]:checked`)?.value || '1';

        const valorEl = document.querySelector(`[name="pecas[${indexPrefix}][valor_produto]"]`);
        const idValorProduto = valorEl ? valorEl.value : '';

        console.log('Valores capturados:', {
            idContrato, idContratoModelo, idGrupoPecas, idPecas, idValorProduto
        });

        // Validações
        if (!idContrato) {
            alert('Informe o Contrato!');
            return false;
        }
        if (!idContratoModelo) {
            alert('Informe o Contrato Modelo!');
            return false;
        }
        if (!idGrupoPecas) {
            alert('Informe o Grupo de Serviço!');
            return false;
        }
        if (!idValorProduto) {
            alert('Informe o Valor do Serviço!');
            return false;
        }

        const pecasData = {
            id_pecas_forn: isEditMode ? editingId : null,
            id_contrato_forn: idContrato,
            id_contrato_modelo: idContratoModelo,
            id_grupo_pecas: idGrupoPecas,
            descricao_grupo: descricaoGrupo,
            id_produto: idPecas,
            descricao: descricaoPecas,
            valor_produto: idValorProduto,
            is_valido: idValidoPecas,
        };

        if (isEditMode && editingId) {
            const index = pecasTemporarias.findIndex(item => 
                String(item.id_pecas_forn) === String(editingId) || 
                String(item.temp_id) === String(editingId)
            );

            if (index !== -1) {
                // Preservar o temp_id se existir
                pecasData.temp_id = pecasTemporarias[index].temp_id;
                pecasTemporarias[index] = pecasData;
                atualizarLinhaPecas(pecasTemporarias[index]);
                salvarPecasForm(pecasTemporarias[index], index, true);
                resetarBotaoPecas();
            } else {
                console.error('Índice não encontrado para edição');
            }

        } else {
            // Adicionar novo item
            const newTempId = 'temp_' + Date.now();
            pecasData.temp_id = newTempId;
            pecasTemporarias.push(pecasData);
            const index = pecasTemporarias.length - 1;
            adicionarLinhaPecas(pecasData);
            salvarPecasForm(pecasData, index, false);
        }
    }


    function salvarPecasForm(pecas, index, isEdit = false) {
        const container = document.getElementById('pecas-hidden');

        // Identificador correto: temp_id ou id_pecas_forn
        const identificador = pecas.temp_id || pecas.id_pecas_forn;

        // se for edição, remove versão anterior
        let wrapper = container.querySelector(`[data-id="${identificador}"]`);
        if (wrapper) wrapper.remove();

        wrapper = document.createElement('div');
        wrapper.dataset.id = identificador;

        wrapper.innerHTML = `
            <input type="hidden" name="pecas[${index}][id_pecas_forn]" value="${pecas.id_pecas_forn || ''}">
            <input type="hidden" name="pecas[${index}][id_grupo_pecas]" value="${pecas.id_grupo_pecas || ''}">
            <input type="hidden" name="pecas[${index}][id_produto]" value="${pecas.id_produto || 0}">
            <input type="hidden" name="pecas[${index}][id_contrato_forn]" value="${pecas.id_contrato_forn || ''}">
            <input type="hidden" name="pecas[${index}][id_contrato_modelo]" value="${pecas.id_contrato_modelo || ''}">
            <input type="hidden" name="pecas[${index}][valor_produto]" value="${pecas.valor_produto || ''}">
            <input type="hidden" name="pecas[${index}][is_valido]" value="${pecas.is_valido || 1}">
        `;

        container.appendChild(wrapper);
    }


    function editarPecas(id) {
        console.log('Editando peça ID:', id);

        const modelo = pecasTemporarias.find(item => 
            String(item.id_pecas_forn) === String(id) || String(item.temp_id) === String(id)
        );

        if (modelo) {
            console.log('Peça encontrado para edição:', modelo);

            const form = document.getElementById('pecas-form');
            form.setAttribute('data-edit-mode', 'true');
            form.setAttribute('data-editing-id', id);

            setTimeout(() => {
                preencherFormularioCorrigido(modelo);

                const btn = document.getElementById('btn_adicionar_pecas');
                if (btn) {
                    btn.textContent = 'Atualizar';
                    btn.setAttribute('data-edit-mode', 'true');
                    btn.setAttribute('data-editing-id', id);
                }
            }, 100);
        } else {
            console.error('Peça não encontrado para edição. Lista atual:', pecasTemporarias);
            alert('Peça não encontrado!');
        }
    }


    function preencherFormularioCorrigido(pecas) {
        console.log('Preenchendo formulário com:', pecas);
        
        // SEMPRE usar índice 0 para o formulário
        const index = 0;
        
        // Preencher campos do formulário - use métodos mais robustos
        const setValue = (name, value) => {
            const element = document.querySelector(`[name="${name}"]`);
            if (element) {
                element.value = value || '';
                // Disparar eventos para componentes reactivos
                element.dispatchEvent(new Event('change', { bubbles: true }));
                element.dispatchEvent(new Event('input', { bubbles: true }));
            }
        };

        const setRadio = (name, value) => {
            const radio = document.querySelector(`input[name="${name}"][value="${value || 1}"]`);
            if (radio) radio.checked = true;
        };

        const setDisplayText = (selector, text) => {
            const element = document.querySelector(selector);
            if (element) element.textContent = text || '';
        };

        // Preencher valores
        setValue(`pecas[${index}][id_contrato_forn]`, pecas.id_contrato_forn);
        setValue(`pecas[${index}][id_contrato_modelo]`, pecas.id_contrato_modelo);
        setValue(`pecas[${index}][id_grupo_pecas]`, pecas.id_grupo_pecas);
        setValue(`pecas[${index}][id_produto]`, pecas.id_produto);
        setValue(`pecas[${index}][valor_produto]`, pecas.valor_produto);
        
        // Marcar radio button
        setRadio(`pecas[${index}][is_valido]`, pecas.is_valido);
        
        // Atualizar displays visuais
        setDisplayText(`.select-display[name="pecas[${index}][id_grupo_pecas]"]`, pecas.descricao_grupo);
        setDisplayText(`.pecas-display[name="pecas[${index}][id_produto]"]`, pecas.descricao_pecas);

        // Log para debug
        console.log('Formulário preenchido. Verificando valores:');
        console.log('Contrato:', document.querySelector(`[name="pecas[${index}][id_contrato_forn]"]`)?.value);
        console.log('Grupo:', document.querySelector(`[name="pecas[${index}][id_grupo_pecas]"]`)?.value);
    }

    // Função auxiliar para preencher smart-selects
    function preencherSmartSelect(name, value) {
        const select = document.querySelector(`[name="${name}"]`);
        if (!select) return;
        
        select.value = value || '';
        
        // Disparar eventos para componentes reactivos
        select.dispatchEvent(new Event('change', { bubbles: true }));
        select.dispatchEvent(new Event('input', { bubbles: true }));
        
        // Se for um componente customizado, pode precisar de eventos específicos
        select.dispatchEvent(new CustomEvent('update:modelValue', { 
            detail: value,
            bubbles: true 
        }));
    }


    // Adicione esta função para debug
    
    
    

    function excluirPecas(id) {
        // 1. Tratamento seguro do event
        

        // 3. Verificação do ID
        if (!id || id === 'novo') {
            // Caso seja um peças não persistido
            const linha = event.currentTarget.closest('tr');
            if (linha) {
                linha.style.transition = 'opacity 0.3s';
                linha.style.opacity = '0';
                setTimeout(() => linha.remove(), 300);
                showFeedback('message', 'Contrato não persistido removido com sucesso');
                return;
            }
        }

        // 4. Confirmação do usuário
        if (!confirm('Tem certeza que deseja excluir este peças permanentemente?')) {
            return;
        }

        // 5. Obter o botão para feedback visual
        const btnExcluir = event.currentTarget;
        if (!btnExcluir) {
            console.error('Elemento do botão não encontrado');
            return;
        }

        const originalHTML = btnExcluir.innerHTML;

        

        // 7. Chamada AJAX
        fetch(`/admin/fornecedores/pecas/destroy/${id}`, {
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
                throw new Error(errorData.message || 'Erro ao excluir serviço');
            }
            return response.json();
        })
        .then(data => {
            // 8. Remover a linha da tabela
            const linha = document.querySelector(`tr[data-id="${id}"]`);
            if (linha) {
                linha.style.transition = 'opacity 0.3s';
                linha.style.opacity = '0';
                setTimeout(() => linha.remove(), 300);
            }

            // 9. Feedback ao usuário
            showFeedback('success', data.message || 'Peças excluída com sucesso');
        })
        .then(response => {
            if (response.success) {
                // Remove a linha temporária (se existir)
                const tempLine = document.querySelector('tr[data-id="novo"]');
                if (tempLine) tempLine.remove();
                
                // Adiciona a linha com o ID real
                adicionarLinhaPecas(response.pecas, true);
            }
        })
        .catch(error => {
            console.error('Erro na exclusão:', error);
            showFeedback('error', error.message || 'Falha ao excluir contrato');
        })
        .finally(() => {
            // 10. Restaurar botão
            if (btnExcluir && originalHTML) {
                btnExcluir.innerHTML = originalHTML;
                btnExcluir.disabled = false;
            }
        });
    }

    function adicionarLinhaPecas(data) {
        const tabela = document.getElementById('pecas-lista');
        if (!tabela) return;

        // Remover linha vazia se existir
        const emptyRow = tabela.querySelector('tr td');
        if (emptyRow && emptyRow.textContent.includes('Nenhuma peça cadastrada')) {
            emptyRow.parentElement.remove();
        }

        // ID unificado como string
        const id = data.id_pecas_forn ? String(data.id_pecas_forn) : 'temp_' + Date.now();
        data.temp_id = data.id_pecas_forn ? null : id;

        const row = document.createElement('tr');
        row.id = `linha-peca-${id}`;
        row.className = 'bg-white border-b hover:bg-gray-50';
        row.setAttribute('data-id', id);

        row.innerHTML = `
            <td class="py-3 px-6">${data.id_pecas_forn || 'Novo'}</td>
            <td class="py-3 px-6">${data.descricao_grupo || data.id_grupo_pecas}</td>
            <td class="py-3 px-6">${data.descricao || data.id_produto}</td>
            <td class="py-3 px-6">${data.id_contrato_forn}</td>
            <td class="py-3 px-6">${data.id_contrato_modelo}</td>
            <td class="py-3 px-6">R$ ${parseFloat(data.valor_produto).toFixed(2)}</td>
            <td class="py-3 px-6">${data.is_valido == 1 ? 'Sim' : 'Não'}</td>
            <td class="py-3 px-6">
                <div class="flex space-x-2">
                    <button type="button" onclick="editarPecas('${id}')"
                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button type="button" onclick="excluirPecas('${id}')"
                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        `;
        
        tabela.appendChild(row);
    }

    // Adicione estas funções que estão sendo chamadas mas não existem
    function resetarBotaoPecas() {
        const btn = document.getElementById('btn_adicionar_pecas');
        if (btn) {
            btn.textContent = 'Adicionar Peças';
            btn.setAttribute('data-edit-mode', 'false');
            btn.removeAttribute('data-editing-id');
        }
        
        const form = document.getElementById('pecas-form');
        if (form) {
            form.setAttribute('data-edit-mode', 'false');
            form.removeAttribute('data-editing-id');
        }
        
        // Limpar formulário se necessário
        document.querySelectorAll('#pecas-form input, #pecas-form select').forEach(field => {
            if (field.type !== 'radio') {
                field.value = '';
            }
        });
        
        // Resetar radios
        const radio = document.querySelector('input[name^="pecas["][name$="[is_valido]"]');
        if (radio) radio.checked = true;
    }

    function atualizarLinhaPecas(pecasData) {
        const id = pecasData.temp_id || pecasData.id_pecas_forn;
        const row = document.getElementById(`linha-peca-${id}`);
        
        if (row) {
            // Atualizar a linha existente de forma segura (sem innerHTML)
            const cells = row.querySelectorAll('td');
            if (cells.length >= 7) {
                cells[0].textContent = pecasData.id_pecas_forn || 'Novo';
                cells[1].textContent = pecasData.id_grupo_pecas || pecasData.id_grupo_pecas;
                cells[2].textContent = pecasData.id_produto || pecasData.id_produto;
                cells[3].textContent = pecasData.id_contrato_forn;
                cells[4].textContent = pecasData.id_contrato_modelo;
                cells[5].textContent = `R$ ${Number(pecasData.valor_produto || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                cells[6].textContent = pecasData.is_valido == 1 ? 'Sim' : 'Não';
            }
        }
    }

</script>


<script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.getElementById("pecas[0][valor_produto]");

        input.addEventListener("input", function (e) {
            let value = e.target.value.replace(/\D/g, ""); // só números
            value = (value / 100).toFixed(2) + ""; // sempre 2 casas
            value = value.replace(".", "."); // vírgula decimal
            value = value.replace(/\B(?=(\d{3})+(?!\d))/g, "."); // separador milhar
            e.target.value = value;
        });
    });
</script>
@endpush