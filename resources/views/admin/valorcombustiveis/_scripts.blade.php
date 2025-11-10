<script>
    // Scripts para o módulo ValorCombustivelTerceiro
    
    /**
     * Função para exibir alerta simplificado
     */
    function showAlert(options) {
        if (confirm(options.message || 'Você tem certeza?')) {
            if (typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
        }
    }
    
    /**
     * Função para mostrar notificação
     */
    function notify(options) {
        alert(options.message);
    }
    
    /**
     * Confirma a exclusão de um registro
     */
    function confirmarExclusao(id) {
        showAlert({
            message: 'Deseja excluir este Valor de Combustível?',
            onConfirm: () => excluirValorCombustivel(id)
        });
    }
    
    /**
     * Realiza a exclusão do registro via AJAX
     */
    function excluirValorCombustivel(id) {
        // Obter o token CSRF
        let csrfToken;
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfInput = document.querySelector('input[name="_token"]');
        
        if (csrfMeta) {
            csrfToken = csrfMeta.content;
        } else if (csrfInput) {
            csrfToken = csrfInput.value;
        } else {
            console.error('Token CSRF não encontrado');
            notify({
                message: 'Erro: Token CSRF não encontrado'
            });
            return;
        }
    
        fetch(`/admin/valorcombustiveis/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notify({
                    message: 'Valor de Combustível excluído com sucesso'
                });
                window.location.reload();
            } else {
                notify({
                    message: data.message || 'Erro ao excluir Valor de Combustível'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notify({
                message: 'Erro ao excluir Valor de Combustível'
            });
        });
    }
    
    /**
     * Valida o ID da bomba selecionada
     */
    function ValidarIdBomba() {
        const idBomba = document.querySelector('[name="boma_combustivel"]');
        
        if (!idBomba || !idBomba.value || idBomba.value === "true") {
            return; // Não faz nada se o elemento não existir, não tiver valor ou for "true"
        }
        
        if (!isNaN(parseInt(idBomba.value)) && parseInt(idBomba.value) > 0) {
            // Só continua se for um número válido maior que zero
            return true;
        }
        
        return false;
    }
    
    /**
     * Exibe ou oculta o campo de data de fim
     */
    function mostraDataFim(param) {
        const dataFimInput = document.getElementById('data_fim_div');
        if (dataFimInput) {
            if (param === "mostrar") {
                dataFimInput.style.display = "block";
            } else {
                dataFimInput.style.display = "none";
            }
        }
    }
    
    /**
     * Limpa o formulário após confirmação
     */
    function limparFormulario() {
        if (!confirm('Deseja realmente limpar todos os dados do formulário?')) return;
        
        document.getElementById('valorCombustivelForm').reset();
        document.getElementById('tipoCombustivel').value = '';
        document.getElementById('valor_diesel').value = '';
    }
    
    /**
     * Busca e atualiza os dados relacionados à bomba selecionada
     */
    function atualizarDadosBomba(idBomba) {
        // Validação extra para garantir que idBomba seja um número válido
        if (!idBomba || idBomba === "true" || idBomba === true || isNaN(parseInt(idBomba)) || parseInt(idBomba) <= 0) {
            console.error('ID de bomba inválido:', idBomba);
            return;
        }
        
        // Converter para inteiro
        idBomba = parseInt(idBomba);
        console.log('Buscando dados da bomba ID:', idBomba);
        
        // Obter o token CSRF do formulário ou do meta tag
        let csrfToken;
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfInput = document.querySelector('input[name="_token"]');
        
        if (csrfMeta) {
            csrfToken = csrfMeta.content;
        } else if (csrfInput) {
            csrfToken = csrfInput.value;
        } else {
            console.error('Token CSRF não encontrado');
            return;
        }
        
        fetch("/admin/valorcombustiveis/get-valor-bomba", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "Accept": "application/json"
            },
            body: JSON.stringify({ idBomba: idBomba })
        })
        .then(response => {
            console.log('Status da resposta:', response.status);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('Resposta não-OK:', text);
                    throw new Error(`Erro na resposta do servidor: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log("Dados recebidos da bomba:", data); // Ajuda no debugging
            
            if (data.error) {
                console.error('Erro recebido do servidor:', data.error);
                alert(data.error);
                return;
            }
            
            // Preenche os campos relacionados
            const tipoCombustivel = document.getElementById('tipoCombustivel');
            const valorDiesel = document.getElementById('valor_diesel');
            
            if (tipoCombustivel) tipoCombustivel.value = data.tipo_combustivel || '';
            if (valorDiesel) valorDiesel.value = data.vlrunitario_interno || '';
        })
        .catch(error => {
            console.error('Erro ao buscar dados da bomba:', error);
            alert("Erro ao buscar dados da bomba. Por favor, tente novamente.");
        });
    }
    
    // Define o callback global que será chamado pelo componente SmartSelect
    window.atualizarDadosBombaCallback = function(idBomba) {
        // Validação extra para garantir que idBomba seja um número válido
        if (!idBomba || idBomba === "true" || idBomba === true) {
            console.error('ID de bomba inválido recebido pelo callback:', idBomba);
            return;
        }
        
        // Tentar converter para número e verificar se é válido
        const numericId = parseInt(idBomba);
        if (!isNaN(numericId) && numericId > 0) {
            console.log('Callback recebeu ID válido:', numericId);
            atualizarDadosBomba(numericId);
        } else {
            console.error('ID de bomba não é um número válido:', idBomba);
        }
    };
    
    // Inicializa os eventos quando o DOM estiver pronto
    document.addEventListener("DOMContentLoaded", function() {
        console.log('DOM loaded - inicializando scripts do módulo ValorCombustivelTerceiro');
        
        // Monitora mudanças na seleção da bomba
        const bombaSelect = document.querySelector('[name="boma_combustivel"]');
        
        if (bombaSelect) {
            console.log('Select da bomba encontrado, valor atual:', bombaSelect.value);
            
            // Esta é uma estratégia de defesa - esperar um pequeno atraso antes de processar o valor inicial
            // Isso permite que o browser termine de processar os bindings iniciais
            setTimeout(function() {
                // Verificar novamente o valor após o delay
                const initialValue = bombaSelect.value;
                console.log('Valor após delay:', initialValue);
                
                // Verificar se é um valor numérico válido
                if (initialValue && initialValue !== "true" && initialValue !== true) {
                    const numericId = parseInt(initialValue);
                    if (!isNaN(numericId) && numericId > 0) {
                        console.log('Valor inicial válido após delay, buscando dados:', numericId);
                        atualizarDadosBomba(numericId);
                    }
                }
            }, 500); // 500ms de delay deve ser suficiente
            
            bombaSelect.addEventListener("change", function() {
                const idBomba = this.value;
                console.log('Select da bomba alterado para:', idBomba);
                
                // Validação extra
                if (!idBomba || idBomba === "true" || idBomba === true) {
                    console.warn('Valor inválido selecionado, ignorando');
                    return;
                }
                
                // Tentar converter para número e verificar se é válido
                const numericId = parseInt(idBomba);
                if (!isNaN(numericId) && numericId > 0) {
                    console.log('Chamando atualização para ID válido:', numericId);
                    atualizarDadosBomba(numericId);
                } else {
                    console.error('ID de bomba não é um número válido:', idBomba);
                }
            });
        } else {
            console.warn('Select da bomba não encontrado no DOM');
        }
    });
</script>