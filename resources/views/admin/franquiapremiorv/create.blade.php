<x-app-layout>
    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl text-black font-bold">Cadastrar Franquia</h2>
                        <a href="{{ route('admin.franquiapremiorv.index') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                            Voltar
                        </a>
                    </div>

                    <form action="{{ route('admin.franquiapremiorv.store') }}" method="POST">
                        @csrf
                        @method('POST')

                        @include('admin.franquiapremiorv._form')
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>