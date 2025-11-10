@php
use Illuminate\Support\Facades\Storage;
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <x-forms.input name="id_produto" label="Código do Produto" readonly
        value="{{ old('id_produto', $cadastroProdutos->id_produto ?? '') }}" />

    {{-- Verificar se existe imagem cadastrada --}}
    @if (isset($cadastroProdutos) && $cadastroProdutos->imagem_produto)
    <div class="flex justify-center">
        <img src="{{ Storage::url($cadastroProdutos->imagem_produto) }}" alt="Imagem do produto"
            style="max-width: 200px;">
    </div>
    @else
    <div class="flex justify-center">
        <p>Nenhuma imagem cadastrada</p>
    </div>
    @endif


    <x-forms.smart-select name="id_estoque_produto" label="Estoque" placeholder="Selecione o estoque..."
        :options="$formOptions['estoque']"
        :selected="old('id_estoque_produto', $cadastroProdutos->id_estoque_produto ?? '')" asyncSearch="false" />

    <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
        :options="$formOptions['filiais']" :selected="old('id_filial', $cadastroProdutos->id_filial ?? '')"
        asyncSearch="false" />

    <x-forms.input name="descricao_produto" label="Descrição do Produto"
        value="{{ old('descricao_produto', $cadastroProdutos->descricao_produto ?? '') }}" />

    <div class="mt-5">
        <span class="block text-sm font-medium text-gray-900 ml-3">Peça Original</span>
        <fieldset class="inline-flex items-center">
            <div class="relative flex items-center">
                <input type="radio" id="is_original" name="is_original" value="1" {{ isset($cadastroProdutos) &&
                    $cadastroProdutos->is_original == 1 ? 'checked' : '' }}
                class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
            </div>
            <label for="descarte" class="ml-2">Sim</label>
        </fieldset>
        <fieldset class="inline-flex items-center">
            <div class="relative flex items-center">
                <input type="radio" id="is_original_nao" name="is_original" value="0" checked {{
                    isset($cadastroProdutos) && $cadastroProdutos->is_original == 0 ? 'checked' : '' }}
                class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
            </div>
            <label for="descarte" class="ml-2">Não</label>
        </fieldset>
    </div>

