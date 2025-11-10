<div class="space-y-6">
    @if ($errors->any())
        <div class="mb-4 bg-red-50 p-4 rounded">
            <ul class="list-disc list-inside text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-4 bg-white border-b border-gray-200">
            <div class="p-6 bg-white border-b border-gray-200">
                <form id="descartePneuForm" method="POST" action="{{ $action }}" class="space-y-4"
                    enctype="multipart/form-data">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-2 items-center ">

                        <!-- Código -->
                        <div>
                            <label for="id_descarte_pneu" class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" id="id_descarte_pneu" name="id_descarte_pneu" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $descartePneus->id_descarte_pneu ?? '' }}">
                        </div>

                        <!-- Nº Fogo -->
                        <x-forms.smart-select name="id_pneu" label="Nª Fogo" placeholder="Selecione o Nª Fogo..."
                            :options="$pneu" :selected="old('id_pneu', $descartePneus->id_pneu ?? '')" />

                    </div>

                    <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3 items-center mt-4">
                        <!-- Tipo Descarte -->
                        <x-forms.smart-select name="id_tipo_descarte" label="Tipo de descarte"
                            placeholder="Selecione o Tipo de descarte..." :options="$tipodescarte" :searchUrl="route('admin.api.tipodescarte.search')"
                            :selected="old('id_tipo_descarte', $descartePneus->id_tipo_descarte ?? '')" asyncSearch="true" />

                        <!-- Valor Descarte -->
                        <div>
                            <label for="valor_venda_pneu" class="block text-sm font-medium text-gray-700">
                                Valor de venda do Pneu
                            </label>
                            <input type="text" oninput="formatarMoedaBrasileira(this)" id="valor_venda_pneu"
                                name="valor_venda_pneu" step="0.01" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ old('valor_venda_pneu', $descartePneus->valor_venda_pneu ?? '') }}">
                        </div>

                        <!-- Anexo Laudo -->
                        <div class="mt-4">
                            <label for="nome_arquivo" class="block text-sm font-medium text-gray-700">
                                Arquivo do Laudo
                            </label>
                            <input type="file" id="nome_arquivo" name="nome_arquivo" accept=".pdf,.jpg,.jpeg,.png"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">
                                Formatos aceitos: PDF, JPG, JPEG, PNG. Tamanho máximo: 2MB.
                            </p>
                            @if (isset($descartePneus) && $descartePneus->nome_arquivo)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $descartePneus->nome_arquivo) }}" target="_blank"
                                        class="inline-flex items-center px-2 py-1 border border-transparent text-xs leading-4 font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Visualizar Anexo
                                    </a>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div class="grid gap-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-1 items-center mt-4">
                        <div>
                            <label for="id_ajuste_km_abastecimento"
                                class="block text-sm font-medium text-gray-700">Motivo
                                Descarte</label>
                            <textarea id="observacao" name="observacao" rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $descartePneus->observacao ?? '') }}</textarea>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="flex justify-end space-x-3 col-span-full mt-3">
                        <a href="{{ route('admin.descartepneus.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Cancelar
                        </a>
                        <button type="submit" id="submit-form"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
    @include('admin.descartepneus._scripts')
@endpush
