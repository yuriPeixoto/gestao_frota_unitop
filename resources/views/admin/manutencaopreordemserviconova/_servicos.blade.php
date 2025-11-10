<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">

    <div>
        <x-forms.smart-select name="id_servico" label="Serviços" placeholder="Selecione o serviço..." :options="$servicosFrequentes"
            :searchUrl="route('admin.servicos.search')" :selected="old('id_servico', $preOrdemFinalizada->servico[0]->id_servico ?? '')" asyncSearch="true" minSearchLength="2" />
    </div>

    <div>
        <label for="observacao" class="block text-sm font-medium text-gray-700">observacao</label>
        <textarea id="observacao" name="observacao"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('observacao', $relacaoImobilizados->observacao ?? '') }}</textarea>
    </div>

    <div class="flex justify-start items-center mt-6">
        {{-- Botão Adicionar Manutenção --}}
        <button type="button" onclick="adicionarServico()"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Adicionar Serviço
        </button>
    </div>
</div>



<!-- Campo hidden para armazenar os históricos -->
<input type="hidden" name="servicos" id="servicos_json">

<div class="col-span-full mt-6">
    <table class="min-w-full divide-y divide-gray-200 tabelaservico">
        <thead class="bg-gray-50">
            <tr>
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
                    Serviço
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Observação
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                </th>
            </tr>
        </thead>
        <tbody id="tabelaServicoBody" class="bg-white divide-y divide-gray-200">
            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
        </tbody>
    </table>
</div>
