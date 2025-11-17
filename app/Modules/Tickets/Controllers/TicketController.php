<?php

namespace App\Modules\Tickets\Controllers;

use App\Modules\Tickets\Enums\TicketPriority;
use App\Modules\Tickets\Enums\TicketStatus;
use App\Modules\Tickets\Enums\TicketType;
use App\Modules\Tickets\Models\SupportTicket;
use App\Modules\Tickets\Models\TicketCategory;
use App\Modules\Tickets\Models\TicketTag;
use App\Modules\Configuracoes\Models\User;
use App\Modules\Tickets\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Dashboard de tickets
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = SupportTicket::with(['user', 'category', 'assignedTo'])
            ->forUser($user);

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Abas
        $tab = $request->get('tab', 'meus');

        switch ($tab) {
            case 'meus':
                $query->where('user_id', $user->id);
                break;
            case 'atribuidos':
                $query->where('assigned_to', $user->id);
                break;
            case 'abertos':
                $query->open();
                break;
            case 'fechados':
                $query->closed();
                break;
            case 'aguardando_qualidade':
                if ($user->hasRole('Equipe Qualidade')) {
                    $query->awaitingQuality();
                }
                break;
        }

        $tickets = $query->latest()->paginate(20);

        // Estatísticas
        $stats = [
            'total_abertos' => SupportTicket::open()->forUser($user)->count(),
            'meus_abertos' => SupportTicket::open()->createdBy($user->id)->count(),
            'atribuidos_mim' => SupportTicket::open()->assignedTo($user->id)->count(),
            'atrasados' => SupportTicket::overdue()->forUser($user)->count(),
        ];

        if ($user->hasRole('Equipe Qualidade')) {
            $stats['aguardando_qualidade'] = SupportTicket::awaitingQuality()->count();
        }

        $categories = TicketCategory::active()->ordered()->get();

        return view('tickets.index', compact('tickets', 'stats', 'categories', 'tab'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $categories = TicketCategory::active()->ordered()->get();
        $tags = TicketTag::orderBy('name')->get();

        return view('tickets.create', compact('categories', 'tags'));
    }

    /**
     * Criar ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:ticket_categories,id',
            'type' => 'required|in:'.implode(',', TicketType::toArray()),
            'priority' => 'nullable|in:'.implode(',', TicketPriority::toArray()),
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'url' => 'nullable|url|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:ticket_tags,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,txt',
        ]);

        try {
            DB::beginTransaction();

            // Adicionar informações de contexto
            $validated['browser'] = $request->userAgent();
            $validated['device'] = $this->detectDevice($request);

            $ticket = $this->ticketService->createTicket($validated, Auth::user());

            // Upload de anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->ticketService->uploadAttachment($ticket, $file, Auth::user());
                }
            }

            DB::commit();

            return redirect()
                ->route('tickets.show', $ticket)
                ->with('success', "Chamado #{$ticket->ticket_number} criado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar ticket: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao criar chamado: '.$e->getMessage());
        }
    }

    /**
     * Detalhes do ticket
     */
    public function show(SupportTicket $ticket)
    {
        $user = Auth::user();

        if (! $ticket->canBeViewedBy($user)) {
            abort(403, 'Você não tem permissão para visualizar este chamado.');
        }

        $ticket->load([
            'user',
            'category',
            'assignedTo',
            'qualityReviewer',
            'filial',
            'responses' => function ($query) use ($user) {
                // Mostrar apenas públicas para clientes
                if (! $user->hasAnyRole(['Equipe Unitop', 'Equipe Qualidade'])) {
                    $query->public();
                }
                $query->with(['user', 'attachments'])->latest();
            },
            'attachments',
            'statusHistory.user',
            'assignments.assignedTo',
            'tags',
            'watchers',
        ]);

        // Atendentes disponíveis (para atribuição)
        $availableAgents = [];
        $unitopTeam = [];

        if ($user->can('tickets.assign') || $user->isSuperuser() || $user->hasAnyRole(['Equipe Unitop', 'Equipe Qualidade'])) {
            $availableAgents = User::role('Equipe Unitop')
                ->where('is_ativo', true)
                ->orderBy('name')
                ->get();
            $unitopTeam = $availableAgents; // Alias para compatibilidade com a view
        }

        return view('tickets.show', compact('ticket', 'availableAgents', 'unitopTeam'));
    }

    /**
     * Adicionar resposta
     */
    public function addResponse(Request $request, SupportTicket $ticket)
    {
        $user = Auth::user();

        if (! $ticket->canBeViewedBy($user)) {
            abort(403);
        }

        $validated = $request->validate([
            'message' => 'required|string',
            'is_internal' => 'boolean',
            'is_solution' => 'boolean',
            'time_spent_minutes' => 'nullable|integer|min:1',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        try {
            DB::beginTransaction();

            // Apenas Unitop/Qualidade podem fazer notas internas
            if (isset($validated['is_internal']) && $validated['is_internal']) {
                if (! $user->can('tickets.add_internal_note')) {
                    $validated['is_internal'] = false;
                }
            }

            $response = $this->ticketService->addResponse($ticket, $validated, $user);

            // Upload de anexos
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $this->ticketService->uploadAttachment($ticket, $file, $user, $response->id);
                }
            }

            DB::commit();

            return back()->with('success', 'Resposta adicionada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Erro ao adicionar resposta: '.$e->getMessage());
        }
    }

    /**
     * Mudar status
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        if (! Auth::user()->can('tickets.change_status')) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:'.implode(',', TicketStatus::toArray()),
            'comment' => 'nullable|string',
        ]);

        try {
            $newStatus = TicketStatus::from($validated['status']);

            $this->ticketService->updateStatus(
                $ticket,
                $newStatus,
                Auth::user(),
                $validated['comment'] ?? null
            );

            return back()->with('success', "Status atualizado para: {$newStatus->label()}");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atualizar status: '.$e->getMessage());
        }
    }

    /**
     * Atribuir ticket
     */
    public function assign(Request $request, SupportTicket $ticket)
    {
        if (! Auth::user()->can('tickets.assign')) {
            abort(403);
        }

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'comment' => 'nullable|string',
        ]);

        try {
            $assignee = User::findOrFail($validated['assigned_to']);

            $this->ticketService->assignTicket(
                $ticket,
                $assignee,
                Auth::user(),
                $validated['comment'] ?? null
            );

            return back()->with('success', "Ticket atribuído para {$assignee->name}");

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao atribuir ticket: '.$e->getMessage());
        }
    }

    /**
     * Definir estimativa
     */
    public function setEstimate(Request $request, SupportTicket $ticket)
    {
        if (! Auth::user()->can('tickets.set_estimate')) {
            abort(403);
        }

        $validated = $request->validate([
            'estimated_hours' => 'required|numeric|min:0.5|max:1000',
        ]);

        try {
            $this->ticketService->setEstimate(
                $ticket,
                $validated['estimated_hours'],
                Auth::user()
            );

            return back()->with('success', 'Estimativa definida com sucesso!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao definir estimativa: '.$e->getMessage());
        }
    }

    /**
     * Adicionar/remover observador
     */
    public function toggleWatcher(SupportTicket $ticket)
    {
        $user = Auth::user();

        if (! $ticket->canBeViewedBy($user)) {
            abort(403);
        }

        $isWatching = $ticket->watchers()->where('user_id', $user->id)->exists();

        if ($isWatching) {
            $this->ticketService->removeWatcher($ticket, $user);
            $message = 'Você não está mais observando este ticket.';
        } else {
            $this->ticketService->addWatcher($ticket, $user);
            $message = 'Você agora está observando este ticket.';
        }

        return back()->with('success', $message);
    }

    /**
     * Avaliar ticket
     */
    public function rate(Request $request, SupportTicket $ticket)
    {
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Apenas o criador pode avaliar o ticket.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        try {
            $this->ticketService->addSatisfactionRating(
                $ticket,
                $validated['rating'],
                $validated['comment'] ?? null
            );

            return back()->with('success', 'Obrigado pela sua avaliação!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao avaliar: '.$e->getMessage());
        }
    }

    /**
     * Download de anexo
     */
    public function downloadAttachment($attachmentId)
    {
        $attachment = \App\Models\TicketAttachment::findOrFail($attachmentId);

        if (! $attachment->ticket->canBeViewedBy(Auth::user())) {
            abort(403);
        }

        return response()->download(
            storage_path('app/public/'.$attachment->file_path),
            $attachment->original_name
        );
    }

    /**
     * Helper: Detectar tipo de dispositivo
     */
    protected function detectDevice(Request $request): string
    {
        $userAgent = $request->userAgent();

        if (preg_match('/mobile/i', $userAgent)) {
            return 'Mobile';
        } elseif (preg_match('/tablet/i', $userAgent)) {
            return 'Tablet';
        } else {
            return 'Desktop';
        }
    }
}
