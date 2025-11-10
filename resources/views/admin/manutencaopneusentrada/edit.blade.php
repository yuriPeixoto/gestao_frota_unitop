<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-black leading-tight">
                Editar Entrada Manutenção de Pneu: {{ $manutencaoPneusEntrada->id_manutencao_entrada }}
            </h2>
        </div>
    </x-slot>

    <div class="w-full">
        <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
            <form action="{{ route('admin.manutencaopneusentrada.update', $manutencaoPneusEntrada->id_manutencao_entrada) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
        
                @include('admin.manutencaopneusentrada._form', ['manutencaoPneusEntrada' => $manutencaoPneusEntrada])
            </form>
        </div>
    </div>

</x-app-layout>