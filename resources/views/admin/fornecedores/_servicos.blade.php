{{-- Conteúdo da aba de Serviços --}}
<div class="bg-gray-50 p-4 rounded-lg">
    <form id="servico-form" class="space-y-4" data-edit-mode="false" data-editing-id="">
        <input type="hidden" id="servico_index" name="servico_index" value="0">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="servico[0][id_servico_forn]" class="block text-sm font-medium text-gray-700">Cód.:</label>
                <input type="text" id="servico[0][id_servico_forn]" name="servico[0][id_servico_forn]" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="servico[0][id_contrato_forn]"
                    class="block text-sm font-medium text-gray-700">Contrato:</label>
                <x-forms.smart-select id="servico[0][id_contrato_forn]" name="servico[0][id_contrato_forn]"
                    placeholder="Selecionar" :options="$modeloContrato"
                    :selected="request('servico[0][id_contrato_forn]')" minSearchLength="2"
                    display-class="select-display" />
            </div>

            <div>
                <label for="servico[0][id_contrato_modelo]" class="block text-sm font-medium text-gray-700">Contrato x
                    Modelo:</label>
                <x-forms.smart-select id="servico[0][id_contrato_modelo]" name="servico[0][id_contrato_modelo]"
                    placeholder="Selecionar" :options="$modeloContratox"
                    :selected="request('servico[0][id_contrato_modelo]')" minSearchLength="2"
                    display-class="select-display" />
            </div>

            <div>
                <label for="servico[0][id_grupo_servico]" class="block text-sm font-medium text-gray-700">Grupo de
                    Serviço:</label>
                <x-forms.smart-select id="servico[0][id_grupo_servico]" name="servico[0][id_grupo_servico]"
                    placeholder="Selecionar" :options="$gruposServicos"
                    :selected="request('servico[0][id_grupo_servico]')" minSearchLength="2"
                    display-class="select-display" :on-select-callback="'handleGrupoServicoChange'" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">


            <input type="hidden" id="servico[0][id_servico]" name="servico[0][id_servico]" placeholder="Selecionar"
                :selected="request('servico[0][id_servico]')" minSearchLength="2" display-class="select-display" />


            <div>
                <label for="servico[0][valor_servico]" class="block text-sm font-medium text-gray-700">Valor
                    Serviço:</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">R$</span>
                    </div>
                    <input type="text" step="0.01" id="servico[0][valor_servico]" name="servico[0][valor_servico]"
                        @if(request('aba')==='servicos' ) required @endif
                        class="pl-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="0,00">
                </div>
            </div>

            <div>
                <label for="servico[0][is_valido]" class="block text-sm font-medium text-gray-700">Ativo:</label>
                <div class="mt-1 space-x-4">
                    <div class="inline-flex items-center">
                        <input type="radio" id="is_valido_sim" name="servico[0][is_valido]" value="1" checked
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="is_valido_sim" class="ml-2 block text-sm text-gray-700">Sim</label>
                    </div>
                    <div class="inline-flex items-center">
                        <input type="radio" id="is_valido_nao" name="servico[0][is_valido]" value="0"
                            class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                        <label for="is_valido_nao" class="ml-2 block text-sm text-gray-700">Não</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" id="btn_adicionar_servico"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Adicionar
            </button>
        </div>
    </form>

    {{-- Lista de Serviços --}}
    <div class="mt-6">
        <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-700">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="py-3 px-6">Cód.</th>
                        <th scope="col" class="py-3 px-6">Grupo</th>
                        <th scope="col" class="py-3 px-6">Serviço</th>
                        <th scope="col" class="py-3 px-6">Contrato</th>
                        <th scope="col" class="py-3 px-6">Modelo</th>
                        <th scope="col" class="py-3 px-6">Valor</th>
                        <th scope="col" class="py-3 px-6">Ativo</th>
                        <th scope="col" class="py-3 px-6">Ações</th>
                    </tr>
                </thead>
                <tbody id="servicos-lista">
                    @if(isset($servicos) && count($servicos) > 0)
                    @foreach($servicos as $servico)
                    <tr class="bg-white border-b hover:bg-gray-50" id="linha-servico-{{ $servico->id_servico_forn }}"
                        data-id="{{ $servico->id_servico_forn }}" data-id-grupo="{{ $servico->id_grupo_servico }}"
                        data-id-servico="{{ $servico->id_servico }}" data-id-contrato="{{ $servico->id_contrato_forn }}"
                        data-id-contrato-modelo="{{ $servico->id_contrato_modelo }}"
                        data-id="{{ $servico->id_servico_forn }}">
                        <td class="py-3 px-6">{{ $servico->id_servico_forn }}</td>
                        <td class="py-3 px-6">{{ $servico->grupoServico->descricao_grupo ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $servico->servico->descricao_servico ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $servico->contrato->id_contrato_forn ?? '-' }}</td>
                        <td class="py-3 px-6">{{ $servico->contratoModelo->modelo->descricao_modelo_veiculo ?? '-' }}
                        </td>
                        <td class="py-3 px-6">R$ {{ number_format($servico->valor_servico, 2, ',', '.') }}</td>
                        <td class="py-3 px-6">{{ $servico->is_valido ? 'Sim' : 'Não' }}</td>
                        <td class="py-3 px-6">
                            <div class="flex space-x-2">
                                <button type="button" onclick="editarServico({{ $servico->id_servico_forn }})"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" onclick="excluirServico({{ $servico->id_servico_forn }})"
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
                        <td colspan="9" class="px-6 py-3 text-center text-gray-500">Nenhum Serviço cadastrado
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
    let servicosTemporarios = [];
    let servicoModeloEditando = null;

    document.addEventListener('DOMContentLoaded', function(){
        const btn_adicionar_servico = document.getElementById('btn_adicionar_servico');
        const grupoSelect = document.querySelector('select[name="servico[0][id_grupo_servico]"]');
        const servicoSelect = document.querySelector('select[name="servico[0][id_servico]"]');    

        // Debug: verifique se os elementos foram encontrados
        console.log('Grupo select:', grupoSelect);
        console.log('Servico select:', servicoSelect);

        // ação do botão adicionar
        if(btn_adicionar_servico) {
            btn_adicionar_servico.addEventListener('click', function() {
                adicionarServicoLocal();
            });
        }


        // capturar form
        const form = document.querySelector('form-fornecedor'); // ajustado
        if(form){
            form.addEventListener('submit', function(){
                document.querySelectorAll('[name^="servico["]').forEach(field => field.remove());

                servicosTemporarios.forEach((servico, index) => {
                    const idServicoInput = document.createElement('input');
                    idServicoInput.type = 'hidden';
                    idServicoInput.name = `servico[${index}][id_servico_forn]`;
                    idServicoInput.value = servico.id_servico_forn || '';
                    form.appendChild(idServicoInput);

                    const idContrato = document.createElement('input'); 
                    idContrato.type = 'hidden'; 
                    idContrato.name = `servico[${index}][id_contrato_forn]`; 
                    idContrato.value = servico.id_contrato_forn || ''; 
                    form.appendChild(idContrato); 
                    
                    const idContratoModelo = document.createElement('input'); 
                    idContratoModelo.type = 'hidden'; 
                    idContratoModelo.name = `servico[${index}][id_contrato_modelo]`;
                    idContratoModelo.value = servico.id_contrato_modelo || ''; 
                    form.appendChild(idContratoModelo); 
                    
                    const idGrupoServico = document.createElement('input'); 
                    idGrupoServico.type = 'hidden'; 
                    idGrupoServico.name = `servico[${index}][id_grupo_servico]`;
                    idGrupoServico.value = servico.id_grupo_servico || ''; 
                    form.appendChild(idGrupoServico);

                    const IdServico = document.createElement('input'); 
                    IdServico.type = 'hidden'; 
                    IdServico.name = `servico[${index}][id_servico]`;
                    IdServico.value = servico.id_servico || ''; 
                    form.appendChild(IdServico);

                    const idValorServico = document.createElement('input'); 
                    idValorServico.type = 'hidden'; 
                    idValorServico.name = `servico[${index}][valor_servico]`;
                    //idValorServico.value = servico.valor_servico || '';
                    // normaliza: remove pontos de milhar e troca vírgula por ponto
                    let valorFormatado = servico.valor_servico || '';
                    valorFormatado = valorFormatado.replace(/\./g, '').replace(',', '.');

                    idValorServico.value = valorFormatado;

                    form.appendChild(idValorServico); 
                    
                    const idValidoServico = document.createElement('input'); 
                    idValidoServico.type = 'hidden'; 
                    idValidoServico.name = `servico[${index}][is_valido]`; 
                    idValidoServico.value = servico.is_valido || 1; 
                    form.appendChild(idValidoServico)
                });
            });

           

        }

        @if(isset($servicos) && $servicos->count() > 0)
            @foreach($servicos as $servico)
            servicosTemporarios.push({
                id_servico_forn: '{{ $servico->id_servico_forn }}'.trim(),
                id_grupo_servico: '{{ $servico->id_grupo ?? '' }}'.trim(),
                descricao_grupo: '{{ $servico->grupoServico->descricao_grupo ?? '' }}'.trim(),
                id_servico: '{{$servico->id_servico ?? '' }}'.trim(),
                descricao_servico: '{{ $servico->servico->descricao_servico ?? '' }}'.trim(),
                id_contrato_forn: '{{ $servico->id_contrato_forn }}'.trim(),
                id_contrato_modelo: '{{ $servico->id_contrato_modelo ?? ''  }}'.trim(),
                descricao_contrato_modelo: '{{ $servico->contratoModelo->modelo->descricao_modelo_veiculo ?? '' }}'.trim(),
                valor_servico: '{{ $servico->valor_servico }}',
                is_valido: '{{ $servico->is_valido }}'
            });
            @endforeach
        @endif

        console.log('Array inicial de Peças:', servicosTemporarios);
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
                const grupoHiddenInput = document.querySelector('input[name="servico[0][id_grupo_servico]"]');
                if (grupoHiddenInput && grupoHiddenInput.value) {
                    grupoId = grupoHiddenInput.value;
                    console.log('Valor encontrado no input hidden do grupo:', grupoId);
                }
            }

            // Se encontrou um grupo, carregar as peças
            if (grupoId) {
                carregarServicos(grupoId);
                
                // Se houver peças temporárias, tentar selecionar a peça correspondente
                if (servicosTemporarios.length > 0 && servicosTemporarios[0].id_servico) {
                    setTimeout(() => {
                        const primeiroServico = servicosTemporarios[0];
                        console.log('Tentando selecionar peça existente:', primeiroServico.id_servico);
                        selecionarPecaExistente(primeiroServico.id_servico);
                    }, 800); // Delay menor
                }
            } else {
                console.log('Nenhum grupo selecionado para carregar peças');
            }
        }, 300);

        // Configurar o callback global para o smart-select
        window.handleGrupoServicoChange = function(selectedValue, selectedOption) {
            console.log('Grupo selecionado (callback):', selectedValue, 'Opção:', selectedOption);
            
            if (selectedValue) {
                carregarServicos(selectedValue);
            } else {
                limparSelectServicos();
            }
        };
    });

    

    function SelecionarServicoExistente(idServico) {
        const servicoSelect = encontrarSelectServicos();
            if (servicoSelect && servicoSelect.tagName === 'SELECT') {
                servicoSelect.value = idServico;
                console.log('Serviço selecionado no select:', idServico);
            } else if (servicoSelect && servicoSelect.tagName === 'INPUT') {
                // Para inputs hidden (Alpine)
            servicoSelect.value = idServico;
            console.log('Input hidden de servico definido:', idServico);
            
            // Tentar atualizar também o componente Alpine se existir
            const servicoComponent = document.querySelector('[x-data*="smartSelect"]');
            if (servicoComponent && servicoComponent.__x) {
                const alpine = servicoComponent.__x;
                alpine.$data.selectedValues = [idServico];
                const servicoOption = alpine.$data.initialOptions.find(opt => opt.value == idServico);
                if (servicoOption) {
                    alpine.$data.selectedLabels = [servicoOption.label];
                }
                console.log('Componente Alpine atualizado com serviço selecionado');
            }
        }
    }

     // Função global para handle do grupo
    window.handleGrupoServicoChange = function(selectedValue, selectedOption) {
        console.log('Grupo selecionado:', selectedValue, 'Opção:', selectedOption);
        
        // Não confie no selectName, use o ID fixo
        carregarServicos(selectedValue);
    };

    // Função para carregar serviços
    function carregarServicos(idGrupo) {
        console.log('Carregando serviços para grupo:', idGrupo);
        
        if (!idGrupo || idGrupo === '') {
            limparSelectServicos();
            return;
        }

        fetch(`/admin/fornecedores/servicos/grupo/${idGrupo}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro na resposta do servidor');
                return response.json();
            })
            .then(data => {
                console.log('Serviços recebidos:', data);
                atualizarSelectServicos(data);
            })
            .catch(err => {
                console.error('Erro ao carregar serviços:', err);
                limparSelectServicos('Erro ao carregar serviços');
            });
    }

    function limparSelectServicos(mensagem = 'Selecione um grupo primeiro') {
        document.getElementById('servico[0][id_contrato_forn]').value = '';
        document.getElementById('servico[0][id_contrato_modelo]').value = '';
        document.getElementById('servico[0][id_grupo_servico]').value = '';
        document.getElementById('servico[0][id_servico]').value = '';
        document.getElementById('servico[0][valor_servico]').value = '';
        
        // desmarca radio
        const radios = document.querySelectorAll('input[name="servico[0][is_valido]"]');
        radios.forEach(r => r.checked = false);
    }

    function encontrarSelectServicos() {
        // Tentar diferentes seletores possíveis
        const seletores = [
            'select[name="servico[0][id_servico]"]',
            'select[name^="servico["][name$="[id_servico]"]',
            '#servico\\[0\\]\\[id_servico\\] select',
            '[id="servico\\[0\\]\\[id_servico\\]"] select',
            'select[id^="servico_"][id$="_id_servico"]',
            'input[name^="servico["][name$="[id_servico]"]',
            'input[name="servico[0][id_servico]"]'
        ];
        
        for (const seletor of seletores) {
            const elemento = document.querySelector(seletor);
            if (elemento) {
                console.log('Select/encontrado com seletor:', seletor, elemento);
                return elemento;
            }
        }
        
        console.warn('Nenhum select de serviços encontrado com os seletores tentados');
        return null;
    }

    function atualizarSelectServicos(servicos) {
        const servicoSelect = encontrarSelectServicos();
        
        if (!servicoSelect) {
            console.error('Select de serviços não encontrado após busca ampla!');
            return;
        }

        console.log('Atualizando select:', servicoSelect);
        
        // Verificar se é um select ou input hidden
        if (servicoSelect.tagName === 'SELECT') {
            // É um select normal
            servicoSelect.innerHTML = '<option value="">Selecione o serviço</option>';
            
            servicos.forEach(servico => {
                const option = document.createElement('option');
                option.value = servico.value;
                option.textContent = servico.label;
                servicoSelect.appendChild(option);
            });

            servicoSelect.dispatchEvent(new Event('change', { bubbles: true }));
            
        } else if (servicoSelect.tagName === 'INPUT' && servicoSelect.type === 'hidden') {
            // É um input hidden - precisamos atualizar o componente Alpine
            console.log('É um input hidden, atualizando Alpine...');
            atualizarAlpineSelectServicos(servicos, servicoSelect.value);
        }
    }

    function atualizarAlpineSelectServicos(servicos, valorSelecionado = '') {
        // Encontrar o componente Alpine pelo ID
        const servicoComponent = document.querySelector('[id="servico\\[0\\]\\[id_servico\\]"]');
        
        if (servicoComponent && servicoComponent.__x) {
            const alpine = servicoComponent.__x;
            console.log('Componente Alpine encontrado:', alpine);
            
            // Atualizar as opções
            alpine.$data.initialOptions = servicos;
            alpine.$data.filteredOptions = servicos;
            alpine.$data.loading = false;
            
            // Se houver um valor selecionado, definir
            if (valorSelecionado) {
                alpine.$data.selectedValues = [valorSelecionado];
                alpine.$data.selectedLabels = [servicos.find(s => s.value == valorSelecionado)?.label || ''];
            }
            
            console.log('Smart-select de serviços atualizado');
        } else {
            console.warn('Componente Alpine não encontrado');
            
            // Tentativa alternativa: criar um select temporário se não existir
            criarSelectTemporarioServicos(servicos);
        }
    }

    function criarSelectTemporarioServicos(servicos) {
        console.log('Criando select temporário com busca expansível...');

        let container = document.querySelector('#servico-temp-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'servico-temp-container';
            container.style.marginTop = '15px';
            document.querySelector('label[for*="id_servico"]')?.parentNode?.appendChild(container);
        }

        container.innerHTML = `
        <label for="servico[0][id_servico]" class="block text-sm font-medium text-gray-700">Serviço:</label>
            <div style="max-width: 400px; position: relative;">
                <div id="servico-dropdown-toggle" 
                    style="
                        padding: 10px 12px;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        cursor: pointer;
                        background: #fff;
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    ">
                    <span id="servico-selected" style="
                        flex: 1;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    ">Selecione o serviço</span>
                    <span style="margin-left: 8px;"></span>
                </div>
                <div id="servico-dropdown" 
                    style="
                        display: none;
                        position: absolute;
                        top: 100%;
                        left: 0;
                        right: 0;
                        border: 1px solid #d1d5db;
                        border-radius: 6px;
                        background: #fff;
                        z-index: 1000;
                        padding: 8px;
                        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
                    ">
                    <input type="text" id="servico-search" placeholder="Pesquisar..." 
                        style="
                            width: 100%;
                            padding: 8px;
                            margin-bottom: 6px;
                            border: 1px solid #d1d5db;
                            border-radius: 4px;
                            font-size: 14px;
                        "
                    >
                    <div id="servico-options" style="max-height: 150px; overflow-y: auto;"></div>
                </div>
            </div>
        `;

        const toggle = container.querySelector('#servico-dropdown-toggle');
        const dropdown = container.querySelector('#servico-dropdown');
        const searchInput = container.querySelector('#servico-search');
        const optionsContainer = container.querySelector('#servico-options');
        const selectedText = container.querySelector('#servico-selected');

        function renderOptions(filtro = '') {
            optionsContainer.innerHTML = servicos
                .filter(s => s.label.toLowerCase().includes(filtro.toLowerCase()))
                .map(s => `<div data-value="${s.value}" style="
                    padding: 6px;
                    cursor: pointer;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                " title="${s.label}">${s.label}</div>`)
                .join('');
        }

        renderOptions();

        toggle.addEventListener('click', () => {
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
                const hiddenInput = document.querySelector('input[name="servico[0][id_servico]"]');
                if (hiddenInput) {
                    hiddenInput.value = e.target.dataset.value;
                }
            }
        });

        document.addEventListener('click', (e) => {
            if (!container.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }




    document.addEventListener('DOMContentLoaded', function() {
        console.log('Select de grupo:', document.querySelector('select[name="servico[0][id_grupo_servico]"]'));
        console.log('Select de serviço:', document.querySelector('select[name="servico[0][id_servico]"]'));
        
        // Verificar todos os elementos com x-data
        setTimeout(() => {
            document.querySelectorAll('[x-data]').forEach(el => {
                console.log('Elemento Alpine:', el, el.__x ? 'Com instância' : 'Sem instância');
            });
        }, 1000);
    });

    console.log('Todos os selects na página:');

    document.querySelectorAll('select').forEach(select => {
        console.log('Select:', select.name, select.id, select);
    });

    console.log('Todos os inputs hidden:');
    document.querySelectorAll('input[type="hidden"]').forEach(input => {
        if (input.name.includes('servico')) {
            console.log('Input servico:', input.name, input.value);
        }
    });

    // Verificar a estrutura do smart-select de serviços
    const servicoSmartComponent = document.querySelector('[id="servico\\[0\\]\\[id_servico\\]"]');
    console.log('Componente smart-select de serviços:', servicoSmartComponent);

    if (servicoSmartComponent) {
        // Procurar o select real dentro do componente
        const selectReal = servicoSmartComponent.querySelector('select');
        console.log('Select real dentro do componente:', selectReal);
    }

    function adicionarServicoLocal() {
        const btn_adicionar_servico = document.getElementById('btn_adicionar_servico');
        const isEditMode = btn_adicionar_servico?.getAttribute('data-edit-mode') === 'true';
        const editingId = btn_adicionar_servico?.getAttribute('data-editing-id');

        // SEMPRE usar índice 0 porque temos apenas um formulário
        const indexPrefix = 0;
        
        const idContratoEl = document.querySelector(`[name="servico[${indexPrefix}][id_contrato_forn]"]`);
        const idContrato = idContratoEl ? idContratoEl.value : '';

        const idContratoModeloEl = document.querySelector(`[name="servico[${indexPrefix}][id_contrato_modelo]"]`);
        const idContratoModelo = idContratoModeloEl ? idContratoModeloEl.value : '';

        const grupoEl = document.querySelector(`[name="servico[${indexPrefix}][id_grupo_servico]"]`);
        const idGrupoServico = grupoEl ? grupoEl.value : '';
        
        // Correção: Verificar se grupoEl existe antes de acessar suas propriedades
        let descricaoGrupo = '';
        const grupoDisplayEl = document.querySelector(`.select-display[name="servico[${indexPrefix}][id_grupo_servico]"]`);
        
        if (grupoDisplayEl) {
            descricaoGrupo = grupoDisplayEl.textContent.trim();
        } else if (grupoEl && grupoEl.options && grupoEl.selectedIndex !== -1) {
            descricaoGrupo = grupoEl.options[grupoEl.selectedIndex].text;
        }

        const servicoEl = document.querySelector(`[name="servico[${indexPrefix}][id_servico]"]`);
        const idServico = servicoEl ? servicoEl.value : '';
        
        // Correção: Verificar se servicoEl existe antes de acessar suas propriedades
        let descricaoServico = '';
        const servicoDisplayEl = document.querySelector(`.servico-display[name="servico[${indexPrefix}][id_servico]"]`);
        
        if (servicoDisplayEl) {
            descricaoServico = servicoDisplayEl.textContent.trim();
        } else if (servicoEl && servicoEl.options && servicoEl.selectedIndex !== -1) {
            descricaoServico = servicoEl.options[servicoEl.selectedIndex].text;
        }

        const idValidoServico = document.querySelector(`input[name="servico[${indexPrefix}][is_valido]"]:checked`)?.value || '1';

        const valorEl = document.querySelector(`[name="servico[${indexPrefix}][valor_servico]"]`);
        const idValorServico = valorEl ? valorEl.value : '';

        console.log('Valores capturados:', {
            idContrato, idContratoModelo, idGrupoServico, idServico, idValorServico
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
        if (!idGrupoServico) {
            alert('Informe o Grupo de Serviço!');
            return false;
        }
        if (!idValorServico) {
            alert('Informe o Valor do Serviço!');
            return false;
        }

        const servicoData = {
            id_servico_forn: isEditMode ? editingId : null,
            id_contrato_forn: idContrato,
            id_contrato_modelo: idContratoModelo,
            id_grupo_servico: idGrupoServico,
            descricao_grupo: descricaoGrupo,
            id_servico: idServico,
            descricao_servico: descricaoServico,
            valor_servico: idValorServico,
            is_valido: idValidoServico,
        };

        if (isEditMode && editingId) {
            const index = servicosTemporarios.findIndex(item => 
                String(item.id_servico_forn) === String(editingId) || 
                String(item.temp_id) === String(editingId)
            );

            if (index !== -1) {
                // Preservar o temp_id se existir
                servicoData.temp_id = servicosTemporarios[index].temp_id;
                servicosTemporarios[index] = servicoData;
                atualizarLinhaServico(servicosTemporarios[index]);
                salvarServicoNoForm(servicosTemporarios[index], index, true);
                resetarBotaoServico();
            } else {
                console.error('Índice não encontrado para edição');
            }

        } else {
            // Adicionar novo item
            const newTempId = 'temp_' + Date.now();
            servicoData.temp_id = newTempId;
            servicosTemporarios.push(servicoData);
            const index = servicosTemporarios.length - 1;
            adicionarLinhaServico(servicoData);
            salvarServicoNoForm(servicoData, index, false);
        }
    }


    function salvarServicoNoForm(servico, index, isEdit = false) {
        const container = document.getElementById('servico-hidden');

        // Identificador correto: temp_id ou id_servico_forn
        const identificador = servico.temp_id || servico.id_servico_forn;

        // se for edição, remove versão anterior
        let wrapper = container.querySelector(`[data-id="${identificador}"]`);
        if (wrapper) wrapper.remove();

        wrapper = document.createElement('div');
        wrapper.dataset.id = identificador;

        wrapper.innerHTML = `
            <input type="hidden" name="servico[${index}][id_servico_forn]" value="${servico.id_servico_forn || ''}">
            <input type="hidden" name="servico[${index}][id_grupo_servico]" value="${servico.id_grupo_servico || ''}">
            <input type="hidden" name="servico[${index}][id_servico]" value="${servico.id_servico || 0}">
            <input type="hidden" name="servico[${index}][id_contrato_forn]" value="${servico.id_contrato_forn || ''}">
            <input type="hidden" name="servico[${index}][id_contrato_modelo]" value="${servico.id_contrato_modelo || ''}">
            <input type="hidden" name="servico[${index}][valor_servico]" value="${servico.valor_servico || ''}">
            <input type="hidden" name="servico[${index}][is_valido]" value="${servico.is_valido || 1}">
        `;

        container.appendChild(wrapper);
    }


    function editarServico(id) {
        console.log('Editando serviço ID:', id);

        const modelo = servicosTemporarios.find(item => 
            String(item.id_servico_forn) === String(id) || String(item.temp_id) === String(id)
        );

        if (modelo) {
            console.log('Serviço encontrado para edição:', modelo);

            const form = document.getElementById('servico-form');
            form.setAttribute('data-edit-mode', 'true');
            form.setAttribute('data-editing-id', id);

            setTimeout(() => {
                preencherFormularioCorrigido(modelo);

                const btn = document.getElementById('btn_adicionar_servico');
                if (btn) {
                    btn.textContent = 'Atualizar';
                    btn.setAttribute('data-edit-mode', 'true');
                    btn.setAttribute('data-editing-id', id);
                }
            }, 100);
        } else {
            console.error('Serviço não encontrado para edição. Lista atual:', servicosTemporarios);
            alert('Serviço não encontrado!');
        }
    }


    function preencherFormularioCorrigido(servico) {
        console.log('Preenchendo formulário com:', servico);
        
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
        setValue(`servico[${index}][id_contrato_forn]`, servico.id_contrato_forn);
        setValue(`servico[${index}][id_contrato_modelo]`, servico.id_contrato_modelo);
        setValue(`servico[${index}][id_grupo_servico]`, servico.id_grupo_servico);
        setValue(`servico[${index}][id_servico]`, servico.id_servico);
        setValue(`servico[${index}][valor_servico]`, servico.valor_servico);
        
        // Marcar radio button
        setRadio(`servico[${index}][is_valido]`, servico.is_valido);
        
        // Atualizar displays visuais
        setDisplayText(`.select-display[name="servico[${index}][id_grupo_servico]"]`, servico.descricao_grupo);
        setDisplayText(`.servico-display[name="servico[${index}][id_servico]"]`, servico.descricao_servico);

        // Log para debug
        console.log('Formulário preenchido. Verificando valores:');
        console.log('Contrato:', document.querySelector(`[name="servico[${index}][id_contrato_forn]"]`)?.value);
        console.log('Grupo:', document.querySelector(`[name="servico[${index}][id_grupo_servico]"]`)?.value);
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
    function debugEstruturaCompleta() {
        console.log('=== DEBUG DOS CAMPOS ===');
        const campos = [
            'servico[0][id_grupo_servico]',
            'servico[0][id_servico]',
            'servico[0][id_contrato_forn]',
            'servico[0][id_contrato_modelo]',
            'servico[0][valor_servico]'
        ];
        
        campos.forEach(name => {
            const el = document.querySelector(`[name="${name}"]`);
            console.log(`${name}:`, el ? {
                existe: true,
                valor: el.value,
                tipo: el.type,
                id: el.id
            } : 'NÃO ENCONTRADO');
        });
    }
    
    

    function excluirServico(id) {
        // 1. Tratamento seguro do event
        

        // 3. Verificação do ID
        if (!id || id === 'novo') {
            // Caso seja um servico não persistido
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
        if (!confirm('Tem certeza que deseja excluir este servico permanentemente?')) {
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
        fetch(`/admin/fornecedores/servicos/destroy/${id}`, {
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
            showFeedback('success', data.message || 'Contrato excluído com sucesso');
        })
        .then(response => {
            if (response.success) {
                // Remove a linha temporária (se existir)
                const tempLine = document.querySelector('tr[data-id="novo"]');
                if (tempLine) tempLine.remove();
                
                // Adiciona a linha com o ID real
                adicionarLinhaServico(response.servico, true);
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

    function adicionarLinhaServico(data) {
        const tabela = document.getElementById('servicos-lista');
        if (!tabela) return;

        // Remover linha de "Nenhum Serviço cadastrado" se existir
        tabela.querySelectorAll('tr').forEach(row => {
            if (row.textContent.trim().toLowerCase().includes('nenhum serviço cadastrado')) {
                row.remove();
            }
        });

        // Adiciona a nova linha
        const id = data.id_servico_forn ? String(data.id_servico_forn) : 'temp_' + Date.now();
        data.temp_id = data.id_servico_forn ? null : id;

        const row = document.createElement('tr');
        row.id = `linha-servico-${id}`;
        row.className = 'bg-white border-b hover:bg-gray-50';
        row.setAttribute('data-id', id);

        row.innerHTML = `
            <td class="py-3 px-6">${data.id_servico_forn || 'Novo'}</td>
            <td class="py-3 px-6">${data.descricao_grupo || data.id_grupo_servico}</td>
            <td class="py-3 px-6">${data.descricao_servico || data.id_servico}</td>
            <td class="py-3 px-6">${data.id_contrato_forn}</td>
            <td class="py-3 px-6">${data.id_contrato_modelo}</td>
            <td class="py-3 px-6">R$ ${parseFloat(data.valor_servico).toFixed(2)}</td>
            <td class="py-3 px-6">${data.is_valido == 1 ? 'Sim' : 'Não'}</td>
            <td class="py-3 px-6">
                <div class="flex space-x-2">
                    <button type="button" onclick="editarServico('${id}')"
                        class="inline-flex items-center p-1 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </button>
                    <button type="button" onclick="excluirServico('${id}')"
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
    function resetarBotaoServico() {
        const btn = document.getElementById('btn_adicionar_servico');
        if (btn) {
            btn.textContent = 'Adicionar Serviço';
            btn.setAttribute('data-edit-mode', 'false');
            btn.removeAttribute('data-editing-id');
        }
        
        const form = document.getElementById('servico-form');
        if (form) {
            form.setAttribute('data-edit-mode', 'false');
            form.removeAttribute('data-editing-id');
        }
        
        // Limpar formulário se necessário
        document.querySelectorAll('#servico-form input, #servico-form select').forEach(field => {
            if (field.type !== 'radio') {
                field.value = '';
            }
        });
        
        // Resetar radios
        const radio = document.querySelector('input[name^="servico["][name$="[is_valido]"]');
        if (radio) radio.checked = true;
    }

    function atualizarLinhaServico(servicoData) {
        const id = servicoData.temp_id || servicoData.id_servico_forn;
        const row = document.getElementById(`linha-servico-${id}`);
        
        if (row) {
            // Atualizar a linha existente de forma segura (sem innerHTML)
            const cells = row.querySelectorAll('td');
            if (cells.length >= 7) {
                cells[0].textContent = servicoData.id_servico_forn || 'Novo';
                cells[1].textContent = servicoData.id_grupo_servico || servicoData.id_grupo_servico;
                cells[2].textContent = servicoData.id_servico || servicoData.id_servico;
                cells[3].textContent = servicoData.id_contrato_forn;
                cells[4].textContent = servicoData.id_contrato_modelo;
                cells[5].textContent = `R$ ${Number(servicoData.valor_servico || 0).toLocaleString('pt-BR', {minimumFractionDigits: 2})}`;
                cells[6].textContent = servicoData.is_valido == 1 ? 'Sim' : 'Não';
            }
        }
    }

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const input = document.getElementById("servico[0][valor_servico]");

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