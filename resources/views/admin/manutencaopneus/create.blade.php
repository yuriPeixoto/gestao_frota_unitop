<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Cadastro de Manutenção do Pneu
            </h2>
        </div>
    </x-slot>
    <div class="p-12 bg-white rounded-md">
        <form id="form-manutencao" action="{{ route('admin.manutencaopneus.store') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('POST')
            @include('admin.manutencaopneus._form')
        </form>
    </div>
</x-app-layout>