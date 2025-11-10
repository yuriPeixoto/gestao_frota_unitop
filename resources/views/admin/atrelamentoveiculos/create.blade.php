<x-app-layout>
    <div>
        <div>
            <div class="bg-white  shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Cadastrar Atrelamentos</h2>
                        <a href="{{ route('admin.atrelamentoveiculos.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Voltar
                        </a>
                    </div>

                    <form action="{{ route('admin.atrelamentoveiculos.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        @include('admin.atrelamentoveiculos._form', ['veiculos' => $veiculos])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>