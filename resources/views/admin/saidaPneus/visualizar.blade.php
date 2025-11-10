<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Visualizar Requisição de Saída de Pneus') }} - #{{ $requisicao->id_requisicao_pneu }}
            </h2>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.saidaPneus.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <x-icons.arrow-back class="h-4 w-4 mr-2" />
                    Voltar
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white border-b border-gray-200">
            
            <!-- Informações da Requisição -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informações Gerais</h3>
                    <dl class="space-y-2">
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Código:</dt>
                            <dd class="text-gray-900">{{ $requisicao->id_requisicao_pneu }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Data Inclusão:</dt>
                            <dd class="text-gray-900">{{ format_date($requisicao->data_inclusao, 'd/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Data Alteração:</dt>
                            <dd class="text-gray-900">{{ format_date($requisicao->data_alteracao, 'd/m/Y H:i') }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Situação:</dt>
                            <dd>
                                @php
                                    $situacaoDisplay = $requisicao->situacao === 'FINALIZADA' ? 'BAIXADA' : $requisicao->situacao;
                                    $badgeColor = match($requisicao->situacao) {
                                        'APROVADO' => 'bg-green-50 text-green-700 ring-green-600/20',
                                        'INICIADA' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                                        'FINALIZADA' => 'bg-gray-50 text-gray-700 ring-gray-600/20',
                                        'AGUARDANDO DOCUMENTO DE VENDA' => 'bg-yellow-50 text-yellow-700 ring-yellow-600/20',
                                        'BAIXADO PARCIAL' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
                                        default => 'bg-gray-50 text-gray-700 ring-gray-600/20'
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $badgeColor }}">
                                    {{ $situacaoDisplay }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Responsáveis</h3>
                    <dl class="space-y-2">
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Filial:</dt>
                            <dd class="text-gray-900">{{ $requisicao->filial->name ?? 'Não informado' }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Usuário Solicitante:</dt>
                            <dd class="text-gray-900">{{ $requisicao->usuarioSolicitante->name ?? 'Não informado' }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Usuário Estoque:</dt>
                            <dd class="text-gray-900">{{ $requisicao->usuarioEstoque->name ?? 'Não assumido' }}</dd>
                        </div>
                        @if($requisicao->observacao)
                        <div class="flex">
                            <dt class="font-medium text-gray-500 w-1/3">Observação:</dt>
                            <dd class="text-gray-900">{{ $requisicao->observacao }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Modelos de Pneus -->
            @if($requisicao->requisicaoPneuModelos->count() > 0)
            <div class="mt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Modelos de Pneus Solicitados</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Itens</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($requisicao->requisicaoPneuModelos as $modelo)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $modelo->modeloPneu->nome ?? 'Modelo não encontrado' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $modelo->quantidade }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($modelo->requisicaoPneuItens->count() > 0)
                                        <div class="space-y-1">
                                            @foreach($modelo->requisicaoPneuItens as $item)
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                        Pneu ID: {{ $item->id_pneu }}
                                                    </span>
                                                    @if($item->pneu)
                                                    <span class="text-xs text-gray-500">
                                                        {{ $item->pneu->numero_fogo ?? 'S/N' }}
                                                    </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-500 italic">Nenhum pneu selecionado</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>