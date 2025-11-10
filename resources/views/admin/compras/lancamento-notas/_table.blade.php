<script>
    const spinnerHTML = `
        <div class="flex items-center justify-center py-10">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle  cx="12" cy="12" r="10"
                    stroke="currentColor" stroke-width="4"></circle>
                <path fill="currentColor"
                    d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="ml-3 text-gray-600 font-medium">Carregando...</span>
        </div>
    `;
</script>
<div class="results-table transition-opacity duration-300 opacity-100" id="tabela-compras">

    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>
                <x-forms.checkbox name="id_pedido_compras" class="select-all-checkbox" />
            </x-tables.head-cell>
            <x-tables.head-cell>Cód.Pedido</x-tables.head-cell>
            <x-tables.head-cell>N° O.S</x-tables.head-cell>
            <x-tables.head-cell>Vlr. Pedido</x-tables.head-cell>
            <x-tables.head-cell>Nome Fornecedor</x-tables.head-cell>
            <x-tables.head-cell>CNPJ</x-tables.head-cell>
            <x-tables.head-cell>Data Pedido</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Tipo Compra</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse($listaCompra as $itens)
            <x-tables.row>
                <x-tables.head-cell>
                    <x-forms.checkbox name="id_pedido_compras[]" value="{{ $itens->id_pedido_compras }}"
                        class="pedido-checkbox" />
                </x-tables.head-cell>
                <x-tables.cell>{{ $itens->id_pedido_compras}}</x-tables.cell>
                <x-tables.cell>{{ $itens->id_ordem_servico}}</x-tables.cell>
                <x-tables.cell>
                    R$ {{ number_format($itens->valor_total_desconto, 2, ',', '.') }}
                </x-tables.cell>
                <x-tables.cell>{{ $itens->fornecedor->nome_fornecedor}}</x-tables.cell>
                <x-tables.cell>{{ $itens->fornecedor->cnpj_fornecedor ?? $itens->fornecedor->cpf_fornecedor }}
                </x-tables.cell>
                <x-tables.cell nowrap>{{ format_date($itens->data_inclusao) }}</x-tables.cell>
                <x-tables.cell>{{ $itens->placa}}</x-tables.cell>
                <x-tables.cell>
                    <span class="px-2 py-1 rounded text-xs font-semibold
                    {{ $itens->tipo_compra === 'COMPRA DE SERVIÇOS' ? 'bg-blue-100 text-blue-800' :
                       ($itens->tipo_compra === 'Reforma Pneu' ? 'bg-green-100 text-green-800' :
                       ($itens->tipo_compra === 'COMPRA DE PRODUTOS' ? 'bg-purple-100 text-purple-800' :
                       ($itens->tipo_compra === 'Compras pela Ordem' ? 'bg-yellow-100 text-yellow-800' : ''))) }}">
                        {{ $itens->tipo_compra }}
                    </span>
                </x-tables.cell>
            </x-tables.row>
            @empty
            <x-tables.empty cols="8" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    @if($listaCompra->hasPages())
    <div class="mt-4">
        {{ $listaCompra->links() }}
    </div>
    @endif
</div>
<!-- REMOVA Alpine.js e use apenas isso no HTML -->
<div id="modalTransferencia"
    class="hidden fixed inset-0 z-40 bg-black/40 backdrop-blur-md flex items-center justify-center">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl p-6 relative">
        <h2 class="text-xl font-semibold mb-4">Lançamento de Nota</h2>

        <div id="conteudoModalTransferencia" class="overflow-y-auto max-h-[70vh]">
            <p class="text-gray-500">Carregando...</p>
        </div>

        <button onclick="fecharModalTransferencia()" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
            Fechar
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.querySelector('.select-all-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                // Seleciona todos os checkboxes de pedidos na página atual
                document.querySelectorAll('.pedido-checkbox').forEach(cb => {
                    cb.checked = selectAll.checked;
                });
            });
        }
    });
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