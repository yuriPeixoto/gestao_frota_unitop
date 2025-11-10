@php
// Definir todos os valores padrão diretamente aqui na view
$componenteId = $componenteId ?? 'upload-anexos-' . uniqid();
$anexos = $anexos ?? [];
$urlUpload = $urlUpload ?? route('admin.anexos.upload');
$urlExcluir = $urlExcluir ?? route('admin.anexos.destroy', '_id_placeholder');
$entidadeId = $entidadeId ?? null;
$entidadeTipo = $entidadeTipo ?? 'generico';
$tiposPermitidos = $tiposPermitidos ?? '.pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv';
$tamanhoMaximo = $tamanhoMaximo ?? 10;
$tamanhoMaximoBytes = $tamanhoMaximo * 1024 * 1024;
$somenteVisualizacao = $somenteVisualizacao ?? false;
$multiplosArquivos = $multiplosArquivos ?? true;
$textoAjuda = $textoAjuda ?? 'Arraste e solte arquivos aqui ou clique para selecionar';
$classesCss = $classesCss ?? '';

// Função para descrever os tipos permitidos
$descricaoTiposPermitidos = function() use ($tiposPermitidos) {
$tipos = explode(',', $tiposPermitidos);
$tipos = array_map(function($tipo) {
return strtoupper(str_replace('.', '', $tipo));
}, $tipos);

if (count($tipos) > 5) {
$primeiros = array_slice($tipos, 0, 5);
return implode(', ', $primeiros) . ' e outros';
}

return implode(', ', $tipos);
};
@endphp

