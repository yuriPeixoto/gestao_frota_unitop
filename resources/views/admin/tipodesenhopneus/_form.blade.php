<div class="space-y-6">
    <div class="bg-gray-50 p-4 rounded-lg">
        <form id="abastecimentoForm" method="POST" action="{{ $action }}" class="space-y-4">
            @csrf
            @if ($method === 'PUT')
            @method('PUT')
            @endif
            <h3 class="text-lg font-medium text-gray-900 mb-4">Desenho</h3>
            <div class="grid md:grid-cols-4 gap-4 sm:grid-cols-1">
                <div>
                    <x-forms.input name="descricao_desenho_pneu" label="Descrição"
                        value="{{ old('descricao_desenho_pneu', $tipodesenhopneus->descricao_desenho_pneu ?? '') }}"
                        required="true" />
                    @error('descricao_desenho_pneu')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-forms.input name="numero_sulcos" label="Número de Sulcos" type="number"
                        value="{{ old('numero_sulcos', $tipodesenhopneus->numero_sulcos ?? '') }}" />
                    @error('numero_sulcos')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-forms.input name="quantidade_lona_pneu" label="Quantidade Lona" type="number"
                        value="{{ old('quantidade_lona_pneu', $tipodesenhopneus->quantidade_lona_pneu ?? '') }}" />
                    @error('quantidade_lona_pneu')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <x-forms.input name="dias_calibragem" label="Dias Calibragem" type="number"
                        value="{{ old('dias_calibragem', $tipodesenhopneus->dias_calibragem ?? '') }}" />
                    @error('dias_calibragem')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3 col-span-full">
                    <a href="{{ route('admin.tipodesenhopneus.index') }}"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancelar
                    </a>

                    <!-- Botão Enviar -->
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        <span>{{ isset($tipoequipamento) ? 'Atualizar' : 'Salvar' }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>