<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Empresa: {{ $empresa->razaosocial }}
            </h2>
        </div>
    </x-slot>


    <form action="{{ route('admin.empresas.update', $empresa->idempresa ) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.empresas._form', ['empresa' => $empresa])
    </form>
</x-app-layout>