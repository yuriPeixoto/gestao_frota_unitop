<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Manutenção') }}
            </h2>
        </div>
    </x-slot>
    @if (session('error'))
    <div class="alert-danger alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
    <div class="mb-4 rounded bg-red-50 p-4">
        <ul class="list-inside list-disc text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            <form method="POST"
                action="{{ route('admin.manutencaopremio.updatemotorista', $distancia->id_distancia_sem) }}">
                @csrf



                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <x-forms.smart-select name="idmotorista" label="Motorista:" :options="$motorista" />
                    <x-forms.input name="id_veiculo" label="Veículo:" value="{{ $distancia->veiculo->placa }}"
                        readonly />
                    <x-forms.input name="subcategoria" label="Subcategoria:" value="{{ $distancia->subcategoria }}"
                        readonly />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <x-forms.input name="km_sem_mot" label="KM:" value="{{ $distancia->km_sem_mot }}" />
                    <x-forms.input name="media" label="Média:" type="number" required />
                </div>

                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.manutencaopremio.index') }}"
                        class="px-4 py-2 text-sm font-medium text-gray-700 uppercase bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white uppercase bg-indigo-600 border border-transparent rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        {{ isset($veiculo) && $veiculo?->id ? 'Atualizar' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>