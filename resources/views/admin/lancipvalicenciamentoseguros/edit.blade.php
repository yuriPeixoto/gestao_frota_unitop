<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Licenciamento Veiculo: {{ $licenciamentoveiculos->id_licenciamento }}
            </h2>
        </div>
    </x-slot>

    @if($errors->any())
    <div class="mb-4 bg-red-50 p-4 rounded">
        <ul class="list-disc list-inside text-red-600">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.lancipvalicenciamentoseguros.update', $licenciamentoveiculos->id_licenciamento) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.lancipvalicenciamentoseguros._form', ['lancipvalicenciamentoseguros' => $licenciamentoveiculos])
    </form>
</x-app-layout>