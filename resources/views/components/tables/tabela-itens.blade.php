<div id="{{ $componenteId }}" class="relative {{ $classesCss }}" x-data="tabelaItens({
        itens: {{ json_encode($itens) }},
        colunas: {{ json_encode($colunas) }},
        urlBusca: '{{ $urlBusca }}',
        urlSalvar: '{{ $urlSalvar }}',
        entidadeId: {{ $entidadeId ?? 'null' }},
        entidadeTipo: '{{ $entidadeTipo }}',
        podeSelecionarOrcamento: {{ $podeSelecionarOrcamento ?? 'false' }},
        permiteAdicionar: {{ $permiteAdicionar ? 'true' : 'false' }},
        permiteEditar: {{ $permiteEditar ? 'true' : 'false' }},
        permiteExcluir: {{ $permiteExcluir ? 'true' : 'false' }},
        tipoItem: '{{ $tipoItem }}',
        somenteVisualizacao: {{ $somenteVisualizacao ? 'true' : 'false' }},
        exibirTotal: {{ $exibirTotal ? 'true' : 'false' }},
        colunasTotal: {{ json_encode($colunasTotal) }}
    })">
    <!-- Resto do conteúdo do componente permanece igual -->
    <div class="overflow-hidden">
        <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-700">Itens</h3>
            <div class="flex items-center space-x-2">
                @if($permiteAdicionar && !$somenteVisualizacao)
                <button @click="adicionarItem()" type="button"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Adicionar
                </button>
                @endif
            </div>
        </div>

        <!-- Conteúdo da tabela e outros elementos continuam aqui... -->
    </div>
</div>