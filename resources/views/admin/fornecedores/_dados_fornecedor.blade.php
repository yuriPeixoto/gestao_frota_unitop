<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
    {{-- is_ativo --}}
    <div class="flex text-[#000001] items-center w-full col-span-2">
        <!-- Bloco Ativo -->
        <div class="flex flex-col space-y-2">
            <div class="text-center">
                <label>Ativo</label>
            </div>
            <div class="flex items-center space-x-2">
                <div class="flex items-center">
                    <input id="is_ativo_sim" name="is_ativo" type="radio" value="1" {{ old('is_ativo',
                        isset($fornecedor) ? $fornecedor->is_ativo : 1) == 1 ? 'checked' : '' }}
                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_ativo_sim" class="ml-2 block text-sm text-gray-700">Sim</label>
                </div>
                <div class="flex items-center">
                    <input id="is_ativo_nao" name="is_ativo" type="radio" value="0" {{ old('is_ativo',
                        isset($fornecedor) ? $fornecedor->is_ativo : '') == 0 ? 'checked' : '' }}
                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_ativo_nao" class="ml-2 block text-sm text-gray-700">Não</label>
                </div>
            </div>
        </div>

        <!-- Bloco Tipo Pessoa F/J -->
        <div class="flex flex-col space-y-2 w-full">
            <div class="flex items-center justify-end space-x-4">
                <div class="flex items-center">
                    <input id="is_juridico_sim" name="is_juridico" type="radio" value="1" {{ old('is_juridico',
                        isset($fornecedor) ? $fornecedor->is_juridico : 1) == 1 ? 'checked' : '' }}
                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_juridico_sim" class="ml-2 block text-sm text-gray-700">Pessoa Jurídica</label>
                </div>
                <div class="flex items-center">
                    <input id="is_juridico_nao" name="is_juridico" type="radio" value="0" {{ old('is_juridico',
                        isset($fornecedor) ? $fornecedor->is_juridico : '') == 0 ? 'checked' : '' }}
                    class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_juridico_nao" class="ml-2 block text-sm text-gray-700">Pessoa Física</label>
                </div>
            </div>
        </div>
    </div>

    {{-- Código do Fornecedor (desabilitado, só mostra na edição) --}}
    @if(isset($fornecedor) && $fornecedor->id_fornecedor)
    <div>
        <label for="id_fornecedor" class="block text-sm font-medium text-gray-700">Cód. Fornecedor</label>
        <input type="text" id="id_fornecedor" name="id_fornecedor" value="{{ $fornecedor->id_fornecedor }}" readonly
            class="mt-1 bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
    </div>
    @endif

    {{-- id_tipo_fornecedor --}}

    <div> <label for="id_tipo_fornecedor" class="block text-sm font-medium text-gray-700">Tipo Fornecedor <span
                class="text-red-500">*</span></label>
        <x-forms.smart-select id="id_tipo_fornecedor" name="id_tipo_fornecedor" placeholder="Selecionar"
            :options="$tipoFornecedor" :selected="old('id_tipo_fornecedor', $fornecedor->id_tipo_fornecedor ?? '')"
            minSearchLength="2" display-class="select-display" />
    </div>

    {{-- Nome Fornecedor --}}
    <div class="col-span-2">
        <label for="nome_fornecedor" class="block text-sm font-medium text-gray-700">Nome/Razão Social <span
                class="text-red-500">*</span></label>
        <input type="text" id="nome_fornecedor" name="nome_fornecedor" required
            value="{{ old('nome_fornecedor', $fornecedor->nome_fornecedor ?? '') }}"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Nome/Razão Social">
    </div>

    {{-- apelido_fornecedor --}}
    <div class="col-span-2">
        <label for="apelido_fornecedor" class="block text-sm font-medium text-gray-700">Nome Fantasia/Apelido</label>
        <input type="text" id="apelido_fornecedor" name="apelido_fornecedor"
            value="{{ old('apelido_fornecedor', $fornecedor->apelido_fornecedor ?? '') }}"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Nome Fantasia/Apelido">

    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Usuário do Sistema</label>

        <input type="text" id="usuario" name="usuario" required value="{{ old('usuario', auth()->user()->name ?? '') }}"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            disabled>
    </div>

    {{-- cnpj_fornecedor ou cpf_fornecedor dependendo do tipo de pessoa --}}
    <div>
        <label for="campo_cpf_cnpj" class="block text-sm font-medium text-gray-700" id="label_cpf_cnpj">
            {{ (old('is_juridico', isset($fornecedor) ? $fornecedor->is_juridico : 1) == 1) ? 'CNPJ' : 'CPF' }} <span
                class="text-red-500">*</span>
        </label>
        @php
        $isJuridico = old('is_juridico', $fornecedor->is_juridico ?? 1);
        $campoValor = $isJuridico ? 'cnpj_fornecedor' : 'cpf_fornecedor';
        $valor = old($campoValor, $fornecedor->{$campoValor} ?? '');
        @endphp

        <input type="text" id="campo_cpf_cnpj" name="{{ $campoValor }}" value="{{ $valor }}"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="{{ $isJuridico ? 'CNPJ' : 'CPF' }}" required>
        @if((old('is_juridico', isset($fornecedor) ? $fornecedor->is_juridico : 1) == 1))
        <button type="button" id="btn_buscar_cnpj"
            class="mt-1 inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            Buscar CNPJ
        </button>
        @endif
    </div>


    {{-- inscricao_estadual --}}
    <div>
        <label for="inscricao_estadual" class="block text-sm font-medium text-gray-700">Inscrição Estadual</label>
        <input type="text" id="inscricao_estadual" name="inscricao_estadual"
            value="{{ old('inscricao_estadual', $fornecedor->inscricao_estadual ?? '') }}"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Inscrição Estadual">
    </div>

    {{-- email --}}
    <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email <span
                class="text-red-500">*</span></label>
        <input type="email" id="email" name="email" required value="{{ old('email', $fornecedor->email ?? '') }}"
            class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
            placeholder="Email">
    </div>

    {{-- id_filial --}}
    <div>
        <label for="id_filial" class="block text-sm font-medium text-gray-700">Filial</label>
        <x-forms.smart-select id="id_filial" name="id_filial" placeholder="Selecionar" :options="$filiais"
            :selected="old('id_filial', $fornecedor->id_filial ?? '')" minSearchLength="2"
            display-class="select-display" />
    </div>
