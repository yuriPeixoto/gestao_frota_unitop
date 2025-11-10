<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form action="{{ $action ?? route('admin.jornadaferiado.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-forms.input label="Cód. Feriado:" name="id" value="{{ old('id', $feriado->id ?? '') }}"
                        readonly />
                </div>
                <div>
                    <x-forms.input label="Descrição Feriado:" name="descricao"
                        value="{{ old('descricao', $feriado->descricao ?? '') }}" />
                </div>
                <div>
                    <x-forms.input label="Tipo:" name="tipo" value="{{ old('tipo', $feriado->tipo ?? '') }}" />
                </div>
            </div>

            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.jornadaferiado.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($feriado) && $feriado?->id ? 'Atualizar' : 'Salvar' }}

                </button>
            </div>
        </form>
    </div>
</div>
@push('scripts')
@include('admin.jornadaferiado._scripts')
@endpush