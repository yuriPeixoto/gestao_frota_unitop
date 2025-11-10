<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-black leading-tight">
                Editar Contagem de Pneu : {{ $contagemPneus->id_contagem_pneu }}
            </h2>
        </div>
    </x-slot>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="flex justify-between items-center">
                        <h2 class="text-2xl text-black font-bold"></h2>
                        <a href="{{ route('admin.contagempneus.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Voltar
                        </a>
                    </div>

                    <form action="{{ route('admin.contagempneus.update', $contagemPneus->id_contagem_pneu) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @include('admin.contagempneus._form', ['contagemPneus' => $contagemPneus])
                    </form>
                </div>
            </div>
        </div>
</x-app-layout>
