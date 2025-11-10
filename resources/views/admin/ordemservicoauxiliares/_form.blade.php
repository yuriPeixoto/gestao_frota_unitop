@if (session('error'))
    <div class="mb-4 bg-red-50 p-4 rounded">
        <p class="text-red-600">{{ session('error') }}</p>
    </div>
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form id="ordemServicoAuxiliarForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="mx-auto">
                        <!-- Botões das abas -->
                        <div class="flex space-x-1">
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba1')">
                                Cadastro
                            </button>
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba2')">
                                Veículos
                            </button>
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba3')">
                                Manutenções
                            </button>
                            <button type="button"
                                class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                onclick="openTab(event, 'Aba4')">
                                Serviços
                            </button>
                        </div>
                    </div>

                    <!-- Conteúdo das abas -->
                    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicoauxiliares._cadastro')
                    </div>

                    <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicoauxiliares._veiculos')
                    </div>

                    <div id="Aba3" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicoauxiliares._manutencao')
                    </div>

                    <div id="Aba4" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                        @include('admin.ordemservicoauxiliares._servicos')
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-rigth space-x-4 mt-6">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                        <a href="{{ route('admin.ordemservicoauxiliares.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>
                        <button type="button" onclick="gerarOSPreventivas()"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-icons.gear class="text-cyan-500 w-4, h-4 mr-2" />
                            Gerar O.S. Preventivas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    @include('admin.ordemservicoauxiliares._scripts')
    <script src="{{ asset('js/visualizar-os.js') }}"></script>
    <script src="{{ asset('js/osAuxiliar_Veiculo.js') }}"></script>
    <script src="{{ asset('js/osAuxiliar_Manutencao.js') }}"></script>
    <script src="{{ asset('js/osAuxiliar_Servicos.js') }}"></script>
@endpush
