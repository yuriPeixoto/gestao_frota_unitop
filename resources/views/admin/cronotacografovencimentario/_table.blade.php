<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Certificado</x-tables.head-cell>
            <x-tables.head-cell>Guia Pagamento</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Renavam</x-tables.head-cell>
            <x-tables.head-cell>Vencimento</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Emissão</x-tables.head-cell>
            <x-tables.head-cell>Documento</x-tables.head-cell>
            <x-tables.head-cell>N° Documento</x-tables.head-cell>
            <x-tables.head-cell>Marca</x-tables.head-cell>
            <x-tables.head-cell>Modelo</x-tables.head-cell>
            <x-tables.head-cell>Serie</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($cronotacografoVencimentarios as $index => $cronotacografoVencimentario)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    @if ($cronotacografoVencimentario->certificado)    
                    <button onclick="abrirModalPdf({{ $cronotacografoVencimentario }}, 1)"
                        class="dropdown-button bg-white border px-2 py-1 rounded shadow flex items-center space-x-1">
                        <x-icons.print class="w-4 h-4" />
                        <span>Certificado</span>
                    </button>
                    @endif
                </x-tables.cell>
                <x-tables.cell>
                    @if ($cronotacografoVencimentario->gru_url)    
                    <button onclick="abrirModalPdf({{ $cronotacografoVencimentario }}, 2)"
                        class="dropdown-button bg-white border px-2 py-1 rounded shadow flex items-center space-x-1">
                        <x-icons.pdf-doc class="w-4 h-4" />
                        <span>Guia Pagamento</span>
                    </button>
                    @endif
                </x-tables.cell>

                <x-tables.cell>{{ $cronotacografoVencimentario->placa }}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->renavam }}</x-tables.cell>
                <x-tables.cell>{{ format_date($cronotacografoVencimentario->vencimento, 'd/m/Y') }}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->status }}</x-tables.cell>
                <x-tables.cell>{{ format_date($cronotacografoVencimentario->emissao, 'd/m/Y' )}}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->documento }}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->documento_n }}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->marca }}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->modelo }}</x-tables.cell>
                <x-tables.cell>{{ $cronotacografoVencimentario->serie }}</x-tables.cell>
                
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
        {{ $cronotacografoVencimentarios->links() }}
    </div>
</div>