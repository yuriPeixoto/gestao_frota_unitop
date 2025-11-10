<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ação</x-tables.head-cell>
            <x-tables.head-cell>Renavam</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>N° Certificado</x-tables.head-cell>
            <x-tables.head-cell>Data Inspeção</x-tables.head-cell>
            <x-tables.head-cell>Data Vencimento</x-tables.head-cell>
            <x-tables.head-cell>Tipo</x-tables.head-cell>
            <x-tables.head-cell>Status</x-tables.head-cell>
            <x-tables.head-cell>Licença</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($controlelicencavencimentarios as $index => $controlelicencavencimentario)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    @if ($controlelicencavencimentario->url)    
                    <button onclick="abrirModalPdf({{ $controlelicencavencimentario }})"
                        class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2">
                        <x-icons.print class="w-4 h-4" />
                        <span>Certificado</span>
                    </button>
                    @endif
                </x-tables.cell>

                <x-tables.cell>{{ $controlelicencavencimentario->renavam }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->placa }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->numerocertificado }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->datainspecao }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->datavencimento }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->tipo }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->status }}</x-tables.cell>
                <x-tables.cell>{{ $controlelicencavencimentario->licenca_tabela }}</x-tables.cell>

                    <div id="pdfModal" class="pdf-modal-overlay">
                        <div class="pdf-modal-container">
                            <!-- Header do Modal -->
                            <div class="pdf-modal-header">
                                <div class="pdf-modal-title">Licenciamento Digital - Visualização de Impressão</div>
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
        {{ $controlelicencavencimentarios->links() }}
    </div>
</div>