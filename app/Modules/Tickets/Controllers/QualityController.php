<?php

namespace App\Modules\Tickets\Controllers;

use App\Modules\Tickets\Enums\TicketType;
use App\Http\Controllers\Controller;
use App\Modules\Tickets\Models\SupportTicket;
use App\Modules\Tickets\Services\TicketService;
use App\Traits\ExportableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QualityController extends Controller
{
    use ExportableTrait;

    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * Verifica se o usuário tem acesso
     */
    protected function checkAccess(): void
    {
        if (!Auth::user()->hasRole('Equipe Qualidade') && !Auth::user()->isSuperuser()) {
            abort(403, 'Acesso restrito à Equipe de Qualidade');
        }
    }

    /**
     * Dashboard da Qualidade
     */
    public function index(Request $request)
    {
        $this->checkAccess();

        // Buscar tickets pendentes (aguardando análise)
        $pendingTickets = SupportTicket::with(['user', 'category', 'filial'])
            ->byType(TicketType::MELHORIA)
            ->awaitingQuality()
            ->latest()
            ->paginate(20);

        $tickets = $pendingTickets; // Alias para compatibilidade
        $status = $request->get('status', 'aguardando');

        // Buscar análises recentes (últimas 10 melhorias revisadas)
        $recentReviews = SupportTicket::with(['user', 'qualityReviewer'])
            ->byType(TicketType::MELHORIA)
            ->whereNotNull('quality_reviewed_at')
            ->whereIn('status', ['aprovado_qualidade', 'rejeitado_qualidade'])
            ->orderBy('quality_reviewed_at', 'desc')
            ->limit(10)
            ->get();

        // Estatísticas
        $aprovadosMes = SupportTicket::where('status', 'aprovado_qualidade')
            ->whereMonth('quality_reviewed_at', now()->month)
            ->count();
        $rejeitadosMes = SupportTicket::where('status', 'rejeitado_qualidade')
            ->whereMonth('quality_reviewed_at', now()->month)
            ->count();
        $totalRevisadosMes = $aprovadosMes + $rejeitadosMes;

        $stats = [
            'aguardando' => SupportTicket::awaitingQuality()->count(),
            'aprovados_mes' => $aprovadosMes,
            'rejeitados_mes' => $rejeitadosMes,
            'total_melhorias' => SupportTicket::byType(TicketType::MELHORIA)->count(),
            'taxa_aprovacao' => $totalRevisadosMes > 0
                ? round(($aprovadosMes / $totalRevisadosMes) * 100, 1)
                : 0,
        ];

        return view('quality.index', compact('pendingTickets', 'tickets', 'stats', 'status', 'recentReviews'));
    }

    /**
     * Revisar melhoria
     */
    public function review(Request $request, SupportTicket $ticket)
    {
        $this->checkAccess();

        if ($ticket->type !== TicketType::MELHORIA) {
            return back()->with('error', 'Apenas melhorias podem ser revisadas pela qualidade.');
        }

        $validated = $request->validate([
            'approved' => 'required|boolean',
            'comments' => 'required|string|min:10',
        ]);

        try {
            $this->ticketService->qualityReview(
                $ticket,
                Auth::user(),
                (bool) $validated['approved'],
                $validated['comments']
            );

            $message = $validated['approved']
                ? 'Melhoria aprovada com sucesso!'
                : 'Melhoria rejeitada.';

            return redirect()
                ->route('quality.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao revisar: ' . $e->getMessage());
        }
    }

    /**
     * Relatório de melhorias
     */
    public function report(Request $request)
    {
        $this->checkAccess();

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $melhorias = SupportTicket::byType(TicketType::MELHORIA)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'qualityReviewer'])
            ->get();

        $stats = [
            'total' => $melhorias->count(),
            'aguardando' => $melhorias->where('status', 'aguardando_qualidade')->count(),
            'aprovadas' => $melhorias->where('status', 'aprovado_qualidade')->count(),
            'rejeitadas' => $melhorias->where('status', 'rejeitado_qualidade')->count(),
            'tempo_medio_revisao' => $melhorias
                ->filter(fn($t) => $t->quality_reviewed_at)
                ->avg(fn($t) => $t->created_at->diffInHours($t->quality_reviewed_at)),
        ];

        return view('quality.report', compact('melhorias', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Lista de filtros válidos para exportação
     */
    protected function getValidExportFilters(): array
    {
        return [
            'start_date',
            'end_date',
        ];
    }

    /**
     * Constrói a query para exportação com filtros aplicados
     */
    protected function buildExportQuery(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        return SupportTicket::byType(TicketType::MELHORIA)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user', 'qualityReviewer', 'category']);
    }

    /**
     * Exportar relatório para PDF
     */
    public function exportPdf(Request $request)
    {
        $this->checkAccess();

        try {
            $query = $this->buildExportQuery($request);

            // Verificar se tem filtros
            if (!$this->hasAnyFilter($request, $this->getValidExportFilters())) {
                return redirect()->back()->with([
                    'error' => 'É necessário aplicar pelo menos um filtro antes de exportar.',
                    'export_error' => true
                ]);
            }

            // Verificar se precisa de confirmação para grande volume
            if ($request->has('confirmed') || !$this->exceedsExportLimit($query, 500)) {
                $data = $query->get();

                $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
                $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

                // Calcular estatísticas
                $stats = [
                    'total' => $data->count(),
                    'aguardando' => $data->where('status', 'aguardando_qualidade')->count(),
                    'aprovadas' => $data->where('status', 'aprovado_qualidade')->count(),
                    'rejeitadas' => $data->where('status', 'rejeitado_qualidade')->count(),
                    'tempo_medio_revisao' => $data
                        ->filter(fn($t) => $t->quality_reviewed_at)
                        ->avg(fn($t) => $t->created_at->diffInHours($t->quality_reviewed_at)),
                ];

                // Configurar PDF
                $pdf = app('dompdf.wrapper');
                $pdf->setPaper('a4', 'landscape');

                // Carregar view
                $pdf->loadView('quality.pdf', compact('data', 'stats', 'startDate', 'endDate'));

                // Download
                return $pdf->download('relatorio_qualidade_' . date('Y-m-d_His') . '.pdf');
            } else {
                // Confirmação para grande volume
                $currentUrl = $request->fullUrlWithQuery(['confirmed' => 1]);

                return redirect()->back()->with([
                    'warning' => "Você está tentando exportar mais de 500 registros, o que pode levar mais tempo.",
                    'export_confirmation' => true,
                    'export_url' => $currentUrl
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao gerar PDF de qualidade: ' . $e->getMessage());
            Log::error($e->getTraceAsString());

            return redirect()->back()->with([
                'error' => 'Erro ao gerar o PDF: ' . $e->getMessage(),
                'export_error' => true
            ]);
        }
    }
}
