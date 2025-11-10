<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Cadastrar Veículos') }}
            </h2>
        </div>
    </x-slot>
    <div>
        <div>
            <div class="bg-white  shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.veiculos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        @include('admin.veiculos._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>