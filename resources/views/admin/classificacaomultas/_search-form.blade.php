{{-- @if (session('error'))
<div class="alert-danger alert">{{ session('error') }}</div>
@endif --}}

@if ($errors->any())
<div class="mb-4 rounded bg-red-50 p-4">
    <ul class="list-inside list-disc text-red-600">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<form method="GET" action=" {{ route('admin.classificacaomultas.index')}}" class="space-y-4">
    {{-- Linha 1 --}}
    <div class="flex w-full gap-3">
        <div class="w-full">
            <x-forms.input name="id_classificacao_multa" label="Código" />
        </div>

        {{-- Botões --}}
        <div class="flex justify-between mt-4">
            <div>
                <div class="flex space-x-2">
                    <button type="submit"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <x-icons.magnifying-glass class="h-4 w-4 mr-2" />
                        Buscar
                    </button>

                    <a href="{{ route('admin.compras.lancamento-notas.index') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <x-icons.trash class="h-4 w-4 mr-2" />
                        Limpar
                    </a>
                </div>
            </div>
        </div>
</form>