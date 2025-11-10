<script>
    // Cria o container caso não exista
    // Cria o container centralizado caso não exista
    function showFeedback(type, message, duration = 4000) {
        const colors = {
            success: 'bg-green-200 text-green-800',
            error: 'bg-red-200 text-red-800',
            warning: 'bg-yellow-200 text-yellow-800',
            info: 'bg-blue-200 text-blue-800'
        };

        const icons = {
            success: '✔️',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const color = colors[type] || 'bg-gray-200 text-gray-800';
        const icon = icons[type] || '';

        const toast = document.createElement('div');
        toast.className = `
            flex items-center space-x-2 max-w-md w-full px-5 py-3 rounded-lg shadow-md pointer-events-auto
            transform transition-all duration-300 opacity-0 -translate-y-8 ${color}
        `;
        toast.innerHTML = `
            <span class="text-lg">${icon}</span>
            <span>${message}</span>
            <div class="h-1 w-full bg-white bg-opacity-50 rounded-full mt-2 overflow-hidden">
                <div class="h-1 bg-white rounded-full" style="width:100%; transition: width ${duration}ms linear;"></div>
            </div>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Force reflow para ativar transição
        requestAnimationFrame(() => {
            toast.classList.remove('opacity-0', '-translate-y-8');
            toast.classList.add('opacity-100', 'translate-y-0');
        });

        // Inicia a animação da barra
        const progress = toast.querySelector('div > div');
        requestAnimationFrame(() => {
            progress.style.width = '0%';
        });

        // Remove toast após duration
        setTimeout(() => {
            toast.classList.add('opacity-0', '-translate-y-8');
            toast.addEventListener('transitionend', () => toast.remove());
        }, duration);
    }

    function showConfirm(message, callbackYes) {
        const modal = document.getElementById('confirm-modal');
        const msg = document.getElementById('confirm-message');
        const btnYes = document.getElementById('confirm-yes');
        const btnNo = document.getElementById('confirm-no');

        msg.textContent = message;
        modal.classList.remove('hidden');

        // Remove listeners anteriores para não acumular
        btnYes.replaceWith(btnYes.cloneNode(true));
        btnNo.replaceWith(btnNo.cloneNode(true));

        const newYes = document.getElementById('confirm-yes');
        const newNo = document.getElementById('confirm-no');

        newYes.addEventListener('click', () => {
            modal.classList.add('hidden');
            callbackYes();
        });

        newNo.addEventListener('click', () => {
            modal.classList.add('hidden');
        });
    }

    function enviarManutencao() {
        // Pegar todos os checkboxes marcados
        const checkedBoxes = Array.from(document.querySelectorAll('.pedido-checkbox:checked'));
        const selecionados = checkedBoxes.map(chk => chk.value);

        // Validar destinação solicitada: não permitir enviar para manutenção se a destinacao contém 'estoque'
        const invalid = checkedBoxes.some(chk => {
            const dest = (chk.getAttribute('data-destinacao') || '').toLowerCase();
            return dest.includes('estoque') || dest.includes('stock');
        });

        if (invalid) {
            showFeedback('error',
                'Não é possível enviar para manutenção: pelo menos um dos pneus selecionados foi solicitado para envio ao Estoque.'
                );
            return;
        }

        if (selecionados.length === 0) {
            showFeedback('error', 'Selecione pelo menos um pneu para enviar a Manutenção.');
            return;
        }
        showConfirm(`Tem certeza que deseja enviar ${selecionados.length} pneu(s) para manutenção?`, () => {
            fetch(`/admin/pneusdeposito/manutencao`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        pneus: selecionados
                    })
                })
                .then(async response => {
                    const data = await response.json().catch(() => null);
                    if (!response.ok) {
                        throw new Error(data?.message || 'Erro ao enviar manutenção');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showFeedback('success', data.message);
                        window.location.reload();
                    } else {
                        showFeedback('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro na manutenção:', error);
                    showFeedback('error', error.message || 'Falha ao enviar manutenção');
                });
        });
    }


    function enviarEstoque() {
        // Pegar todos os checkboxes marcados
        const checkedBoxes = Array.from(document.querySelectorAll('.pedido-checkbox:checked'));
        const selecionados = checkedBoxes.map(chk => chk.value);

        // Validar destinação solicitada: não permitir enviar para estoque se a destinacao contém 'manuten' (manutenção)
        const invalid = checkedBoxes.some(chk => {
            const dest = (chk.getAttribute('data-destinacao') || '').toLowerCase();
            return dest.includes('manuten') || dest.includes('manutenção') || dest.includes('manutencao');
        });

        if (invalid) {
            showFeedback('error',
                'Não é possível enviar para o Estoque: pelo menos um dos pneus selecionados foi solicitado para envio à Manutenção.'
                );
            return;
        }

        if (selecionados.length === 0) {
            showFeedback('error', 'Selecione pelo menos um pneu para enviar ao Estoque.');
            return;
        }
        showConfirm(`Tem certeza que deseja enviar ${selecionados.length} pneu(s) para estoque?`, () => {
            fetch(`/admin/pneusdeposito/estoque`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        pneus: selecionados
                    })
                })
                .then(async response => {
                    const data = await response.json().catch(() => null);
                    if (!response.ok) {
                        throw new Error(data?.message || 'Erro ao enviar estoque');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showFeedback('success', data.message);
                        window.location.reload();
                    } else {
                        showFeedback('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro na estoque:', error);
                    showFeedback('error', error.message || 'Falha ao enviar estoque');
                });
        });

    }

    function enviarDescarte() {
        // Pegar todos os checkboxes marcados
        const selecionados = Array.from(document.querySelectorAll('.pedido-checkbox:checked'))
            .map(chk => chk.value);

        if (selecionados.length === 0) {
            showFeedback('error', 'Selecione pelo menos um pneu para enviar ao Descarte.');
            return;
        }
        showConfirm(`Tem certeza que deseja enviar ${selecionados.length} pneu(s) para descarte?`, () => {
            fetch(`/admin/pneusdeposito/descarte`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        pneus: selecionados
                    })
                })
                .then(async response => {
                    const data = await response.json().catch(() => null);
                    if (!response.ok) {
                        throw new Error(data?.message || 'Erro ao enviar ao descarte');
                    }
                    return data;
                })
                .then(data => {
                    if (data.success) {
                        showFeedback('success', data.message);
                        window.location.reload();
                    } else {
                        showFeedback('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro na ao descarte:', error);
                    showFeedback('error', error.message || 'Falha ao enviar ao descarte');
                });
        });
    }
</script>
