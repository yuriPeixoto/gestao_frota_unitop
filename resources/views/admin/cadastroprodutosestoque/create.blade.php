<x-app-layout>
    <div>
        <div>
            <div class="bg-white  shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">


                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">Cadastrar novo Produto</h2>
                    </div>

                    <form action="{{ route('admin.cadastroprodutosestoque.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        @include('admin.cadastroprodutosestoque._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
