<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif



            <!-- Abas -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <!-- Aba Cadastro Pré-O.S (ativa por padrão) -->
                        <button onclick="openTab(event, 'cadastro')"
                            class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-indigo-500 text-indigo-600">
                            Cadastro Pré-O.S
                        </button>
                        <!-- Aba Serviços de Pré-O.S -->
                        <button onclick="openTab(event, 'servicos')"
                            class="tab-link whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Serviços de Pré-O.S
                        </button>
                    </nav>
                </div>
            </div>

            <form method="POST" action="{{ $action }}" class="space-y-4">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <!-- Conteúdo das Abas -->
                <div>
                    <!-- Conteúdo da Aba Cadastro Pré-O.S -->
                    <div id="cadastro" class="tab-content">
                        @include('admin.manutencaopreordemserviconova._cadastro')
                    </div>

                    <!-- Conteúdo da Aba Serviços de Pré-O.S -->
                    <div id="servicos" class="tab-content" style="display: none;">
                        @include('admin.manutencaopreordemserviconova._servicos')
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('admin.manutencaopreordemserviconova.index') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Voltar
                    </a>

                    @if (isset($preOrdemFinalizada->id_pre_os))
                        <a href="{{ route('admin.manutencaopreordemserviconova.preventiva', $preOrdemFinalizada->id_pre_os) }}"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Gerar O.S Preventiva
                        </a>
                    @endif

                    @if (isset($preOrdemFinalizada->id_pre_os))
                        <button type="button" onclick="gerarCorretiva({{ $preOrdemFinalizada->id_pre_os }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Gerar O.S Corretiva
                        </button>
                    @endif

                    @if (isset($preOrdemFinalizada->id_pre_os))
                        <button type="button"
                            x-on:click="$store.utils.imprimirPreOs({{ $preOrdemFinalizada->id_pre_os }})"
                            :disabled="$store.utils.loading"
                            class="inline-flex items-center px-3 py-2 border border-red-300 shadow-sm text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">

                            <!-- Ícone de loading (quando carregando) -->
                            <span x-show="$store.utils.loading" class="loading-spinner mr-2"></span>
                            <!-- Ícone normal (quando não carregando) -->
                            <x-icons.pdf-doc x-show="!$store.utils.loading" class="h-4 w-4 mr-2" />

                            <!-- Texto do botão -->
                            <span x-text="$store.utils.loading ? 'Gerando...' : 'Imprimir PDF'"></span>
                        </button>

                        <a href="{{ route('admin.manutencaopreordemserviconova.historico', $preOrdemFinalizada->id_pre_os) }}"
                            class="inline-flex items-center px-3 py-2 border border-blue-300 shadow-sm text-sm leading-4 font-medium rounded-md text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Histórico Manutenções do Veículo
                        </a>

                        <button type="button" onclick="confirmaFinalizar({{ $preOrdemFinalizada->id_pre_os }})"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Finalizar Pré O.S
                        </button>
                    @endif

                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/preos/vmanutencaovencidas.js') }}"></script>
    @include('admin.manutencaopreordemserviconova._scripts')
@endpush
