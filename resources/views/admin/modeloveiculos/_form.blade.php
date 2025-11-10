<div class="space-y-6">
    <!-- Informações do Usuário -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Modelo Veículo</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            {{-- Nome --}}
            <div>
                <label for="marca" class="block text-sm font-medium text-gray-700">Marca</label>
                <select name="marca" id="marca"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="{{ old('marca', $modeloveiculo->marca ?? '') }}">{{ old('marca',
                        $modeloveiculo->marca ?? 'Selecione') }}</option>
                </select>
                @error('marca')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="descricao_modelo_veiculo" class="block text-sm font-medium text-gray-700">Modelo</label>
                <input type="text" name="descricao_modelo_veiculo" id="descricao_modelo_veiculo"
                    value="{{ old('descricao_modelo_veiculo', $modeloveiculo->descricao_modelo_veiculo ?? '') }}"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('descricao_modelo_veiculo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ano" class="block text-sm font-medium text-gray-700">Ano</label>
                <select name="ano" id="ano"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione o ano</option>
                    @php
                    $currentYear = date('Y');
                    @endphp
                    @for ($year = 1900; $year <= $currentYear; $year++) <option value="{{ $year }}" {{ old('ano',
                        $modeloveiculo->ano ?? '') == $year ? 'selected' : '' }}>
                        {{ $year }}
                        </option>
                        @endfor
                </select>
                @error('ano')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="multicombustivel" class="block text-sm font-medium text-gray-700">Multicombustível</label>
                <select name="multicombustivel" id="multicombustivel"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione</option>
                    <option value="1" {{ old('multicombustivel', $modeloveiculo->multicombustivel ?? '') == '1' ?
                        'selected' : '' }}>
                        Sim
                    </option>
                    <option value="0" {{ old('multicombustivel', $modeloveiculo->multicombustivel ?? '') == '0' ?
                        'selected' : '' }}>
                        Não
                    </option>
                </select>
                @error('multicombustivel')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ativo" class="block text-sm font-medium text-gray-700">Ativo</label>
                <select name="ativo" id="ativo"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Selecione</option>
                    <option value="1" {{ old('ativo', $modeloveiculo->ativo ?? '') == '1' ? 'selected' : '' }}>
                        Sim
                    </option>
                    <option value="0" {{ old('ativo', $modeloveiculo->ativo ?? '') == '0' ? 'selected' : '' }}>
                        Não
                    </option>
                </select>
                @error('ativo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Botões -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.modeloveiculos.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($tipoveiculo) ? 'Atualizar' : 'Salvar' }}
                </button>
            </div>
        </div>

        {{-- Buscar marcas diretamente da FIPE --}}
        <script>
            document.addEventListener('DOMContentLoaded', async () => {
                const selectMarca = document.getElementById('marca');
                const url = 'https://parallelum.com.br/fipe/api/v1/caminhoes/marcas'; // Exemplo de endpoint FIPE
        
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error('Erro ao buscar marcas');
                    }
                    const marcas = await response.json();
        
                    marcas.forEach(marca => {
                        const option = document.createElement('option');
                        option.value = marca.nome; // Substitua por `marca.nome` se preferir.
                        option.textContent = marca.nome;
                        selectMarca.appendChild(option);
                    });
                } catch (error) {
                    console.error('Erro ao carregar marcas:', error);
                }
            });
        </script>