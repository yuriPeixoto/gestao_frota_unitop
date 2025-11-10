<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Saída de Pneus
            </h2>
        </div>
    </x-slot>

    <div class="bg-white p-4 text-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('admin.requisicaopneusvendassaida.update', $requisicaoPneus->id_requisicao_pneu) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.requisicaopneusvendassaida._form', ['requisicaoPneus' => $requisicaoPneus])
        </form>
    </div>
</x-app-layout>
