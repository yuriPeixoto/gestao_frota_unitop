<div>
    <div>
        <x-forms.smart-select name="aplicacao" label="Modelo Aplicação" :options="$formOptions['modeloVeiculo']"
            selected_value="{{ old('aplicacao', $aplicacao->aplicacao ?? '') }}" />
    </div>
    <button type="button" onclick="adicionarCadastroProduto()"
        class="m-5 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        Adicionar
    </button>

    <input type="hidden" id="cad_produtos" name="cad_produtos"
        value="{{ isset($produtoAplicacao) ? json_encode($produtoAplicacao) : '[]' }}">

    <table class="min-w-full divide-y divide-gray-200 tabelaCadastroProdutoBody">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
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
                    Modelo Veiculo
                </th>
            </tr>
        </thead>
        <tbody id="tabelaCadastroProdutoBody" class="bg-white divide-y divide-gray-200">
            <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
        </tbody>
    </table>
</div>
