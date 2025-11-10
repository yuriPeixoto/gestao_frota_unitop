<x-app-layout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl text-black font-bold">Checklist de Recebimento do Fornecedor</h2>
            </div>

            <form action="{{ route('admin.manutencaopneusentrada.checklist.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('POST')

                @include('admin.manutencaopneusentrada._checklistform')
            </form>
        </div>
    </div>
</x-app-layout>
