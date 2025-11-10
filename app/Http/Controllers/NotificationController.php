<?php

namespace App\Http\Controllers;

use App\Models\NotificationTarget;
use App\Services\NotificationService;
use App\Http\Middleware\ValidateJwtFromLumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Exibe a central de notificações com filtros e histórico completo
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Parâmetros de filtro
        $status = $request->get('status', 'all'); // all, unread, read
        $priority = $request->get('priority'); // urgent, high, normal, low
        $perPage = 20;

        // Buscar notificações targeted (do serviço customizado)
        // Precisa verificar se user_id está no array JSONB target_user_ids
        $targetedQuery = NotificationTarget::whereRaw("target_user_ids::jsonb @> ?", [json_encode([$user->id])])
            ->leftJoin('notification_reads', function($join) use ($user) {
                $join->on('notification_targets.id', '=', 'notification_reads.notification_target_id')
                     ->where('notification_reads.user_id', '=', $user->id);
            })
            ->select('notification_targets.*', 'notification_reads.read_at')
            ->orderBy('notification_targets.created_at', 'desc');

        // Buscar notificações diretas (padrão Laravel)
        $directQuery = $user->notifications()->orderBy('created_at', 'desc');

        // Aplicar filtros
        if ($status === 'unread') {
            $targetedQuery->whereNull('notification_reads.read_at');
            $directQuery->whereNull('read_at');
        } elseif ($status === 'read') {
            $targetedQuery->whereNotNull('notification_reads.read_at');
            $directQuery->whereNotNull('read_at');
        }

        if ($priority) {
            $targetedQuery->where('notification_targets.priority', $priority);
            // Para notificações diretas, filtrar no data JSON (PostgreSQL)
            $directQuery->whereRaw("data->>'priority' = ?", [$priority]);
        }

        // Paginar
        $targetedNotifications = $targetedQuery->paginate($perPage, ['*'], 'targeted_page');
        $directNotifications = $directQuery->paginate($perPage, ['*'], 'direct_page');

        // Estatísticas
        $totalTargeted = NotificationTarget::whereRaw("target_user_ids::jsonb @> ?", [json_encode([$user->id])])->count();
        $unreadTargeted = NotificationTarget::whereRaw("target_user_ids::jsonb @> ?", [json_encode([$user->id])])
            ->leftJoin('notification_reads', function($join) use ($user) {
                $join->on('notification_targets.id', '=', 'notification_reads.notification_target_id')
                     ->where('notification_reads.user_id', '=', $user->id);
            })
            ->whereNull('notification_reads.read_at')
            ->count();

        $urgentTargeted = NotificationTarget::whereRaw("target_user_ids::jsonb @> ?", [json_encode([$user->id])])
            ->where('priority', 'urgent')->count();

        $highTargeted = NotificationTarget::whereRaw("target_user_ids::jsonb @> ?", [json_encode([$user->id])])
            ->where('priority', 'high')->count();

        $stats = [
            'total' => $totalTargeted + $user->notifications()->count(),
            'unread' => $unreadTargeted + $user->unreadNotifications()->count(),
            'urgent' => $urgentTargeted,
            'high' => $highTargeted,
        ];

        return view('notifications.index', compact(
            'targetedNotifications',
            'directNotifications',
            'stats',
            'status',
            'priority'
        ));
    }

    /**
     * Retorna notificações via API (para polling ou carregamento inicial)
     */
    public function getNotifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->input('limit', 50);

        $notifications = $this->notificationService->getUnreadNotifications($user->id, $limit);
        $directNotifications = $user->unreadNotifications()->take($limit)->get();

        // Combinar e formatar notificações
        $combined = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => 'targeted',
                'notification_type' => $notification->notification_type,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'priority' => $notification->priority,
                'data' => $notification->data,
                'url' => $notification->data['url'] ?? null,
                'created_at' => $notification->created_at->toIso8601String(),
                'is_read' => false,
            ];
        });

        $directFormatted = $directNotifications->map(function ($notification) {
            $data = $notification->data;
            return [
                'id' => $notification->id,
                'type' => 'direct',
                'notification_type' => $data['type'] ?? 'system',
                'title' => $data['title'] ?? 'Notificação',
                'message' => $data['message'] ?? '',
                'icon' => $data['icon'] ?? 'bell',
                'color' => $data['color'] ?? 'blue',
                'priority' => $data['priority'] ?? 'normal',
                'data' => $data,
                'url' => $data['url'] ?? null,
                'created_at' => $notification->created_at->toIso8601String(),
                'is_read' => $notification->read_at !== null,
            ];
        });

        $all = $combined->concat($directFormatted)
            ->sortByDesc(function ($item) {
                $priorityOrder = ['urgent' => 4, 'high' => 3, 'normal' => 2, 'low' => 1];
                return $priorityOrder[$item['priority']] ?? 0;
            })
            ->values();

        return response()->json([
            'notifications' => $all,
            'unread_count' => $user->unreadNotificationsCount(),
        ]);
    }

    /**
     * Retorna contagem de não lidas
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        return response()->json([
            'count' => $user->unreadNotificationsCount(),
        ]);
    }

    /**
     * Marca uma notificação como lida
     */
    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        $type = $request->input('type', 'targeted');

        if ($type === 'direct') {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
                return response()->json(['success' => true]);
            }
        } else {
            $success = $this->notificationService->markAsRead($id, $user->id);
            return response()->json(['success' => $success]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Marca todas as notificações como lidas
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Deleta uma notificação
     */
    public function delete($id)
    {
        $user = Auth::user();

        // Tentar deletar notificação direta
        $notification = $user->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * Configurações de notificação do usuário
     */
    public function settings()
    {
        $user = Auth::user();
        $settings = $user->getNotificationSettings();

        return view('notifications.settings', compact('settings'));
    }

    /**
     * Atualiza configurações de notificação
     */
    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $user->getNotificationSettings();

        $validated = $request->validate([
            'enable_database' => 'boolean',
            'enable_email' => 'boolean',
            'enable_broadcast' => 'boolean',
            'enable_push' => 'boolean',
            'quiet_hours_enabled' => 'boolean',
            'quiet_hours_start' => 'nullable|date_format:H:i',
            'quiet_hours_end' => 'nullable|date_format:H:i',
            'notification_preferences' => 'nullable|array',
        ]);

        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Configurações atualizadas com sucesso',
        ]);
    }

    /**
     * Endpoint para receber notificações de sistemas externos (API Lumen Checklist)
     * Requer token de autenticação via header X-API-Token
     */
    public function sendFromExternalSystem(Request $request)
    {
        // Validar token de API
        $apiToken = $request->header('X-API-Token');
        $expectedToken = env('EXTERNAL_API_TOKEN', '');

        if (!$apiToken || !$expectedToken || $apiToken !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token de API inválido ou ausente',
            ], 401);
        }

        // Validar dados da requisição
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
        ]);

        try {
            // Enviar notificação usando o serviço
            $notification = $this->notificationService->sendToUsers(
                userIds: $validated['user_ids'],
                type: $validated['type'],
                title: $validated['title'],
                message: $validated['message'],
                data: $validated['data'] ?? [],
                priority: $validated['priority'] ?? 'normal',
                icon: $validated['icon'] ?? 'bell',
                color: $validated['color'] ?? 'blue'
            );

            return response()->json([
                'success' => true,
                'message' => 'Notificação enviada com sucesso',
                'notification_id' => $notification->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar notificação: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ================================================================
    // MÉTODOS PARA MOBILE APP (Autenticação via JWT do Lumen)
    // ================================================================

    /**
     * Buscar notificações para mobile app
     */
    public function getNotificationsForMobile(Request $request)
    {
        $userId = ValidateJwtFromLumen::getJwtUserId($request);
        $limit = $request->input('limit', 50);

        $notifications = $this->notificationService->getUnreadNotifications($userId, $limit);

        // Formatar notificações
        $formatted = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => 'targeted',
                'notification_type' => $notification->notification_type,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'priority' => $notification->priority,
                'data' => $notification->data,
                'url' => $notification->data['url'] ?? null,
                'created_at' => $notification->created_at->toIso8601String(),
                'is_read' => false,
            ];
        });

        return response()->json([
            'notifications' => $formatted,
            'unread_count' => $notifications->count(),
        ]);
    }

    /**
     * Buscar contador de não lidas para mobile
     */
    public function getUnreadCountForMobile(Request $request)
    {
        $userId = ValidateJwtFromLumen::getJwtUserId($request);

        $count = $this->notificationService->getUnreadNotifications($userId)->count();

        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Marcar notificação como lida (mobile)
     */
    public function markAsReadForMobile(Request $request, $id)
    {
        $userId = ValidateJwtFromLumen::getJwtUserId($request);
        $success = $this->notificationService->markAsRead($id, $userId);

        return response()->json(['success' => $success]);
    }

    /**
     * Marcar todas como lidas (mobile)
     */
    public function markAllAsReadForMobile(Request $request)
    {
        $userId = ValidateJwtFromLumen::getJwtUserId($request);
        $count = $this->notificationService->markAllAsRead($userId);

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Deletar notificação (mobile)
     */
    public function deleteForMobile(Request $request, $id)
    {
        $userId = ValidateJwtFromLumen::getJwtUserId($request);

        // Verificar se a notificação pertence ao usuário
        $notification = NotificationTarget::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($notification) {
            $notification->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * 🧪 TESTE: Criar notificação de teste para o usuário logado
     */
    public function createTestNotification(Request $request)
    {
        $userId = ValidateJwtFromLumen::getJwtUserId($request);
        $user = ValidateJwtFromLumen::getJwtUser($request);

        try {
            // Criar notificação de teste
            $notification = $this->notificationService->sendToUsers(
                userIds: [$userId],
                type: 'test.mobile',
                title: '🧪 Notificação de Teste',
                message: 'Esta é uma notificação de teste enviada em ' . now()->format('d/m/Y H:i:s') . ' para validar o sistema mobile!',
                data: [
                    'test' => true,
                    'timestamp' => now()->toIso8601String(),
                    'user_name' => $user->name ?? 'Usuário',
                ],
                priority: 'high',
                icon: 'flask',
                color: 'purple'
            );

            return response()->json([
                'success' => true,
                'message' => 'Notificação de teste criada com sucesso!',
                'notification' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'created_at' => $notification->created_at->toIso8601String(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar notificação: ' . $e->getMessage(),
            ], 500);
        }
    }
}
