<x-app-layout>
    <div>
        <div class="mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
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
                    <form action="{{ route('admin.licenciamentoveiculos.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('POST')

                        @include('admin.licenciamentoveiculos._form')
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>