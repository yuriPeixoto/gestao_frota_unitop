<script>
    // Função para lidar com a abertura sequencial de dropdowns
    document.addEventListener('DOMContentLoaded', function() {
        // Adiciona evento para fechar todos os outros dropdowns quando um é aberto
        document.addEventListener('click', function(e) {
            const target = e.target;
            const isDropdownButton = target.matches('[id$="-button"]') ||
                target.closest('[id$="-button"]');

            if (!isDropdownButton) return;

            // Coleta todos os botões de dropdown exceto o clicado
            const buttonId = target.id || target.closest('[id$="-button"]').id;
            const allDropdownButtons = document.querySelectorAll('[id$="-button"]');

            // Fecha os outros dropdowns
            allDropdownButtons.forEach(button => {
                if (button.id !== buttonId) {
                    const componentId = button.id.replace('-button', '');
                    const component = document.getElementById(componentId);

                    if (component && component.__x) {
                        component.__x.$data.open = false;
                    }
                }
            });
        });
    });
</script>

<script>
    let usuarioIdParaExcluir = null;

    function confirmarExclusao(id, nome) {
        usuarioIdParaExcluir = id;
        document.getElementById('nome-usuario').textContent = nome;
        document.getElementById('modal-exclusao').classList.remove('hidden');
    }

    function fecharModal() {
        document.getElementById('modal-exclusao').classList.add('hidden');
        usuarioIdParaExcluir = null;
    }

    function excluirUsuario() {
        if (!usuarioIdParaExcluir) return;

        fetch(`{{ route('admin.usuarios.destroy', '') }}/${usuarioIdParaExcluir}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                fecharModal();

                // Exibir notificação
                if (data.notification) {
                    alert(data.notification.message);
                }

                // Recarregar a página
                window.location.reload();
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao excluir usuário.');
                fecharModal();
            });
    }

    // Gerenciar o carregamento da tabela
    document.addEventListener('DOMContentLoaded', function() {
        const searchForm = document.querySelector('form');
        const tableLoading = document.getElementById('table-loading');
        const resultsTable = document.getElementById('results-table');

        if (searchForm) {
            searchForm.addEventListener('submit', function() {
                tableLoading.style.display = 'flex';
                resultsTable.style.opacity = '0.3';
            });
        }

        // Se estiver usando HTMX, interceptar os eventos
        document.body.addEventListener('htmx:beforeRequest', function(evt) {
            if (evt.detail.target.id === 'results-table') {
                tableLoading.style.display = 'flex';
                resultsTable.style.opacity = '0.3';
            }
        });

        document.body.addEventListener('htmx:afterRequest', function(evt) {
            if (evt.detail.target.id === 'results-table') {
                setTimeout(function() {
                    tableLoading.style.display = 'none';
                    resultsTable.style.opacity = '1';
                }, 300);
            }
        });
    });
</script>

<script>
    function cloneUser(id) {
        if (confirm('Tem certeza que deseja clonar esse usuário?')) {
            const url = `usuarios/${id}/clone`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na resposta do servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    alert(data.message || 'Usuário clonado com sucesso!');
                    if (!data.error) {
                        window.location.href = "{{ route('admin.usuarios.index') }}";
                    }
                })
                .catch(error => {
                    console.error('Erro ao clonar o usuário:', error);
                    alert('Ocorreu um erro ao clonar o usuário. Por favor, tente novamente.');
                });
        }
    }
</script>
