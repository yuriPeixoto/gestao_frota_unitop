<div class="flex gap-2 mt-4">
    <div>
        <button type="button" name="adicionar" id="adicionar"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <div class="flex items-center">
                <x-icons.plus />
                Carregar Peças NF
            </div>
        </button>
    </div>
    <div>
        <button type="button" name="adicionar" id="adicionar"
            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <div class="flex items-center">
                <x-icons.plus />
                Carregar Serviços NF
            </div>
        </button>
    </div>
</div>

<!-- Tabela de Itens -->
<div class="mt-6">
    <div class="overflow-x-auto relative shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left text-gray-700">
            <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                <tr>
                    <th scope="col" class="py-3 px-6">Fornecedor</th>
                    <th scope="col" class="py-3 px-6">Serviço</th>
                    <th scope="col" class="py-3 px-6">Produto</th>
                    <th scope="col" class="py-3 px-6">Data Inclusão</th>
                    <th scope="col" class="py-3 px-6">N° da Nota Fiscal</th>
                    <th scope="col" class="py-3 px-6">Série</th>
                    <th scope="col" class="py-3 px-6">Data Emissão NF</th>
                    <th scope="col" class="py-3 px-6">Valor Total do Item</th>
                    <th scope="col" class="py-3 px-6">Valor Desconto do Item</th>
                    <th scope="col" class="py-3 px-6">Valor Bruto da NF</th>
                    <th scope="col" class="py-3 px-6">Valor do Desconto NF</th>
                    <th scope="col" class="py-3 px-6">Valor Líquido da NF</th>
                    <th scope="col" class="py-3 px-6">Observação</th>
                </tr>
            </thead>
            <tbody>
                <!-- Template Alpine.js removido - funcionalidade de NF desabilitada -->
                <tr class="bg-white border-b">
                    <td colspan="13" class="py-3 px-6 text-center text-gray-500">Nenhum item adicionado</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
