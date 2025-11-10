<script>
    function confirmarExclusao(id) {
        if (confirm('Deseja realmente excluir esta Meta por Tipo de Equipamento? Esta ação não pode ser desfeita.')) {
            excluirMeta(id);
        }
    }

    function excluirMeta(id) {
        fetch(`/admin/metatipoequipamentos/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Meta por Tipo de Equipamento excluída com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao excluir Meta por Tipo de Equipamento');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao excluir Meta por Tipo de Equipamento');
            });
    }

    /**
 * Validação de Campos de Data
 * 
 * Este script implementa validação de datas nos campos de formulário,
 * verificando formatos de data válidos e prevenindo a submissão de 
 * formulários com datas inválidas.
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando validação de datas...');
    
    // Função para verificar se uma data é válida
    function isValidDate(dateString) {
        // Se estiver vazio, é considerado válido (campo opcional)
        if (!dateString) return true;
        
        // Verifica datas no formato brasileiro (DD/MM/YYYY)
        if (dateString.includes('/')) {
            const parts = dateString.split('/');
            if (parts.length !== 3) return false;
            
            const day = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1; // Meses em JS são 0-indexed
            const year = parseInt(parts[2], 10);
            
            // Verifica limites básicos
            if (isNaN(day) || isNaN(month) || isNaN(year)) return false;
            if (year < 1900 || year > 2100) return false;
            if (month < 0 || month > 11) return false;
            if (day < 1 || day > 31) return false;
            
            // Cria um objeto Date e verifica se os valores são iguais
            const date = new Date(year, month, day);
            return date.getFullYear() === year && 
                   date.getMonth() === month && 
                   date.getDate() === day;
        }
        
        // Verifica datas no formato ISO (YYYY-MM-DD)
        const date = new Date(dateString);
        return !isNaN(date.getTime()) && 
               dateString.match(/^\d{4}-\d{2}-\d{2}$/);
    }
    
    // Função para validar um campo de data e mostrar feedback visual
    function validateDateField(input) {
        const dateValue = input.value.trim();
        const isValid = isValidDate(dateValue);
        
        // Remove classes e mensagens anteriores
        input.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
        const errorMessage = input.parentNode.querySelector('.date-error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
        
        // Se inválido e não vazio, aplica estilos de erro
        if (!isValid && dateValue) {
            input.classList.add('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
            
            // Cria mensagem de erro
            const errorDiv = document.createElement('div');
            errorDiv.className = 'date-error-message text-red-500 text-xs mt-1';
            errorDiv.textContent = 'Data inválida. Use o formato DD/MM/AAAA ou AAAA-MM-DD.';
            input.parentNode.appendChild(errorDiv);
            
            return false;
        }
        
        return true;
    }
    
    // Função para verificar se a data inicial é anterior à data final
    function validateDateRange(initialDateInput, finalDateInput) {
        if (!initialDateInput || !finalDateInput) return true;
        
        const initialValue = initialDateInput.value.trim();
        const finalValue = finalDateInput.value.trim();
        
        // Se algum campo estiver vazio, considera válido
        if (!initialValue || !finalValue) return true;
        
        let initialDate, finalDate;
        
        // Converte para objeto Date baseado no formato
        if (initialValue.includes('/')) {
            const parts = initialValue.split('/');
            initialDate = new Date(parts[2], parts[1] - 1, parts[0]);
        } else {
            initialDate = new Date(initialValue);
        }
        
        if (finalValue.includes('/')) {
            const parts = finalValue.split('/');
            finalDate = new Date(parts[2], parts[1] - 1, parts[0]);
        } else {
            finalDate = new Date(finalValue);
        }
        
        // Remove mensagem de erro anterior
        const rangeErrorMessage = document.querySelector('.date-range-error-message');
        if (rangeErrorMessage) {
            rangeErrorMessage.remove();
        }
        
        // Verifica se a data inicial é posterior à data final
        if (initialDate > finalDate) {
            // Cria e exibe mensagem de erro
            const errorDiv = document.createElement('div');
            errorDiv.className = 'date-range-error-message text-red-500 text-xs mt-2 mb-2';
            errorDiv.textContent = 'A data inicial não pode ser posterior à data final.';
            
            // Insere após o campo de data final
            finalDateInput.parentNode.parentNode.insertBefore(errorDiv, finalDateInput.parentNode.nextSibling);
            
            return false;
        }
        
        return true;
    }
    
    // Encontra todos os campos de data no formulário
    const dateForms = document.querySelectorAll('form');
    dateForms.forEach(form => {
        // Busca campos de data dentro do formulário
        const dateInputs = form.querySelectorAll('input[type="date"]');
        
        // Para cada campo de data, adiciona validação no evento blur e input
        dateInputs.forEach(input => {
            // Ao perder o foco, valida o campo
            input.addEventListener('blur', function() {
                validateDateField(this);
                
                // Verifica o range de datas se for data inicial ou final
                if (this.name === 'data_inicial' || this.name === 'data_final') {
                    const initialDateInput = form.querySelector('input[name="data_inicial"]');
                    const finalDateInput = form.querySelector('input[name="data_final"]');
                    validateDateRange(initialDateInput, finalDateInput);
                }
            });
            
            // Ao digitar, remove estilos de erro
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500', 'focus:border-red-500', 'focus:ring-red-500');
                const errorMessage = this.parentNode.querySelector('.date-error-message');
                if (errorMessage) {
                    errorMessage.remove();
                }
                
                const rangeErrorMessage = document.querySelector('.date-range-error-message');
                if (rangeErrorMessage) {
                    rangeErrorMessage.remove();
                }
            });
        });
        
        // Adiciona validação no envio do formulário
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Valida todos os campos de data
            dateInputs.forEach(input => {
                if (!validateDateField(input)) {
                    isValid = false;
                }
            });
            
            // Verifica o range de datas, se existirem
            const initialDateInput = form.querySelector('input[name="data_inicial"]');
            const finalDateInput = form.querySelector('input[name="data_final"]');
            if (initialDateInput && finalDateInput) {
                if (!validateDateRange(initialDateInput, finalDateInput)) {
                    isValid = false;
                }
            }
            
            // Se algum campo for inválido, previne o envio do formulário
            if (!isValid) {
                event.preventDefault();
                
                // Feedback visual para o usuário
                const firstInvalidInput = form.querySelector('input.border-red-500');
                if (firstInvalidInput) {
                    firstInvalidInput.focus();
                    
                    // Adiciona mensagem de erro se não existir
                    if (!document.querySelector('.form-error-message')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'form-error-message text-red-500 text-sm font-medium mb-4';
                        errorDiv.textContent = 'Por favor, corrija os erros antes de enviar o formulário.';
                        form.insertBefore(errorDiv, form.firstChild);
                        
                        // Remove a mensagem após alguns segundos
                        setTimeout(() => {
                            const message = document.querySelector('.form-error-message');
                            if (message) {
                                message.remove();
                            }
                        }, 5000);
                    }
                }
            }
        });
        
        // Compatibilidade com HTMX
        if (typeof htmx !== 'undefined') {
            // Antes de uma requisição HTMX
            htmx.on('htmx:beforeRequest', function(event) {
                if (event.detail.elt.tagName === 'FORM') {
                    let isValid = true;
                    
                    // Validar todos os campos de data no formulário
                    const dateInputs = event.detail.elt.querySelectorAll('input[type="date"]');
                    dateInputs.forEach(input => {
                        if (!validateDateField(input)) {
                            isValid = false;
                        }
                    });
                    
                    // Verificar range de datas
                    const initialDateInput = event.detail.elt.querySelector('input[name="data_inicial"]');
                    const finalDateInput = event.detail.elt.querySelector('input[name="data_final"]');
                    if (initialDateInput && finalDateInput) {
                        if (!validateDateRange(initialDateInput, finalDateInput)) {
                            isValid = false;
                        }
                    }
                    
                    // Se inválido, cancelar a requisição
                    if (!isValid) {
                        event.preventDefault();
                    }
                }
            });
        }
    });
    
    console.log('Validação de datas inicializada.');
});
</script>