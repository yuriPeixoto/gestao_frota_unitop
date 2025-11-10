<div class="space-y-6">
    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif

    <h3 class="text-lg font-medium text-gray-900 mb-4">Produto para estoque</h3>

    <!-- Cabeçalho -->
    <div class="mx-auto">
        <!-- Botões das abas -->
        <div class="flex space-x-1">
            <button type="button"
                class="tablink py-1 px-3 text-sm bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                onclick="openTab(event, 'Aba1')">
                Dados do Produto
            </button>
            <button type="button"
                class="tablink py-1 px-3 text-sm bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                onclick="openTab(event, 'Aba2')">
                Aplicação
            </button>
        </div>
    </div>

    <!-- Conteúdo das abas -->
    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
        @include('admin.cadastroprodutosestoque._dados_produto')
    </div>

    <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
        @include('admin.cadastroprodutosestoque._aplicacao')
    </div>


    <!-- Botões -->
    <div class="flex justify-end space-x-3 col-span-full">
        <a href="{{ route('admin.cadastroprodutosestoque.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>
        <button type="submit" id="submit-form"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($cadastroProdutos) ? 'Atualizar' : 'Salvar' }}
        </button>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/estoque/produto/cadastro-produto-estoque.js') }}"></script>
    @include('admin.cadastroprodutosestoque._scripts')
@endpush
