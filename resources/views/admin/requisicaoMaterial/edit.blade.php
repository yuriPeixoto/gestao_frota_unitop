<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Requisição de Material #{{ $requisicaoMaterial->id_solicitacao_pecas }}
            </h2>
        </div>
    </x-slot>

    <div class="bg-white p-4 text-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('admin.requisicaoMaterial.update', $requisicaoMaterial->id_solicitacao_pecas) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.requisicaoMaterial._form', ['requisicaoMaterial' => $requisicaoMaterial])
        </form>
    </div>
</x-app-layout>
