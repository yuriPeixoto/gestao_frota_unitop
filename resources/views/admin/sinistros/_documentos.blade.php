{{-- Seção de upload e gerenciamento de documentos --}}
<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center">
    <div class="col-span-3">
        <label for="documento" class="block text-sm font-medium text-gray-700 mb-1">Documento</label>
        <input type="file" name="documento" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx"
            {{ $bloquear ? 'disabled' : '' }}
            class="document-interface w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}">
        <p class="mt-1 text-xs text-gray-500">Formatos aceitos: PDF, imagens e documentos Office. Tamanho máximo: 10MB
        </p>
    </div>

    <div class="flex justify-center items-center">
        <button type="button" data-action="upload-document" {{ $bloquear ? 'disabled' : '' }}
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Adicionar Documento
        </button>
    </div>

    {{-- Campo hidden para armazenar os documentos --}}
    <input type="hidden" name="documentos" id="documentos_json"
        value="{{ isset($historicosinistroDocumentos) ? json_encode($historicosinistroDocumentos) : '[]' }}">

    {{-- Campo hidden para ID do sinistro (em caso de edição) --}}
    @if (isset($sinistro) && isset($sinistro->id_sinistro))
        <input type="hidden" name="sinistro_id" id="sinistro_id" value="{{ $sinistro->id_sinistro }}">
    @endif

    {{-- Tabela de documentos --}}
    <div class="col-span-full mt-4">
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Inclusão
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Data Alteração
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Documentos
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody id="tabelaDocumentoBody" class="bg-white divide-y divide-gray-200">
                    {{-- Linhas serão adicionadas dinamicamente pelo JavaScript --}}
                </tbody>
            </table>
        </div>

        {{-- Mensagem sem documentos --}}
        <div id="documentos-empty" class="hidden w-full text-center py-4 text-sm text-gray-500">
            Nenhum documento adicionado. Use o botão acima para adicionar documentos.
        </div>

        {{-- Área para feedback visual --}}
        <div id="upload-feedback" class="hidden mt-4 p-3 rounded"></div>
    </div>
</div>
