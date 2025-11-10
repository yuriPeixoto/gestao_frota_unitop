<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>CRLV</x-tables.head-cell>
            <x-tables.head-cell>Boleto</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Tipo</x-tables.head-cell>
            <x-tables.head-cell>Uf</x-tables.head-cell>
            <x-tables.head-cell>Mes</x-tables.head-cell>
            <x-tables.head-cell>Ano</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Valor</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($licenciamentos as $index => $licenciamento)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    @if ($licenciamento->url)    
                    <button onclick="abrirModalPdf({{ $licenciamento }}, 1)"
                        class="dropdown-button bg-white border px-2 py-1 rounded shadow flex items-center space-x-1">
                        <x-icons.print class="w-4 h-4" />
                        <span>Certificado</span>
                    </button>
                    @endif
                </x-tables.cell>
                <x-tables.cell>
                    @if ($licenciamento->guia)    
                    <button onclick="abrirModalPdf({{ $licenciamento }}, 2)"
                        class="dropdown-button bg-white border px-2 py-1 rounded shadow flex items-center space-x-1">
                        <x-icons.pdf-doc class="w-4 h-4" />
                        <span>Guia Pagamento</span>
                    </button>
                    @endif
                </x-tables.cell>

                <x-tables.cell>{{ $licenciamento->placa }}</x-tables.cell>
                <x-tables.cell>{{ $licenciamento->tipo }}</x-tables.cell>
                <x-tables.cell>{{ $licenciamento->uf }}</x-tables.cell>
                <x-tables.cell>{{ $licenciamento->mes }}</x-tables.cell>
                <x-tables.cell>{{ $licenciamento->ano }}</x-tables.cell>
                <x-tables.cell>{{ $licenciamento->status }}</x-tables.cell>
                <x-tables.cell>{{ $licenciamento->valor }}</x-tables.cell>
                
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
        {{ $licenciamentos->links() }}
    </div>
</div>