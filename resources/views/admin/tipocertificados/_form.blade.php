<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <form id="tipoCertificadoForm" method="POST" action="{{ $action }}" class="space-y-4">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <!-- Cabeçalho -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Tipo de Certificado</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($tipoCertificado->id_tipo_certificado ?? false)
                        <div>
                            <label for="id_tipo_certificado" class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" id="id_tipo_certificado" name="id_tipo_certificado" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $tipoCertificado->id_tipo_certificado ?? '' }}">
                        </div>
                        @endif

                        <div class="{{ $tipoCertificado->id_tipo_certificado ?? false ? '' : 'md:col-span-2' }}">
                            <label for="descricao_certificado" class="block text-sm font-medium text-gray-700">Descrição do Certificado</label>
                            <input type="text" id="descricao_certificado" name="descricao_certificado" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('descricao_certificado', $tipoCertificado->descricao_certificado ?? '') }}">
                        </div>

                        <div class="md:col-span-2">
                            <label for="orgao_certificado" class="block text-sm font-medium text-gray-700">Órgão Certificador</label>
                            <input type="text" id="orgao_certificado" name="orgao_certificado" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('orgao_certificado', $tipoCertificado->orgao_certificado ?? '') }}">
                        </div>
                    </div>

                    @if($tipoCertificado->id_tipo_certificado ?? false)
                    <!-- Informações de Criação/Edição (apenas para edição) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data de Inclusão</label>
                            <input type="text" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $tipoCertificado->data_inclusao ? $tipoCertificado->data_inclusao->format('d/m/Y H:i') : '' }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Data de Alteração</label>
                            <input type="text" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $tipoCertificado->data_alteracao ? $tipoCertificado->data_alteracao->format('d/m/Y H:i') : 'Nunca alterado' }}">
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Botões de Ação -->
                <div class="flex justify-end space-x-4 mt-6">
                    <a href="{{ route('admin.tipocertificados.index') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Voltar
                    </a>

                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>