// Máscara para valor monetário (R$)
document.addEventListener('DOMContentLoaded', () => {
    const maskedInputs = document.querySelectorAll('input[data-mask="valor"]');
    if (maskedInputs.length > 0) {
        maskedInputs.forEach(input => {
            input.addEventListener('input', (event) => {
                let value = input.value.replace(/\./g, '').replace(',', '');
                const cursorPosition = input.selectionStart;

                if (event.inputType === 'insertText' && event.data === ',') {
                    value = value.slice(0, cursorPosition - 1) + ',' + value.slice(cursorPosition - 1);
                } else {
                    value = (parseInt(value || '0', 10) / 100).toFixed(2).replace('.', ',');
                }

                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                input.value = value;

                // Ajusta a posição do cursor
                if (event.inputType === 'insertText' && event.data === ',') {
                    input.setSelectionRange(cursorPosition, cursorPosition);
                }
            });
        });
    }
});


// Exemplo de uso do SweetAlert2 para confirmação de exclusão
document.addEventListener('DOMContentLoaded', () => {
    const deleteForms = document.querySelectorAll('form[data-confirm-delete]');

    deleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault(); // Impede o envio do formulário imediatamente

            Swal.fire({
                title: 'Tem certeza?',
                text: 'Você não poderá reverter esta ação!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit(); // Envia o formulário se confirmado
                }
            });
        });
    });
});

// Exemplo de notificação de sucesso usando SweetAlert2
window.showSuccessNotification = (message) => {
    Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: message,
        timer: 3000,
        showConfirmButton: false,
    });
};

// Exemplo de notificação de erro usando SweetAlert2
window.showErrorNotification = (message) => {
    Swal.fire({
        icon: 'error',
        title: 'Erro!',
        text: message,
        timer: 3000,
        showConfirmButton: false,
    });
};
