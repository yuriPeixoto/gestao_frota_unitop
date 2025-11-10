<div class="results-table">
    <x-tables.table>
        <x-tables.header>
            <x-tables.head-cell>Cód.<br>Devolução<br>Veículo</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Inclusão</x-tables.head-cell>
            <x-tables.head-cell>Data<br>Alteração</x-tables.head-cell>
            <x-tables.head-cell>Tipo Veiculo</x-tables.head-cell>
            <x-tables.head-cell>Placa</x-tables.head-cell>
            <x-tables.head-cell>Usuario</x-tables.head-cell>
            <x-tables.head-cell>Data Inicio</x-tables.head-cell>
            <x-tables.head-cell>Data Fim</x-tables.head-cell>
            <x-tables.head-cell>situação</x-tables.head-cell>
            <x-tables.head-cell>Ações</x-tables.head-cell>
        </x-tables.header>

        <x-tables.body>
            @forelse ($devolucaoImobilizadoVeiculo as $index => $devolucaoImobilizadoVeiculos)
            <x-tables.row :index="$index">
                <x-tables.cell>{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}</x-tables.cell>
                <x-tables.cell >{{ $devolucaoImobilizadoVeiculos->data_inclusao?->format('d/m/Y') }}</x-tables.cell>
                <x-tables.cell >{{ $devolucaoImobilizadoVeiculos->data_alteracao?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $devolucaoImobilizadoVeiculos->tipoEquipamento->descricao_tipo ?? '-' }}</x-tables.cell>
                <x-tables.cell>{{ $devolucaoImobilizadoVeiculos->veiculo->placa ?? '-' }}</x-tables.cell>
                <x-tables.cell >{{ $devolucaoImobilizadoVeiculos->user->name ?? $devolucaoImobilizadoVeiculos->id_usuario }}</x-tables.cell>
                <x-tables.cell >{{ $devolucaoImobilizadoVeiculos->data_inicio?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                <x-tables.cell >{{ $devolucaoImobilizadoVeiculos->data_fim?->format('d/m/Y') ?? '-' }}</x-tables.cell>
                <x-tables.cell>
                    <a href="#" class="inline-flex items-center p-1.5 border border-transparent gap-2 rounded-full bg-green-100 text-green-800"
                         onclick="openTimeline( {{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }} )">
                        <x-icons.eye class="h-3.5 w-3.5" /> {{ $devolucaoImobilizadoVeiculos->departamentoTransferencia->departamento }} 
                    </a>
                </x-tables.cell>

                <x-tables.cell>
                    <div class="flex items-center space-x-2">
                        @if ($devolucaoImobilizadoVeiculos->status != '8' && $devolucaoImobilizadoVeiculos->status != '9')                                                      
                        <a href="{{ route('admin.devolucaoimobilizadoveiculo.edit', $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo) }}"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            title="Editar Transferência Imobilizado">
                            <x-icons.pencil class="h-3.5 w-3.5" />
                        </a>
                        @endif

                        {{-- Agora usa os dados processados no controller --}}
                        @if($devolucaoImobilizadoVeiculos->status_config['processo'])
                        <a onclick="abrirModal({{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}, '{{ ($devolucaoImobilizadoVeiculos->veiculo->placa ?? '') }}')"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer transition-colors duration-200 {{ $devolucaoImobilizadoVeiculos->status_config['classes'] }}"
                            title="{{ $devolucaoImobilizadoVeiculos->status_config['title'] }}">
                            {{-- Ícone de check só aparece se o status for 'Aprovado' --}}
                            <x-icons.check class="h-3.5 w-3.5" />
                        </a>
                        @endif

                        {{-- Se houver ordem de serviço --}}
                        @if($devolucaoImobilizadoVeiculos->id_ordem_servico || $devolucaoImobilizadoVeiculos->id_sinistro)
                        <a onclick="showModal('visualizar-sinistro-os-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}')"
                            class="inline-flex items-center p-1.5 border border-transparent gap-2 rounded-full bg-green-100 text-green-800" 
                            title="Visualizar Ordem de Serviço">
                            <x-icons.eye class="h-3.5 w-3.5" />
                        </a>
                        @endif

                        @if($devolucaoImobilizadoVeiculos->checklist_id)
                        <a onclick="showModal(`visualizar-checklist-{{ $devolucaoImobilizadoVeiculos->id_devolucao_imobilizado_veiculo }}`)"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm  focus:outline-none focus:ring-2 focus:ring-offset-2 bg-green-100 text-green-800"
                            title="Visualizar Checklist">
                            {{-- Ícone de check só aparece se o status for 'Aprovado' --}}
                            <x-icons.eye class="h-3.5 w-3.5" />
                        </a>
                        @endif

                        @if($devolucaoImobilizadoVeiculos->status == '9')
                        <a href="{{ route('admin.veiculos.edit', $devolucaoImobilizadoVeiculos->id_veiculo) }}"
                            class="inline-flex items-center p-1.5 border border-transparent rounded-full shadow-sm  focus:outline-none focus:ring-2 focus:ring-offset-2 bg-green-100 text-green-800"
                            title="Visualizar Veiculo">
                            <x-icons.truck class="h-3.5 w-3.5 text-black-800" />
                        </a>
                        @endif
                    </div>
                </x-tables.cell>

                @include('admin.devolucaoimobilizadoveiculo._modals')

            </x-tables.row>
            @empty
            <x-tables.empty cols="9" message="Nenhum registro encontrado" />
            @endforelse

        </x-tables.body>

        <!-- Modal -->
        <div id="timelineModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Timeline da Transferência</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="transferInfo" class="transfer-info">
                        <!-- Informações da transferência serão inseridas aqui -->
                    </div>
                    <div class="timelinetransf">
                        <div id="timelineContainer" class="timelinetransf-container">

                            <!-- Timeline será inserida aqui -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </x-tables.table>

    <div class="mt-4">
        {{ $devolucaoImobilizadoVeiculo->links() }}
    </div>

</div>
