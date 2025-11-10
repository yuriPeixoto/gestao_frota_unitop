<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Classificação Multa</h3>
        <div class="grid grid-flow-row-dense grid-cols-3 grid-rows-3 gap-2">
            {{-- Descrição Classificação Multas --}}

            <div class="col-span-2">
                <x-bladewind::input label="Descrição Multa" name="descricao_multa" error_message="descricao_multa"
                    selected_value="{{ old('descricao_multa', $classificacaoMultas->descricao_multa ?? '') }}" />
                @error('descricao_multa')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            {{-- Número de pontos --}}
            <div>
                <x-bladewind::input label="Número de Pontos" name="pontos" error_message="pontos"
                    selected_value="{{ old('pontos', $classificacaoMultas->pontos ?? '') }}" />
                @error('pontos')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3 col-span-3">
                <a href=" {{ route('admin.classificacaomultas.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($classificacaoMultas) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>
    </div>
</div>