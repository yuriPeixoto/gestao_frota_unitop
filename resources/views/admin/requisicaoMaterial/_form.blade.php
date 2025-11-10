@php
    use Illuminate\Support\Facades\Auth;
@endphp

<div class="border-b border-gray-200 bg-white p-4">
    <!-- Mensagens de Sucesso/Erro -->
    @if (session('success'))
        <div class="mb-6 rounded-r-lg border-l-4 border-green-400 bg-green-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 rounded-r-lg border-l-4 border-red-400 bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('notification'))
        <div class="mb-6 rounded-r-lg border-l-4 border-blue-400 bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-800">{{ session('notification') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-r-lg border-l-4 border-red-400 bg-red-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Corrija os seguintes erros:</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Card Principal -->
    <div class="overflow-hidden rounded-lg bg-white shadow-lg">
        <form id="requisicao-form" method="POST" enctype="multipart/form-data"
            action="{{ isset($requisicaoMaterial) ? route('admin.requisicaoMaterial.update', $requisicaoMaterial->id_solicitacao_pecas) : route('admin.requisicaoMaterial.store') }}">
            @csrf
            @if (isset($requisicaoMaterial))
                @method('PUT')
            @endif

            <div class="space-y-8 px-6 py-8">
                <!-- Informações Básicas -->
                <section>
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Informações Básicas
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Preencha as informações principais da requisição.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        {{-- id Solicitação --}}
                        <div>
                            <x-forms.input name="id_solicitacao_pecas" label="Código Requisição Material" readonly
                                value="{{ old('id_solicitacao_pecas', $requisicaoMaterial->id_solicitacao_pecas ?? '') }}" />
                        </div>

                        {{-- Departamento --}}
                        <div>
                            <x-forms.smart-select name="id_departamento" label="Descrição Departamento"
                                :options="$forms['departamento']" :selected="old(
                                    'id_departamento',
                                    $requisicaoMaterial->id_departamento ?? Auth::user()->id_departamento,
                                )" />
                        </div>

                        {{-- Usuário --}}
                        <div>
                            <x-forms.input name="id_usuario_abertura" label="Usuário" readonly
                                value="{{ old('id_usuario_abertura', $requisicaoMaterial->usuarioSolicitante->name ?? Auth::user()->name) }}" />
                        </div>

                        {{-- Filial --}}
                        <div>
                            <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
                                :options="$forms['filial']" :selected="old('id_filial', $requisicaoMaterial->id_filial ?? GetterFilial())" asyncSearch="false" />

                            <input type="hidden" name="id_filial"
                                value="{{ old('id_filial', $requisicaoMaterial->id_filial ?? GetterFilial()) }}" />
                        </div>
                    </div>
                </section>

                <!-- Configurações da Requisição -->
                <section>
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Configurações da Requisição
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Configure o tipo e características da requisição.</p>
                    </div>

                    <div x-data="{
                        isTerceiro: {{ old('is_terceiro', isset($requisicaoMaterial) && !empty($requisicaoMaterial->id_terceiro) ? 1 : 0) }},
                        requisicaoPneu: {{ (int) old('requisicao_pneu', isset($requisicaoMaterial) ? $requisicaoMaterial->requisicao_pneu : 0) }},
                        requisicaoTi: {{ (int) old('requisicao_ti', isset($requisicaoMaterial) ? $requisicaoMaterial->requisicao_ti : 0) }},
                    
                        togglePneu() {
                            this.requisicaoPneu = this.requisicaoPneu == 1 ? 0 : 1;
                    
                            if (this.requisicaoPneu == 1 && this.requisicaoTi == 1) {
                                alert('Não é possível selecionar Requisição de Pneus e Requisição T.I. ao mesmo tempo.');
                                this.requisicaoTi = 0;
                            }
                    
                            this.atualizarProdutos();
                        },
                    
                        toggleTi() {
                            this.requisicaoTi = this.requisicaoTi == 1 ? 0 : 1;
                    
                            if (this.requisicaoTi == 1 && this.requisicaoPneu == 1) {
                                alert('Não é possível selecionar Requisição T.I. e Requisição de Pneus ao mesmo tempo.');
                                this.requisicaoPneu = 0;
                            }
                    
                            this.atualizarProdutos();
                        },
                    
                        atualizarProdutos() {
                            // Dispatch evento customizado
                            window.dispatchEvent(new CustomEvent('toggleChanged', {
                                detail: {
                                    pneu: this.requisicaoPneu,
                                    ti: this.requisicaoTi
                                }
                            }));
                        }
                    }">
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                            <!-- Solicitação para Terceiro -->
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col items-center space-y-4">
                                    <span class="text-sm font-semibold text-gray-900">Solicitação para Terceiro</span>

                                    <!-- Toggle Switch -->
                                    <div class="flex items-center">
                                        <input type="radio" name="is_terceiro" value="0" id="is_terceiro_nao"
                                            class="sr-only" x-model="isTerceiro" @checked(old('is_terceiro', isset($requisicaoMaterial) && !empty($requisicaoMaterial->id_terceiro) ? 1 : 0) == 0)>
                                        <input type="radio" name="is_terceiro" value="1" id="is_terceiro"
                                            class="sr-only" x-model="isTerceiro" @checked(old('is_terceiro', isset($requisicaoMaterial) && !empty($requisicaoMaterial->id_terceiro) ? 1 : 0) == 1)>

                                        <button type="button" @click="isTerceiro = isTerceiro == 1 ? 0 : 1"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            :class="isTerceiro == 1 ? 'bg-indigo-600' : 'bg-gray-200'">
                                            <span
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="isTerceiro == 1 ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </button>
                                        <span class="ml-3 text-sm"
                                            :class="isTerceiro == 1 ? 'text-indigo-600 font-medium' : 'text-gray-500'">
                                            <span x-text="isTerceiro == 1 ? 'SIM' : 'NÃO'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Requisição de Pneus -->
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col items-center space-y-4">
                                    <span class="text-sm font-semibold text-gray-900">Requisição de Pneus</span>

                                    <!-- Toggle Switch -->
                                    <div class="flex items-center">
                                        <input type="radio" name="requisicao_pneu" value="0"
                                            id="requisicao_pneu_nao" class="sr-only" x-model="requisicaoPneu"
                                            @checked((int) old('requisicao_pneu', isset($requisicaoMaterial) ? $requisicaoMaterial->requisicao_pneu : 0) == 0)>
                                        <input type="radio" name="requisicao_pneu" value="1"
                                            id="requisicao_pneu" class="sr-only" x-model="requisicaoPneu"
                                            @checked((int) old('requisicao_pneu', isset($requisicaoMaterial) ? $requisicaoMaterial->requisicao_pneu : 0) == 1)>

                                        <button type="button" @click="togglePneu()"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            :class="requisicaoPneu == 1 ? 'bg-indigo-600' : 'bg-gray-200'">
                                            <span
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="requisicaoPneu == 1 ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </button>
                                        <span class="ml-3 text-sm"
                                            :class="requisicaoPneu == 1 ? 'text-indigo-600 font-medium' : 'text-gray-500'">
                                            <span x-text="requisicaoPneu == 1 ? 'SIM' : 'NÃO'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Requisição T.I. -->
                            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                                <div class="flex flex-col items-center space-y-4">
                                    <span class="text-sm font-semibold text-gray-900">Requisição T.I.</span>

                                    <!-- Toggle Switch -->
                                    <div class="flex items-center">
                                        <input type="radio" name="requisicao_ti" value="0"
                                            id="requisicao_ti_nao" class="sr-only" x-model="requisicaoTi"
                                            @checked((int) old('requisicao_ti', isset($requisicaoMaterial) ? $requisicaoMaterial->requisicao_ti : 0) == 0)>
                                        <input type="radio" name="requisicao_ti" value="1" id="requisicao_ti"
                                            class="sr-only" x-model="requisicaoTi" @checked((int) old('requisicao_ti', isset($requisicaoMaterial) ? $requisicaoMaterial->requisicao_ti : 0) == 1)>

                                        <button type="button" @click="toggleTi()"
                                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            :class="requisicaoTi == 1 ? 'bg-indigo-600' : 'bg-gray-200'">
                                            <span
                                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                                :class="requisicaoTi == 1 ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </button>
                                        <span class="ml-3 text-sm"
                                            :class="requisicaoTi == 1 ? 'text-indigo-600 font-medium' : 'text-gray-500'">
                                            <span x-text="requisicaoTi == 1 ? 'SIM' : 'NÃO'"></span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Informações de Terceiros e Veículos -->
                        <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2" x-show="isTerceiro == 1" x-transition>
                            <div>
                                <x-forms.smart-select name="id_terceiro" label="Terceiro" :options="$fornecedor"
                                    :searchUrl="route('admin.api.fornecedores.search')" :selected="old(
                                        'id_terceiro',
                                        $requisicaoMaterial->fornecedor->nome_fornecedor ?? '',
                                    )" />
                            </div>

                            <div>
                                <x-forms.smart-select name="id_veiculo" label="Placa" :options="$placa"
                                    :searchUrl="route('admin.api.veiculos.search')" :selected="old('id_veiculo', $requisicaoMaterial->veiculo->placa ?? '')" />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Observações -->
                <section>
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Observações
                        </h3>
                        <p class="mt-1 text-sm text-gray-600">Adicione informações complementares sobre a requisição.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="observacao" class="mb-2 block text-sm font-medium text-gray-700">
                                Observações Gerais
                            </label>
                            <textarea name="observacao" id="observacao" rows="4"
                                placeholder="Descreva informações adicionais, requisitos especiais ou outras observações relevantes..."
                                class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $requisicaoMaterial->observacao ?? '') }}</textarea>
                        </div>

                        <div>
                            <label for="anexo_requisicao" class="mb-2 block text-sm font-medium text-gray-700">
                                Anexo da Requisição
                            </label>
                            <input type="file" name="anexo_requisicao" id="anexo_requisicao"
                                accept="image/*,.pdf,.doc,.docx"
                                class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500">Aceita imagens, PDF e documentos Word (máx. 10MB)</p>

                            @if (isset($requisicaoMaterial) && $requisicaoMaterial->anexo_imagem)
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">Arquivo atual:</p>
                                    <a href="{{ asset('storage/' . $requisicaoMaterial->anexo_imagem) }}"
                                        target="_blank"
                                        class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                            </path>
                                        </svg>
                                        {{ basename($requisicaoMaterial->anexo_imagem) }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </section>

                <!-- Divisor Visual -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center">
                        <span class="bg-white px-3 text-lg font-medium text-gray-900">Produtos da Requisição</span>
                    </div>
                </div>

                <!-- Adicionar Produtos -->
                <section>
                    <!-- Formulário de Adição de Produtos -->
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        <!-- Header da Seção -->
                        <div
                            class="rounded-t-lg border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4">
                            <h4 class="flex items-center text-lg font-semibold text-gray-800">
                                <svg class="mr-2 h-5 w-5 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Selecionar Produto
                            </h4>
                        </div>

                        <!-- Conteúdo da Seção -->
                        <div class="space-y-6 p-6">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                {{-- Grupo --}}
                                <div class="col-span-full">
                                    <x-forms.input name="id_grupo" label="Grupo" readonly />
                                </div>

                                <div class="flex items-end gap-2 md:col-span-2">
                                    {{-- Produto --}}
                                    <div class="w-full">
                                        <x-forms.smart-select id="id_produto" name="id_produto" label="Produtos"
                                            :options="[]" :searchUrl="route('admin.requisicaoMaterial.searchProdutos')" :selected="old('id_produto')" />
                                    </div>
                                    <div class="flex flex-shrink-0 gap-1">
                                        {{-- Visualizar Produto --}}
                                        <button type="button" id="btn-visualizar-produto"
                                            onclick="abrirModalVisualizarProduto()"
                                            class="group mb-1 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition-all duration-200 hover:border-blue-400 hover:bg-blue-50 hover:text-blue-600 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 active:scale-95"
                                            title="Visualizar produto">
                                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        {{-- Disponibilidade Produto --}}
                                        <button type="button" id="btn-disponibilidade-produto"
                                            onclick="abrirModalDisponibilidade()"
                                            class="group mb-1 inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-600 shadow-sm transition-all duration-200 hover:border-green-400 hover:bg-green-50 hover:text-green-600 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 active:scale-95"
                                            title="Ver disponibilidade nas filiais">
                                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:scale-110"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                {{-- Estoque Filial --}}
                                <div>
                                    <x-forms.input name="estoque_filial" label="Estoque Filial" readonly />
                                </div>

                                {{-- Quantidade --}}
                                <div>
                                    <x-forms.input id="quantidade" name="quantidade" label="Quantidade" />
                                </div>

                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <textarea name="observacao_produto" id="observacao_produto" rows="4" placeholder="Observações do produto"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                </div>

                                <div>
                                    <label for="anexo_produto" class="mb-2 block text-sm font-medium text-gray-700">
                                        Anexo do Produto
                                    </label>
                                    <input type="file" name="anexo_produto" id="anexo_produto"
                                        accept="image/*,.pdf,.doc,.docx" onchange="handleProductAttachment()"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm transition duration-200 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Aceita imagens, PDF e documentos Word (máx.
                                        10MB)</p>
                                </div>
                            </div>

                            <!-- Botão Adicionar -->
                            <div class="flex justify-end border-t border-gray-200 pt-4">
                                <button type="button" onclick="adicionarRequisicoes()"
                                    class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    <x-icons.plus />
                                    Adicionar Produto
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Campo hidden para armazenar os produtos -->
                    <input type="hidden" name="tabelaReqMats" id="tabelaReqMats_json"
                        value="{{ isset($produtosSolicitados) ? json_encode($produtosSolicitados) : '[]' }}">
                    <input type="hidden" name="codReqProduto" id="codReqProduto">

                    <!-- Container para anexos dos produtos -->
                    <div id="anexos-produtos-container"></div>

                    <!-- Tabela de Produtos -->
                    <div class="mt-6">
                        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                                <h5 class="text-lg font-medium text-gray-900">Produtos Adicionados</h5>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="tabelaReqMateriasBody min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Ações
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Código Produto
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Produtos
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Quantidade de Produtos
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Situação
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Data Inclusão
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Data Alteração
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Observação
                                            </th>
                                            <th scope="col"
                                                class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                Anexo
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabelaReqMateriasBody" class="divide-y divide-gray-200 bg-white">
                                        <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <a href="{{ route('admin.requisicaoMaterial.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Cancelar
                </a>
                <button type="submit" id="submit-form"
                    class="inline-flex items-center rounded-lg border border-transparent bg-indigo-600 px-6 py-2 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    {{ isset($requisicaoMaterial) ? 'Atualizar Requisição' : 'Salvar Requisição' }}
                </button>
            </div>
        </form>
    </div>
</div>

@include('admin.requisicaoMaterial._modals')

@push('scripts')
    <script src="{{ asset('js/requisicaoMaterial/attachment-utils.js') }}"></script>
    <script src="{{ asset('js/requisicaoMaterial/requisicao_material.js') }}"></script>
    @include('admin.requisicaoMaterial._scripts')
@endpush
