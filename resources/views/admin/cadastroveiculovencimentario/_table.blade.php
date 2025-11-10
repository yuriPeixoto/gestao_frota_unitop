<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Ação</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Renavam</x-tables.head-cell>
            <x-tables.head-cell>Municipio</x-tables.head-cell>
            <x-tables.head-cell>UF</x-tables.head-cell>
            <x-tables.head-cell>Tipo Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Combustível</x-tables.head-cell>
            <x-tables.head-cell>Cor</x-tables.head-cell>
            <x-tables.head-cell>Marca</x-tables.head-cell>
            <x-tables.head-cell>Ano Fabricação</x-tables.head-cell>
            <x-tables.head-cell>Licenciamento</x-tables.head-cell>
            <x-tables.head-cell>Exercicio</x-tables.head-cell>
            <x-tables.head-cell>Restrições</x-tables.head-cell>
            <x-tables.head-cell>Observações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($smartecVeiculo as $index => $smartecVeiculos)
            <x-tables.row :index="$index">
                <x-tables.cell>
                    <button onclick="carregarCrlv({{ $smartecVeiculos->id_smartec_veiculo }})"
                        class="dropdown-button bg-white border px-4 py-2 rounded shadow flex items-center space-x-2">
                        <x-icons.pdf-doc class="w-4 h-4" />
                        <span>CRLV</span>
                    </button>


                </x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->placa ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->renavam ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->municipio ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->uf ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->tipo ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->combustivel ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->cor ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->marca ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->ano_fabricacao ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->licenciamento_vigente ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->exercicio_licenciamento ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->restricoes ?? 'N/A'}}</x-tables.cell>
                <x-tables.cell>{{ $smartecVeiculos->observacao ?? 'N/A'}}</x-tables.cell>

                <x-bladewind.modal name="crlvForm" cancel_button_label="Cancelar" ok_button_label="" title="CRLV"
                    size="large">
                    <!-- Informações do Veículo -->
                    <div class="grid grid-cols-4 gap-4 mb-6 bg-gray-50 p-4 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Renavam:</label>
                            <div class="bg-gray-200 px-3 py-2 rounded text-sm font-mono renavam-info"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Licenciamento:</label>
                            <div class="bg-gray-200 px-3 py-2 rounded text-sm licenciamento-info"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Uf:</label>
                            <div class="bg-gray-200 px-3 py-2 rounded text-sm uf-info"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Município:</label>
                            <div class="bg-gray-200 px-3 py-2 rounded text-sm municipio-info"></div>
                        </div>
                    </div>

                    <!-- Link de impressão -->
                    <div class="mb-4">
                        <button onclick="abrirModalPdf(veiculoAtual, crlvAtual)"
                            class="inline-flex items-center text-black-600 hover:text-black-800 text-sm font-medium bg-white border px-4 py-2 rounded shadow">
                            <x-icons.pdf-doc class="w-4 h-4 me-2 text-orange-600" />
                            Imprimir CRLV
                        </button>
                    </div>
                </x-bladewind.modal>

                <div id="pdfModal" class="pdf-modal-overlay">
                    <div class="pdf-modal-container">
                        <!-- Header do Modal -->
                        <div class="pdf-modal-header">
                            <div class="pdf-modal-title">CRLV Digital - Visualização de Impressão</div>
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
                            <iframe id="pdfFrame" class="pdf-iframe" style="display: none;" onload="pdfCarregado()">
                            </iframe>
                        </div>
                    </div>
                </div>


            </x-tables.row>
            @empty
            <x-tables.empty cols="14" message="Nenhum registro encontrado" />
            @endforelse
        </x-tables.body>
    </x-tables.table>

    <div class="mt-4">
        {{ $smartecVeiculo->links() }}
    </div>
    @include('admin.cadastroveiculovencimentario._scripts')
</div>