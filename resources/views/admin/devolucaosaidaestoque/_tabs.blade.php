<div class="mt-6">
    <div class="mx-auto">
        <!-- Botões das abas -->
        <div class="flex space-x-1">
            <button type="button"
                class="tablink py-1 px-3 text-md bg-white rounded-t-lg text-black hover:bg-blue-200 hover:text-black transition-colors"
                onclick="openTab(event, 'Aba1')">
                {{-- <x-icons.square-3-stack-3d class="inline-block w-4 h-4 mr-1" /> --}}
                Devolução de Produtos O.S.
            </button>
            <button type="button"
                class="tablink py-1 px-3 text-md bg-white rounded-t-lg text-black hover:bg-blue-200 hover:text-black transition-colors"
                onclick="openTab(event, 'Aba2')">
                {{-- <x-icons.square-3-stack-3d class="inline-block w-4 h-4 mr-1" /> --}}
                Devolução de Materiais
            </button>
        </div>
    </div>

    <!-- Conteúdo das abas -->
    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">

        @include('admin.devolucaosaidaestoque._search-form_devolucao_prod')
        @include('admin.devolucaosaidaestoque._table_devolucao_prod')

    </div>

    <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">

        @include('admin.devolucaosaidaestoque._search-form_devolucao_mats')
        @include('admin.devolucaosaidaestoque._table_devolucao_mats')

    </div>
</div>
