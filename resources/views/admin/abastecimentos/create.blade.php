<x-app-layout>
    <div>
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($errors->any())
                    <div class="mb-4 bg-red-50 p-4 rounded">
                        <ul class="list-disc list-inside text-red-600">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Novo Abastecimento</h2>
                        <a href="{{ route('admin.abastecimentos.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Voltar
                        </a>
                    </div>

                    <form action="{{ route('admin.abastecimentos.store') }}" method="POST">
                        @csrf
                        @method('POST')

                        @include('admin.abastecimentos._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>