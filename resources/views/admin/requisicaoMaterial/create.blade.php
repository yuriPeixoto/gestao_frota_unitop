<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nova Requisição de Material
            </h2>
        </div>
    </x-slot>

    <div class="bg-white p-4 text-gray-900 overflow-hidden shadow-sm sm:rounded-lg">
        <form action="{{ route('admin.requisicaoMaterial.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('POST')
            @include('admin.requisicaoMaterial._form')
        </form>
    </div>
</x-app-layout>