<div id="{{ $componenteId }}" class="relative {{ $classesCss }}" x-data="uploadAnexos({
        anexos: {{ json_encode($anexos) }},
        urlUpload: '{{ $urlUpload }}',
        urlExcluir: '{{ $urlExcluir }}', 
        entidadeId: {{ json_encode($entidadeId) }},
        entidadeTipo: '{{ $entidadeTipo }}',
        tiposPermitidos: '{{ $tiposPermitidos }}',
        tamanhoMaximo: {{ $tamanhoMaximoBytes }},
        somenteVisualizacao: {{ $somenteVisualizacao ? 'true' : 'false' }},
        multiplosArquivos: {{ $multiplosArquivos ? 'true' : 'false' }}
    })" x-init="init()">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="p-4 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-700">Anexos</h3>
        </div>

        <!-- Lista de anexos existentes -->
        <div class="p-4">
            <div x-show="anexos.length > 0" class="space-y-2 mb-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Arquivos anexados</h4>

                <div class="space-y-2">
                    <template x-for="(anexo, index) in anexos" :key="anexo.id || index">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-md">
                            <div class="flex items-center space-x-3">
                                <!-- Ícone do tipo de arquivo -->
                                <div class="flex-shrink-0">
                                    {{-- Verificando se o arquivo é uma imagem --}}
                                    <template
                                        x-if="anexo.arquivo_nome && ['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(anexo.arquivo_nome.split('.').pop().toLowerCase())">
                                        <img :src="anexo.url" class="h-10 w-10 object-cover rounded"
                                            :alt="anexo.arquivo_nome">
                                    </template>

                                    {{-- Ícone para outros tipos de arquivo --}}
                                    <template
                                        x-if="anexo.arquivo_nome && !['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(anexo.arquivo_nome.split('.').pop().toLowerCase())">
                                        <div class="h-10 w-10 flex items-center justify-center bg-gray-100 rounded">
                                            <svg class="h-6 w-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd"
                                                    d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </template>
                                </div>

                                <!-- Nome e tamanho do arquivo -->
                                <div>
                                    <p class="text-sm font-medium text-gray-700" x-text="anexo.arquivo_nome"></p>
                                    <p class="text-xs text-gray-500">
                                        <span x-text="formatarTamanho(anexo.tamanho)"></span>
                                        <span x-show="anexo.data_upload">- Enviado em <span
                                                x-text="formatarData(anexo.data_upload)"></span></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="flex items-center space-x-2">
                                <button @click="visualizarAnexo(anexo)" type="button"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full text-blue-600 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    title="Visualizar">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>

                                <button @click="baixarAnexo(anexo)" type="button"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full text-green-600 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                    title="Baixar">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </button>

                                <button x-show="!somenteVisualizacao" @click="excluirAnexo(anexo, index)" type="button"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full text-red-600 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                    title="Excluir">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Área de upload quando não está em modo somente visualização -->
            <div x-show="!somenteVisualizacao" class="mt-2">
                <div x-show="!uploadIniciado"
                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md cursor-pointer hover:bg-gray-50"
                    @click="$refs.fileInput.click()" @dragover.prevent="dragOver = true"
                    @dragleave.prevent="dragOver = false" @drop.prevent="handleDrop($event)"
                    :class="{'bg-blue-50 border-blue-300': dragOver}">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                            viewBox="0 0 48 48" aria-hidden="true">
                            <path
                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label
                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>{{ $textoAjuda }}</span>
                                <input type="file" class="sr-only" x-ref="fileInput" @change="handleFileSelect" {{
                                    $multiplosArquivos ? 'multiple' : '' }} accept="{{ $tiposPermitidos }}">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ $descricaoTiposPermitidos() }} até {{ $tamanhoMaximo }}MB
                        </p>
                    </div>
                </div>

                <!-- Área de progresso de upload -->
                <div x-show="uploadIniciado" class="mt-4">
                    <div class="space-y-2">
                        <template x-for="(arquivo, index) in arquivosParaUpload" :key="index">
                            <div class="flex items-center space-x-3">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-sm font-medium text-gray-700" x-text="arquivo.nome"></p>
                                        <p class="text-xs text-gray-500" x-text="arquivo.status"></p>
                                    </div>
                                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-blue-600 rounded-full transition-all duration-300 ease-in-out"
                                            :style="'width: ' + arquivo.progresso + '%'"></div>
                                    </div>
                                </div>
                                <button @click="cancelarUpload(index)" type="button"
                                    class="inline-flex items-center p-1 border border-transparent rounded-full text-red-600 hover:bg-red-100 focus:outline-none"
                                    :disabled="arquivo.status === 'Concluído'">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button @click="concluirUpload" type="button"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            :disabled="!todosUploadsCompletos">
                            Concluir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mensagem quando não há anexos -->
            <div x-show="anexos.length === 0 && somenteVisualizacao" class="py-6 text-center">
                <p class="text-sm text-gray-500">Nenhum anexo disponível</p>
            </div>
        </div>
    </div>

    <!-- Modal de visualização de anexo -->
    <div x-show="modalVisualizacaoAberto" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalVisualizacaoAberto" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="modalVisualizacaoAberto" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title"
                                x-text="anexoSelecionado?.arquivo_nome || 'Visualizar Anexo'">
                                Visualizar Anexo
                            </h3>
                            <div class="mt-4 flex justify-center">
                                <template x-if="eAnexoImagem">
                                    <img :src="anexoSelecionado?.url" class="max-h-96 max-w-full"
                                        :alt="anexoSelecionado?.arquivo_nome">
                                </template>
                                <template x-if="eAnexoPdf">
                                    <iframe :src="anexoSelecionado?.url" class="w-full h-96"></iframe>
                                </template>
                                <template x-if="!eAnexoImagem && !eAnexoPdf">
                                    <div class="p-8 text-center">
                                        <svg class="h-16 w-16 text-gray-400 mx-auto" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                        <p class="mt-4 text-gray-600">Este tipo de arquivo não pode ser visualizado
                                            diretamente. Utilize o botão "Baixar" para abri-lo.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="baixarAnexoSelecionado()" type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Baixar
                    </button>
                    <button @click="fecharModalVisualizacao()" type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('uploadAnexos', (config) => ({
            anexos: config.anexos || [],
            urlUpload: config.urlUpload,
            urlExcluir: config.urlExcluir,
            entidadeId: config.entidadeId,
            entidadeTipo: config.entidadeTipo,
            tiposPermitidos: config.tiposPermitidos,
            tamanhoMaximo: config.tamanhoMaximo,
            somenteVisualizacao: config.somenteVisualizacao,
            multiplosArquivos: config.multiplosArquivos,
            
            // Estados internos
            dragOver: false,
            uploadIniciado: false,
            arquivosParaUpload: [],
            controllers: [], // AbortController para cada upload
            
            // Modal de visualização
            modalVisualizacaoAberto: false,
            anexoSelecionado: null,
            
            init() {
                // Não é necessário fazer nada aqui por enquanto
            },
            
            // Tratamento de arquivos selecionados via input file
            handleFileSelect(event) {
                const arquivos = Array.from(event.target.files || []);
                if (arquivos.length === 0) return;
                
                this.prepararUpload(arquivos);
            },
            
            // Tratamento de arquivos via drag and drop
            handleDrop(event) {
                this.dragOver = false;
                const arquivos = Array.from(event.dataTransfer.files || []);
                if (arquivos.length === 0) return;
                
                this.prepararUpload(arquivos);
            },
            
            // Preparar arquivos para upload
            prepararUpload(arquivos) {
                // Validar e preparar arquivos
                let arquivosValidos = arquivos.filter(arquivo => {
                    // Validar tamanho
                    if (arquivo.size > this.tamanhoMaximo) {
                        alert(`O arquivo "${arquivo.name}" excede o tamanho máximo permitido.`);
                        return false;
                    }
                    
                    // Validar tipo (se especificado)
                    if (this.tiposPermitidos && this.tiposPermitidos !== '*') {
                        const extensao = '.' + arquivo.name.split('.').pop().toLowerCase();
                        const extensoesPermitidas = this.tiposPermitidos.split(',').map(e => e.trim().toLowerCase());
                        
                        if (!extensoesPermitidas.includes(extensao) && !extensoesPermitidas.includes('*')) {
                            alert(`O arquivo "${arquivo.name}" não é de um tipo permitido.`);
                            return false;
                        }
                    }
                    
                    return true;
                });
                
                // Se não há arquivos válidos, não faz nada
                if (arquivosValidos.length === 0) return;
                
                // Se não é permitido múltiplos arquivos, pega apenas o primeiro
                if (!this.multiplosArquivos) {
                    arquivosValidos = [arquivosValidos[0]];
                }
                
                // Adicionar arquivos à lista de upload
                this.arquivosParaUpload = arquivosValidos.map(arquivo => ({
                    arquivo: arquivo,
                    nome: arquivo.name,
                    tamanho: arquivo.size,
                    progresso: 0,
                    status: 'Aguardando',
                    erro: null
                }));
                
                this.uploadIniciado = true;
                
                // Iniciar uploads
                this.iniciarUploads();
            },
            
            // Iniciar o processo de upload para cada arquivo
            iniciarUploads() {
                this.arquivosParaUpload.forEach((item, index) => {
                    if (item.status === 'Aguardando') {
                        this.fazerUpload(index);
                    }
                });
            },
            
            // Realizar o upload de um arquivo específico
            fazerUpload(index) {
                const item = this.arquivosParaUpload[index];
                if (!item || item.status !== 'Aguardando') return;
                
                // Atualizar status
                item.status = 'Enviando';
                item.progresso = 0;
                
                // Preparar FormData
                const formData = new FormData();
                formData.append('arquivo', item.arquivo);
                formData.append('entidade_tipo', this.entidadeTipo);
                formData.append('entidade_id', this.entidadeId);
                
                // Criar AbortController para permitir cancelamento
                const controller = new AbortController();
                this.controllers[index] = controller;
                
                // Enviar arquivo
                fetch(this.urlUpload, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    signal: controller.signal,
                    // Não é possível rastrear o progresso diretamente com fetch
                    // Em produção, você pode precisar usar XMLHttpRequest
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Upload concluído com sucesso
                        item.status = 'Concluído';
                        item.progresso = 100;
                        
                        // Adicionar novo anexo à lista se estiver disponível na resposta
                        if (data.anexo) {
                            this.anexos.push(data.anexo);
                        }
                    } else {
                        // Erro no upload
                        item.status = 'Erro';
                        item.erro = data.message || 'Erro desconhecido';
                        console.error('Erro ao fazer upload:', data.message);
                    }
                })
                .catch(error => {
                    // Ignora erros de cancelamento
                    if (error.name === 'AbortError') return;
                    
                    item.status = 'Erro';
                    item.erro = error.message;
                    console.error('Erro ao fazer upload:', error);
                });
                
                // Simulação de progresso (já que fetch não tem evento de progresso)
                // Em produção, use XMLHttpRequest para rastrear o progresso real
                const simulateProgress = () => {
                    if (item.status !== 'Enviando') return;
                    
                    // Incrementa progresso gradualmente até 90%
                    if (item.progresso < 90) {
                        item.progresso += Math.random() * 10;
                        if (item.progresso > 90) item.progresso = 90;
                        
                        setTimeout(simulateProgress, 500);
                    }
                };
                
                simulateProgress();
            },
            
            // Cancelar upload de um arquivo
            cancelarUpload(index) {
                const item = this.arquivosParaUpload[index];
                if (!item) return;
                
                // Cancelar requisição se estiver em andamento
                if (this.controllers[index]) {
                    this.controllers[index].abort();
                    this.controllers[index] = null;
                }
                
                // Atualizar status
                item.status = 'Cancelado';
                
                // Remover da lista
                this.arquivosParaUpload.splice(index, 1);
                
                // Se não há mais arquivos, resetar estado
                if (this.arquivosParaUpload.length === 0) {
                    this.uploadIniciado = false;
                }
            },
            
            // Concluir o processo de upload e resetar estado
            concluirUpload() {
                this.uploadIniciado = false;
                this.arquivosParaUpload = [];
                this.controllers = [];
                
                // Resetar input file
                this.$refs.fileInput.value = '';
            },
            
            // Verificar se todos os uploads foram concluídos
            get todosUploadsCompletos() {
                return this.arquivosParaUpload.every(item => 
                    item.status === 'Concluído' || 
                    item.status === 'Erro' || 
                    item.status === 'Cancelado'
                );
            },
            
            // Excluir anexo
            excluirAnexo(anexo, index) {
                if (confirm(`Tem certeza que deseja excluir o anexo "${anexo.arquivo_nome}"?`)) {
                    const url = this.urlExcluir.replace('_id_placeholder', anexo.id);
                    
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remover da lista
                            this.anexos.splice(index, 1);
                        } else {
                            alert(data.message || 'Erro ao excluir o anexo.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao excluir anexo:', error);
                        alert('Erro ao excluir o anexo. Tente novamente mais tarde.');
                    });
                }
            },
            
            // Visualizar anexo
            visualizarAnexo(anexo) {
                this.anexoSelecionado = anexo;
                this.modalVisualizacaoAberto = true;
            },
            
            // Fechar modal de visualização
            fecharModalVisualizacao() {
                this.modalVisualizacaoAberto = false;
                this.anexoSelecionado = null;
            },
            
            // Baixar anexo
            baixarAnexo(anexo) {
                if (anexo && anexo.url) {
                    window.open(anexo.url_download || anexo.url, '_blank');
                }
            },
            
            // Baixar anexo selecionado no modal
            baixarAnexoSelecionado() {
                if (this.anexoSelecionado) {
                    this.baixarAnexo(this.anexoSelecionado);
                }
            },
            
            // Verificar se o anexo selecionado é uma imagem
            get eAnexoImagem() {
                if (!this.anexoSelecionado) return false;
                
                const extensoes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                const ext = this.anexoSelecionado.arquivo_nome.split('.').pop().toLowerCase();
                
                return extensoes.includes(ext);
            },
            
            // Verificar se o anexo selecionado é um PDF
            get eAnexoPdf() {
                if (!this.anexoSelecionado) return false;
                
                return this.anexoSelecionado.arquivo_nome.toLowerCase().endsWith('.pdf');
            },
            
            // Formatadores
            formatarTamanho(bytes) {
                if (!bytes || bytes === 0) return '0 Bytes';
                
                const units = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                
                return parseFloat((bytes / Math.pow(1024, i)).toFixed(2)) + ' ' + units[i];
            },
            
            formatarData(data) {
                if (!data) return '';
                
                try {
                    return new Date(data).toLocaleDateString('pt-BR');
                } catch (e) {
                    return data;
                }
            }
        }));
    });
</script>
@endpush