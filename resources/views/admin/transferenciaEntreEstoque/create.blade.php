<x-app-layout>
    <div>
        <div>
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6">

                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-2xl font-bold text-black">Confirmar Recebimento</h2>
                    </div>

                    <form action="{{ route('admin.transferenciaEntreEstoque.storeProduto', $id) }}" method="POST">
                        @csrf
                        @method('POST')

                        @include('admin.transferenciaEntreEstoque._form')
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-app-layout>
