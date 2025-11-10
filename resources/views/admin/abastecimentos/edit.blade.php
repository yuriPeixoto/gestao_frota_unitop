<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Sinistro: {{ $sinistro->id_sinistro }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.sinistros.update', $sinistro->id_sinistro) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.sinistros._form', ['sinistros' => $sinistro])
    </form>
</x-app-layout>
