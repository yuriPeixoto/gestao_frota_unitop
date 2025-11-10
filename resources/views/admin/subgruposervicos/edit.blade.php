<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Grupo: {{ $subgruposervico->descricao_subgrupo }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.subgruposervicos.update', $subgruposervico->id_subgrupo) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.subgruposervicos._form', ['subgruposervico' => $subgruposervico])
    </form>
</x-app-layout>