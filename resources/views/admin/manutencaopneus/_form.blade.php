<div class="space-y-6">
    {{-- Mensagens de Feedback --}}
    @if (session('error'))
    <div class="alert-danger alert">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
    <div class="mb-4 rounded bg-red-50 p-4">
        <ul class="list-inside list-disc text-red-600">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-forms.input name="id_manutencao_pneu" label="Código Requisição" readonly
            value="{{ old('id_manutencao_pneu', $manutencaoPneus->id_manutencao_pneu ?? '') }}" />

        <x-forms.smart-select name="id_filial" label="Filial" placeholder="Selecione a filial..."
            :options="$formOptions['filiais']" :selected="old('id_filial', $manutencaoPneus->id_filial ?? '')"
            asyncSearch="false" />

        <x-forms.smart-select name="id_fornecedor" label="Fornecedor" placeholder="Selecione o fornecedor..."
            :options="$fornecedoresFrequentes" :searchUrl="route('admin.api.fornecedores.search')"
            :selected="old('id_fornecedor', $manutencaoPneus->id_fornecedor ?? '')" asyncSearch="true" />
    </div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <x-forms.smart-select name="id_pneu" label="Número de fogo" placeholder="Selecione o número de fogo..."
            :options="$formOptions['pneus']" :searchUrl="route('admin.api.pneu.search')"
            :selected="old('id_pneu', $manutencaoPneus->id_pneu ?? '')" asyncSearch="true" />

        <x-forms.input name=id_tipo_modelo_pneu label="Modelo Pneu" readonly
            value="{{ old('id_tipo_modelo_pneu', $manutencaoPneus->id_tipo_modelo_pneu ?? '') }}" />

        <x-forms.smart-select name="id_tipo_manutencao" label="Tipo Manutenção" :options="$formOptions['tipoReforma']"
            :selected="old('id_tipo_manutencao', $manutencaoPneus->id_tipo_manutencao ?? 4)" />

    </div>
    {{--
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php
        $isUpdate = request()->routeIs('admin.manutencaopneus.edit');
        @endphp

        @if($isUpdate)
        <x-forms.input name="nf_envio" label="Nº NF Envio"
            value="{{ old('nf_envio', $manutencaoPneus->nf_envio ?? '') }}" />

        <x-forms.input name="chave_nf_envio" label="Chave Acesso NF Envio"
            value="{{ old('chave_nf_envio', $manutencaoPneus->chave_nf_envio ?? '') }}" />
        @endif
    </div> --}}

    <div class="flex justify-start items-center">
        <button type="button" onclick="adicionarHistorico()"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Adicionar Manutenção
        </button>
    </div>

    <!-- Campo hidden para armazenar os históricos -->
    <input type="hidden" name="historicos" id="historicos_json"
        value="{{ isset($historicos) ? json_encode($historicos) : '[]' }}">

    <div class="col-span-full">
        <table class="min-w-full divide-y divide-gray-200 tabelaHistorico">
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
                        Nº de Fogo
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Modelo Pneu
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipo Manutenção
                    </th>
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Ações
                    </th>
                </tr>
            </thead>
            <tbody id="tabelaHistoricoBody" class="bg-white divide-y divide-gray-200">
                <!-- Linhas serão adicionadas dinamicamente pelo JavaScript -->
            </tbody>
        </table>
    </div>

    <!-- Botões -->
    <div class="flex justify-right space-x-3 col-span-full">
        <button type="button" id="submit-form" onclick="verificarSalvar()"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            {{ isset($manutencaoPneus) ? 'Atualizar' : 'Salvar' }}
        </button>

        <a href="{{ route('admin.envioerecebimentopneus.index') }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Cancelar
        </a>

        <a href="{{ route('admin.manutencaopneus.imprimir', isset($manutencaoPneus) ? $manutencaoPneus->id_manutencao_pneu : 0) }}"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            <x-icons.print class="h-3 w-3 mr-2" />
            Imprimir Relação
        </a>
    </div>
