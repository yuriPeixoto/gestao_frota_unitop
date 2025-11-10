<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('tickets.index') }}" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Chamado #{{ $ticket->ticket_number }}
                    </h2>
                    <p class="text-sm text-gray-600">{{ $ticket->subject }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @can('tickets.assign')
                @if(!$ticket->assigned_to)
                <button onclick="document.getElementById('assign-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <i class="fas fa-user-plus mr-2"></i>
                    Atribuir
                </button>
                @endif
                @endcan

                <button onclick="document.getElementById('watcher-form').submit()"
                    class="px-4 py-2 {{ $ticket->watchers->contains(auth()->id()) ? 'bg-yellow-600' : 'bg-gray-600' }} text-white rounded-md hover:opacity-90">
                    <i class="fas fa-eye mr-2"></i>
                    {{ $ticket->watchers->contains(auth()->id()) ? 'Observando' : 'Observar' }}
                </button>

                <form id="watcher-form" method="POST" action="{{ route('tickets.toggle-watcher', $ticket) }}"
                    class="hidden">
                    @csrf
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Coluna Principal --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Informações do Ticket --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        class="px-3 py-1 text-sm rounded-full bg-{{ $ticket->type->color() }}-100 text-{{ $ticket->type->color() }}-800">
                                        <i class="fas fa-{{ $ticket->type->icon() }} mr-1"></i>
                                        {{ $ticket->type->label() }}
                                    </span>

                                    <span
                                        class="px-3 py-1 text-sm rounded-full bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800">
                                        {{ $ticket->priority->label() }}
                                    </span>

                                    <span
                                        class="px-3 py-1 text-sm rounded-full bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800">
                                        <i class="fas fa-{{ $ticket->status->icon() }} mr-1"></i>
                                        {{ $ticket->status->label() }}
                                    </span>

                                    @if($ticket->isOverdue())
                                    <span class="px-3 py-1 text-sm rounded-full bg-red-100 text-red-800">
                                        <i class="fas fa-clock mr-1"></i>
                                        ATRASADO
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="prose max-w-none">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
                            </div>

                            @if($ticket->url)
                            <div class="mt-4 p-3 bg-gray-50 rounded">
                                <span class="text-sm text-gray-600">URL:</span>
                                <a href="{{ $ticket->url }}" target="_blank" class="text-blue-600 hover:underline ml-2">
                                    {{ $ticket->url }}
                                    <i class="fas fa-external-link-alt text-xs ml-1"></i>
                                </a>
                            </div>
                            @endif

                            @if($ticket->attachments->isNotEmpty())
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-900 mb-3">Anexos ({{ $ticket->attachments->count()
                                    }})</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($ticket->attachments as $attachment)
                                    <a href="{{ route('tickets.download-attachment', $attachment) }}"
                                        class="flex items-center gap-3 p-3 border rounded-lg hover:bg-gray-50 transition">
                                        <div class="flex-shrink-0">
                                            @if(Illuminate\Support\Str::startsWith($attachment->mime_type, 'image/'))
                                            <i class="fas fa-image text-2xl text-blue-500"></i>
                                            @elseif($attachment->mime_type === 'application/pdf')
                                            <i class="fas fa-file-pdf text-2xl text-red-500"></i>
                                            @else
                                            <i class="fas fa-file text-2xl text-gray-500"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{
                                                $attachment->filename }}</p>
                                            <p class="text-xs text-gray-500">{{ number_format($attachment->file_size /
                                                1024, 1) }} KB</p>
                                        </div>
                                        <i class="fas fa-download text-gray-400"></i>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($ticket->tags->isNotEmpty())
                            <div class="mt-6">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($ticket->tags as $tag)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ $tag->name }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Respostas --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <h3 class="font-semibold text-lg text-gray-900">
                                <i class="fas fa-comments mr-2"></i>
                                Respostas ({{ $ticket->responses->count() }})
                            </h3>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @forelse($ticket->responses as $response)
                            <div class="p-6 {{ $response->is_internal ? 'bg-yellow-50' : '' }}">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-10 h-10 rounded-full bg-{{ $response->user_id === $ticket->user_id ? 'blue' : 'gray' }}-500 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($response->user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-semibold text-gray-900">{{ $response->user->name }}</span>
                                            @if($response->user_id === $ticket->user_id)
                                            <span
                                                class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">Cliente</span>
                                            @elseif($response->user->hasRole('Equipe Unitop'))
                                            <span
                                                class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Unitop</span>
                                            @elseif($response->user->hasRole('Equipe Qualidade'))
                                            <span
                                                class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Qualidade</span>
                                            @endif
                                            @if($response->is_internal)
                                            <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded">
                                                <i class="fas fa-lock mr-1"></i>
                                                Interno
                                            </span>
                                            @endif
                                            <span class="text-xs text-gray-500">{{
                                                $response->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="prose max-w-none">
                                            <p class="text-gray-700 whitespace-pre-wrap">{{ $response->message }}</p>
                                        </div>
                                        @if($response->attachments->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach($response->attachments as $attachment)
                                            <a href="{{ route('tickets.download-attachment', $attachment) }}"
                                                class="inline-flex items-center gap-2 px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm">
                                                <i class="fas fa-paperclip"></i>
                                                <span>{{ $attachment->filename }}</span>
                                            </a>
                                            @endforeach
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="p-6 text-center text-gray-500">
                                <i class="fas fa-comments text-4xl mb-2 text-gray-300"></i>
                                <p>Nenhuma resposta ainda</p>
                            </div>
                            @endforelse
                        </div>

                        {{-- Formulário de Resposta --}}
                        @if(!$ticket->status->isClosed())
                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <form method="POST" action="{{ route('tickets.add-response', $ticket) }}"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-4">
                                    <textarea name="message" rows="4" required placeholder="Digite sua resposta..."
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>

                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center cursor-pointer">
                                                <input type="file" name="attachments[]" multiple class="hidden"
                                                    id="response-attachments" onchange="updateResponseFileList(this)">
                                                <span
                                                    class="px-4 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                                    <i class="fas fa-paperclip mr-2"></i>
                                                    Anexar arquivos
                                                </span>
                                            </label>

                                            @can('tickets.internal_response')
                                            <label class="flex items-center">
                                                <input type="checkbox" name="is_internal" value="1"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                <span class="ml-2 text-sm text-gray-700">
                                                    <i class="fas fa-lock mr-1"></i>
                                                    Resposta interna
                                                </span>
                                            </label>
                                            @endcan
                                        </div>

                                        <button type="submit"
                                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            Enviar Resposta
                                        </button>
                                    </div>

                                    <div id="response-file-list" class="text-sm text-gray-600"></div>
                                </div>
                            </form>
                        </div>
                        @else
                        <div class="p-6 bg-gray-100 border-t border-gray-200 text-center">
                            <i class="fas fa-lock text-gray-400 text-2xl mb-2"></i>
                            <p class="text-gray-600">Este chamado está fechado</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Ações Rápidas --}}
                    @if(!$ticket->status->isClosed())
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900">Ações</h4>
                        </div>
                        <div class="p-4 space-y-2">
                            @can('tickets.update_status')
                            <button onclick="document.getElementById('status-modal').classList.remove('hidden')"
                                class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm">
                                <i class="fas fa-sync-alt mr-2"></i>
                                Alterar Status
                            </button>
                            @endcan

                            @can('tickets.set_estimate')
                            @if(!$ticket->estimated_completion_at)
                            <button onclick="document.getElementById('estimate-modal').classList.remove('hidden')"
                                class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm">
                                <i class="fas fa-calendar mr-2"></i>
                                Definir Estimativa
                            </button>
                            @endif
                            @endcan

                            @if($ticket->status === \App\Enums\TicketStatus::RESOLVIDO && !$ticket->resolution_rating &&
                            $ticket->user_id === auth()->id())
                            <button onclick="document.getElementById('rating-modal').classList.remove('hidden')"
                                class="w-full px-4 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-md text-sm">
                                <i class="fas fa-star mr-2"></i>
                                Avaliar Atendimento
                            </button>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Detalhes --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900">Detalhes</h4>
                        </div>
                        <div class="p-4 space-y-4">
                            <div>
                                <span class="text-sm text-gray-600">Criado por</span>
                                <p class="font-medium text-gray-900">{{ $ticket->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            </div>

                            @if($ticket->assigned_to)
                            <div>
                                <span class="text-sm text-gray-600">Atribuído para</span>
                                <p class="font-medium text-gray-900">{{ $ticket->assignedTo->name }}</p>
                            </div>
                            @else
                            <div>
                                <span class="text-sm text-gray-600">Atribuído para</span>
                                <p class="font-medium text-gray-500 italic">Não atribuído</p>
                            </div>
                            @endif

                            <div>
                                <span class="text-sm text-gray-600">Categoria</span>
                                <p class="font-medium text-gray-900">{{ $ticket->category->name }}</p>
                            </div>

                            @if($ticket->due_date)
                            <div>
                                <span class="text-sm text-gray-600">Prazo SLA</span>
                                <p class="font-medium {{ $ticket->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $ticket->due_date->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            @endif

                            @if($ticket->estimated_completion_at)
                            <div>
                                <span class="text-sm text-gray-600">Estimativa</span>
                                <p class="font-medium text-gray-900">
                                    {{ $ticket->estimated_completion_at->format('d/m/Y H:i') }}
                                </p>
                                @if($ticket->estimated_hours)
                                <p class="text-xs text-gray-500">{{ $ticket->estimated_hours }}h estimadas</p>
                                @endif
                            </div>
                            @endif

                            @if($ticket->resolved_at)
                            <div>
                                <span class="text-sm text-gray-600">Resolvido em</span>
                                <p class="font-medium text-gray-900">{{ $ticket->resolved_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif

                            @if($ticket->closed_at)
                            <div>
                                <span class="text-sm text-gray-600">Fechado em</span>
                                <p class="font-medium text-gray-900">{{ $ticket->closed_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif

                            @if($ticket->resolution_rating)
                            <div>
                                <span class="text-sm text-gray-600">Avaliação</span>
                                <div class="flex items-center mt-1">
                                    @for($i = 1; $i <= 5; $i++) <i
                                        class="fas fa-star text-{{ $i <= $ticket->resolution_rating ? 'yellow' : 'gray' }}-400">
                                        </i>
                                        @endfor
                                </div>
                                @if($ticket->resolution_feedback)
                                <p class="text-sm text-gray-600 mt-1">{{ $ticket->resolution_feedback }}</p>
                                @endif
                            </div>
                            @endif

                            @if($ticket->watchers->isNotEmpty())
                            <div>
                                <span class="text-sm text-gray-600">Observadores ({{ $ticket->watchers->count()
                                    }})</span>
                                <div class="mt-2 space-y-1">
                                    @foreach($ticket->watchers as $watcher)
                                    <p class="text-sm text-gray-700">{{ $watcher->name }}</p>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Timeline de Status --}}
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 bg-gray-50 border-b border-gray-200">
                            <h4 class="font-semibold text-gray-900">
                                <i class="fas fa-history mr-2"></i>
                                Histórico
                            </h4>
                        </div>
                        <div class="p-4">
                            <div class="space-y-4">
                                @foreach($ticket->statusHistory()->latest()->get() as $history)
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="w-8 h-8 rounded-full bg-{{ $history->to_status->color() }}-100 flex items-center justify-center">
                                            <i
                                                class="fas fa-{{ $history->to_status->icon() }} text-{{ $history->to_status->color() }}-600 text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">{{ $history->to_status->label() }}
                                        </p>
                                        <p class="text-xs text-gray-600">{{ $history->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $history->created_at->format('d/m/Y H:i') }}
                                        </p>
                                        @if($history->comment)
                                        <p class="text-xs text-gray-600 mt-1 italic">{{ $history->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Alterar Status --}}
    <div id="status-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Alterar Status</h3>
                <button onclick="document.getElementById('status-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('tickets.update-status', $ticket) }}">
                @csrf
                @method('PATCH')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Novo Status</label>
                        <select name="status" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach(\App\Enums\TicketStatus::cases() as $status)
                            @if($ticket->status->canTransitionTo($status))
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observações (opcional)</label>
                        <textarea name="notes" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('status-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Alterar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Atribuir --}}
    <div id="assign-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Atribuir Chamado</h3>
                <button onclick="document.getElementById('assign-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('tickets.assign', $ticket) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Atribuir para</label>
                        <select name="assigned_to" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Selecione...</option>
                            @foreach($unitopTeam as $member)
                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('assign-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Atribuir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Definir Estimativa --}}
    <div id="estimate-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Definir Estimativa</h3>
                <button onclick="document.getElementById('estimate-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('tickets.set-estimate', $ticket) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Horas Estimadas</label>
                        <input type="number" name="estimated_hours" step="0.5" min="0.5" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data de Conclusão</label>
                        <input type="datetime-local" name="estimated_completion_at" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button"
                            onclick="document.getElementById('estimate-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Salvar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal: Avaliar --}}
    <div id="rating-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Avaliar Atendimento</h3>
                <button onclick="document.getElementById('rating-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('tickets.rate', $ticket) }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Avaliação</label>
                        <div class="flex gap-2">
                            @for($i = 1; $i <= 5; $i++) <label class="cursor-pointer">
                                <input type="radio" name="rating" value="{{ $i }}" required class="sr-only peer">
                                <i
                                    class="fas fa-star text-3xl text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-300"></i>
                                </label>
                                @endfor
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Comentário (opcional)</label>
                        <textarea name="feedback" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('rating-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Enviar Avaliação
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateResponseFileList(input) {
            const fileList = document.getElementById('response-file-list');
            if (input.files.length > 0) {
                const files = Array.from(input.files).map(f => f.name).join(', ');
                fileList.textContent = `Arquivos selecionados: ${files}`;
            } else {
                fileList.textContent = '';
            }
        }
    </script>
    @endpush
</x-app-layout>