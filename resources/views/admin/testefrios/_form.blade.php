<div>
    @if (session('notification'))
        <x-notification :notification="session('notification')" />
    @endif
    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Teste de Frio</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @if (isset($testefrio) && $testefrio->id_certificado_veiculo)
            <div>
                <label for="id_certificado_veiculo" class="block text-sm font-medium text-gray-700">Código</label>
                <input type="text" id="id_certificado_veiculo" name="id_certificado_veiculo" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $testefrio->id_certificado_veiculo ?? '' }}">
            </div>
        @endif
        <div>
            <x-forms.smart-select name="id_tipo_certificado" label="Tipo de Certificado"
                placeholder="Selecione o tipo..." :options="$tiposCertificados" :selected="old('id_tipo_certificado', $testefrio->id_tipo_certificado ?? 1)" asyncSearch="false"
                required="true" />
        </div>

        <div>
            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..." :options="$veiculosFrequentes"
                :searchUrl="route('admin.api.veiculos.search')" :selected="old('id_veiculo', $testefrio->id_veiculo ?? '')" asyncSearch="true" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <label for="chassis" class="block text-sm font-medium text-gray-700">
                Chassi:
            </label>
            <input type="text" name="chassi" id="chassi" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>
        <div>
            <label for="filial" class="block text-sm font-medium text-gray-700">
                Filial:
            </label>
            <input type="text" name="filial" id="filial" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>
        <div>
            <label for="renavam" class="block text-sm font-medium text-gray-700">
                Renavam:
            </label>
            <input type="text" name="renavam" id="renavam" readonly
                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
        </div>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <x-forms.smart-select name="id_uf" label="UF" placeholder="Selecione a UF..." :options="$estados"
                :selected="old('id_uf', $testefrio->id_uf ?? '')" asyncSearch="false" />
        </div>
        <div>
            <label for="numero_certificado" class="block text-sm font-medium text-gray-700">
                Nº do Certificado <span class="text-red-500">*</span>
            </label>
            <input type="text" id="numero_certificado" name="numero_certificado" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('numero_certificado', $testefrio->numero_certificado ?? '') }}">
        </div>
        <div>
            <label for="data_certificacao" class="block text-sm font-medium text-gray-700">
                Data do Certificado <span class="text-red-500">*</span>
            </label>
            <input type="date" id="data_certificacao" name="data_certificacao" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('data_certificacao', isset($testefrio->data_certificacao) ? date('Y-m-d', strtotime($testefrio->data_certificacao)) : '') }}">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
            <label for="data_vencimento" class="block text-sm font-medium text-gray-700">
                Data de Vencimento <span class="text-red-500">*</span>
            </label>
            <input type="date" id="data_vencimento" name="data_vencimento" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('data_vencimento', isset($testefrio->data_vencimento) ? date('Y-m-d', strtotime($testefrio->data_vencimento)) : '') }}">
        </div>

        <div>
            <label for="valor_certificado" class="block text-sm font-medium text-gray-700">
                Valor do Certificado <span class="text-red-500">*</span>
            </label>
            <input type="text" id="valor_certificado" name="valor_certificado" step="0.01" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                value="{{ old('valor_certificado', $testefrio->valor_certificado ?? '') }}">
        </div>
        <div>
            <label for="arquivo" class="block text-sm font-medium text-gray-700">
                Arquivo do Laudo
            </label>
            <input type="file" id="arquivo" name="arquivo" accept=".pdf,.jpg,.jpeg,.png"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <p class="mt-1 text-sm text-gray-500">
                Formatos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 10MB.
            </p>

            @if (isset($testefrio) && $testefrio->caminho_arquivo)
                <div class="mt-2">
                    <a href="{{ asset('storage/' . $testefrio->caminho_arquivo) }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Visualizar arquivo atual
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>


@push('scripts')
    @include('admin.testefrios._scripts')
@endpush
