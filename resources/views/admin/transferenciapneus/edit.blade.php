<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edição de Transferência de Pneus
            </h2>
        </div>
    </x-slot>


    <form action="{{ route('admin.transferenciapneus.update', $transferenciaPneus->id_transferencia_pneus) }}"
        method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('admin.transferenciapneus._form', ['transferenciaPneus' => $transferenciaPneus])
    </form>
    @push('scripts')
        <script src="{{ asset('js/transferencia_pneus/transferencia_pneus.js') }}"></script>
        @include('admin.transferenciapneus._scripts')
    @endpush
</x-app-layout>
