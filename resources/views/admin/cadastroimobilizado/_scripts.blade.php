<script>
    // ======================================================
    // INICIALIZAÇÃO GERAL DO DOCUMENTO
    // ======================================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado - Inicializando scripts');
        
        // Inicializar manipulação da tabela
         initTableLoading();
    }); 
    

    // ======================================================
    // GESTÃO DE LOADING DA TABELA DE RESULTADOS
    // ======================================================
    function initTableLoading() {
        const loadingElement = document.getElementById('table-loading');
        const resultsElement = document.getElementById('results-table');
        
        if (loadingElement && resultsElement) {
            console.log('Elementos de loading/resultado encontrados');
            
            // Esconder o loading e mostrar os resultados após carregamento da página
            setTimeout(function() {
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
                console.log('Tabela de resultados exibida');
            }, 300);
            
            // Lidar com eventos HTMX
            document.body.addEventListener('htmx:beforeRequest', function(event) {
                if (event.detail.target && 
                    (event.detail.target.id === 'results-table' || 
                     event.detail.target.closest('#results-table'))) {
                    console.log('HTMX request iniciada - mostrando loading');
                    loadingElement.style.display = 'flex';
                    resultsElement.classList.add('opacity-0');
                }
            });
            
            document.body.addEventListener('htmx:afterSwap', function(event) {
                if (event.detail.target && 
                    (event.detail.target.id === 'results-table' || 
                     event.detail.target.closest('#results-table'))) {
                    console.log('HTMX swap concluído - escondendo loading');
                    loadingElement.style.display = 'none';
                    resultsElement.classList.remove('opacity-0');
                    resultsElement.classList.add('opacity-100');
                }
            });
            
            // Backup em caso de falha no HTMX
            document.body.addEventListener('htmx:responseError', function(event) {
                console.log('HTMX erro - escondendo loading');
                loadingElement.style.display = 'none';
                resultsElement.classList.remove('opacity-0');
                resultsElement.classList.add('opacity-100');
            });
        } else {
            console.log('Elementos de loading/resultado não encontrados');
        }
    }
    
    function openTab(evt, tabName) {
        // Esconde todos os conteúdos das abas
        const tabcontents = document.querySelectorAll(".tabcontent");
        tabcontents.forEach((tab) => {
            tab.classList.add("hidden");
        });

        // Remove a classe "active" de todos os botões
        const tablinks = document.querySelectorAll(".tablink");
        tablinks.forEach((link) => {
            link.classList.remove("bg-blue-500", "text-white");
            link.classList.add("bg-gray-200", "text-gray-700");
        });

        // Mostra o conteúdo da aba atual e adiciona a classe "active" ao botão
        document.getElementById(tabName).classList.remove("hidden");
        evt.currentTarget.classList.remove("bg-gray-200", "text-gray-700");
        evt.currentTarget.classList.add("bg-blue-500", "text-white");
    }

    // Mostra a primeira aba por padrão
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelector(".tablink").click();
    });

</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const currencyFirstLoad = document.querySelectorAll('.monetario');
        currencyFirstLoad.forEach(input => {
            if (input.value.trim() !== '') {
                let valor = parseFloat(input.value); // Converte o valor diretamente para número

                // Formata o valor para BRL
                input.value = new Intl.NumberFormat('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(valor);
            }
        });
    });

    const currencyList = document.querySelectorAll('.monetario');
    const currencyInputs = Array.from(currencyList);

    currencyInputs.forEach(input => {
        input.addEventListener('input', () => {
            // Remove o formato de moeda para manipular o valor
            let valor = input.value.replace(/[^\d-]/g, ''); // Mantém apenas números e o sinal de menos

            // Verifica se o valor é negativo
            const isNegative = valor.startsWith('-');

            // Remove o sinal de menos para o cálculo, se presente
            valor = valor.replace('-', '');

            // Ajusta os centavos
            valor = (parseInt(valor || '0', 10) / 100).toFixed(2);

            // Adiciona o sinal de menos de volta, se for o caso
            if (isNegative) {
                valor = '-' + valor;
            }

            // Formata o valor para BRL
            input.value = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(valor);
        });
    });

    currencyInputs.forEach(input => {
        input.addEventListener('change', () => {
            // Remove o formato de moeda para manipular o valor
            let valor = input.value.replace(/[^\d-]/g, ''); // Mantém apenas números e o sinal de menos

            // Verifica se o valor é negativo
            const isNegative = valor.startsWith('-');

            // Remove o sinal de menos para o cálculo, se presente
            valor = valor.replace('-', '');

            // Ajusta os centavos
            valor = (parseInt(valor || '0', 10) / 100).toFixed(2);

            // Adiciona o sinal de menos de volta, se for o caso
            if (isNegative) {
                valor = '-' + valor;
            }

            // Formata o valor para BRL
            input.value = new Intl.NumberFormat('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            }).format(valor);
        });
    });

</script>

<script>
    // exclusao da Manutenção
    function destroyCadastroImobilizado(id) {
        showModal('delete-autorizacao');
        autorizacaooId = id;
        domEl('.bw-delete-autorizacao .title').innerText = id;
    }

    function excluirCadastroImobilizado(id) {
        fetch(`{{ route('admin.cadastroimobilizado.destroy', ':id') }}`.replace(':id', autorizacaooId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (!response.ok) {
                return response.text().then(errorText => {
                    console.error('Error response text:', errorText);
                    throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
                });
            }
            return response.json();
        }).then(data => {
            if (data.notification) {
                showNotification(
                    data.notification.title,
                    data.notification.message,
                    data.notification.type
                );

                setTimeout(() => {
                    window.location.reload();
                }, 500);
            }
        }).catch(error => {
            console.error('Full error:', error);

            showNotification(
                'Erro',
                error.message,
                'error'
            );
        });
    }

    @if(session('notification') && is_array(session('notification')))
        showNotification('{{ session('notification')['title'] }}', '{{ session('notification')['message'] }}', '{{ session('notification')['type'] }}');
    @endif
</script>