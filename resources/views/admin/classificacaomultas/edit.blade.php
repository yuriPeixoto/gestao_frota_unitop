<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Classificação Multa: {{ $classificacaoMultas->descricao_multa }}
            </h2>
        </div>
    </x-slot>

    <form action="{{ route('admin.classificacaomultas.update', $classificacaoMultas->id_classificacao_multa ) }}"
        method="POST">
        @csrf
        @method('PUT')

        @include('admin.classificacaomultas._form', ['classificacaoMultas' => $classificacaoMultas])
    </form>
</x-app-layout>