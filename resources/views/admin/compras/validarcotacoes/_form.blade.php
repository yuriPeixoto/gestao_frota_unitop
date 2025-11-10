<x-slot name="header">
    <div class="flex w-full items-center justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Nova Solicitação de Compra') }}
        </h2>
        <div class="flex items-center space-x-4">
            <a href="{{ route('admin.compras.validarcotacoes.index') }}"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Voltar para a Lista
            </a>
        </div>
    </div>
</x-slot>

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

    <!-- Formulário de Criação -->
    <form
        action="{{ isset($solicitacoes) ? route('admin.compras.validarcotacoes.update', $solicitacoes->id_validarcotacoes_compras) : route('admin.compras.validarcotacoes.store') }}"
        method="POST" enctype="multipart/form-data" id="formsolicitacoes">
        @csrf
        @if (isset($solicitacoes))
            @method('PUT')
        @endif

        <!-- Card Principal -->
        <div class="overflow-hidden rounded-lg bg-white shadow-lg">
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
                        <p class="mt-1 text-sm text-gray-600">Preencha as informações principais da solicitação.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <!-- Cód. Solicitação -->
                        <div>
                            <x-forms.input name="solicitacoes_compra_consulta" label="Cód. Solicitação de Compras"
                                value="{{ $solicitacao->id_solicitacoes_compras ?? '' }}" readonly />
                        </div>

                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <!-- Comprador -->
                        <div>
                            <x-forms.input name="id_comprador" label="Comprador"
                                value="{{ $solicitacao->comprador->name ?? '' }}" readonly />
                        </div>

                        <!-- Departamento -->
                        <div>
                            <x-forms.input name="id_departamento" label="Departamento"
                                value="{{ $solicitacao->departamento->descricao_departamento ?? '' }}" readonly />
                        </div>

                        <!-- Prioridade -->
                        <div>
                            <x-forms.input name="prioridade" label="Prioridade"
                                value="{{ $solicitacao->prioridade ?? '' }}" readonly />
                        </div>
                    </div>
                </section>

                <!-- Campo de Observação (sempre visível) -->
                <section class="mt-8">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Observação:</label>
                    </div>
                    <textarea name="observacao" rows="4"
                        class="w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                        placeholder="Digite suas observações aqui..."></textarea>
                </section>

                <!-- Cotações (sempre visíveis, preenchidas após processar) -->
                <section id="cotacoes-section">
                    <div class="mb-6 border-b border-gray-200 pb-4">
                        <h3 class="flex items-center text-lg font-semibold text-gray-900">
                            <svg class="mr-2 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            Cotações
                        </h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3" id="cotacoes-container">
                        <!-- Cotação 01 -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-500">Cód. Cotação:</h4>
                                    <input type="text" id="cotacao-01-codigo"
                                        class="w-20 border-0 bg-transparent p-0 text-right text-lg font-bold text-gray-900"
                                        readonly>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Cotação - 01</h3>
                                <p class="text-sm text-gray-600" id="cotacao-01-fornecedor"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="overflow-hidden">
                                        <div
                                            class="grid grid-cols-5 gap-2 rounded-t bg-blue-100 p-2 text-xs font-medium text-gray-600">
                                            <div>Produto</div>
                                            <div class="text-center">Qtd</div>
                                            <div class="text-center">Vlr</div>
                                            <div class="text-center">Vlr.Bruto</div>
                                            <div class="text-center">Vlr. com Desconto</div>
                                        </div>
                                        <div id="cotacao-01-itens" class="min-h-[60px] rounded-b bg-blue-50">
                                            <div class="p-3 text-center text-sm text-gray-500">Nenhum registro foi
                                                encontrado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cotação 02 -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-500">Cód. Cotação:</h4>
                                    <input type="text" id="cotacao-02-codigo"
                                        class="w-20 border-0 bg-transparent p-0 text-right text-lg font-bold text-gray-900"
                                        readonly>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Cotação - 02</h3>
                                <p class="text-sm text-gray-600" id="cotacao-02-fornecedor"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="overflow-hidden">
                                        <div
                                            class="grid grid-cols-5 gap-2 rounded-t bg-blue-100 p-2 text-xs font-medium text-gray-600">
                                            <div>Produto</div>
                                            <div class="text-center">Qtd</div>
                                            <div class="text-center">Vlr</div>
                                            <div class="text-center">Vlr.Bruto</div>
                                            <div class="text-center">Vlr. com Desconto</div>
                                        </div>
                                        <div id="cotacao-02-itens" class="min-h-[60px] rounded-b bg-blue-50">
                                            <div class="p-3 text-center text-sm text-gray-500">Nenhum registro foi
                                                encontrado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cotação 03 -->
                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
                            <div class="mb-4">
                                <div class="mb-2 flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-500">Cód. Cotação:</h4>
                                    <input type="text" id="cotacao-03-codigo"
                                        class="w-20 border-0 bg-transparent p-0 text-right text-lg font-bold text-gray-900"
                                        readonly>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Cotação - 03</h3>
                                <p class="text-sm text-gray-600" id="cotacao-03-fornecedor"></p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="overflow-hidden">
                                        <div
                                            class="grid grid-cols-5 gap-2 rounded-t bg-blue-100 p-2 text-xs font-medium text-gray-600">
                                            <div>Produto</div>
                                            <div class="text-center">Qtd</div>
                                            <div class="text-center">Vlr</div>
                                            <div class="text-center">Vlr.Bruto</div>
                                            <div class="text-center">Vlr. com Desconto</div>
                                        </div>
                                        <div id="cotacao-03-itens" class="min-h-[60px] rounded-b bg-blue-50">
                                            <div class="p-3 text-center text-sm text-gray-500">Nenhum registro foi
                                                encontrado</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-start space-x-3 border-t border-gray-200 bg-gray-50 px-6 py-4">
                <button type="button" onclick="validarCotacao()"
                    class="inline-flex cursor-pointer items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-icons.check class="mr-2 h-4 w-4" />
                    Validar Cotação
                </button>

                <button type="button" onclick="recusarCotacao()"
                    class="inline-flex cursor-pointer items-center rounded-md border border-transparent bg-red-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"
                        fill="white" stroke="white" stroke-width="25">
                        <path
                            d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96zM231 231C240.4 221.6 255.6 221.6 264.9 231L319.9 286L374.9 231C384.3 221.6 399.5 221.6 408.8 231C418.1 240.4 418.2 255.6 408.8 264.9L353.8 319.9L408.8 374.9C418.2 384.3 418.2 399.5 408.8 408.8C399.4 418.1 384.2 418.2 374.9 408.8L319.9 353.8L264.9 408.8C255.5 418.2 240.3 418.2 231 408.8C221.7 399.4 221.6 384.2 231 374.9L286 319.9L231 264.9C221.6 255.5 221.6 240.3 231 231z" />
                    </svg>
                    Recusar Cotações
                </button>

                <button type="button" onclick="cancelarCotacao()"
                    class="inline-flex cursor-pointer items-center rounded-md border border-transparent bg-yellow-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    <svg class="mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"
                        fill="white" stroke="white" stroke-width="25">
                        <path
                            d="M320 64C334.7 64 348.2 72.1 355.2 85L571.2 485C577.9 497.4 577.6 512.4 570.4 524.5C563.2 536.6 550.1 544 536 544L104 544C89.9 544 76.9 536.6 69.6 524.5C62.3 512.4 62.1 497.4 68.8 485L284.8 85C291.8 72.1 305.3 64 320 64zM320 232C306.7 232 296 242.7 296 256L296 368C296 381.3 306.7 392 320 392C333.3 392 344 381.3 344 368L344 256C344 242.7 333.3 232 320 232zM346.7 448C347.3 438.1 342.4 428.7 333.9 423.5C325.4 418.4 314.7 418.4 306.2 423.5C297.7 428.7 292.8 438.1 293.4 448C292.8 457.9 297.7 467.3 306.2 472.5C314.7 477.6 325.4 477.6 333.9 472.5C342.4 467.3 347.3 457.9 346.7 448z" />
                    </svg>
                    Cancelar Cotações
                </button>

                <a href="{{ route('admin.compras.validarcotacoes.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <x-icons.arrow-back class="mr-2 text-cyan-500" />
                    Voltar
                </a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
    @include('admin.compras.validarcotacoes._scripts')
@endpush
