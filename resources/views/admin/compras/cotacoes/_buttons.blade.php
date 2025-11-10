<a href="{{ route('admin.compras.cotacoes.index') }}"
    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
    <x-icons.arrow-back class="mr-2 text-cyan-500" />
    Voltar
</a>

<button type="submit" form="form-cotacao"
    class="inline-flex items-center whitespace-nowrap rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
    <x-icons.check class="mr-2 h-4 w-4" />
    Salvar
</button>

<button type="button" onclick="enviarCotacoes()"
    class="inline-flex items-center whitespace-nowrap rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
    <x-icons.heroicon-s-arrow-up class="mr-2 h-4 w-4 text-cyan-500" />
    Enviar Cotações
</button>

{{-- @push('scripts')
    @include('admin.compras.cotacoes._scripts')
@endpush --}}