</div>
<!-- Modal NF e Chave -->
<div id="modalNF" class="fixed inset-0 flex items-center justify-center  hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            Preencha os campos para enviar para aprovação <br>

        </h2>
        <span>Após o preenchimento, não será possível realizar alterações futuras.</span>
        <div class="mb-4">
            <x-forms.input name="nf_envio" label="Nº NF Envio"
                value="{{ old('nf_envio', $manutencaoPneus->nf_envio ?? '') }}" />
        </div>

        <div class="mb-4">
            <x-forms.input name="chave_nf_envio" label="Chave Acesso NF Envio"
                value="{{ old('chave_nf_envio', $manutencaoPneus->chave_nf_envio ?? '') }}" id="chave_nf_envio"
                maxlength="44" oninput="atualizarContagem()" />

            <small id="contador-chave" class="text-green-500">
                0 / 44 dígitos
            </small>
        </div>

        <div class="mb-4">
            <x-forms.input id="valor_nf" name="valor_nf" label="Valor Nota Fiscal" class="valor-moeda"
                value="{{ old('nf_envio', $manutencaoPneus->nf_envio ?? '') }}" />
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">NF (PDF, JPG, PNG)</label>
            <input type="file" name="doc_nf" accept=".pdf,.jpg,.jpeg,.png"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            <small class="text-gray-500">Formatos: PDF, JPG, PNG (Max: 2MB)</small>
        </div>
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Extrato coleta do pneu (PDF, JPG, PNG)</label>
            <input type="file" name="doc_extrato" accept=".pdf,.jpg,.jpeg,.png"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            <small class="text-gray-500">Formatos: PDF, JPG, PNG (Max: 2MB)</small>
        </div>

        <div class="flex justify-end space-x-3 mt-6">
            <button type="button" onclick="fecharModalNF()"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Cancelar
            </button>
            <button type="button" onclick="confirmarSalvar()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Confirmar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js\pneus\manutencaopneus\manutencao-pneus.js') }}"></script>
@include('admin.manutencaopneus._scripts')
@endpush




<script>
    function verificarSalvar() {
    document.getElementById('modalNF').classList.remove('hidden');
}

function fecharModalNF() {
    document.getElementById('modalNF').classList.add('hidden');
    document.getElementById('form-manutencao').submit();
}

function confirmarSalvar() {
    const nf = document.querySelector('[name="nf_envio"]').value;
    const chave = document.querySelector('[name="chave_nf_envio"]').value;

    if (nf.trim() === '' || chave.trim() === '') {
        alert('Preencha o número da NF e a chave de acesso!');
        return;
    }

    fecharModalNF();
    document.getElementById('form-manutencao').submit();
}


</script>
<script>
    function atualizarContagem() {
        const input = document.getElementById("chave_nf_envio");
        const contador = document.getElementById("contador-chave");
        contador.textContent = input.value.length + " / 44 dígitos";
    }

    // inicializa ao carregar a página
    document.addEventListener("DOMContentLoaded", atualizarContagem);

    
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Função para formatar como moeda
        function formatarMoeda(valor) {
            // Remove tudo que não é número
            let numero = valor.replace(/\D/g, '');
            
            // Converte para float e divide por 100 para ter decimais
            numero = (numero / 100).toFixed(2);
            
            // Formata como moeda brasileira
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(numero);
        }

        // Aplica a formatação aos campos com a classe 'valor-moeda'
        document.querySelectorAll('.valor-moeda').forEach(function(campo) {
            // Formata o valor inicial
            if (campo.value && campo.value !== '0') {
                campo.value = formatarMoeda(campo.value.toString());
            } else {
                campo.value = 'R$ 0,00';
            }

            // Formata enquanto digita
            campo.addEventListener('input', function(e) {
                let valor = e.target.value;
                e.target.value = formatarMoeda(valor);
            });
        });
    });

</script>