<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-4 bg-white border-b border-gray-200">
        <div class="p-6 bg-white border-b border-gray-200">
            @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Erro!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif
            @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Atenção!</strong>
                <span class="block sm:inline">Por favor, corrija os erros abaixo e tente novamente.</span>
            </div>
            @endif

            <div>
                <form id="pessoasForm" method="POST" action="{{ $action }}" class="space-y-4"
                    enctype="multipart/form-data">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif
                    <div class="space-y-6">
                        <!-- Informações do Usuário -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Cadastro de Pessoas</h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                {{-- Nome --}}
                                @php
                                $catcnh = [
                                ['label' => 'A', 'value' => 'A'],
                                ['label' => 'B', 'value' => 'B'],
                                ['label' => 'C', 'value' => 'C'],
                                ['label' => 'D', 'value' => 'D'],
                                ['label' => 'E', 'value' => 'E'],
                                ['label' => 'AB', 'value' => 'AB'],
                                ['label' => 'AC', 'value' => 'AC'],
                                ['label' => 'AD', 'value' => 'AD'],
                                ['label' => 'AE', 'value' => 'AE'],
                                ['label' => 'BC', 'value' => 'BC'],
                                ['label' => 'BD', 'value' => 'BD'],
                                ['label' => 'BE', 'value' => 'BE'],
                                ['label' => 'CD', 'value' => 'CD'],
                                ['label' => 'CE', 'value' => 'CE'],
                                ['label' => 'DE', 'value' => 'DE'],
                                ['label' => 'ABC', 'value' => 'ABC'],
                                ['label' => 'ABD', 'value' => 'ABD'],
                                ['label' => 'ABE', 'value' => 'ABE'],
                                ['label' => 'ACD', 'value' => 'ACD'],
                                ['label' => 'ACE', 'value' => 'ACE'],
                                ['label' => 'ADE', 'value' => 'ADE'],
                                ['label' => 'BCD', 'value' => 'BCD'],
                                ['label' => 'BCE', 'value' => 'BCE'],
                                ['label' => 'BDE', 'value' => 'BDE'],
                                ['label' => 'CDE', 'value' => 'CDE'],
                                ['label' => 'ABCD', 'value' => 'ABCD'],
                                ['label' => 'ABCE', 'value' => 'ABCE'],
                                ['label' => 'ABDE', 'value' => 'ABDE'],
                                ['label' => 'ACDE', 'value' => 'ACDE'],
                                ['label' => 'BCDE', 'value' => 'BCDE'],
                                ['label' => 'ABCDE', 'value' => 'ABCDE'],
                                ];
                                @endphp

                                <div class="w-80 pt-2 bg-gray-200 items-center gap-2 shadow-md">
                                    <!-- Avatar -->
                                    @php
                                    $avatarUrl = isset($pessoas) && $pessoas->imagem_pessoal
                                    ? url('storage/' . $pessoas->imagem_pessoal)
                                    : asset('images/avatar-placeholder.svg');
                                    @endphp
                                    <img src="{{ $avatarUrl }}" alt="Foto do colaborador"
                                        class="object-cover w-24 h-24 rounded-full border border-gray-300 bg-gray-100"
                                        id="imagem_preview"
                                        onerror="this.onerror=null;this.src='{{ asset('images/avatar-placeholder.svg') }}';">
                                    <input type="file" name="imagem_pessoal" id="imagem_pessoal" accept=".jpg, .png"
                                        class="block w-full text-sm text-gray-700 @error('imagem_pessoal') border-red-500 @enderror" />
                                    @error('imagem_pessoal')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="justify-self-end self-end text-[#000000]">
                                    <div>
                                        <label class="font-medium">Ativo</label>
                                    </div>
                                    <div class="mt-2">
                                        <input type="radio" id="ativo_sim" name="ativo" value="1"
                                            class="w-4 h-4 accent-blue" {{ old('ativo', isset($pessoas) ?
                                            $pessoas->ativo : '1') == 1 ? 'checked' : '' }}>
                                        <label for="ativo_sim" class="mr-4 font-medium">Sim</label>

                                        <input type="radio" id="ativo_nao" name="ativo" value="0"
                                            class="w-4 h-4 accent-blue" {{ old('ativo', isset($pessoas) ?
                                            $pessoas->ativo : '') === 0 ? 'checked' : '' }}>
                                        <label for="ativo_nao" class="mr-4 font-medium">Não</label>
                                    </div>
                                    @error('ativo')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                            </div>

                            <!-- Cabeçalho -->
                            <div class="mx-auto mt-7">
                                <!-- Botões das abas -->
                                <div class="flex space-x-1">
                                    <button type="button"
                                        class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                        onclick="openTab(event, 'Aba1')">
                                        Dados Pessoais
                                    </button>
                                    <button type="button"
                                        class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                        onclick="openTab(event, 'Aba2')">
                                        Endereços
                                    </button>
                                    <button type="button"
                                        class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                        onclick="openTab(event, 'Aba3')">
                                        Telefones
                                    </button>
                                    <button type="button"
                                        class="tablink flex-1 py-2 px-4 bg-gray-200 text-gray-700 rounded-t-lg hover:bg-blue-400 hover:text-white transition-colors"
                                        onclick="openTab(event, 'Aba4')">
                                        Dados de Acessos
                                    </button>
                                </div>
                            </div>

                            <!-- Conteúdo das abas -->
                            <div id="Aba1" class="tabcontent p-6 bg-white rounded-b-lg shadow-lg">
                                <div class="grid grid-cols-7 gap-3">
                                    <div>
                                        {{-- Matricula --}}
                                        <label for="matricula"
                                            class="block text-sm font-medium text-gray-700">Matricula</label>
                                        <input id="matricula" name="matricula" type="text" inputmode="numeric"
                                            pattern="[0-9]*" maxlength="20" autocomplete="off"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('matricula') border-red-500 @enderror"
                                            value="{{ old('matricula', $pessoas->matricula ?? '') }}">
                                        @error('matricula')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Filial --}}
                                    <div>
                                        <label for="id_filial"
                                            class="block text-sm font-medium text-gray-700">Filial</label>
                                        <select name="id_filial" id="id_filial"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('id_filial') border-red-500 @enderror"
                                            required>
                                            <option value="">Selecione a filial...</option>
                                            @foreach ($filiais as $filial)
                                            <option value="{{ $filial['value'] }}" {{ old('id_filial', $pessoas->
                                                id_filial ?? '') == $filial['value'] ? 'selected' : '' }}>
                                                {{ $filial['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('id_filial')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        {{-- tipo pessoal --}}
                                        <label for="id_tipo_pessoal"
                                            class="block text-sm font-medium text-gray-700">Tipo Pessoal</label>
                                        <select name="id_tipo_pessoal" id="id_tipo_pessoal"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('id_tipo_pessoal') border-red-500 @enderror"
                                            required>
                                            <option value="">Selecione o tipo...</option>
                                            @foreach ($tipopessoas as $tipopessoal)
                                            <option value="{{ $tipopessoal['value'] }}" {{ old('id_tipo_pessoal',
                                                $pessoas->id_tipo_pessoal ?? '') == $tipopessoal['value'] ? 'selected' :
                                                '' }}>
                                                {{ $tipopessoal['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('id_tipo_pessoal')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="col-span-2">
                                        {{-- departamento --}}
                                        <label for="id_departamento"
                                            class="block text-sm font-medium text-gray-700">Departamento</label>
                                        <select name="id_departamento" id="id_departamento"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('id_departamento') border-red-500 @enderror"
                                            required>
                                            <option value="">Selecione...</option>
                                            @foreach ($departamentos as $departamento)
                                            <option value="{{ $departamento['value'] }}" {{ old('id_departamento',
                                                $pessoas->id_departamento ?? '') == $departamento['value'] ? 'selected'
                                                : '' }}>
                                                {{ $departamento['label'] }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('id_departamento')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="col-span-2">
                                        {{-- rotas --}}
                                        <label for="id_rotas"
                                            class="block text-sm font-medium text-gray-700">Rotas</label>
                                        <select name="id_rota" id="id_rota"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('id_rota') border-red-500 @enderror">
                                            <option value="">Selecione...</option>
                                            @foreach ($rotas as $rota)
                                            <option value="{{ $rota->value }}" {{ old('id_rota', $pessoas->id_rota ??
                                                '') == $rota->value ? 'selected' : '' }}>
                                                {{ $rota->label }}
                                            </option>
                                            @endforeach
                                        </select>

                                        @error('id_rota')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-5 gap-2 mt-5">
                                    {{-- Data Nascimento --}}
                                    <div>
                                        <label for="data_nascimento"
                                            class="block text-sm font-medium text-gray-700">Data Nascimento</label>
                                        <input type="date" name="data_nascimento" id="data_nascimento"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('data_nascimento') border-red-500 @enderror"
                                            required
                                            value="{{ old('data_nascimento', isset($pessoas) && $pessoas->data_nascimento ? format_date($pessoas->data_nascimento, 'Y-m-d') : '') }}">
                                        @error('data_nascimento')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Data Admissao --}}
                                    <div>
                                        <label for="data_admissao" class="block text-sm font-medium text-gray-700">Data
                                            Admissão</label>
                                        <input type="date" name="data_admissao" id="data_admissao"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('data_admissao') border-red-500 @enderror"
                                            value="{{ old('data_admissao', isset($pessoas) && $pessoas->data_admissao ? format_date($pessoas->data_admissao, 'Y-m-d') : '') }}">
                                        @error('data_admissao')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- CPF --}}
                                    <div class="relative">
                                        <label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label>
                                        <input type="text" name="cpf" id="cpf"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('cpf') border-red-500 @enderror"
                                            required maxlength="14" value="{{ old('cpf', $pessoas->cpf ?? '') }}">
                                        <div class="absolute top-11 left-0">
                                            <span id="cpf-feedback" class="error"></span>
                                        </div>
                                        @error('cpf')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- RG --}}
                                    <div>
                                        <label for="rg" class="block text-sm font-medium text-gray-700">RG</label>
                                        <input type="text" name="rg" id="rg"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('rg') border-red-500 @enderror"
                                            value="{{ old('rg', $pessoas->rg ?? '') }}">
                                        @error('rg')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Órgão Emissor --}}
                                    <div>
                                        <label for="orgao_emissor" class="block text-sm font-medium text-gray-700">Órgão
                                            Emissor</label>
                                        <input type="text" name="orgao_emissor" id="orgao_emissor"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('orgao_emissor') border-red-500 @enderror"
                                            value="{{ old('orgao_emissor', $pessoas->orgao_emissor ?? '') }}">
                                        @error('orgao_emissor')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-5 gap-2 mt-5">
                                    {{-- nome --}}
                                    <div class="col-span-3">
                                        <label for="nome" class="block text-sm font-medium text-gray-700">Nome
                                            completo</label>
                                        <input type="text" name="nome" id="nome" placeholder="Nome completo"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('nome') border-red-500 @enderror"
                                            required value="{{ old('nome', $pessoas->nome ?? '') }}">
                                        @error('nome')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    {{-- Email --}}
                                    <div class="col-span-2">
                                        <label for="email"
                                            class="block text-sm font-medium text-gray-700">E-mail</label>
                                        <input type="email" name="email" id="email"
                                            placeholder="teste@unitopconsultoria.com.br"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('email') border-red-500 @enderror"
                                            value="{{ old('email', $pessoas->email ?? '') }}">
                                        @error('email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-4 gap-2 mt-5">
                                    {{-- cnh --}}
                                    <div>
                                        <label for="cnh" class="block text-sm font-medium text-gray-700">CNH</label>
                                        <input type="text" name="cnh" id="cnh"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('cnh') border-red-500 @enderror"
                                            value="{{ old('cnh', $pessoas->cnh ?? '') }}">
                                        @error('cnh')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Validade CNH --}}
                                    <div>
                                        <label for="validade_cnh"
                                            class="block text-sm font-medium text-gray-700">Validade
                                            CNH</label>
                                        <input type="date" name="validade_cnh" id="validade_cnh"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('validade_cnh') border-red-500 @enderror"
                                            value="{{ old('validade_cnh', isset($pessoas) && $pessoas->validade_cnh ? format_date($pessoas->validade_cnh, 'Y-m-d') : '') }}">
                                        @error('validade_cnh')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- tipo cnh --}}
                                    <div>
                                        <label for="tipo_cnh"
                                            class="block text-sm font-medium text-gray-700">Categoria</label>
                                        <select name="tipo_cnh" id="tipo_cnh"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('tipo_cnh') border-red-500 @enderror">
                                            <option value="">Categoria</option>
                                            @foreach ($catcnh as $cat)
                                            <option value="{{ $cat['value'] }}" {{ old('tipo_cnh', $pessoas->tipo_cnh ??
                                                '') == $cat['value'] ? 'selected' : '' }}>
                                                {{ $cat['label'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('tipo_cnh')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- pis --}}
                                    <div>
                                        <label for="pis" class="block text-sm font-medium text-gray-700">PIS</label>
                                        <input type="text" name="pis" id="pis"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('pis') border-red-500 @enderror"
                                            value="{{ old('pis', $pessoas->pis ?? '') }}" maxlength="11">
                                        @error('pis')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div id="Aba2" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                                {{-- endereco --}}
                                <div class="grid grid-cols-5 gap-2">
                                    <div>
                                        <label for="cep" class="block text-sm font-medium text-gray-700">CEP</label>
                                        <input type="text" name="cep" id="cep" pattern="[0-9]{5}-[0-9]{3}"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('cep') border-red-500 @enderror"
                                            value="{{ old('cep', $pessoas->endereco[0]->cep ?? '') }}">
                                        @error('cep')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="col-span-2">
                                        <label for="rua" class="block text-sm font-medium text-gray-700">Rua</label>
                                        <input type="text" name="rua" id="rua"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('rua') border-red-500 @enderror"
                                            value="{{ old('rua', $pessoas->endereco[0]->rua ?? '') }}">
                                        @error('rua')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="numero"
                                            class="block text-sm font-medium text-gray-700">Número</label>
                                        <input type="text" name="numero" id="numero"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('numero') border-red-500 @enderror"
                                            value="{{ old('numero', $pessoas->endereco[0]->numero ?? '') }}">
                                        @error('numero')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="complemento"
                                            class="block text-sm font-medium text-gray-700">Complemento</label>
                                        <input type="text" name="complemento" id="complemento"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('complemento') border-red-500 @enderror"
                                            value="{{ old('complemento', $pessoas->endereco[0]->complemento ?? '') }}">
                                        @error('complemento')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 mt-3">
                                    {{-- bairro --}}
                                    <div>
                                        <label for="bairro"
                                            class="block text-sm font-medium text-gray-700">Bairro</label>
                                        <input name="bairro" id="bairro" label="Bairro" type="text"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('bairro') border-red-500 @enderror"
                                            value="{{ old('bairro', $pessoas->endereco[0]->bairro ?? '') }}" />
                                        @error('bairro')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- nome_municipio --}}
                                    <div>
                                        <label for="nome_municipio"
                                            class="block text-sm font-medium text-gray-700">Cidade</label>
                                        <input name="nome_municipio" id="nome_municipio" label="Cidade" type="text"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('nome_municipio') border-red-500 @enderror"
                                            value="{{ old('nome_municipio', $pessoas->endereco[0]->municipio->nome_municipio ?? '') }}" />
                                        @error('nome_municipio')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="w-full mt-1">
                                        <label for="id_uf"
                                            class="block text-sm font-medium text-gray-700">Estado</label>
                                        <select name="id_uf" id="id_uf"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('id_uf') border-red-500 @enderror">
                                            <option value="">Estado</option>
                                            @foreach ($estados as $estado)
                                            <option value="{{ $estado->uf }}" {{ old('id_uf', $pessoas->
                                                endereco[0]->id_uf ?? '') == $estado->id_uf ? 'selected' : '' }}>
                                                {{ $estado->uf }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('id_uf')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div id="Aba3" class="tabcontent hidden p-6 bg-white rounded-b-lg shadow-lg">
                                {{-- telefone --}}
                                <div class="grid grid-cols-3 gap-2">
                                    <div>
                                        <label for="telefone_fixo"
                                            class="block text-sm font-medium text-gray-700">Telefone Fixo</label>
                                        <input name="telefone_fixo" id="telefone_fixo" label="Telefone Fixo" type="text"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('telefone_fixo') border-red-500 @enderror"
                                            value="{{ old('telefone_fixo', $pessoas->telefone[0]->telefone_fixo ?? '') }}" />
                                        @error('telefone_fixo')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="telefone_celular"
                                            class="block text-sm font-medium text-gray-700">Celular</label>
                                        <input name="telefone_celular" id="telefone_celular" label="Celular" type="text"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('telefone_celular') border-red-500 @enderror"
                                            value="{{ old('telefone_celular', $pessoas->telefone[0]->telefone_celular ?? '') }}" />
                                        @error('telefone_celular')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="telefone_contato"
                                            class="block text-sm font-medium text-gray-700">Contato</label>
                                        <input name="telefone_contato" id="telefone_contato" label="Contato" type="text"
                                            class="w-full block text-sm font-medium text-gray-700 rounded-md border-gray-300 hover:border-gray-400 @error('telefone_contato') border-red-500 @enderror"
                                            value="{{ old('telefone_contato', $pessoas->telefone[0]->telefone_contato ?? '') }}" />
                                        @error('telefone_contato')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Botões -->
                            <div class="flex justify-end space-x-3 mt-7">
                                <a href="{{ route('admin.pessoas.index') }}"
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    Cancelar
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ isset($pessoas) ? 'Atualizar' : 'Salvar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@include('admin.pessoas._scripts');
@endpush