<div class="mt-6">
    <div class="mx-auto">
        @if (session('notification'))
            <x-notification :notification="session('notification')" />
        @endif
        <!-- Botões das abas -->
        <div class="flex space-x-1">
            <button type="button"
                class="tablink py-1 px-3 text-md bg-white rounded-t-lg text-black hover:bg-blue-200 hover:text-black transition-colors"
                onclick="openTab(event, 'Aba1')">
                {{-- <x-icons.square-3-stack-3d class="inline-block w-4 h-4 mr-1" /> --}}
                Devolução Transferência Direta Estoque
            </button>
            <button type="button"
                class="tablink py-1 px-3 text-md bg-white rounded-t-lg text-black hover:bg-blue-200 hover:text-black transition-colors"
                onclick="openTab(event, 'Aba2')">
                {{-- <x-icons.square-3-stack-3d class="inline-block w-4 h-4 mr-1" /> --}}
                Devolução Requisição de Peças
            </button>
            <button type="button"
                class="tablink py-1 px-3 text-md bg-white rounded-t-lg text-black hover:bg-blue-200 hover:text-black transition-colors"
                onclick="openTab(event, 'Aba3')">
                {{-- <x-icons.square-3-stack-3d class="inline-block w-4 h-4 mr-1" /> --}}
                Devolução de Materiais para Matriz
            </button>
        </div>
    </div>

    <!-- Conteúdo das abas -->
    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">

        @include('admin.devolucoes._search-form_devTransfDireta')
        @include('admin.devolucoes._table_devTransfDireta')

    </div>

    <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">

        @include('admin.devolucoes._search-form_devRequisicaoPecas')
        @include('admin.devolucoes._table_devRequisicaoPecas')

    </div>

    <div id="Aba3" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">

        @include('admin.devolucoes._search-form_devMatsMatriz')
        @include('admin.devolucoes._table_devMatsMatriz')

    </div>
</div>
