<!-- Modal Transferência -->
<div id="modal-transferencia-{{ $itemDevolucao->id_tranferencia }}"
    class="fixed inset-0 z-50 flex items-center justify-center hidden" style="background: rgba(0,0,0,0.25);">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold text-gray-700">Transferência Matriz</h2>
            <button onclick="closeModalTransferencia({{ $itemDevolucao->id_tranferencia }})"
                class="text-gray-400 hover:text-gray-700 text-xl">&times;</button>
        </div>
        <div id="modalTransferencia" class="mb-4 overflow-x-auto">
            <table class="min-w-full text-sm text-left text-gray-700 border">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-2 py-1 border">
                            <input type="checkbox" id="checkAll-{{ $itemDevolucao->id_tranferencia }}"
                                onclick="toggleCheckAll({{ $itemDevolucao->id_tranferencia }})">
                        </th>
                        <th class="px-2 py-1 border">Código</th>
                        <th class="px-2 py-1 border">Data Inclusão</th>
                        <th class="px-2 py-1 border">Data Alteração</th>
                        <th class="px-2 py-1 border">Código Devolução Matriz</th>
                        <th class="px-2 py-1 border">Produto</th>
                        <th class="px-2 py-1 border">Qtd Disponível Envio</th>
                        <th class="px-2 py-1 border">Qtd Enviada</th>
                    </tr>
                </thead>
                <tbody id="modalDevolucaoItens-{{ $itemDevolucao->id_tranferencia }}"></tbody>
            </table>
        </div>
        <div class="flex justify-end space-x-2">
            <button onclick="closeModalTransferencia({{ $itemDevolucao->id_tranferencia }})"
                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancelar</button>
            <button onclick="confirmarEnvioTransferencia({{ $itemDevolucao->id_tranferencia }})"
                class="px-4 py-2 bg-blue-600 rounded text-white hover:bg-blue-700">Gerar Transferência
                Matriz</button>
        </div>
    </div>
</div>
