<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Modelo de Veículo: {{ $modeloveiculo->descricao_modelo_veiculo }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.modeloveiculos.update', $modeloveiculo->id_modelo_veiculo) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.modeloveiculos._form', ['modeloveiculo' => $modeloveiculo])
    </form>
</x-app-layout>