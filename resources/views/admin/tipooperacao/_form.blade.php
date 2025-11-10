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
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">


        <form action="{{ $action ?? route('admin.tipooperacao.store') }}" method="POST">
            @csrf
            @if(isset($method) && strtoupper($method) !== 'POST')
            @method($method)
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-forms.input label="Cód. Tipo Operação:" name="id_tipo_operacao"
                        value="{{ old('id_tipo_operacao', $operacao->id_tipo_operacao ?? '') }}" readonly />
                </div>
                <div>
                    <x-forms.input label="Descrição Tipo Operação:" name="descricao_tipo_operacao"
                        value="{{ old('descricao_tipo_operacao', $operacao->descricao_tipo_operacao ?? '') }}" />
                </div>
                <div>
                    <x-forms.input type="number" label="Quilometragem média:" name="km_operacao"
                        value="{{ old('km_operacao', $operacao->km_operacao ?? '') }}" />
                </div>
            </div>

            <div class="flex justify-end space-x-3 col-span-full">
                <a href="{{ route('admin.tipooperacao.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Cancelar
                </a>

                <button type="submit" id="submit-form"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    {{ isset($operacao) && $operacao?->id_tipo_operacao ? 'Atualizar' : 'Salvar' }}

                </button>
            </div>
        </form>

    </div>
</div>
@push('scripts')
@include('admin.tipooperacao._scripts')
@endpush