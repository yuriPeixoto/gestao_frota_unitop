<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🎫 Chamados de Suporte
            </h2>
            <a href="{{ route('tickets.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-2"></i>
                Novo Chamado
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Estatísticas --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <i class="fas fa-ticket-alt text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Abertos</p>
                            <p class="text-2xl font-bold">{{ $stats['total_abertos'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 text-green-600">
                            <i class="fas fa-user text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Meus Abertos</p>
                            <p class="text-2xl font-bold">{{ $stats['meus_abertos'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                            <i class="fas fa-user-check text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Atribuídos a Mim</p>
                            <p class="text-2xl font-bold">{{ $stats['atribuidos_mim'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 text-red-600">
                            <i class="fas fa-exclamation-triangle text-2xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-600">Atrasados</p>
                            <p class="text-2xl font-bold">{{ $stats['atrasados'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Abas --}}
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <a href="?tab=meus" class="px-6 py-3 border-b-2 {{ $tab === 'meus' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Meus Chamados
                        </a>
                        <a href="?tab=atribuidos" class="px-6 py-3 border-b-2 {{ $tab === 'atribuidos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Atribuídos a Mim
                        </a>
                        <a href="?tab=abertos" class="px-6 py-3 border-b-2 {{ $tab === 'abertos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Todos Abertos
                        </a>
                        <a href="?tab=fechados" class="px-6 py-3 border-b-2 {{ $tab === 'fechados' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Fechados
                        </a>
                        @can('tickets.quality_review')
                        <a href="?tab=aguardando_qualidade" class="px-6 py-3 border-b-2 {{ $tab === 'aguardando_qualidade' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <span class="flex items-center">
                                Aguardando Qualidade
                                @if(($stats['aguardando_qualidade'] ?? 0) > 0)
                                    <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $stats['aguardando_qualidade'] }}</span>
                                @endif
                            </span>
                        </a>
                        @endcan
                    </nav>
                </div>

                {{-- Filtros --}}
                <div class="p-4 border-b border-gray-200">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <input type="hidden" name="tab" value="{{ $tab }}">

                        <input type="text" name="search" placeholder="Buscar..." class="form-input rounded-md" value="{{ request('search') }}">

                        <select name="type" class="form-select rounded-md">
                            <option value="">Todos os Tipos</option>
                            @foreach(\App\Enums\TicketType::options() as $value => $label)
                                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <select name="priority" class="form-select rounded-md">
                            <option value="">Todas Prioridades</option>
                            @foreach(\App\Enums\TicketPriority::options() as $value => $label)
                                <option value="{{ $value }}" {{ request('priority') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>

                        <select name="category_id" class="form-select rounded-md">
                            <option value="">Todas Categorias</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search mr-2"></i>
                            Filtrar
                        </button>
                    </form>
                </div>
            </div>

            {{-- Lista de Tickets --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                @if($tickets->isEmpty())
                    <div class="p-12 text-center">
                        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 text-lg">Nenhum chamado encontrado</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($tickets as $ticket)
                            <a href="{{ route('tickets.show', $ticket) }}" class="block hover:bg-gray-50 p-4 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-mono text-sm font-bold text-gray-700">#{{ $ticket->ticket_number }}</span>

                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->type->color() }}-100 text-{{ $ticket->type->color() }}-800">
                                                <i class="fas fa-{{ $ticket->type->icon() }} mr-1"></i>
                                                {{ $ticket->type->label() }}
                                            </span>

                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->priority->color() }}-100 text-{{ $ticket->priority->color() }}-800">
                                                {{ $ticket->priority->label() }}
                                            </span>

                                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $ticket->status->color() }}-100 text-{{ $ticket->status->color() }}-800">
                                                <i class="fas fa-{{ $ticket->status->icon() }} mr-1"></i>
                                                {{ $ticket->status->label() }}
                                            </span>

                                            @if($ticket->isOverdue())
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    ATRASADO
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $ticket->subject }}</h3>

                                        <p class="text-sm text-gray-600 mb-2">{{ Illuminate\Support\Str::limit($ticket->description, 150) }}</p>

                                        <div class="flex items-center gap-4 text-xs text-gray-500">
                                            <span>
                                                <i class="fas fa-user mr-1"></i>
                                                {{ $ticket->user->name }}
                                            </span>
                                            <span>
                                                <i class="fas fa-folder mr-1"></i>
                                                {{ $ticket->category->name }}
                                            </span>
                                            <span>
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $ticket->created_at->diffForHumans() }}
                                            </span>
                                            @if($ticket->assigned_to)
                                                <span>
                                                    <i class="fas fa-user-check mr-1"></i>
                                                    Atribuído: {{ $ticket->assignedTo->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-4">
                                        <i class="fas fa-chevron-right text-gray-400"></i>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Paginação --}}
                    <div class="p-4 border-t border-gray-200">
                        {{ $tickets->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