</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
    <x-forms.input name="marca" label="Marca" value="{{ old('marca', $cadastroProdutos->marca ?? '') }}" />

    <x-forms.input name="modelo" label="Modelo" value="{{ old('modelo', $cadastroProdutos->modelo ?? '') }}" />

    <div class="mt-5">
        <span class="block text-sm font-medium text-gray-900 ml-3">Pré Cadastro</span>
        <fieldset class="inline-flex items-center">
            <div class="relative flex items-center">
                <input type="radio" id="pre_cadastro" name="pre_cadastro" value="1" {{ isset($cadastroProdutos) &&
                    $cadastroProdutos->pre_cadastro == 1 ? 'checked' : '' }}
                class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
            </div>
            <label for="descarte" class="ml-2">Sim</label>
        </fieldset>
        <fieldset class="inline-flex items-center">
            <div class="relative flex items-center">
                <input type="radio" id="pre_cadastro_nao" name="pre_cadastro" value="0" checked {{
                    isset($cadastroProdutos) && $cadastroProdutos->pre_cadastro == 0 ? 'checked' : '' }}
                class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
            </div>
            <label for="descarte" class="ml-2">Não</label>
        </fieldset>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
    <x-forms.smart-select name="curva_abc" label="Curva ABC"
        :options="[['value' => 'A', 'label' => 'A'], ['value' => 'B', 'label' => 'B'], ['value' => 'C', 'label' => 'C']]"
        :selected="old('curva_abc', $cadastroProdutos->curva_abc ?? '')" asyncSearch="false" />

    <x-forms.input name="cod_fabricante_" label="Código do Fabricante"
        value="{{ old('cod_fabricante_', $cadastroProdutos->cod_fabricante_ ?? '') }}" />

    <x-forms.input name="cod_alternativo_1_" label="Código Alternativo 1"
        value="{{ old('cod_alternativo_1_', $cadastroProdutos->cod_alternativo_1_ ?? '') }}" />

    <x-forms.input name="cod_alternativo_2_" label="Código Alternativo 2"
        value="{{ old('cod_alternativo_2_', $cadastroProdutos->cod_alternativo_2_ ?? '') }}" />

    <div class="col-span-2">
        <x-forms.input name="cod_alternativo_3_" label="Código Alternativo 3"
            value="{{ old('cod_alternativo_3_', $cadastroProdutos->cod_alternativo_3_ ?? '') }}" />
    </div>

    <x-forms.input name="tempo_garantia" label="Tempo de Garantia"
        value="{{ old('tempo_garantia', $cadastroProdutos->tempo_garantia ?? '') }}" />

    <x-forms.smart-select name="id_unidade_produto" label="Unidade" placeholder="Selecione a unidade..."
        :options="$formOptions['unidadeProduto']"
        :selected="old('id_unidade_produto', $cadastroProdutos->id_unidade_produto ?? '')" asyncSearch="false" />

    <x-forms.input name="ncm" label="NCM" value="{{ old('ncm', $cadastroProdutos->ncm ?? '') }}" />

    <x-forms.smart-select name="id_grupo_servico" label="Grupo" placeholder="Selecione um grupo..."
        :options="$formOptions['grupoServico']"
        :selected="old('id_grupo_servico', $cadastroProdutos->id_grupo_servico ?? '')" asyncSearch="false" />

    <x-forms.smart-select name="id_produto_subgrupo" placeholder="SubGrupo" asyncSearch="false" label="SubGrupo"
        :selected="old('id_produto_subgrupo', $produto->id_produto_subgrupo ?? '')"
        :options="$formOptions['subgrupoServico']" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-forms.input name="estoque_minimo" type="number" label="Estoque Mínimo"
            value="{{ old('estoque_minimo', $cadastroProdutos->estoque_minimo ?? '') }}" />

        <x-forms.input name="estoque_maximo" type="number" label="Estoque Máximo"
            value="{{ old('estoque_maximo', $cadastroProdutos->estoque_maximo ?? '') }}" />
    </div>

    <x-forms.input name="localizacao_produto" type="text" label="Localização"
        value="{{ old('localizacao_produto', $cadastroProdutos->localizacao_produto ?? '') }}" />

    <div>
        <span class="block text-md font-medium text-gray-900 mb-2">Imagem do Produto</span>
        <fieldset class="inline-flex items-center">
            <div class="relative flex items-center">
                <input type="file" name="imagem_produto" id="imagem_produto" accept="image/jpeg, image/png" />
            </div>
        </fieldset>
    </div>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-5">
    <x-forms.input name="quantidade_atual_produto" label="Quantidade Do Produto" readonly
        value="{{ old('quantidade_atual_produto', $qtdProduto->quantidade_produto ?? '0') }}" />

    <x-forms.input name="valor_medio" label="Valor Médio" readonly
        value="{{ old('valor_medio', $cadastroProdutos->valor_medio ?? '') }}" />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-forms.smart-select name="id_modelo_pneu" placeholder="Modelo do Pneu" searchable="true"
            label="Modelo do Pneu" selected_value="old('id_modelo_pneu', $produto->id_modelo_pneu ?? '')"
            :options="$formOptions['modelopneu']" />

        <div class="mt-5">
            <span class="block text-sm font-medium text-gray-900 ml-5">Ativo</span>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="is_ativo" name="is_ativo" value="1" checked {{ isset($cadastroProdutos) &&
                        $cadastroProdutos->is_ativo == 1 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="descarte" class="ml-2">Sim</label>
            </fieldset>
            <fieldset class="inline-flex items-center">
                <div class="relative flex items-center">
                    <input type="radio" id="is_ativo_nao" name="is_ativo" value="0" {{ isset($cadastroProdutos) &&
                        $cadastroProdutos->is_ativo == 0 ? 'checked' : '' }}
                    class="appearance-none w-4 h-4 border-2 rounded-full checked:border-blue-600" />
                </div>
                <label for="descarte" class="ml-2">Não</label>
            </fieldset>
        </div>

    </div>

</div>