<div id="visualizarModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 max-w-7xl">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex justify-between items-center border-b pb-4 mb-6">
                <h1 class="text-xl font-bold text-gray-800">Visualizar Ordens de Serviços</h1>
            </div>

            <!-- Seção de Informações Gerais -->
            <div class="mb-6 overflow-x-auto">
                <h2 class="text-lg font-medium text-gray-700 mb-4">Informações Gerais</h2>
                <table class="w-full text-sm text-left text-gray-700">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                        <tr>
                            <th scope="col" class="py-3 px-6">Cod. O.S.</th>
                            <th scope="col" class="py-3 px-6">Placa</th>
                            <th scope="col" class="py-3 px-6">Data Abertura Aux.</th>
                            <th scope="col" class="py-3 px-6">Situação Ordem de Serviço</th>
                            <th scope="col" class="py-3 px-6">Recepcionista</th>
                            <th scope="col" class="py-3 px-6">Departamento</th>
                            <th scope="col" class="py-3 px-6">Local Manutenção</th>
                            <th scope="col" class="py-3 px-6">Recepcionista Encerramento</th>
                            <th scope="col" class="py-3 px-6">Cód. Lançamento O.S. Auxiliar</th>
                        </tr>
                    </thead>
                    <tbody id="modal-os-geral" class="bg-white divide-y divide-gray-200">
                        <!-- As linhas serão adicionadas dinamicamente pelo JavaScript -->
                    </tbody>
                </table>
            </div>

            <div class="mt-8 flex justify-end space-x-3 border-t pt-6">
                <button onclick="fecharModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
