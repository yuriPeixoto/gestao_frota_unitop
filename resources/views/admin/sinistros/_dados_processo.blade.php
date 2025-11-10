{{-- <h3 class="font-medium text-gray-800 mb-10 uppercase ">Informações do Sinistro</h3> --}}
<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 items-center ">

    <div class="col-span-2">
        <x-forms.smart-select name="id_tipo_orgao" label="Orgão de Registro" placeholder="Selecione o Orgão..."
            :options="$formOptions['tiposOrgaos']" :selected="old('id_tipo_orgao', $sinistro->id_tipo_orgao ?? '')" disabled="{{ $bloquear }}" />
    </div>

    <div class="col-span-2">
        <label for="numero_processo" class="block text-sm font-medium text-gray-700">N° do Processo/B.O</label>
        <input name="numero_processo" {{ $bloquear ? 'disabled' : '' }}
            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('numero_processo', $sinistro->numero_processo ?? '') }}">
    </div>

</div>

<hr class="mt-10 mb-10">

<h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados de Pagamento</h3>

<div class="grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4 ">

    <div class="col-span-2">
        <label for="valor_apagar" class="block text-sm font-medium text-gray-700">Valor a Pagar</label>
        <input name="valor_apagar" {{ $bloquear ? 'disabled' : '' }}
            class="monetario w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('valor_apagar', $sinistro->valor_apagar ?? '') }}">
    </div>

    <div class="col-span-2">
        <label for="valor_apagar" class="block text-sm font-medium text-gray-700">Custo Carvalima</label>
        <input name="valor_pago" {{ $bloquear ? 'disabled' : '' }}
            class="monetario w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('valor_pago', $sinistro->valor_pago ?? '') }}">
    </div>

    <div class="col-span-2">
        <label for="valorpagoseguradora" class="block text-sm font-medium text-gray-700">Valor Pago Seguradora</label>
        <input name="valorpagoseguradora" {{ $bloquear ? 'disabled' : '' }}
            class="monetario w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('valorpagoseguradora', $sinistro->valorpagoseguradora ?? '') }}">
    </div>

    <div class="col-span-1">
        <label for="valorpagofrota" class="block text-sm font-medium text-gray-700">Custo Colaborador</label>
        <input name="valorpagofrota" {{ $bloquear ? 'disabled' : '' }}
            class="monetario w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('valorpagofrota', $sinistro->valorpagofrota ?? '') }}">
    </div>

    <div class="col-span-1">
        <label for="valor_pago_terceiro" class="block text-sm font-medium text-gray-700">Valor Pago Terceiro</label>
        <input name="valor_pago_terceiro" {{ $bloquear ? 'disabled' : '' }}
            class="monetario w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 {{ $bloquear ? 'bg-gray-100' : '' }}"
            value="{{ old('valor_pago_terceiro', $sinistro->valor_pago_terceiro ?? '') }}">
    </div>

</div>
