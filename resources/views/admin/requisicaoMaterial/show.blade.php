<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Requisição de Material #{{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.requisicaoMaterial.index') }}"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar
                </a>

                <!-- Botões de ação -->
                <div class="flex space-x-2">
                    @if ($relacaoSolicitacaoPecas->podeSerEditado())
                        <a href="{{ route('admin.requisicaoMaterial.edit', $relacaoSolicitacaoPecas->id_solicitacao_pecas) }}"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                </path>
                            </svg>
                            Editar
                        </a>
                    @endif

                    @if ($relacaoSolicitacaoPecas->podeSerEnviadaParaAprovacao())
                        <button
                            onclick="podeSerEnviadaParaAprovacao({{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }})"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8">
                                </path>
                            </svg>
                            Enviar para Aprovação
                        </button>
                    @endif

                    @if ($relacaoSolicitacaoPecas->podeAprovar())
                        <button onclick="aprovar({{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }})"
                            class="inline-flex items-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Aprovar
                        </button>

                        <button onclick="aprovarSemTransferencia({{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }})"
                            class="inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            Aprovar sem Transferência
                        </button>

                        <button onclick="revisar({{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }})"
                            class="inline-flex items-center rounded-md border border-transparent bg-yellow-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                            <x-icons.arrow-path-rounded-square class="mr-1 h-4 w-4" />
                            Revisar
                        </button>

                        <button onclick="reprovar({{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }})"
                            class="inline-flex items-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Reprovar
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Mensagens de Sucesso/Erro -->
            @if (session('success'))
                <div class="mb-4 border-l-4 border-green-500 bg-green-100 p-4 text-green-700" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 border-l-4 border-red-500 bg-red-100 p-4 text-red-700" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 bg-white p-6">
                    <div class="space-y-6">
                        <!-- Status atual destacado -->
                        <div class="mb-4 flex items-center">
                            <span
                                class="@if ($relacaoSolicitacaoPecas->situacao == 'AGUARDANDO APROVAÇÃO') bg-yellow-100 text-yellow-800
                                    @elseif($relacaoSolicitacaoPecas->situacao == 'APROVADO') bg-green-100 text-green-800
                                    @elseif($relacaoSolicitacaoPecas->situacao == 'FINALIZADA') bg-blue-100 text-blue-800
                                    @elseif($relacaoSolicitacaoPecas->situacao == 'CANCELADA') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif mr-2 inline-flex rounded-full px-3 py-1 text-sm font-semibold leading-5">
                                {{ $relacaoSolicitacaoPecas->situacao }}
                            </span>

                            @if ($relacaoSolicitacaoPecas->aprovacao_gestor === true)
                                <span class="text-sm text-gray-600">
                                    Aprovada por {{ $relacaoSolicitacaoPecas->usuarioAprovador->name ?? 'N/A' }} em
                                    {{ $relacaoSolicitacaoPecas->data_aprovacao ? \Carbon\Carbon::parse($relacaoSolicitacaoPecas->data_aprovacao)->format('d/m/Y H:i') : 'N/A' }}
                                </span>
                            @elseif($relacaoSolicitacaoPecas->aprovacao_gestor === false && $relacaoSolicitacaoPecas->situacao == 'REPROVADO')
                                <span class="text-sm text-gray-600">
                                    Reprovada
                                </span>
                            @endif
                        </div>

                        <!-- Informações principais em 2 colunas -->
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Coluna 1 -->
                            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações da Requisição
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Número da Requisição
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->id_solicitacao_pecas }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Usuário Responsável
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->usuario->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Departamento
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->departamentoPecas->descricao_departamento ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Data da Requisição
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->data_inclusao ? \Carbon\Carbon::parse($relacaoSolicitacaoPecas->data_inclusao)->format('d/m/Y H:i') : 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Situação
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                <span
                                                    class="@if ($relacaoSolicitacaoPecas->situacao == 'AGUARDANDO APROVAÇÃO') bg-yellow-100 text-yellow-800
                                                        @elseif($relacaoSolicitacaoPecas->situacao == 'APROVADO') bg-green-100 text-green-800
                                                        @elseif($relacaoSolicitacaoPecas->situacao == 'FINALIZADA') bg-blue-100 text-blue-800
                                                        @elseif($relacaoSolicitacaoPecas->situacao == 'CANCELADA') bg-red-100 text-red-800
                                                        @else bg-gray-100 text-gray-800 @endif inline-flex rounded-md px-2 text-xs font-semibold leading-5">
                                                    {{ $relacaoSolicitacaoPecas->situacao }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <!-- Coluna 2 -->
                            <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                                <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Informações Adicionais
                                    </h3>
                                </div>
                                <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                                    <dl class="sm:divide-y sm:divide-gray-200">
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Filial
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->filial->name ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Veículo
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->veiculo->placa ?? 'N/A' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Fornecedor/Terceiro
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                {{ $relacaoSolicitacaoPecas->fornecedor->nome_fornecedor ?? 'Não especificado' }}
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Requisição Pneu
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                @if ($relacaoSolicitacaoPecas->requisicao_pneu)
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">
                                                        Sim
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                                        Não
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                        <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6 sm:py-5">
                                            <dt class="text-sm font-medium text-gray-500">
                                                Requisição TI
                                            </dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 sm:mt-0">
                                                @if ($relacaoSolicitacaoPecas->requisicao_ti)
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">
                                                        Sim
                                                    </span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">
                                                        Não
                                                    </span>
                                                @endif
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Observações -->
                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Observações
                                </h3>
                            </div>
                            <div class="border-t border-gray-200 p-6">
                                @if ($relacaoSolicitacaoPecas->observacao)
                                    <p class="whitespace-pre-line text-sm text-gray-700">
                                        {{ $relacaoSolicitacaoPecas->observacao }}</p>
                                @else
                                    <p class="text-sm italic text-gray-500">Nenhuma observação.</p>
                                @endif

                                @if ($relacaoSolicitacaoPecas->anexo_imagem)
                                    <div class="mt-4 rounded-lg border border-green-100 bg-green-50 p-4">
                                        <h4 class="mb-2 text-sm font-medium text-green-800">Anexo da Requisição:</h4>
                                        <div class="flex items-center space-x-3">
                                            @if (in_array(pathinfo($relacaoSolicitacaoPecas->anexo_imagem, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                                <img src="{{ asset('storage/' . $relacaoSolicitacaoPecas->anexo_imagem) }}"
                                                    alt="Anexo"
                                                    class="h-16 w-16 cursor-pointer rounded border object-cover"
                                                    onclick="openAttachmentPreview('{{ asset('storage/' . $relacaoSolicitacaoPecas->anexo_imagem) }}', '{{ basename($relacaoSolicitacaoPecas->anexo_imagem) }}')">
                                            @else
                                                <div class="flex h-16 w-16 cursor-pointer items-center justify-center rounded border bg-gray-100"
                                                    onclick="openAttachmentPreview('{{ asset('storage/' . $relacaoSolicitacaoPecas->anexo_imagem) }}', '{{ basename($relacaoSolicitacaoPecas->anexo_imagem) }}')">
                                                    <svg class="h-8 w-8 text-gray-600" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                        </path>
                                                    </svg>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                @endif

                                @if ($relacaoSolicitacaoPecas->justificativa_de_finalizacao)
                                    <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4">
                                        <h4 class="mb-1 text-sm font-medium text-blue-800">Justificativa de
                                            Finalização:
                                        </h4>
                                        <p class="whitespace-pre-line text-sm text-blue-700">
                                            {{ $relacaoSolicitacaoPecas->justificativa_de_finalizacao }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Produtos Solicitados -->
                        @php
                            $produtosSolicitados = \App\Models\ProdutosSolicitacoes::with(['produto'])
                                ->where('id_relacao_solicitacoes', $relacaoSolicitacaoPecas->id_solicitacao_pecas)
                                ->get();
                        @endphp

                        <div class="overflow-hidden bg-white shadow sm:rounded-lg">
                            <div class="flex items-center justify-between bg-gray-50 px-4 py-5 sm:px-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">
                                    Produtos Solicitados
                                </h3>
                                <span class="text-sm text-gray-500">{{ $produtosSolicitados->count() }}
                                    item(ns)</span>
                            </div>
                            <div class="border-t border-gray-200">
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Código
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Descrição
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Quantidade
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Observação
                                                </th>
                                                <th scope="col"
                                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                                    Anexo
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @forelse($produtosSolicitados as $item)
                                                <tr>
                                                    <td
                                                        class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                                        {{ $item->id_protudos ?? 'N/A' }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        {{ $item->produto->descricao_produto ?? 'Produto não encontrado' }}
                                                    </td>
                                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                                        {{ number_format($item->quantidade, 2, ',', '.') }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        {{ $item->observacao ?? '-' }}
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-500">
                                                        @if ($item->anexo_imagem)
                                                            <div class="flex items-center space-x-2">
                                                                @if (in_array(pathinfo($item->anexo_imagem, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                                                    <img src="{{ asset('storage/' . $item->anexo_imagem) }}"
                                                                        alt="Anexo"
                                                                        class="h-8 w-8 cursor-pointer rounded border border-gray-300 object-cover"
                                                                        onclick="openAttachmentPreview('{{ asset('storage/' . $item->anexo_imagem) }}', '{{ basename($item->anexo_imagem) }}')">
                                                                @else
                                                                    <div class="flex h-8 w-8 cursor-pointer items-center justify-center rounded border bg-gray-100"
                                                                        onclick="openAttachmentPreview('{{ asset('storage/' . $item->anexo_imagem) }}', '{{ basename($item->anexo_imagem) }}')">
                                                                        <svg class="h-4 w-4 text-gray-600"
                                                                            fill="none" stroke="currentColor"
                                                                            viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                stroke-width="2"
                                                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                                            </path>
                                                                        </svg>
                                                                    </div>
                                                                @endif

                                                            </div>
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5"
                                                        class="px-6 py-4 text-center text-sm text-gray-500">
                                                        Nenhum produto encontrado.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/requisicaoMaterial/attachment-utils.js') }}"></script>
        <script>
            function podeSerEnviadaParaAprovacao(id) {
                const confirmacao = confirm("Você tem certeza que deseja enviar para aprovação esta requisição?");
                if (!confirmacao) return;

                // Validar se o ID foi fornecido
                if (!id || id === 'undefined' || id === undefined) {
                    alert('Erro: ID da requisição não encontrado.');
                    return;
                }

                let formData = new FormData();

                formData.append('id', id);

                fetch(`{{ route('admin.requisicaoMaterial.enviarAprovacao') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Solicitação enviada para aprovação com sucesso!');
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || 'Erro ao enviar solicitação para aprovação');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao processar a solicitação');
                    });
            };

            function aprovar(id) {
                console.log('=== APROVAR DEBUG ===');
                console.log('ID recebido:', id);

                const confirmacao = confirm("Você tem certeza que deseja aprovar esta requisição?");
                if (!confirmacao) return;

                // Validar se o ID foi fornecido
                if (!id || id === 'undefined' || id === undefined) {
                    alert('Erro: ID da requisição não encontrado.');
                    return;
                }

                let formData = new FormData();
                formData.append('id', id);

                console.log('FormData criado:', formData.get('id'));
                console.log('URL da requisição:', `{{ route('admin.requisicaoMaterial.aprovar') }}`);

                fetch(`{{ route('admin.requisicaoMaterial.aprovar') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response ok:', response.ok);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            // Se há itens de transferência, mostrar modal
                            if (data.show_transfer_modal && data.transfer_items) {
                                console.log('Mostrando modal de transferência');
                                mostrarModalTransferencias(id, data.transfer_items);
                            } else {
                                // Aprovação normal sem transferências
                                console.log('Aprovação normal');
                                alert('Solicitação aprovada com sucesso!');
                                if (data.redirect_url) {
                                    window.location.href = data.redirect_url;
                                } else {
                                    window.location.reload();
                                }
                            }
                        } else {
                            console.error('Erro do servidor:', data.message);
                            alert(data.message || 'Erro ao aprovar solicitação');
                        }
                    })
                    .catch(error => {
                        console.error('Error details:', error);
                        alert('Erro ao processar a solicitação: ' + error.message);
                    });
            }

            function aprovarSemTransferencia(id) {
                console.log('=== APROVAR SEM TRANSFERENCIA DEBUG ===');
                console.log('ID recebido:', id);

                const confirmacao = confirm("Você tem certeza que deseja aprovar esta requisição SEM gerar transferências?");
                if (!confirmacao) return;

                // Validar se o ID foi fornecido
                if (!id || id === 'undefined' || id === undefined) {
                    alert('Erro: ID da requisição não encontrado.');
                    return;
                }

                let formData = new FormData();
                formData.append('id', id);

                console.log('FormData criado:', formData.get('id'));
                console.log('URL da requisição:', `{{ route('admin.requisicaoMaterial.aprovarSemTransferencia') }}`);

                fetch(`{{ route('admin.requisicaoMaterial.aprovarSemTransferencia') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        console.log('Response status:', response.status);
                        console.log('Response ok:', response.ok);
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Response data:', data);
                        if (data.success) {
                            alert('Solicitação aprovada com sucesso (sem transferências)!');
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            console.error('Erro do servidor:', data.message);
                            alert(data.message || 'Erro ao aprovar solicitação');
                        }
                    })
                    .catch(error => {
                        console.error('Error details:', error);
                        alert('Erro ao processar a solicitação: ' + error.message);
                    });
            }

            function revisar(id) {
                const confirmacao = confirm("Você tem certeza que deseja revisar esta requisição?");
                if (!confirmacao) return;

                // Validar se o ID foi fornecido
                if (!id || id === 'undefined' || id === undefined) {
                    alert('Erro: ID da requisição não encontrado.');
                    return;
                }

                let formData = new FormData();

                formData.append('id', id);

                fetch(`{{ route('admin.requisicaoMaterial.revisar') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Solicitação enviada para aprovação com sucesso!');
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || 'Erro ao enviar solicitação para aprovação');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao processar a solicitação');
                    });
            };

            function reprovar(id) {
                const confirmacao = confirm("Você tem certeza que deseja reprovar esta requisição?");
                if (!confirmacao) return;

                // Validar se o ID foi fornecido
                if (!id || id === 'undefined' || id === undefined) {
                    alert('Erro: ID da requisição não encontrado.');
                    return;
                }

                let formData = new FormData();

                formData.append('id', id);

                fetch(`{{ route('admin.requisicaoMaterial.reprovar') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Solicitação enviada para aprovação com sucesso!');
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || 'Erro ao enviar solicitação para aprovação');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao processar a solicitação');
                    });
            };

            // Funções para modal de transferências
            function mostrarModalTransferencias(requisicaoId, transferItems) {
                // Remover modal existente se houver
                const modalExistente = document.getElementById('modalTransferencias');
                if (modalExistente) {
                    modalExistente.remove();
                }

                // Criar conteúdo do modal
                let itensHtml = '';
                transferItems.forEach((item, index) => {
                    itensHtml += `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <label class="flex items-center">
                                    <input type="checkbox"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                           data-item='${JSON.stringify(item)}'
                                           checked>
                                </label>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${item.produto_codigo}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                ${item.produto_nome}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <input type="number" disabled
                                       class="quantity-input w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       value="${item.quantidade}"
                                       min="1"
                                       max="${item.quantidade}"
                                       data-item-id="${item.id}">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                ${item.observacao}
                            </td>
                        </tr>
                    `;
                });

                const modalHtml = `
                    <div id="modalTransferencias" class="fixed inset-0 z-50 overflow-y-auto bg-gray-600 bg-opacity-50">
                        <div class="flex items-center justify-center min-h-full p-4">
                            <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        Itens para Transferência
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Selecione quais itens devem ser transferidos ao aprovar esta requisição.
                                    </p>
                                </div>

                                <div class="px-6 py-4">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        <label class="flex items-center">
                                                            <input type="checkbox" id="selectAll"
                                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                                                   checked>
                                                        </label>
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Código
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Produto
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Quantidade
                                                    </th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        Observação
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                ${itensHtml}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                                    <button type="button" onclick="fecharModalTransferencias()"
                                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        Cancelar
                                    </button>
                                    <button type="button" onclick="processarAprovacaoComTransferencias(${requisicaoId})"
                                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        Aprovar e Criar Transferências
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Adicionar modal ao DOM
                document.body.insertAdjacentHTML('beforeend', modalHtml);

                // Configurar eventos
                configurarEventosModal();
            }

            function configurarEventosModal() {
                // Evento para selecionar/deselecionar todos
                document.getElementById('selectAll').addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('#modalTransferencias tbody input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                });

                // Fechar modal ao clicar fora
                document.getElementById('modalTransferencias').addEventListener('click', function(e) {
                    if (e.target === this) {
                        fecharModalTransferencias();
                    }
                });

                // Fechar modal com ESC
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        fecharModalTransferencias();
                    }
                });
            }

            function fecharModalTransferencias() {
                const modal = document.getElementById('modalTransferencias');
                if (modal) {
                    modal.remove();
                }
            }

            function processarAprovacaoComTransferencias(requisicaoId) {
                const checkboxes = document.querySelectorAll('#modalTransferencias tbody input[type="checkbox"]:checked');
                const itensTransferencia = [];

                checkboxes.forEach(checkbox => {
                    const itemData = JSON.parse(checkbox.getAttribute('data-item'));
                    const quantityInput = checkbox.closest('tr').querySelector('.quantity-input');
                    const quantidade = parseInt(quantityInput.value);

                    if (quantidade > 0) {
                        itensTransferencia.push({
                            id: itemData.id,
                            quantidade: quantidade,
                            filial_origem: itemData.filial_origem
                        });
                    }
                });

                if (itensTransferencia.length === 0) {
                    alert('Selecione ao menos um item para transferência.');
                    return;
                }

                const formData = new FormData();
                formData.append('id', requisicaoId);
                formData.append('itens_transferencia', JSON.stringify(itensTransferencia));

                fetch(`{{ route('admin.requisicaoMaterial.aprovarComTransferencia') }}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        fecharModalTransferencias();

                        if (data.success) {
                            alert('Solicitação aprovada e transferências criadas com sucesso!');
                            if (data.redirect_url) {
                                window.location.href = data.redirect_url;
                            } else {
                                window.location.reload();
                            }
                        } else {
                            alert(data.message || 'Erro ao processar aprovação com transferências');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        fecharModalTransferencias();
                        alert('Erro ao processar a solicitação');
                    });
            }
        </script>
    @endpush
</x-app-layout>
