<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-black leading-tight">
                Contagem de Pneus
            </h2>
        </div>
    </x-slot>
    <div class="py-22">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="bg-white border-b border-gray-200">

                <form action="{{ route('admin.contagempneus.store') }}" method="POST">
                    @csrf
                    @method('POST')

                    @include('admin.contagempneus._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
