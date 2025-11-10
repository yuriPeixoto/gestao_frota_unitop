<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Produto: {{ $cadastroProdutos->id_produto }}
            </h2>
        </div>
    </x-slot>

    <div class="p-6 bg-white border-b border-gray-200">
        <form action="{{ route('admin.cadastroprodutosestoque.update', $cadastroProdutos->id_produto) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @include('admin.cadastroprodutosestoque._form', ['cadastroProdutos' => $cadastroProdutos])
        </form>
    </div>
</x-app-layout>
