/**
 * Integração de Dados do Veículo
 * 
 * Este script gerencia a integração entre a seleção de veículo e o preenchimento automático
 * de dados relacionados (departamento, capacidade do tanque, km anterior)
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando integração de dados do veículo');
    
    // Obter token CSRF para requisições AJAX
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    // Referências aos elementos
    const veiculoInput = document.querySelector('input[name="id_veiculo"]');
    const departamentoInput = document.querySelector('input[name="id_departamento"]');
    const capacidadeTanqueInput = document.getElementById('capacidade_tanque');
    const kmAnteriorInput = document.getElementById('km_anterior');
    
    if (!veiculoInput) {
        console.error('Elemento input[name="id_veiculo"] não encontrado');
        return;
    }
    
    /**
     * Função principal para atualizar dados do veículo
     * @param {string|number} idVeiculo - ID do veículo selecionado
     */
    function atualizarDadosVeiculo(idVeiculo) {
        if (!idVeiculo) {
            console.log('ID do veículo não fornecido');
            return;
        }
        
        console.log('Atualizando dados para veículo ID:', idVeiculo);
        
        // Configurar headers para a requisição
        const headers = {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };
        
        // Buscar dados do veículo via API
        fetch(`/admin/api/veiculos/${idVeiculo}/dados`, {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Dados do veículo recebidos:', data);
            
            // Atualizar capacidade do tanque
            if (capacidadeTanqueInput) {
                const capacidade = data.capacidade_tanque_principal || 
                                 data.capacidade_tanque || 
                                 'N/A';
                capacidadeTanqueInput.value = capacidade;
                console.log('Capacidade do tanque atualizada para:', capacidade);
                
                // Disparar evento de atualização para Alpine.js
                if (window.abastecimentoFormComponent) {
                    window.abastecimentoFormComponent.capacidadeTanque = capacidade;
                    console.log('Capacidade do tanque atualizada no componente Alpine');
                }
            }
            
            // Atualizar KM anterior
            if (kmAnteriorInput) {
                kmAnteriorInput.value = data.km_atual || 'N/A';
                console.log('KM anterior atualizado para:', kmAnteriorInput.value);
                
                // Disparar evento de atualização para Alpine.js
                if (window.abastecimentoFormComponent) {
                    window.abastecimentoFormComponent.kmAnterior = data.km_atual || '';
                    console.log('KM anterior atualizado no componente Alpine');
                }
            }
            
            // Atualizar departamento
            if (data.id_departamento && departamentoInput) {
                console.log('Atualizando departamento para:', data.id_departamento);
                
                // Atualizar o valor do campo hidden
                departamentoInput.value = data.id_departamento;
                
                // Disparar eventos para notificar mudanças
                departamentoInput.dispatchEvent(new Event('input', { bubbles: true }));
                departamentoInput.dispatchEvent(new Event('change', { bubbles: true }));
                
                // Disparar evento personalizado para o smart-select
                document.dispatchEvent(new CustomEvent('option-selected', {
                    detail: {
                        targetId: 'id_departamento',
                        value: data.id_departamento
                    }
                }));
                
                console.log('Eventos disparados para atualização do departamento');
                
                // Atualizar a interface visual do smart-select
                setTimeout(() => {
                    const smartSelectButton = document.querySelector('#id_departamento-button');
                    if (smartSelectButton) {
                        // Procurar o departamento nos options disponíveis
                        const allOptions = Array.from(document.querySelectorAll('#id_departamento-listbox [role="option"]'));
                        const selectedOption = allOptions.find(option => {
                            try {
                                const optionData = option.__x?.dataset?.option;
                                if (optionData) {
                                    const parsedOption = JSON.parse(optionData);
                                    return parsedOption.value == data.id_departamento;
                                }
                            } catch (e) {
                                console.error('Erro ao processar opção:', e);
                            }
                            return false;
                        });
                        
                        if (selectedOption) {
                            const labelElement = selectedOption.querySelector('span');
                            if (labelElement) {
                                const displayElement = smartSelectButton.querySelector('span.block.truncate');
                                if (displayElement) {
                                    displayElement.textContent = labelElement.textContent;
                                    displayElement.classList.remove('text-gray-500');
                                    console.log('Display visual do departamento atualizado para:', labelElement.textContent);
                                }
                            }
                        }
                    }
                }, 100);
            }
        })
        .catch(error => {
            console.error('Erro ao buscar dados do veículo:', error);
        });
    }
    
    // Registrar eventos para mudanças no veículo selecionado
    
    // 1. Evento nativo de mudança do input
    veiculoInput.addEventListener('change', function() {
        console.log('Evento change no input id_veiculo detectado:', this.value);
        atualizarDadosVeiculo(this.value);
    });
    
    // 2. Evento do componente smart-select
    document.addEventListener('select-change', function(e) {
        if (e.detail && e.detail.name === 'id_veiculo') {
            console.log('Evento select-change para veículo detectado:', e.detail.value);
            atualizarDadosVeiculo(e.detail.value);
        }
    });
    
    // 3. Evento específico para o veículo
    window.addEventListener('id_veiculo:selected', function(e) {
        if (e.detail && e.detail.value) {
            console.log('Evento id_veiculo:selected detectado:', e.detail.value);
            atualizarDadosVeiculo(e.detail.value);
        }
    });
    
    // 4. Callback específico para o smart-select (usado na declaração do componente)
    window.atualizarDadosVeiculoCallback = function(idVeiculo) {
        console.log('Callback atualizarDadosVeiculoCallback chamado:', idVeiculo);
        atualizarDadosVeiculo(idVeiculo);
    };
    
    // Verificar se já existe um valor inicial e atualizar dados
    setTimeout(() => {
        if (veiculoInput.value) {
            console.log('Valor inicial do veículo encontrado:', veiculoInput.value);
            atualizarDadosVeiculo(veiculoInput.value);
        }
    }, 500);
    
    // Expor função globalmente para debug/testes
    window.testarAtualizacaoVeiculo = function(idVeiculo) {
        console.log('Teste manual iniciado para veículo ID:', idVeiculo);
        atualizarDadosVeiculo(idVeiculo);
    };
});