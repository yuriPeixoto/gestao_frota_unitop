<div class="space-y-6">
    <!-- Formulário de reprocessamento ATS -->
    <form id="ats-form" action="{{ route('admin.reprocessar.ats') }}" method="POST"
        class="bg-white p-4 rounded-md shadow-sm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-input-label for="data_inicial" value="Data Início" />
                <x-text-input id="data_inicial" type="date" name="data_inicial"
                    :value="old('data_inicial', date('Y-m-d', strtotime('-7 days')))" class="mt-1 block w-full"
                    required />
                <x-input-error :messages="$errors->get('data_inicial')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="data_final" value="Data Fim" />
                <x-text-input id="data_final" type="date" name="data_final" :value="old('data_final', date('Y-m-d'))"
                    class="mt-1 block w-full" required />
                <x-input-error :messages="$errors->get('data_final')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="id_veiculo" value="Placa" />
                <select id="id_veiculo" name="id_veiculo"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach ($veiculos as $veiculo)
                    <option value="{{ $veiculo['value'] }}">{{ $veiculo['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <x-input-label for="bomba" value="Bomba" />
                <select id="bomba" name="bomba"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach ($bombas as $bomba)
                    <option value="{{ $bomba['value'] }}">{{ $bomba['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 bg-gray-50 p-4 rounded-md">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Informações Importantes</h3>
            <ul class="ml-5 list-disc text-xs text-gray-600 space-y-1">
                <li>O reprocessamento ATS está temporariamente desabilitado.</li>
                <li>As datas de início e fim são obrigatórias.</li>
                <li>Filtre por Placa e/ou Bomba específica para um processamento mais direcionado.</li>
                <li>O reprocessamento ocorre apenas para abastecimentos não tratados.</li>
                <li>Abastecimentos já tratados não serão modificados na plataforma.</li>
            </ul>
        </div>

        <div class="flex justify-end mt-4">
            <x-primary-button type="submit" class="ml-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                        clip-rule="evenodd" />
                </svg>
                Processar
            </x-primary-button>
        </div>
    </form>
</div>