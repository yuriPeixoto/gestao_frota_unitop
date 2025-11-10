<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Atrelamento: 
            </h2>
        </div>
    </x-slot>


    <form action="{{ route('admin.atrelamentoveiculos.update', $registroAtrelamento->id_atrelamento) }}" method="POST"
        enctype="multipart/form-data">
        @csrf
        @method('PUT')
    
        @include('admin.atrelamentoVeiculos._form', ['veiculos' => $veiculos, 'carretas' => $carretas, 'registroAtrelamento' => $registroAtrelamento])
    </form>
</x-app-layout>