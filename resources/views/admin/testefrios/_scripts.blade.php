<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableLoading = document.getElementById('table-loading');
        const resultsTable = document.getElementById('results-table');

        function formatarMoeda(input) {
            let valor = input.target.value.replace(/\D/g, '');

            // Converte para centavos
            valor = (parseInt(valor) / 100).toFixed(2);

            // Formata como moeda brasileira
            this.value = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(valor);
        }

        document.getElementById('valor_certificado').addEventListener('input', formatarMoeda);

        document.addEventListener('DOMContentLoaded', () => {
            formatarMoeda(document.getElementById('valor_certificado'));
        });

        // Função para verificar se a tabela está completamente carregada
        function checkTableReady() {
            if (document.querySelectorAll('#results-table table tbody tr').length > 0) {
                // Esconde o loading e mostra a tabela com uma pequena transição
                setTimeout(function() {
                    if (tableLoading) {
                        tableLoading.style.opacity = '0';
                        if (resultsTable) resultsTable.classList.remove('opacity-0');

                        // Remove completamente o loading após a transição
                        setTimeout(function() {
                            if (tableLoading) tableLoading.style.display = 'none';
                        }, 300);
                    }
                }, 300);
            } else {
                // Tenta novamente em 100ms se ainda não estiver pronto
                setTimeout(checkTableReady, 100);
            }
        }

        // Inicia a verificação se o elemento existir
        if (tableLoading && resultsTable) {
            setTimeout(checkTableReady, 500);

            // Mostra loading quando o formulário de busca for submetido
            const searchForm = document.querySelector('form');
            if (searchForm) {
                searchForm.addEventListener('submit', function() {
                    tableLoading.style.display = 'flex';
                    tableLoading.style.opacity = '1';
                    resultsTable.classList.add('opacity-0');
                });
            }

            // Se estiver usando HTMX, intercepta os eventos
            document.body.addEventListener('htmx:beforeRequest', function(evt) {
                if (evt.detail.target.id === 'results-table') {
                    tableLoading.style.display = 'flex';
                    tableLoading.style.opacity = '1';
                    resultsTable.classList.add('opacity-0');
                }
            });

            document.body.addEventListener('htmx:afterRequest', function(evt) {
                if (evt.detail.target.id === 'results-table') {
                    setTimeout(function() {
                        tableLoading.style.opacity = '0';
                        resultsTable.classList.remove('opacity-0');

                        setTimeout(function() {
                            tableLoading.style.display = 'none';
                        }, 300);
                    }, 300);
                }
            });
        }
    });

    function confirmarExclusao(id) {
        if (confirm('Tem certeza que deseja desativar este teste de frio?')) {
            excluirTesteFrio(id);
        }
    }

    function excluirTesteFrio(id) {
        fetch(`/admin/testefrios/${id}`, {
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
                    alert('Teste de Frio desativado com sucesso');
                    window.location.reload();
                } else {
                    alert(data.message || 'Erro ao desativado teste de frio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao desativado teste de frio');
            });
    }
</script>

<!-- Novo script para manipulação dos dados de veículo -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Obter referências aos elementos do DOM
        const veiculoSelect = document.querySelector('[name="id_veiculo"]');
        const chassiInput = document.getElementById('chassi');
        const renavamInput = document.getElementById('renavam');

        // Verificar se os elementos existem antes de prosseguir
        if (!veiculoSelect || !chassiInput || !renavamInput) {
            console.error('Elementos do form não encontrados', {
                veiculoSelect: !!veiculoSelect,
                chassiInput: !!chassiInput,
                renavamInput: !!renavamInput
            });
            return; // Sair se algum elemento não for encontrado
        }

        // Log de diagnóstico

        // Função para atualizar os campos quando uma placa for selecionada
        function atualizarDadosVeiculo(idVeiculo, veiculo) {
            if (!idVeiculo) return;
            fetch('{{ route('admin.autorizacoesesptransitos.pega-renavam-data') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        placa: idVeiculo
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.error) {
                        document.getElementById('renavam').value = data.renavam;
                        document.getElementById('chassi').value = data.chassi;
                        document.getElementById('filial').value = data.filial;
                    } else {
                        console.error(data.error);
                    }
                })
                .catch(error => {
                    console.error('Erro ao buscar dados do veículo:', error);
                });
        }

        // Adicionar o callback à window para que o smart-select possa acessá-lo
        window.atualizarDadosVeiculo = atualizarDadosVeiculo;

        // Escutar eventos personalizados do componente smart-select
        window.addEventListener('id_veiculo:selected', function(event) {
            atualizarDadosVeiculo(event.detail.value, event.detail.object);
        });

        // Se um veículo já estiver selecionado ao carregar a página (no caso de edição),
        // buscar seus dados para preencher os campos
        if (veiculoSelect.value) {
            atualizarDadosVeiculo(veiculoSelect.value);
        }
    });
</script>

<script>
    function testefrioDados() {
        const component = {
            chassi: '{{ $testefrio->chassi ?? '' }}',
            renavam: '{{ $testefrio->renavam ?? '' }}',
        };

        return component;
    }
</script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const hiddenInput = document.querySelector('[name="id_veiculo"]');
        const chassiInput = document.getElementById('chassi');
        const filialInput = document.getElementById('renavam');


        // Supondo que o blade esteja gerando isso corretamente
        const veiculosFrequentes = @json($veiculosFrequentes);

        function atualizarChassi() {
            const idVeiculo = hiddenInput.value;
            const veiculoSelecionado = veiculosFrequentes.find(v => v.value == idVeiculo);

            chassiInput.value = veiculoSelecionado ? (veiculoSelecionado.chassi || 'Não encontrado') : '';
        }

        function atualizarRenavam() {
            const idVeiculo = hiddenInput.value;
            const veiculoSelecionado = veiculosFrequentes.find(v => v.value == idVeiculo);

            filialInput.value = veiculoSelecionado ? (veiculoSelecionado.renavam || 'Não encontrado') : '';
        }

        function atualizarTodosCampos() {
            atualizarChassi();
            atualizarRenavam();
        }

        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'value') {
                    atualizarTodosCampos();
                }
            });
        });

        if (hiddenInput) {
            observer.observe(hiddenInput, {
                attributes: true,
                attributeFilter: ['value']
            });

            // 🟢 Aqui ele vai preencher chassi e renavam se id_veiculo já tiver valor
            atualizarTodosCampos();
        } else {
            console.error('Elemento hidden input não encontrado');
        }

        hiddenInput?.addEventListener('change', atualizarTodosCampos);
    });
</script>
