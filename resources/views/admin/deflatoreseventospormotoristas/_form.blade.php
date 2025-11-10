@if (session('error'))
<div class="mb-4 bg-red-50 p-4 rounded">
    {{ session('error') }}
</div>
@elseif(session('success'))
<div class="mb-4 bg-green-50 p-4 rounded">
    {{ session('success') }}
</div>
@elseif(session('warning'))
<div class="mb-4 bg-yellow-50 p-4 rounded">
    {{ session('warning') }}
</div>
@elseif(session('info'))
<div class="mb-4 bg-blue-50 p-4 rounded">
    {{ session('info') }}
</div>
@elseif(session('status'))
<div class="mb-4 bg-blue-50 p-4 rounded">
    {{ session('status') }}
</div>
@elseif(session('message'))
<div class="mb-4 bg-blue-50 p-4 rounded">
    {{ session('message') }}
</div>
@endif
@if (session('error'))
<div class="alert-danger alert">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="mb-4 rounded bg-red-50 p-4">
    <ul class="list-inside list-disc text-red-600">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">


        <form action="{{ $action ?? route('admin.deflatoreseventospormotoristas.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-forms.input label="Cód. Deflatores Motoristas:" name="id_deflatores_motoristas_eventos"
                        value="{{ old('id_deflatores_motoristas_eventos', $evento->id_deflatores_motoristas_eventos ?? '') }}"
                        readonly />
                </div>
                <div>

                    <x-forms.smart-select label="Tipo Deflator:" name="id_deflatores"
                        value="{{ old('id_deflatores', $evento->id_deflatores ?? '') }}" :options="$deflator" />
                </div>
            </div>
            <br />
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <div>
                    <x-forms.smart-select label="Motorista:" name="id_motorista"
                        value="{{ old('id_motorista', $evento->id_motorista ?? '') }}" :options="$motorista" />
                </div>
                <div>
                    <x-forms.smart-select label="Placa:" name="id_veiculo"
                        value="{{ old('id_veiculo', $evento->id_veiculo ?? '') }}" :options="$placa" />
                </div>
                <div>
                    <x-forms.input type="date" label="Data evento:" name="data_evento"
                        value="{{ old('data_evento', \Carbon\Carbon::parse($evento->data_evento ?? '')->format('Y-m-d')) }}" />

                </div>

            </div>
            <br />
            <div>
                <label for="observacao" class="block font-small text-gray-700">Observação:</label>
                <x-forms.textarea label="Observação:" name="observacao"
                    value="{{ old('observacao', $evento->observacao ?? '') }}" class="w-full" />
                {{-- <textarea name="observacao" id="observacao"
                    class="w-full p-2">{{ old('observacao', $evento->observacao ?? '') }}</textarea> --}}
            </div>
            <br />
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <input type="hidden" name="user_lancamento" value="{{auth()->user()->id}}">
                    <x-forms.input label=" Usuário:" name="usuario" value="{{auth()->user()->name}}" readonly />
                </div>
                <div>
                    <input type="hidden" name="filial_lancamento" value="{{auth()->user()->filial_id}}">
                    <x-forms.smart-select label="Filial Lancamento:" name="filial"
                        value="{{ auth()->user()->filial->name }}" :options="$filial" />
                </div>
                <div class="mb-4">
                    <input type="file" name="arquivo" accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                    <small class="text-gray-500">Formatos: PDF, JPG, PNG (Max: 2MB)</small>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            </div>
            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.deflatoreseventospormotoristas.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($evento) && $evento?->id_tipo_evento ? 'Atualizar' : 'Salvar' }}

                </button>
            </div>
        </form>

    </div>
</div>
@push('scripts')
@include('admin.deflatoreseventospormotoristas._scripts')
@endpush