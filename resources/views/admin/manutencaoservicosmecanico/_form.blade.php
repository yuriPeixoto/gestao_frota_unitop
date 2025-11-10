@if (session('error'))
    <div class="mb-4 bg-red-50 p-4 rounded">
        <p class="text-red-600">{{ session('error') }}</p>
    </div>
@elseif(session('info'))
    <div class="mb-4 bg-yellow-50 p-4 rounded">
        <p class="text-yellow-600">{{ session('info') }}</p>
    </div>
@elseif(session('success'))
    <div class="mb-4 bg-green-50 p-4 rounded">
        <p class="text-green-600">{{ session('success') }}</p>
    </div>
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                        @method('PUT')
                    @endif

                    <!-- Cabeçalho -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h3 class="text-lg font-medium mb-4 text-gray-800">Vincular Mecânico</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-forms.input name="id_servico_mecanico" label="Cód. Serviço Mecânico" disabled
                                    :value="$manutancaoServicoMec->id_servico_mecanico ?? ''" />
                            </div>

                            <div>
                                <x-forms.smart-select name="id_user_mecanico" label="Mecânico"
                                    placeholder="Selecione o Mecânico..." :options="$mecanicos" :selected="old('id_user_mecanico', $manutancaoServicoMec->id_user_mecanico ?? '')" />
                            </div>

                            <div>
                                <label for="data_inicial_servicos"
                                    class="block text-sm font-medium text-gray-700">Data/Hora Inicial</label>
                                <input type="datetime-local" id="data_inicial_servicos" name="data_inicial_servicos"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $manutancaoServicoMec->data_inicial_servicos ?? '' }}">
                            </div>
                        </div>
                        {{-- input para enviar o id_servico_mecanico para o backend --}}
                        <input type="hidden" name="id" value="{{ $manutancaoServicoMec->id_servico_mecanico }}">
                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">

                        <a href="{{ route('admin.manutencaoservicosmecanico.index') }}"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Voltar
                        </a>

                        <button type="submit"
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $method === 'PUT' ? 'Iniciar Serviço' : 'Salvar' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
