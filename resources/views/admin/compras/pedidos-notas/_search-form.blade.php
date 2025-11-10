<form method="GET" action="{{ route('admin.compras.pedidos-notas.index') }}" class="space-y-4">
    {{-- Exibir mensagens de erro/confirmação --}}
    <x-ui.export-message />

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <x-forms.input name="id_pedido_compras" label="Cód. Pedido Compras:"
                value="{{ request('id_pedido_compras') }}" />
        </div>

        <div>
            <x-forms.smart-select name="id_fornecedor[]" label="Nome Fornecedor:"
                placeholder="Selecione o fornecedor..." :options="$fornecedoresFrequentes"
                :searchUrl="route('admin.api.fornecedores.search')" :selected="request('id_fornecedor')"
                :multiple="true" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select id="chave_nf" name="chave_nf[]" label="Chave NF"
                placeholder="Selecione a(s) chave(s)..." :options="$chaves" :multiple="true" asyncSearch="true"
                :searchUrl="route('admin.compras.api.chaves.search')" />
        </div>


        <div>
            <x-forms.smart-select id="numero_nf" name="numero_nf[]" label="Número NF"
                placeholder="Selecione a(s) nota(s) fiscal..." :options="$numeros" :multiple="true"
                :searchUrl="route('admin.compras.api.notasfiscais.search')" asyncSearch="true" />
        </div>
    </div>
    <!------------------------------------------------------------------------------------------------------------------->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <x-forms.input name="os" label="Ordem de Serviço" value="{{ request('os') }}" />
        </div>

        <div>
            <x-forms.smart-select name="placa" label="Placa:" placeholder="Selecione o fornecedor..." :options="$placas"
                :searchUrl="route('admin.api.veiculos.search')" :selected="request('placa')" asyncSearch="true" />
        </div>

        <div>
            <x-forms.smart-select name="filial" label="Filial" placaholder="Selecione a filial..." :options="$filial"
                value="{{ request('filial') }}" />
        </div>

        <div>
            <x-forms.select name="tipo_pedido" label="Tipo de Pedido" :options="$tiposPedido"
                :selected="request('tipo_pedido')" placeholder="Selecione o tipo..." />
        </div>
        <!------------------------------------------------------------------------------------------------------------------->



        <div>
            <x-forms.input name="id_pedido_geral" label="Pedido Geral" value="{{ request('id_pedido_geral') }}" />
        </div>
    </div>
    <div>
        {{-- Usar o novo componente de botões de exportação --}}
        <a href="{{ route('admin.compras.pedidos-notas.exportPdf', [
                'id_pedido_compras' => request('id_pedido_compras', []),
                'placa' => request('placa'),
                'chave_nf' => request('chave_nf')
            ]) }}" class=" inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded
            text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2
            focus:ring-indigo-500 export-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-red-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7v0a3 3 0 116 0v0" />
            </svg>
            PDF
        </a>

        {{-- Usar o novo componente de botões de exportação --}}
        <a href="{{ route('admin.compras.pedidos-notas.exportCsv', request()->only(['id_pedido_compras', 'placa', 'chave_nf'])) }}"
            class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 export-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            CSV
        </a>

        {{-- Usar o novo componente de botões de exportação --}}
        <a href="{{ route('admin.compras.pedidos-notas.exportXls', request()->only(['id_pedido_compras', 'placa', 'chave_nf'])) }}"
            class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 export-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-green-700" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            XLS
        </a>

        {{-- Usar o novo componente de botões de exportação {{ route('admin.compras.lancamento-notas.exportXml',
        request()->only(['id_pedido_compras', 'data_inclusao', 'data_final'])) }} --}}
        <a href="{{ route('admin.compras.pedidos-notas.exportXml', request()->only(['id_pedido_compras', 'placa', 'chave_nf'])) }}"
            class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 export-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
            </svg>
            XML
        </a>
    </div>
    <div class="flex justify-between mt-4">
        <div>
        </div>

        <div class="flex space-x-2">
            <a href="{{ route('admin.compras.pedidos-notas.index') }}"
                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-4 w-4 mr-2" />
                Limpar
            </a>

            <button type="submit"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                Buscar
            </button>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.export-btn').forEach(function(btn) {
        btn.addEventListener('click', function(event) {
            let selecionados = [];
            document.querySelectorAll('.pedido-checkbox:checked').forEach(cb => {
                selecionados.push(`id_pedido_compras[]=${encodeURIComponent(cb.value)}`);
            });

            if (selecionados.length === 0) {
                alert("Selecione ao menos um pedido.");
                event.preventDefault(); // Bloqueia clique
                return;
            }

            let baseUrl = this.getAttribute('href');
            let separador = baseUrl.includes('?') ? '&' : '?';

            this.setAttribute('href', `${baseUrl}${separador}${selecionados.join('&')}`);
        });
    });
</script>