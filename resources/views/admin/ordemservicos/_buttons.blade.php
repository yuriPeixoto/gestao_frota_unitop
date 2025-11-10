<button type="button" name="btnSalvar" id="btnSalvar" onclick="validarCampos()"
    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.disk />
    {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
</button>

<!--
<button type="button" id="btnSalvarParcial" onclick="salvarParcial()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    Salvar Parcial
</button>
-->
@if (request('origem') === 'borracharia')
<a href="{{ route('admin.pneus.borracharia.index') }}"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.arrow-back class="text-cyan-500 mr-2" />
    Voltar
</a>
@else
<a href="{{ route('admin.ordemservicos.index') }}"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.arrow-back class="text-cyan-500 mr-2" />
    Voltar
</a>
@endif

<button type="button" x-on:click="items = []; limparNovoItem()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.eraser class="mr-2" />
    Limpar Formulário
</button>

<button type="button" onclick="onActionSolicitarPecas()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.shopping-cart class="text-cyan-500 mr-2" />
    Solicitar Peças
</button>

<button type="button" onclick="onSolicitarServicos()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.clipboard-document-check class="text-cyan-500 mr-2" />
    Solicitar Serviços
</button>

{{-- <button type="button" x-on:click="onActionAtualizar()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.refresh class="text-cyan-500 mr-2" />
    Atualizar
</button> --}}

{{-- <button type="button" onclick="imprimirOS('{{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 0 }}')"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.print class="text-cyan-500 mr-2" />
    Imprimir
</button> --}}

<button type="button" onclick="onImprimirServPec('{{ isset($ordemServico) ? $ordemServico->id_ordem_servico : 0 }}')"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.print class="text-cyan-500 mr-2" />
    Serviço/Peças
</button>

<button type="button" onclick="onCancelarOS()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-yellow-300 hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.error class="text-white mr-2" />
    Cancelar
</button>

<button type="button" onclick="onActionEncerrar()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.poweroff class="text-cyan-500 mr-2" />
    Encerrar
</button>

<button type="button" onclick="onFinalizar()"
    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
    <x-icons.inventory class="mr-2" />
    Finalizar
</button>


@push('scripts')
@include('admin.ordemservicos._scripts')

{{-- @include('admin.ordemservicos._script_parcial_button_script'); --}}
@endpush