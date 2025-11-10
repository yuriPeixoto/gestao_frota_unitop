<x-tables.table>
    <x-tables.header>
        <x-tables.head-cell>Cód. Pedido</x-tables.head-cell>
        <x-tables.head-cell>Cód. Entrada Reforma</x-tables.head-cell>
        <x-tables.head-cell>Fornecedor</x-tables.head-cell>
        <x-tables.head-cell>Valor Total</x-tables.head-cell>
        <x-tables.head-cell>Tipo Compra</x-tables.head-cell>
        <x-tables.head-cell>Data Inclusão</x-tables.head-cell>

    </x-tables.header>
    <x-tables.body>
        @forelse($reforma as $item)
        <x-tables.row>
            <x-tables.cell>{{ $item->id_pedido_compras}}</x-tables.cell>
            <x-tables.cell>{{ $item->id_ordem_servico}}</x-tables.cell>
            <x-tables.cell>{{ $item->fornecedor->nome_fornecedor ?? '-'}}</x-tables.cell>
            <x-tables.cell>{{ $item->valor_total_desconto}}</x-tables.cell>
            <x-tables.cell>{{ $item->tipo_compra}}</x-tables.cell>
            <x-tables.cell>{{ $item->data_inclusao}}</x-tables.cell>
        </x-tables.row>
        @empty
        <x-tables.empty cols="8" message="Nenhum registro encontrado" />
        @endforelse
    </x-tables.body>
</x-tables.table>
@if($bloqueado)
<div class="text-center py-8">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-red-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24"
        stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <h3 class="text-lg font-semibold text-red-600">Nota já lançada</h3>
    <p class="text-gray-500 mt-2">{{ $mensagem ?? 'Este pedido já possui nota fiscal vinculada.' }}</p>
</div>
@else
<form method="POST" action="{{ route('admin.compras.lancamento-notas.lancarnota', $reforma[0]->id_pedido_compras) }}">
    @csrf

    <!-- Adicione este campo hidden para o ID -->
    <input type="hidden" name="id_pedido_compras" value="{{ $reforma[0]->id_pedido_compras }}">

    <div class="flex w-full gap-3">
        <div class="w-full">
            <x-forms.input name="codigo" label="Cód:" value="{{ $reforma[0]->id_pedido_compras }}" readonly />
        </div>
        @if($reforma[0]->tipo_compra == 'COMPRA DE PRODUTOS')
        <div class="w-full">
            <x-forms.input name="chave_nf" label="Chave Acesso NF Envio" id="chave_nf" maxlength="44"
                oninput="atualizarContagem()" />

            <small id="contador-chave" class="text-green-500">
                0 / 44 dígitos
            </small>
        </div>
        @endif
    </div>

    <div class="flex w-full gap-3">
        <div class="w-full">
            <x-forms.input name="fornecedor_nome" label="Fornecedor"
                value="{{ $reforma[0]->fornecedor->nome_fornecedor }}" readonly />
            <input type="hidden" name="id_fornecedor" value="{{ $reforma[0]->id_fornecedor }}">
        </div>

        <div class="w-full">
            <x-forms.input name="numero_nf" label="Nº NF:" type="number" required />
        </div>

        <div class="w-full">
            <x-forms.input name="serie_nf" label="Serie:" maxlength="20" required />
        </div>
    </div>

    <div class="flex w-full gap-3">
        <div class="w-full">
            <x-forms.input type="date" name="data_emissao" label="Emissão:" required />
        </div>

        <div class="w-full">
            <x-forms.input input-class="valor-moeda" name="valor_total_nota" label="Valor Total NF:"
                value="R${{ number_format($reforma[0]->valor_total_desconto, 2,',','.') }}" readonly />
        </div>

        <div class=" w-full">
            <x-forms.input input-class="valor-moeda" name="valor_servico" label="Valor Serviço:"
                value="R${{ number_format($reforma[0]->valor_total_desconto, 2,',','.') }}" readonly />
        </div>
    </div>

    <button type=" submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg">
        Lançar Nota
    </button>
</form>
@endif
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