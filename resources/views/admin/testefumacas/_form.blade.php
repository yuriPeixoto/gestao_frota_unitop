<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="space-y-4">
                <!-- Cabeçalho -->
                <div class="bg-gray-50 p-4 rounded-lg mb-6">
                    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados do Teste de Opacidade</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="id_teste_fumaca" class="block text-sm font-medium text-gray-700">Código</label>
                            <input type="text" id="id_teste_fumaca" name="id_teste_fumaca" readonly
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                value="{{ $testeFumaca->id_teste_fumaca ?? '' }}">
                        </div>

                        <div>
                            <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione o veículo..."
                                :options="$veiculos" :searchUrl="route('admin.api.veiculos.search')" :selected="old('id_veiculo', $testeFumaca->id_veiculo ?? '')" asyncSearch="true"
                                required="true" />
                        </div>

                        <div>
                            <x-forms.input name="prefixo" label="Filial" disabled :value="old('prefixo', $testeFumaca->prefixo ?? '')" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-forms.input type="date" name="data_de_realizacao" label="Data de Realização"
                                :value="old(
                                    'data_de_realizacao',
                                    isset($testeFumaca) && $testeFumaca->data_de_realizacao
                                        ? $testeFumaca->data_de_realizacao->format('Y-m-d')
                                        : '',
                                )" required />
                        </div>

                        <div>
                            <x-forms.input type="date" name="data_de_vencimento" label="Data de Vencimento"
                                :value="old(
                                    'data_de_vencimento',
                                    isset($testeFumaca) && $testeFumaca->data_de_vencimento
                                        ? $testeFumaca->data_de_vencimento->format('Y-m-d')
                                        : '',
                                )" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-forms.input name="kmaximo" label="K Máximo (m⁻¹)" :value="old('kmaximo', $testeFumaca->kmaximo ?? '')" required />
                        </div>

                        <div>
                            <x-forms.input name="kmedido" label="K Médio (m⁻¹)" :value="old('kmedido', $testeFumaca->kmedido ?? '')" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="resultado" class="block text-sm font-medium text-gray-700">Resultado</label>
                            <select name="resultado" id="resultado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                required>
                                <option value="">Selecione...</option>
                                <option value="Aprovado"
                                    {{ old('resultado', $testeFumaca->resultado ?? '') == 'Aprovado' ? 'selected' : '' }}>
                                    Aprovado</option>
                                <option value="Reprovado"
                                    {{ old('resultado', $testeFumaca->resultado ?? '') == 'Reprovado' ? 'selected' : '' }}>
                                    Reprovado</option>
                            </select>
                        </div>

                        <div>
                            <x-forms.input name="transportador" label="Transportador" :value="old('transportador', $testeFumaca->transportador ?? '')" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <x-forms.input name="tecnico" label="Técnico" :value="old('tecnico', $testeFumaca->tecnico ?? '')" required />
                        </div>

                        <div>
                            <label for="anexo_laudo" class="block text-sm font-medium text-gray-700">Anexo Laudo</label>
                            <input type="file" id="anexo_laudo" name="anexo_laudo"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                accept=".pdf,.jpg,.jpeg,.png">

                            @if (isset($testeFumaca) && $testeFumaca->anexo_laudo)
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $testeFumaca->anexo_laudo) }}" target="_blank"
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
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="flex justify-end space-x-4 mt-6">
                <a href="{{ route('admin.testefumacas.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancelar
                </a>

                <button type="submit"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ isset($testeFumaca) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
    @include('admin.testefumacas._scripts')
@endpush
