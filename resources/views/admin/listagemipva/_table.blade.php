<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Boleto</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Renavam</x-tables.head-cell>
            <x-tables.head-cell>Proprietario</x-tables.head-cell>
            <x-tables.head-cell>Tipo</x-tables.head-cell>
            <x-tables.head-cell>Uf</x-tables.head-cell>
            <x-tables.head-cell>Cota Unica sem desconto</x-tables.head-cell>
            <x-tables.head-cell>Cota Unica desconto</x-tables.head-cell>
            <x-tables.head-cell>Boleto cota unica vencimento</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($listagemIpvas as $index => $listagemIpva)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    @if ($listagemIpva->url_cota_unica)    
                    <button onclick="abrirModalPdf({{ $listagemIpva }})"
                        class="dropdown-button bg-white border px-2 py-1 rounded shadow flex items-center space-x-1">
                        <x-icons.pdf-doc class="w-4 h-4" />
                        <span>Imprimir Boleto</span>
                    </button>
                    @endif
                </x-tables.cell>

                <x-tables.cell>{{ $listagemIpva->placa }}</x-tables.cell>
                <x-tables.cell>{{ $listagemIpva->renavam }}</x-tables.cell>
                <x-tables.cell>{{ $listagemIpva->proprietario }}</x-tables.cell>
                <x-tables.cell>{{ $listagemIpva->tipo }}</x-tables.cell>
                <x-tables.cell>{{ $listagemIpva->uf }}</x-tables.cell>
                <x-tables.cell>R$ {{ number_format($listagemIpva->cota_unica_sem_desconto, 2, ',', '.') }}</x-tables.cell>
                <x-tables.cell>{{ $listagemIpva->cota_unica_desconto1 }}</x-tables.cell>
                <x-tables.cell>{{ $listagemIpva->boleto_cota_unica_vencimento }}</x-tables.cell>
                
                <div id="pdfModal" class="pdf-modal-overlay">
                    <div class="pdf-modal-container">
                        <!-- Header do Modal -->
                        <div class="pdf-modal-header">
                            <div class="pdf-modal-title">Documento Digital - Visualização de Impressão</div>
                            <div class="pdf-modal-controls">
                                <button class="close-btn" onclick="fecharModalPdf()">&times;</button>
                            </div>
                        </div>
    
                        <!-- Container do PDF -->
                        <div class="pdf-viewer-container">
                            <div class="pdf-loading" id="pdfLoading">
                                <div class="loading-spinner"></div>
                                Carregando documento...
                            </div>
                            <iframe 
                                id="pdfFrame" 
                                class="pdf-iframe"
                                style="display: none;"
                                onload="pdfCarregado()">
                            </iframe>
                        </div>
                    </div>
                </div>

            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $listagemIpvas->links() }}
    </div>
</div>