<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div x-data="sinistrosForm()">
                <form id="sinistrosForm" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif
                    <div class="space-y-6">
                        <!-- Informações do Usuário -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            @php
                                $bloquear = false;
                                if (
                                    isset($sinistro) &&
                                    ($sinistro->status == 'FINALIZADA' || $sinistro->status == 'Finalizada')
                                ) {
                                    $bloquear = true;
                                }
                            @endphp
                            <div>
                                <!-- Cabeçalho -->
                                <div class="mx-auto mt-7">
                                    <!-- Botões das abas -->
                                    <div class="flex space-x-1 border-b border-gray-300">
                                        <button type="button"
                                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                            x-on:click="openTab('Aba1')">
                                            Dados Sinistro
                                        </button>
                                        <button type="button"
                                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                            x-on:click="openTab('Aba2')">
                                            Dados do Processo
                                        </button>
                                        <button type="button"
                                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                            x-on:click="openTab('Aba3')">
                                            Documentos
                                        </button>
                                        <button type="button"
                                            class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                            x-on:click="openTab('Aba4')">
                                            Dados dos Envolvidos
                                        </button>
                                    </div>

                                    <!-- Conteúdo das abas -->
                                    <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                                        @include('admin.sinistros._dados_sinistros')
                                    </div>

                                    <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                                        @include('admin.sinistros._dados_processo')
                                    </div>

                                    <div id="Aba3" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                                        @include('admin.sinistros._documentos')
                                    </div>

                                    <div id="Aba4" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                                        @include('admin.sinistros._dados_envolvidos')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Botões -->
                    <div class="flex justify-end space-x-3 col-span-full">
                        <a href="{{ route('admin.sinistros.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ $bloquear ? 'Voltar' : 'Cancelar' }}
                        </a>
                        <button type="submit" {{ $bloquear ? 'disabled' : '' }}
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ isset($empresa) ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.appConfig = {
            bloquear: @json($bloquear)
        };
    </script>
    @include('admin.sinistros._scripts')
@endpush
