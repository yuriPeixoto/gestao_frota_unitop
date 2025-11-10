@php
use Illuminate\Support\Facades\Storage;
@endphp

<!-- Cabeçalho -->
<div class="bg-white p-4 rounded-lg mb-6">
    <h3 class="text-lg font-medium mb-4 text-gray-800">Dados da Autorização Especial de Trânsito</h3>
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

    <form id="autorizacaoEspTransitoForm" method="POST" action="{{ $action }}" class="space-y-6"
        enctype="multipart/form-data">
        @csrf
        @if ($method === 'PUT')
        @method('PUT')
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Código -->
            <div>
                <label for="id_certificado_veiculo" class="block text-sm font-medium text-gray-700">Código</label>
                <input type="text" id="id_certificado_veiculo" name="id_certificado_veiculo" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ $autorizacao->id_certificado_veiculo ?? '' }}">
            </div>

            <!-- Tipo de Certificado -->
            <div>
                <x-forms.smart-select name="id_tipo_certificado" label="Tipo de Certificado"
                    placeholder="Selecione o tipo de certificado..." :options="$tiposCertificados"
                    :selected="old('id_tipo_certificado', $autorizacao->id_tipo_certificado ?? '')" required="true" />
            </div>

            <!-- Placa -->
            <div>
                <x-forms.smart-select name="id_veiculo" label="Placa" placeholder="Selecione a placa..."
                    :options="$veiculosFrequentes" :searchUrl="route('admin.api.veiculos.search')"
                    :selected="old('id_veiculo', $autorizacao->id_veiculo ?? '')" asyncSearch="true" />
            </div>

            <!-- Situação -->
            <div>
                <label for="situacao" class="block text-sm font-medium text-gray-700">Situação</label>
                <select name="situacao"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Selecione...</option>
                    <option value="Cancelado" {{ old('situacao', $autorizacao->situacao ?? '') === 'Cancelado' ?
                        'selected' : '' }}>Cancelado
                    </option>
                    <option value="A Vencer" {{ old('situacao', $autorizacao->situacao ?? '') === 'A Vencer' ?
                        'selected' : '' }}>A Vencer
                    </option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <!-- Chassi -->
            <div>
                <label for="chassi" class="block text-sm font-medium text-gray-700">Chassi</label>
                <input type="text" name="chassi" id="chassi" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>

            <div>
                <label for="filial" class="block text-sm font-medium text-gray-700">Filial</label>
                <input type="text" name="filial" id="filial" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>

            <!-- Renavam -->
            <div>
                <label for="renavam" class="block text-sm font-medium text-gray-700">Renavam</label>
                <input type="text" name="renavam" id="renavam" readonly
                    class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>


        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <!-- UF -->
            <div>
                <x-forms.smart-select name="id_uf" label="UF" placeholder="Selecione a UF..." :options="$estados"
                    :selected="old('id_uf', $autorizacao->id_uf ?? '')" />
            </div>
            <!-- Data de Vencimento -->
            <div>
                <x-forms.input type="date" name="data_vencimento" label="Data de Vencimento"
                    value="{{ old('data_vencimento', isset($autorizacao) ? date('Y-m-d', strtotime($autorizacao->data_vencimento)) : '') }}" />
            </div>

            <!-- Data de Emissão do Certificado -->
            <div>
                <x-forms.input type="date" name="data_certificacao" label="Data de Emissão do Certificado"
                    value="{{ old('data_certificacao', isset($autorizacao) ? date('Y-m-d', strtotime($autorizacao->data_certificacao)) : '') }}" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">

            <!-- Número do Certificado -->
            <div>
                <label for="numero_certificado" class="block text-sm font-medium text-gray-700">Número do
                    Certificado</label>
                <input type="text" id="numero_certificado" name="numero_certificado" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ old('numero_certificado', isset($autorizacao) ? $autorizacao->numero_certificado : '') }}">
            </div>
            <!-- Valor do Certificado -->
            <div>
                <label for="valor_certificado" class="block text-sm font-medium text-gray-700">Valor do
                    Certificado</label>
                <input type="text" id="valor_certificado" name="valor_certificado" step="0.01" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    value="{{ old('valor_certificado', $autorizacao->valor_certificado ?? '') }}">
            </div>

            <!-- Anexo Laudo -->
            <div>

                <label for="caminho_arquivo" class="block text-sm font-medium text-gray-700">Anexo Laudo (PDF, máx:
                    1MB)</label>
                <input type="file" id="caminho_arquivo" name="caminho_arquivo" accept=".pdf"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">

                @if (isset($autorizacao) && $autorizacao->caminho_arquivo)
                <div class="mt-2">
                    <span class="text-sm text-gray-500">Arquivo atual: </span>
                    @if (Storage::disk('public')->exists($autorizacao->caminho_arquivo))
                    <a href="{{ url('storage/' . $autorizacao->caminho_arquivo) }}" target="_blank"
                        class="text-sm text-indigo-600 hover:text-indigo-800">
                        Visualizar arquivo
                    </a>
                    @else
                    <span class="text-sm text-red-500">Arquivo não encontrado no servidor</span>
                    @endif
                </div>
                @endif
            </div>
        </div>
        <!-- Botões de Ação -->
        <div class="flex justify-end space-x-4 mt-8 pb-4">
            <a href="{{ route('admin.autorizacoesesptransitos.index') }}"
                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Voltar
            </a>

            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ $method === 'PUT' ? 'Atualizar' : 'Salvar' }}
            </button>
        </div>
    </form>
</div>

@push('scripts')
@include('admin.autorizacoesesptransitos._scripts')
@endpush