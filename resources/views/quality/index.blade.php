<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎯 Dashboard de Qualidade
            </h2>
            <a href="{{ route('quality.report') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                <i class="fas fa-chart-bar mr-2"></i>
                Relatório Completo
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Estatísticas --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-clock text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Aguardando Análise</p>
                            <p class="text-2xl font-bold">{{ $stats['aguardando'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-check text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Aprovados Este Mês</p>
                            <p class="text-2xl font-bold">{{ $stats['aprovados_mes'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-times text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Rejeitados Este Mês</p>
                            <p class="text-2xl font-bold">{{ $stats['rejeitados_mes'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-percentage text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Taxa de Aprovação</p>
                            <p class="text-2xl font-bold">{{ $stats['taxa_aprovacao'] }}%</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Melhorias Aguardando Análise --}}
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-semibold text-lg text-gray-900">
                        <i class="fas fa-lightbulb mr-2 text-yellow-500"></i>
                        Melhorias Aguardando Análise ({{ $pendingTickets->count() }})
                    </h3>
                </div>

                @if($pendingTickets->isEmpty())
                    <div class="p-12 text-center">
                        <i class="fas fa-check-circle text-6xl text-green-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Nenhuma melhoria aguardando análise</p>
                        <p class="text-gray-400 text-sm mt-2">Todas as solicitações foram revisadas!</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($pendingTickets as $ticket)
                            <div class="p-6 hover:bg-gray-50 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-mono text-sm font-bold text-gray-700">#{{ $ticket->ticket_number }}</span>

                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800">
                                                {{ $ticket->priority->label() }}
                                            </span>

                                            @if($ticket->isOverdue())
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    ATRASADO
                                                </span>
                                            @endif

                                            <span class="text-xs text-gray-500">
                                                <i class="fas fa-clock mr-1"></i>
                                                Criado {{ $ticket->created_at->diffForHumans() }}
                                            </span>
                                        </div>

                                        <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $ticket->subject }}</h4>
                                        <p class="text-sm text-gray-600 mb-3">{{ Illuminate\Support\Str::limit($ticket->description, 200) }}</p>

                                        <div class="flex items-center gap-4 text-xs text-gray-500">
                                            <span>
                                                <i class="fas fa-user mr-1"></i>
                                                {{ $ticket->user->name }}
                                            </span>
                                            <span>
                                                <i class="fas fa-folder mr-1"></i>
                                                {{ $ticket->category->name }}
                                            </span>
                                            @if($ticket->due_date)
                                                <span class="{{ $ticket->isOverdue() ? 'text-red-600 font-semibold' : '' }}">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    Prazo: {{ $ticket->due_date->format('d/m/Y H:i') }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($ticket->attachments->isNotEmpty())
                                            <div class="mt-3 flex items-center gap-2">
                                                <i class="fas fa-paperclip text-gray-400"></i>
                                                <span class="text-xs text-gray-600">{{ $ticket->attachments->count() }} anexo(s)</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-6 flex flex-col gap-2">
                                        <a href="{{ route('tickets.show', $ticket) }}"
                                           class="px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 text-center">
                                            <i class="fas fa-eye mr-2"></i>
                                            Ver Detalhes
                                        </a>

                                        <button onclick="openReviewModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}', '{{ $ticket->subject }}')"
                                                class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                                            <i class="fas fa-check mr-2"></i>
                                            Aprovar
                                        </button>

                                        <button onclick="openRejectModal({{ $ticket->id }}, '{{ $ticket->ticket_number }}', '{{ $ticket->subject }}')"
                                                class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                                            <i class="fas fa-times mr-2"></i>
                                            Rejeitar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginação --}}
                    <div class="p-4 border-t border-gray-200">
                        {{ $pendingTickets->links() }}
                    </div>
                @endif
            </div>

            {{-- Análises Recentes --}}
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="font-semibold text-lg text-gray-900">
                        <i class="fas fa-history mr-2"></i>
                        Análises Recentes
                    </h3>
                </div>

                <div class="divide-y divide-gray-200">
                    @forelse($recentReviews as $ticket)
                        <div class="p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <span class="font-mono text-sm font-bold text-gray-700">#{{ $ticket->ticket_number }}</span>
                                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800">
                                            <i class="fas fa-{{ $ticket->status->icon() }} mr-1"></i>
                                            {{ $ticket->status->label() }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">{{ $ticket->subject }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Revisado em {{ $ticket->quality_reviewed_at->format('d/m/Y H:i') }}
                                        @if($ticket->quality_reviewed_by)
                                            por {{ $ticket->qualityReviewer->name }}
                                        @endif
                                    </p>
                                    @if($ticket->quality_review_comments)
                                        <p class="text-xs text-gray-600 mt-1 italic">"{{ $ticket->quality_review_comments }}"</p>
                                    @endif
                                </div>
                                <a href="{{ route('tickets.show', $ticket) }}" class="ml-4 text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">
                            <p>Nenhuma análise recente</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Aprovar --}}
    <div id="approve-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-green-600">
                    <i class="fas fa-check-circle mr-2"></i>
                    Aprovar Melhoria
                </h3>
                <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="approve-form" method="POST">
                @csrf
                <input type="hidden" name="approved" value="1">
                <div class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <p class="text-sm text-blue-700">
                            <strong id="approve-ticket-number"></strong>
                        </p>
                        <p class="text-sm text-blue-600 mt-1" id="approve-ticket-subject"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Comentários da Análise
                        </label>
                        <textarea
                            name="comments"
                            rows="4"
                            placeholder="Descreva os motivos da aprovação, sugestões de implementação, etc..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Estes comentários serão visíveis para a equipe Unitop
                        </p>
                    </div>

                    <div class="bg-green-50 border border-green-200 rounded p-3">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Ao aprovar, esta melhoria será encaminhada para a Equipe Unitop desenvolver.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeReviewModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <i class="fas fa-check mr-2"></i>
                            Aprovar Melhoria
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Rejeitar --}}
    <div id="reject-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-red-600">
                    <i class="fas fa-times-circle mr-2"></i>
                    Rejeitar Melhoria
                </h3>
                <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="reject-form" method="POST">
                @csrf
                <input type="hidden" name="approved" value="0">
                <div class="space-y-4">
                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <p class="text-sm text-blue-700">
                            <strong id="reject-ticket-number"></strong>
                        </p>
                        <p class="text-sm text-blue-600 mt-1" id="reject-ticket-subject"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Motivo da Rejeição <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            name="comments"
                            rows="4"
                            required
                            placeholder="Explique detalhadamente por que esta melhoria não foi aprovada..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        ></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Este feedback será enviado ao solicitante
                        </p>
                    </div>

                    <div class="bg-red-50 border border-red-200 rounded p-3">
                        <p class="text-sm text-red-700">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Ao rejeitar, o chamado será fechado e o solicitante será notificado.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeReviewModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            <i class="fas fa-times mr-2"></i>
                            Rejeitar Melhoria
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openReviewModal(ticketId, ticketNumber, ticketSubject) {
            const modal = document.getElementById('approve-modal');
            const form = document.getElementById('approve-form');

            form.action = `/quality/tickets/${ticketId}/review`;
            document.getElementById('approve-ticket-number').textContent = `#${ticketNumber}`;
            document.getElementById('approve-ticket-subject').textContent = ticketSubject;

            modal.classList.remove('hidden');
        }

        function openRejectModal(ticketId, ticketNumber, ticketSubject) {
            const modal = document.getElementById('reject-modal');
            const form = document.getElementById('reject-form');

            form.action = `/quality/tickets/${ticketId}/review`;
            document.getElementById('reject-ticket-number').textContent = `#${ticketNumber}`;
            document.getElementById('reject-ticket-subject').textContent = ticketSubject;

            modal.classList.remove('hidden');
        }

        function closeReviewModal() {
            document.getElementById('approve-modal').classList.add('hidden');
            document.getElementById('reject-modal').classList.add('hidden');
        }

        // Fechar modal ao clicar fora
        window.onclick = function(event) {
            const approveModal = document.getElementById('approve-modal');
            const rejectModal = document.getElementById('reject-modal');

            if (event.target === approveModal) {
                approveModal.classList.add('hidden');
            }
            if (event.target === rejectModal) {
                rejectModal.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout>
