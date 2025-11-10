<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Veiculo: {{ $veiculo->placa }}
            </h2>
        </div>
    </x-slot>


    <form action="{{ route('admin.veiculos.update', $veiculo->id_veiculo) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="p-6 bg-white border-b border-gray-200 shadow-sm sm:rounded-lg">
            @include('admin.veiculos._form', ['veiculo' => $veiculo])
        </div>
    </form>
</x-app-layout>
