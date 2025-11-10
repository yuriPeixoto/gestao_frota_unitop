@if (session('error'))
<div class="alert-danger alert">{{ session('error') }}</div>
@endif

<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <form action="{{ $action ?? route('admin.premiosuperacao.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-forms.input label="Usuário:" name="id_user"
                        value="{{ old('id_user', $franquia->id_user ?? '') }}" readonly />
                </div>

                <div>
                    <x-forms.input type="date" name="data_inicial" label="Data Inicial:"
                        value="{{ request('data_inicial') }}" />
                </div>

                <div>
                    <x-forms.input type="date" name="data_final" label="Data Final:"
                        value="{{ request('data_final') }}" />
                </div>
            </div>

            <br />

            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.premiosuperacao.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($franquia) && $franquia?->id ? 'Atualizar' : 'Salvar' }}

                </button>
            </div>

        </form>
    </div>
</div>
@push('scripts')
@include('admin.premiosuperacao._scripts')
@endpush