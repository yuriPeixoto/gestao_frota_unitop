<x-app-layout>
    <div class="bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl text-black font-bold">Enviar Transferência </h2>
                <a href="{{ route('admin.transferenciaDiretoEstoque.index') }}"
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Voltar
                </a>
            </div>
        </div>
    </div>

    <div class="bg-white  overflow-hidden p-6 shadow-sm sm:rounded-lg">
        <div class="w-full flex justify-end">
            <a href="{{ route('admin.transferenciaDiretoEstoque.envioTransferencia', $id) }}"
                class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                <span>Enviar</span>
            </a>
        </div>

        <x-tables.table>
            <x-tables.header>

                <x-tables.head-cell>Cod. Transferencia</x-tables.head-cell>
                <x-tables.head-cell>Cod. Transfêrencia Itens</x-tables.head-cell>
                <x-tables.head-cell>Produto</x-tables.head-cell>
            </x-tables.header>

            <x-tables.body id="tbody-produtos">
                @forelse ($transferencia as $result)
                <x-tables.row>


                    <x-tables.cell>
                        {{ $result->id_transferencia }}
                    </x-tables.cell>
                    <x-tables.cell>{{ $result->id_transferencia_item }}</x-tables.cell>
                    <x-tables.cell>
                        {{$result->codigo_produto}} - {{ $result->descricao_produto}}
                    </x-tables.cell>
                </x-tables.row>
                @endforeach
            </x-tables.body>
        </x-tables.table>
        <div class="mt-4">
            {{ $transferencia->links() }}
        </div>
    </div>
</x-app-layout>

<script>


</script>