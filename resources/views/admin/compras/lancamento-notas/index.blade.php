<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Listagem de Notas de Compras') }}
            </h2>
            <div class="flex items-center space-x-4">

                <div class="relative inline-block text-left" x-data="{ helpOpen: false }">
                    <button @click="helpOpen = !helpOpen" type="button"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>

                    <div x-show="helpOpen" @click.away="helpOpen = false"
                        class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                        <div class="py-1">
                            <div class="px-4 py-2">
                                <p class="text-sm leading-5 font-medium text-gray-900 truncate">
                                    Ajuda - Notas Fiscais Lançadas
                                </p>
                                <p class="mt-1 text-xs leading-5 text-gray-500">
                                    Esta tela exibe os registros de calibragens realizadas. Use os filtros abaixo para
                                    refinar sua busca.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <!-- Search Form -->
                @include('admin.compras.lancamento-notas._search-form')
                </br>

                <div>
                    <div class="flex space-x-2">
                        <button type="button" onclick="abrirSelecionados()"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.check class="h-4 w-4 mr-2" />
                            Confirmar
                        </button>
                    </div>
                </div>
                </br>
                <div class="relative">


                    <!-- Bloco de checkboxes alinhado à direita -->
                    <div class="absolute top-0 right-0 flex flex-col gap-2 items-end z-10 -mt-40 w-64">
                        <label
                            class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg bg-blue-100 w-full">
                            <x-forms.checkbox name="tipo_compra" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                                value="COMPRA DE SERVIÇOS" />
                            <span class="text-blue-800 font-medium">Compra de Serviços</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg bg-green-100 w-full">
                            <x-forms.checkbox name="tipo_compra" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                                value="Reforma Pneu" />
                            <span class="text-green-800 font-medium">Reforma Pneu</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 cursor-pointer px-4 py-2 border rounded-lg bg-purple-100 w-full">
                            <x-forms.checkbox name="tipo_compra" class="tipo-compra-checkbox h-5 w-5 text-indigo-600"
                                value="COMPRA DE PRODUTOS" />
                            <span class="text-purple-800 font-medium">Compra de Produtos</span>
                        </label>
                    </div>

                </div>


                <div class="mt-6 overflow-x-auto" id="results-table">
                    <div class="mt-6 overflow-x-auto">
                        @include('admin.compras.lancamento-notas._table')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
    function abrirSelecionados() {
        // pega todos os checkboxes com a classe "pedido-checkbox" que estão marcados
        const selecionados = document.querySelectorAll('.pedido-checkbox:checked');

        if (selecionados.length === 0) {
            alert('Selecione pelo menos um pedido para visualizar.');
            return;
        }

        if (selecionados.length > 1) {
            alert('Selecione apenas um pedido por vez.');
            return;
        }

        // se quiser abrir apenas o primeiro selecionado:
        const id = selecionados[0].value;
        abrirModalTransferencia(id);

        // 👉 se no futuro quiser abrir vários ids, pode concatenar e enviar para o backend
        // const ids = Array.from(selecionados).map(cb => cb.value);
        // abrirModalTransferencia(ids.join(','));
    }

    function abrirModalTransferencia(id) {
        const modal = document.getElementById('modalTransferencia');
        const conteudo = document.getElementById('conteudoModalTransferencia');

        modal.classList.remove('hidden');
        conteudo.innerHTML = '<p class="text-gray-500">Carregando...</p>';

        fetch(`/admin/compras/lancamento-notas/visualizar-modal/${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Erro ao carregar dados');
                return response.text();
            })
            .then(html => {
                conteudo.innerHTML = html;
            })
            .catch(() => {
                conteudo.innerHTML = '<p class="text-red-500">Erro ao carregar a transferência.</p>';
            });
    }

    function fecharModalTransferencia() {
        const modal = document.getElementById('modalTransferencia');
        modal.classList.add('hidden');
    }
</script>

<script>
    const checkboxes = document.querySelectorAll('.tipo-compra-checkbox');
    const tabela = document.getElementById('tabela-compras');

    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const tiposSelecionados = Array.from(checkboxes)
                .filter(c => c.checked)
                .map(c => c.value);

            // Mostrar spinner
            tabela.innerHTML = spinnerHTML;
            tabela.classList.add();

            fetch("{{ route('admin.compras.lancamento-notas.listacompra') }}?" + new URLSearchParams({
                tipo_compra: tiposSelecionados
            }), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(res => {
                    if (!res.ok) throw new Error('Erro na requisição');
                    return res.text();
                })
                .then(html => {
                    tabela.innerHTML = html;
                    tabela.classList.remove();
                    initCheckboxListeners();
                })
                .catch(err => {
                    console.error(err);
                    tabela.innerHTML = '<p class="text-red-500 text-center py-6">Erro ao carregar dados</p>';
                });
        });
    });


    function initCheckboxListeners() {
        const selectAll = document.querySelector('.select-all-checkbox');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    }

    // Inicializar listeners quando a página carregar
    document.addEventListener('DOMContentLoaded', initCheckboxListeners);
</script>

<script>
    function atualizarContagem() {
        const input = document.getElementById("chave_nf");
        const contador = document.getElementById("contador-chave");
        contador.textContent = input.value.length + " / 44 dígitos";
    }

    // inicializa ao carregar a página
    document.addEventListener("DOMContentLoaded", atualizarContagem);

    
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para formatar como moeda
        function formatarMoeda(valor) {
            // Remove tudo que não é número
            let numero = valor.replace(/\D/g, '');
            
            // Converte para float e divide por 100 para ter decimais
            numero = (numero / 100).toFixed(2);
            
            // Formata como moeda brasileira
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(numero);
        }

        // Aplica a formatação aos campos com a classe 'valor-moeda'
        document.querySelectorAll('.valor-moeda').forEach(function(campo) {
            // Formata o valor inicial
            if (campo.value && campo.value !== '0') {
                campo.value = formatarMoeda(campo.value.toString());
            } else {
                campo.value = 'R$ 0,00';
            }

            // Formata enquanto digita
            campo.addEventListener('input', function(e) {
                let valor = e.target.value;
                e.target.value = formatarMoeda(valor);
            });
        });
    });

</script>