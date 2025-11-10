<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selecionar elementos importantes
        const selectAllCheckbox = document.getElementById('select-all');
        const servicoCheckboxes = document.querySelectorAll('.servico-checkbox:not([disabled])');
        const btnLancarNF = document.getElementById('btn-lancar-nf');
        const lancarNFForm = document.getElementById('lancar-nf-form');
        
        // Função para verificar se pelo menos um checkbox está marcado
        function checkButtonState() {
            const anyChecked = Array.from(servicoCheckboxes).some(checkbox => checkbox.checked);
            btnLancarNF.disabled = !anyChecked;
        }
        
        // Event listener para o checkbox "selecionar todos"
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                servicoCheckboxes.forEach(checkbox => {
                    checkbox.checked = selectAllCheckbox.checked;
                });
                checkButtonState();
            });
        }
        
        // Event listeners para os checkboxes individuais
        servicoCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                checkButtonState();
                
                // Verificar se todos estão marcados para atualizar o "selecionar todos"
                const allChecked = Array.from(servicoCheckboxes).every(cb => cb.checked);
                if (selectAllCheckbox) {
                    selectAllCheckbox.checked = allChecked;
                }
            });
        });
        
        // Validar o formulário antes do envio
        if (lancarNFForm) {
            lancarNFForm.addEventListener('submit', function(e) {
                const selectedCheckboxes = document.querySelectorAll('.servico-checkbox:checked');
                
                if (selectedCheckboxes.length === 0) {
                    e.preventDefault();
                    alert('Selecione pelo menos um serviço para lançar a NF.');
                    return false;
                }
                
                // Verificar se todos os serviços selecionados são do mesmo fornecedor
                const fornecedores = new Set();
                selectedCheckboxes.forEach(checkbox => {
                    const row = checkbox.closest('tr');
                    const fornecedor = row.querySelector('td:nth-child(3)').textContent.trim();
                    fornecedores.add(fornecedor);
                });
                
                if (fornecedores.size > 1) {
                    e.preventDefault();
                    alert('Todos os serviços selecionados devem ser do mesmo fornecedor.');
                    return false;
                }
                
                return true;
            });
        }
        
        // Inicializa o estado do botão
        checkButtonState();
    });
</script>