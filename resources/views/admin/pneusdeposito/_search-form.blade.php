<!-- Coloque no body -->
<div id="toast-container" class="fixed top-4 inset-x-0 flex flex-col items-center space-y-2 z-50 pointer-events-none">
</div>


<form method="GET" action="{{ route('admin.pneusdeposito.index') }}" class="space-y-4">

    {{-- Linha 1 --}}
    <div class="flex w-full gap-2">
        <div class="w-full">
            <x-forms.input name="id_pneu" label="N° de Fogo:" />
        </div>

        <div class="w-full">
            <x-forms.smart-select name="descricao_destino" label="Local:" :options="$local" />
        </div>

        <div class="w-full">
            <x-forms.smart-select name="id_departamento" label="Departamento:" :options="$departamento" />
        </div>

        <div class="w-full">
            <x-forms.smart-select name="id_filial" label="Filial:" :options="$filial" />
        </div>

        <div class="w-full">
            <x-forms.smart-select name="id_calibragem_pneu" label="Destinação Solicitada:" />
        </div>

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

                <a href="{{ route('admin.pneusdeposito.index') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.trash class="h-4 w-4 mr-2" />
                    Limpar
                </a>
            </div>
        </div>
    </div>
    @if (isset($deposito) && $deposito->total() > 0)
        <div class="flex justify-between mt-4">
            <div class="flex space-x-2">
                <button type="button" onclick="enviarManutencao()"
                    class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.gear class="h-3 w-3 mr-1" />
                    Enviar Manutenção
                </button>

                <button type="button" onclick="enviarEstoque()"
                    class="inline-flex items-center px-3 py-2 text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.inventory class="h-3 w-3 mr-1" />
                    Enviar Estoque
                </button>

                {{-- <button type="button" onclick="enviarDescarte()"
                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <x-icons.trash class="h-3 w-3 mr-1" />
                Enviar Descarte
            </button> --}}
            </div>
        </div>
    @endif
</form>
<!-- Container para a modal -->
<div id="confirm-modal" class="fixed inset-0 flex items-center justify-center bg-opacity-30 bg-opacity-30 z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-96 max-w-sm text-center">
        <h3 class="text-lg font-semibold mb-4  text-green-600">Confirmação</h3>
        <p id="confirm-message" class="mb-6">Tem certeza que deseja realizar esta operação?</p>
        <div class="flex justify-center space-x-4">
            <button id="confirm-no" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Não</button>
            <button id="confirm-yes" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Sim</button>
        </div>
    </div>
</div>
