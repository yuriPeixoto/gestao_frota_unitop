<div class="mt-5">
    <div>
        <span>Reclamações:</span>
        <hr class="w-full">
    </div>
</div>

<!-- Tabela de reclamações -->
<div class="p-6 bg-white border-gray-200">
    <!-- Campo hidden para carregar reclamações -->
    <input type="hidden" name="tabelaReclamacoes" id="tabelaReclamacoes_json"
        value="{{ isset($tabelaReclamacoes) ? json_encode($tabelaReclamacoes) : '[]' }}">

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 shadow-md overflow-hidden sm:rounded-md tabelaReclamacoesBody">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pré-OS
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Inclusão
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Data Alteração
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Placa
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Motorista
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Telefone Motorista
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        KM Veículo
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Descrição da Reclamação
                    </th>
                </tr>
            </thead>
            <tbody id="tabelaReclamacoesBody" class="bg-white divide-y divide-gray-200">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>
</div>