</div>

{{-- Script para lidar com a mudança entre CNPJ e CPF, e busca de CNPJ --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configurar o botão de busca CNPJ
        const btnBuscarCNPJ = document.getElementById('btn_buscar_cnpj');
        if (btnBuscarCNPJ) {
            btnBuscarCNPJ.addEventListener('click', function() {
                const cnpjInput = document.getElementById('campo_cpf_cnpj');
                if (cnpjInput && cnpjInput.value) {
                    // Remove caracteres não numéricos
                    const cnpj = cnpjInput.value.replace(/\D/g, '');
                    if (cnpj.length === 14) {
                        consultarCNPJ(cnpj);
                    } else {
                        alert('Por favor, informe um CNPJ válido com 14 dígitos.');
                    }
                } else {
                    alert('Por favor, informe um CNPJ.');
                }
            });
        }

        // Configurar a mudança entre Pessoa Jurídica e Física
        const radioJuridica = document.getElementById('is_juridico_sim');
        const radioFisica = document.getElementById('is_juridico_nao');
        const labelCpfCnpj = document.getElementById('label_cpf_cnpj');
        const campoCpfCnpj = document.getElementById('campo_cpf_cnpj');
        
        if (radioJuridica && radioFisica && labelCpfCnpj && campoCpfCnpj) {
            radioJuridica.addEventListener('change', function() {
                if (this.checked) {
                    labelCpfCnpj.innerHTML = 'CNPJ <span class="text-red-500">*</span>';
                    campoCpfCnpj.name = 'cnpj_fornecedor';
                    campoCpfCnpj.placeholder = 'CNPJ';
                    
                    // Mostrar botão de busca CNPJ
                    if (btnBuscarCNPJ) btnBuscarCNPJ.style.display = 'inline-flex';
                }
            });
            
            radioFisica.addEventListener('change', function() {
                if (this.checked) {
                    labelCpfCnpj.innerHTML = 'CPF <span class="text-red-500">*</span>';
                    campoCpfCnpj.name = 'cpf_fornecedor';
                    campoCpfCnpj.placeholder = 'CPF';
                    
                    // Ocultar botão de busca CNPJ
                    if (btnBuscarCNPJ) btnBuscarCNPJ.style.display = 'none';
                }
            });
        }
    });
    
    function consultarCNPJ(cnpj) {
        fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro ao consultar CNPJ');
                }
                return response.json();
            })
            .then(data => {
                if (document.getElementById('nome_fornecedor')) 
                    document.getElementById('nome_fornecedor').value = data.razao_social || '';
                
                if (document.getElementById('apelido_fornecedor')) 
                    document.getElementById('apelido_fornecedor').value = data.nome_fantasia || '';
                
                if (document.getElementById('email'))
                    document.getElementById('email').value = ''; // API normalmente não retorna email
                
                if (document.getElementById('inscricao_estadual'))
                    document.getElementById('inscricao_estadual').value = '';
                
                // Preencher dados de endereço se existirem
                if (document.getElementById('rua'))
                    document.getElementById('rua').value = data.logradouro || '';
                
                if (document.getElementById('numero'))
                    document.getElementById('numero').value = data.numero || '';
                
                if (document.getElementById('complemento'))
                    document.getElementById('complemento').value = data.complemento || '';
                
                if (document.getElementById('bairro'))
                    document.getElementById('bairro').value = data.bairro || '';
                
                if (document.getElementById('nome_municipio'))
                    document.getElementById('nome_municipio').value = data.municipio || '';
                
                if (document.getElementById('cep'))
                    document.getElementById('cep').value = data.cep || '';
                
                if (document.getElementById('id_uf')) {
                    const selectUF = document.getElementById('id_uf');
                    const options = selectUF.options;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === data.uf) {
                            selectUF.selectedIndex = i;
                            break;
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao consultar CNPJ:', error);
                alert('Não foi possível consultar o CNPJ. Por favor, verifique o número e tente novamente.');
            });
    }
</script>