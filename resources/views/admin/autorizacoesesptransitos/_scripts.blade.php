<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('valor_certificado');

        input.addEventListener('input', function(e) {
            // Obter apenas os dígitos do valor atual
            let valor = e.target.value.replace(/\D/g, '');

            // Converter para número com formatação de centavos
            let valorNumerico = parseFloat(valor) / 100;

            // Se tiver dígitos, formatar como moeda
            if (valor !== '') {
                e.target.value = valorNumerico.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                    minimumFractionDigits: 2
                });
            } else {
                e.target.value = '';
            }
        });

        // Se já tiver um valor inicial, formatar corretamente
        if (input.value) {
            // Para valores iniciais não dividimos por 100 novamente
            let valorInicial = input.value.replace(/[^\d.,]/g, '');
            valorInicial = valorInicial.replace(',', '.');
            let valorNumericoInicial = parseFloat(valorInicial);

            if (!isNaN(valorNumericoInicial)) {
                input.value = valorNumericoInicial.toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL',
                    minimumFractionDigits: 2
                });
            }
        }

        // Definir função callback para atualização de dados do veículo
        window.atualizarDadosVeiculoCallback = function(idVeiculo) {
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
        };

        // Inicializar com valor atual, se existir
        const idVeiculo = document.querySelector('[name="id_veiculo"]')?.value;
        if (idVeiculo) {
            window.atualizarDadosVeiculoCallback(idVeiculo);
        }

        // Escutar eventos do smart-select
        window.addEventListener('id_veiculo:selected', function(e) {
            if (e.detail && e.detail.value) {
                window.atualizarDadosVeiculoCallback(e.detail.value);
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tableLoading = document.getElementById('table-loading');
        const resultsTable = document.getElementById('results-table');

        // Função para verificar se a tabela está completamente carregada
        function checkTableReady() {
            if (document.querySelectorAll('#results-table table tbody tr').length > 0) {
                // Esconde o loading e mostra a tabela com uma pequena transição
                setTimeout(function() {
                    tableLoading.style.opacity = '0';
                    resultsTable.classList.remove('opacity-0');

                    // Remove completamente o loading após a transição
                    setTimeout(function() {
                        tableLoading.style.display = 'none';
                    }, 300);
                }, 300);
            } else {
                // Tenta novamente em 100ms se ainda não estiver pronto
                setTimeout(checkTableReady, 100);
            }
        }

        // Inicia a verificação
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
    });
</script>
