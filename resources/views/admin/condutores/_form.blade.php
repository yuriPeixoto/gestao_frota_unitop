@php
$smartecCnh = $smartecCnh ?? null;
@endphp
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    {{-- Mensagens de erro --}}
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif


    @if ($errors->any())
    <div class="mb-4 bg-red-50 p-4 rounded">
        <ul class="list-disc list-inside text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="p-4 bg-white border-b border-gray-200">


        <div class="p-6 bg-white border-b border-gray-200">
            <div>
                <form id="smartecCnh" method="POST" action="{{ $action }}" class="space-y-4">
                    @csrf
                    @if ($method === 'PUT')
                    @method('PUT')
                    @endif

                    <h3 class="font-medium text-gray-800 mb-10 uppercase ">Dados do Condutor</h3>
                    <div class="p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                {{-- Cod. Cadastro Imobilizado --}}
                                <label for="id_smartec_cnh" class="block text-sm font-medium text-gray-700">Código do
                                    Condutor</label>
                                <input type="text" id="id_smartec_cnh" name="id_smartec_cnh" readonly
                                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->id_smartec_cnh ?? '' }}">
                            </div>

                            <div>
                                {{-- Nome --}}
                                <label for="nome" class="block text-sm font-medium text-gray-700">Nome</label>
                                <input type="text" id="nome" name="nome"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->nome ?? '' }}">
                            </div>

                            <div>
                                {{-- CPF --}}
                                <label for="cpf" class="block text-sm font-medium text-gray-700">CPF</label>
                                <input type="text" id="cpf" name="cpf" oninput="aplicarMascaraCPF(this)"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->cpf ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                {{-- CNH --}}
                                <label for="cnh" class="block text-sm font-medium text-gray-700">CNH</label>
                                <input type="text" id="cnh" name="cnh"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->cnh ?? '' }}">
                            </div>

                            <div>
                                {{-- UF --}}
                                <label for="uf" class="block text-sm font-medium text-gray-700">UF</label>
                                <input type="text" id="uf" name="uf"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->uf ?? '' }}">
                            </div>

                            <div>
                                {{-- Renach --}}
                                <label for="renach" class="block text-sm font-medium text-gray-700">Renach</label>
                                <input type="text" id="renach" name="renach"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->renach ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                {{-- vencimento --}}
                                <label for="vencimento" class="block text-sm font-medium text-gray-700">Validade
                                    CNH</label>
                                <input type="date" id="vencimento" name="vencimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh && $smartecCnh->vencimento ? \Carbon\Carbon::parse($smartecCnh->vencimento)->format('Y-m-d') : '' }}">
                            </div>

                            <div>
                                {{-- Data Nasciemnto --}}
                                <label for="data_nascimento" class="block text-sm font-medium text-gray-700">Data
                                    Nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->data_nascimento ?? '' }}">
                            </div>

                            <div>
                                {{-- Cedula --}}
                                <label for="cedula" class="block text-sm font-medium text-gray-700">Cedula</label>
                                <input type="text" id="cedula" name="cedula"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->cedula ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                {{-- Data 1 habilitacao --}}
                                <label for=" data1habilitacao" class="block text-sm font-medium text-gray-700">Data
                                    primeira habilitação</label>
                                <input type="date" id="data1habilitacao" name="data1habilitacao"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->data1habilitacao ?? '' }}">
                            </div>

                            <div>
                                {{-- rg --}}
                                <label for="rg" class="block text-sm font-medium text-gray-700">RG</label>
                                <input type="text" id="rg" name="rg"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->rg ?? '' }}">
                            </div>

                            <div>
                                {{-- Uf Nascimento --}}
                                <label for="uf_nascimento" class="block text-sm font-medium text-gray-700">UF de
                                    Nascimento</label>
                                <input type="text" id="uf_nascimento" name="uf_nascimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->uf_nascimento ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                {{-- Município nascimento --}}
                                <label for="municipio_nascimento"
                                    class="block text-sm font-medium text-gray-700">Município de Nascimento</label>
                                <input type="text" id="municipio_nascimento" name="municipio_nascimento"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->municipio_nascimento ?? '' }}">
                            </div>

                            <div>
                                {{-- Município --}}
                                <label for="municipio"
                                    class="block text-sm font-medium text-gray-700">Município</label>
                                <input type="text" id="municipio" name="municipio"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->municipio ?? '' }}">
                            </div>

                            <div>
                                {{-- Cod. segurança --}}
                                <label for="cod_seguranca" class="block text-sm font-medium text-gray-700">Cod.
                                    segurança</label>
                                <input type="text" id="cod_seguranca" name="cod_seguranca"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->cod_seguranca ?? '' }}">
                            </div>

                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                            <div>
                                {{-- Categoria Cnh --}}
                                <label for="categoria_cnh" class="block text-sm font-medium text-gray-700">Categoria
                                    de
                                    CNH</label>
                                <input type="text" id="categoria_cnh" name="categoria_cnh"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->categoria_cnh ?? '' }}">
                            </div>

                            <div>
                                {{-- Grupo do Condutor --}}
                                <label for="grupo_condutor" class="block text-sm font-medium text-gray-700">Grupo do
                                    Condutor</label>
                                <input type="text" id="grupo_condutor" name="grupo_condutor"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->grupo_condutor ?? '' }}">
                            </div>

                            <div>
                                {{-- Apelido do Condutor --}}
                                <label for="apelido" class="block text-sm font-medium text-gray-700">Apelido do
                                    Condutor</label>
                                <input type="text" id="apelido" name="apelido"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    value="{{ $smartecCnh->apelido ?? '' }}">
                            </div>

                        </div>

                    </div>

                    <!-- Botões de Ação -->
                    <div class="flex justify-end space-x-4 mt-6">
                        <div class="flex justify-end space-x-4 mt-6">
                            <a href="{{ route('admin.condutores.index') }}"
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Voltar
                            </a>

                            <button type="submit" id="submit-button"
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function aplicarMascara(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    let valor = input.value.replace(/\D/g, ''); // Remove tudo que não é dígito
    
    switch (inputId) {
        case 'cpf':
        valor = formatarCPF(valor);
        break;
        //case 'cnh':
       // valor = formatarCNH(valor);
        //break;
        case 'rg':
        valor = formatarRG(valor);
        break;
    }
    
    input.value = valor;
    }

    // Formatação específica para CPF
    function formatarCPF(valor) {
    // Limita a 11 dígitos
    valor = valor.substring(0, 11);
    
    // Aplica a máscara: XXX.XXX.XXX-XX
    if (valor.length > 9) {
        return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
    } else if (valor.length > 6) {
        return valor.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
    } else if (valor.length > 3) {
        return valor.replace(/(\d{3})(\d{0,3})/, '$1.$2');
    }
    return valor;
    }

    // Formatação específica para CNH
    /*function formatarCNH(valor) {
    // Limita a 11 dígitos
    valor = valor.substring(0, 11);
    
    // Aplica a máscara: XXX.XXX.XXX-XX
    if (valor.length > 9) {
        return valor.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, '$1.$2.$3-$4');
    } else if (valor.length > 6) {
        return valor.replace(/(\d{3})(\d{3})(\d{0,3})/, '$1.$2.$3');
    } else if (valor.length > 3) {
        return valor.replace(/(\d{3})(\d{0,3})/, '$1.$2');
    }
    return valor;
    }
*/
    // Formatação específica para RG
    function formatarRG(valor) {
    // Limita a 9 dígitos (formato SP: XX.XXX.XXX-X)
    valor = valor.substring(0, 9);
    
    // Aplica a máscara: XX.XXX.XXX-X
    if (valor.length > 7) {
        return valor.replace(/(\d{2})(\d{3})(\d{3})(\d{0,1})/, '$1.$2.$3-$4');
    } else if (valor.length > 5) {
        return valor.replace(/(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
    } else if (valor.length > 2) {
        return valor.replace(/(\d{2})(\d{0,3})/, '$1.$2');
    }
    return valor;
    }

    // Função para inicializar os event listeners
    function inicializarMascaras() {
    const inputs = ['cpf', 'rg']; //'cnh'
    
    inputs.forEach(id => {
        const input = document.getElementById(id);
        if (input) {
        // Adiciona evento de input para aplicar máscara em tempo real
        input.addEventListener('input', () => aplicarMascara(id));
        
        // Define atributos do input
        switch (id) {
            case 'cpf':
            input.setAttribute('maxlength', '14'); // XXX.XXX.XXX-XX
            input.setAttribute('placeholder', '000.000.000-00');
            break;
            /*
            case 'cnh':
            input.setAttribute('maxlength', '14'); // XXX.XXX.XXX-XX
            input.setAttribute('placeholder', '000.000.000-00');
            break;
            */
            case 'rg':
            input.setAttribute('maxlength', '12'); // XX.XXX.XXX-X
            input.setAttribute('placeholder', '00.000.000-0');
            break;
        }
        }
    });
    }

    // Função para remover máscara e obter apenas números
    function obterApenasNumeros(inputId) {
    const input = document.getElementById(inputId);
    if (!input) return '';
    
    return input.value.replace(/\D/g, '');
    }

    // Função para validar se o campo está completo
    function validarCampo(inputId) {
    const numeros = obterApenasNumeros(inputId);
    
    switch (inputId) {
        case 'cpf':
        return numeros.length === 11;
        case 'cnh':
        return numeros.length === 11;
        case 'rg':
        return numeros.length === 9;
        default:
        return false;
    }
    }

    // Inicializa as máscaras quando o DOM estiver carregado
    document.addEventListener('DOMContentLoaded', inicializarMascaras);

</script>
@include('admin.condutores._scripts')
@endpush