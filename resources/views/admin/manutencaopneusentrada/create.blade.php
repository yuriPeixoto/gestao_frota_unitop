<x-app-layout>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl text-black font-bold">Cadastro de Entrada de Manutenção do Pneu</h2>
            </div>

            <form action="{{ route('admin.manutencaopneusentrada.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                @include('admin.manutencaopneusentrada._form')
            </form>
        </div>
    </div>
</x-app-layout>
