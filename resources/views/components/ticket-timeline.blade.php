@props(['ticket'])

<div {{ $attributes->merge(['class' => 'space-y-4']) }}>
    @foreach($ticket->statusHistory()->latest()->get() as $history)
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <div class="w-8 h-8 rounded-full bg-{{ $history->new_status->color() }}-100 flex items-center justify-center">
                    <i class="fas fa-{{ $history->new_status->icon() }} text-{{ $history->new_status->color() }}-600 text-xs"></i>
                </div>
            </div>
            <div class="flex-1 pb-4 border-l-2 border-gray-200 pl-4 -ml-4 last:border-0">
                <div class="flex items-center gap-2 mb-1">
                    <x-ticket-status-badge :status="$history->new_status" />
                    <span class="text-xs text-gray-500">{{ $history->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-sm text-gray-700">{{ $history->user->name }}</p>
                @if($history->notes)
                    <p class="text-xs text-gray-600 mt-1 italic bg-gray-50 p-2 rounded">
                        "{{ $history->notes }}"
                    </p>
                @endif
                <p class="text-xs text-gray-500 mt-1">{{ $history->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    @endforeach

    {{-- Criação do ticket --}}
    <div class="flex gap-3">
        <div class="flex-shrink-0">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                <i class="fas fa-plus text-blue-600 text-xs"></i>
            </div>
        </div>
        <div class="flex-1 pl-4 -ml-4">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    Criado
                </span>
                <span class="text-xs text-gray-500">{{ $ticket->created_at->diffForHumans() }}</span>
            </div>
            <p class="text-sm text-gray-700">{{ $ticket->user->name }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
