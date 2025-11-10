# 🎫 Sistema de Chamados/Tickets - Guia Completo

## 📋 Visão Geral

Sistema completo de gerenciamento de chamados de suporte integrado com notificações em tempo real.

### ✨ Funcionalidades Principais

- ✅ Criação de chamados (Bug, Melhoria, Dúvida, Suporte)
- ✅ Workflow: Cliente → Qualidade → Unitop (para melhorias)
- ✅ Sistema de prioridades (Baixa, Média, Alta, Urgente)
- ✅ Atribuição de atendentes
- ✅ Respostas e comentários
- ✅ Anexos de arquivos
- ✅ Histórico completo de mudanças
- ✅ Tags para organização
- ✅ Observadores (watchers)
- ✅ SLA automático baseado em prioridade
- ✅ Avaliação de satisfação
- ✅ Notificações em tempo real
- ✅ Estatísticas e relatórios

---

## 🚀 Instalação

### 1. Executar SQLs no DBeaver

```sql
-- 1. Criar tabelas
\i database/sql/create_support_tickets_system.sql

-- 2. Criar roles e permissões
\i database/sql/create_tickets_roles_permissions.sql
```

### 2. Atribuir Usuários às Roles

```sql
-- Adicionar usuários à Equipe Qualidade
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT
    (SELECT id FROM roles WHERE name = 'Equipe Qualidade'),
    'App\Models\User',
    id
FROM users
WHERE id IN (1, 2, 3); -- IDs dos usuários da qualidade

-- Adicionar usuários à Equipe Unitop
INSERT INTO model_has_roles (role_id, model_type, model_id)
SELECT
    (SELECT id FROM roles WHERE name = 'Equipe Unitop'),
    'App\Models\User',
    id
FROM users
WHERE id IN (10, 11, 12); -- IDs dos desenvolvedores
```

---

## 🔄 Workflow do Sistema

### Tipo: Bug, Dúvida, Suporte
```
Cliente cria → NOVO → Unitop atribui → EM_ATENDIMENTO → RESOLVIDO → FECHADO
```

### Tipo: Melhoria
```
Cliente cria → AGUARDANDO_QUALIDADE →
  ├─ Qualidade APROVA → APROVADO_QUALIDADE → Unitop atende → EM_ATENDIMENTO → RESOLVIDO → FECHADO
  └─ Qualidade REJEITA → REJEITADO_QUALIDADE → FECHADO
```

---

## 📊 Status Disponíveis

| Status | Descrição | Cor |
|--------|-----------|-----|
| `novo` | Recém criado | Azul |
| `aguardando_qualidade` | Melhoria aguardando revisão | Roxo |
| `aprovado_qualidade` | Melhoria aprovada | Verde |
| `rejeitado_qualidade` | Melhoria não aprovada | Vermelho |
| `em_atendimento` | Sendo trabalhado | Amarelo |
| `aguardando_cliente` | Aguardando resposta do cliente | Laranja |
| `resolvido` | Resolvido, aguardando fechamento | Verde |
| `fechado` | Fechado/Concluído | Cinza |
| `cancelado` | Cancelado | Vermelho |

---

## 💻 Uso via Código

### Criar um Chamado

```php
use App\Services\TicketService;
use App\Enums\TicketType;
use App\Enums\TicketPriority;

$ticketService = app(TicketService::class);

$ticket = $ticketService->createTicket([
    'category_id' => 1, // ID da categoria
    'type' => TicketType::BUG->value,
    'priority' => TicketPriority::ALTA->value,
    'subject' => 'Erro ao salvar veículo',
    'description' => 'Ao tentar salvar um veículo novo, aparece erro 500...',
    'url' => 'https://gestaofrota.com.br/veiculos/create',
    'browser' => 'Chrome 120',
    'device' => 'Desktop',
    'tags' => [1, 3], // IDs das tags (opcional)
], auth()->user());
```

### Adicionar Resposta

```php
$response = $ticketService->addResponse($ticket, [
    'message' => 'Já estamos investigando o problema...',
    'is_internal' => false, // Visível para o cliente
    'time_spent_minutes' => 30, // Tempo gasto (opcional)
], auth()->user());
```

### Mudar Status

```php
use App\Enums\TicketStatus;

$ticketService->updateStatus(
    $ticket,
    TicketStatus::EM_ATENDIMENTO,
    auth()->user(),
    'Iniciando atendimento'
);
```

### Atribuir para Atendente

```php
$atendente = User::find(10);

$ticketService->assignTicket(
    $ticket,
    $atendente,
    auth()->user(),
    'Melhor pessoa para resolver este tipo de issue'
);
```

### Revisão da Qualidade (Melhorias)

```php
// Aprovar
$ticketService->qualityReview(
    $ticket,
    auth()->user(),
    approved: true,
    comments: 'Excelente sugestão! Vamos implementar.'
);

// Rejeitar
$ticketService->qualityReview(
    $ticket,
    auth()->user(),
    approved: false,
    comments: 'Não se encaixa no roadmap atual.'
);
```

### Definir Estimativa

```php
$ticketService->setEstimate(
    $ticket,
    hours: 8.5, // 8.5 horas
    auth()->user()
);
```

### Upload de Anexo

```php
$attachment = $ticketService->uploadAttachment(
    $ticket,
    $request->file('attachment'),
    auth()->user(),
    responseId: $response->id // Opcional
);
```

### Adicionar Avaliação

```php
$ticketService->addSatisfactionRating(
    $ticket,
    rating: 5, // 1-5 estrelas
    comment: 'Atendimento excelente!'
);
```

---

## 🎯 Permissões

| Permissão | Descrição | Quem tem |
|-----------|-----------|----------|
| `tickets.view` | Ver próprios tickets | Todos |
| `tickets.view_all` | Ver todos os tickets | Unitop, Qualidade |
| `tickets.view_internal` | Ver notas internas | Unitop |
| `tickets.create` | Criar tickets | Todos |
| `tickets.update` | Editar tickets | Criador, Unitop |
| `tickets.delete` | Deletar tickets | Superuser |
| `tickets.assign` | Atribuir tickets | Unitop |
| `tickets.change_status` | Mudar status | Unitop, Qualidade |
| `tickets.set_estimate` | Definir estimativa | Unitop |
| `tickets.add_internal_note` | Adicionar nota interna | Unitop |
| `tickets.quality_review` | Revisar melhorias | Qualidade |
| `tickets.reports` | Ver relatórios | Unitop, Gerentes |
| `tickets.manage_categories` | Gerenciar categorias | Superuser |
| `tickets.manage_tags` | Gerenciar tags | Unitop |

---

## 📧 Notificações Automáticas

O sistema envia notificações automáticas em tempo real para:

### Quando um chamado é criado:
- **Bug/Dúvida/Suporte**: Equipe Unitop recebe notificação
- **Melhoria**: Equipe Qualidade recebe notificação

### Quando status muda:
- Criador do chamado é notificado

### Quando é atribuído:
- Atendente recebe notificação

### Quando há nova resposta:
- Criador + Atendente + Observadores recebem notificação

### Quando qualidade aprova/rejeita:
- Criador é notificado
- Se aprovado: Equipe Unitop é notificada

---

## 🔍 Queries Úteis

### Tickets abertos do usuário

```php
$tickets = SupportTicket::open()
    ->createdBy(auth()->id())
    ->latest()
    ->get();
```

### Tickets atribuídos a mim

```php
$tickets = SupportTicket::open()
    ->assignedTo(auth()->id())
    ->orderBy('priority', 'desc')
    ->get();
```

### Tickets atrasados (SLA vencido)

```php
$tickets = SupportTicket::overdue()
    ->with(['user', 'category'])
    ->get();
```

### Melhorias aguardando qualidade

```php
$tickets = SupportTicket::awaitingQuality()
    ->latest()
    ->get();
```

### Tickets por tipo

```php
use App\Enums\TicketType;

$bugs = SupportTicket::byType(TicketType::BUG)
    ->open()
    ->get();
```

### Estatísticas

```php
// Total abertos por prioridade
$stats = DB::table('v_open_tickets_by_priority')->get();

// Por categoria
$byCategory = DB::table('v_tickets_by_category')->get();

// Performance de atendentes
$performance = DB::table('v_agent_performance')->get();
```

---

## 🎨 Frontend (Exemplo com Blade)

### Listagem de Tickets

```blade
@foreach($tickets as $ticket)
    <div class="ticket-item">
        <span class="badge badge-{{ $ticket->priority->color() }}">
            {{ $ticket->priority->label() }}
        </span>

        <span class="badge badge-{{ $ticket->status->color() }}">
            {{ $ticket->status->label() }}
        </span>

        <h3>#{{ $ticket->ticket_number }} - {{ $ticket->subject }}</h3>

        <p>{{ Str::limit($ticket->description, 100) }}</p>

        <small>
            Criado por {{ $ticket->user->name }} em {{ $ticket->created_at->format('d/m/Y H:i') }}
        </small>

        @if($ticket->isOverdue())
            <span class="badge badge-danger">ATRASADO</span>
        @endif
    </div>
@endforeach
```

### Formulário de Criação

```blade
<form method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data">
    @csrf

    <select name="type" required>
        @foreach(\App\Enums\TicketType::options() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    <select name="priority">
        @foreach(\App\Enums\TicketPriority::options() as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>

    <select name="category_id" required>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>

    <input type="text" name="subject" placeholder="Assunto" required>

    <textarea name="description" placeholder="Descrição detalhada" required></textarea>

    <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.doc,.docx">

    <button type="submit">Criar Chamado</button>
</form>
```

---

## 📱 API REST (Exemplo)

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    // Listar tickets
    Route::get('/tickets', [TicketController::class, 'index']);

    // Ver ticket
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);

    // Criar ticket
    Route::post('/tickets', [TicketController::class, 'store']);

    // Adicionar resposta
    Route::post('/tickets/{ticket}/responses', [TicketController::class, 'addResponse']);

    // Mudar status
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus']);

    // Atribuir
    Route::post('/tickets/{ticket}/assign', [TicketController::class, 'assign']);

    // Revisão qualidade
    Route::post('/tickets/{ticket}/quality-review', [TicketController::class, 'qualityReview']);
});
```

---

## 🔧 Configurações Adicionais

### SLA Customizado

Edite `App\Enums\TicketPriority::slaHours()`:

```php
public function slaHours(): int
{
    return match($this) {
        self::URGENTE => 2,    // 2 horas
        self::ALTA => 8,        // 1 dia útil
        self::MEDIA => 24,      // 3 dias úteis
        self::BAIXA => 72,      // 1 semana
    };
}
```

### Categorias Personalizadas

```sql
INSERT INTO ticket_categories (name, slug, description, icon, color, display_order)
VALUES ('Integrações', 'integracoes', 'Problemas com APIs e integrações', 'plug', 'purple', 10);
```

### Tags Personalizadas

```sql
INSERT INTO ticket_tags (name, slug, color)
VALUES ('Urgente', 'urgente', 'red');
```

---

## 📊 Relatórios

### Dashboard de Tickets

```php
$dashboard = [
    'total_abertos' => SupportTicket::open()->count(),
    'total_atrasados' => SupportTicket::overdue()->count(),
    'aguardando_qualidade' => SupportTicket::awaitingQuality()->count(),
    'meus_tickets' => SupportTicket::assignedTo(auth()->id())->open()->count(),
    'por_prioridade' => DB::table('v_open_tickets_by_priority')->get(),
    'por_status' => SupportTicket::selectRaw('status, COUNT(*) as total')
        ->open()
        ->groupBy('status')
        ->get(),
];
```

---

## 🚨 Troubleshooting

### Notificações não chegam

1. Verificar se Reverb está rodando
2. Verificar se usuário tem as roles corretas
3. Verificar logs em `storage/logs/laravel.log`

### Melhoria não vai para qualidade

1. Verificar se tipo é `TicketType::MELHORIA`
2. Verificar se existem usuários com role "Equipe Qualidade"

### Permissões negadas

1. Executar `create_tickets_roles_permissions.sql`
2. Atribuir usuários às roles corretas
3. Limpar cache: `php artisan cache:clear`

---

## 🎓 Exemplos de Uso Real

### Cenário 1: Bug Urgente

```php
$ticket = $ticketService->createTicket([
    'category_id' => 1, // Bug/Erro
    'type' => TicketType::BUG,
    'priority' => TicketPriority::URGENTE,
    'subject' => 'Sistema não carrega após login',
    'description' => 'Após fazer login, aparece tela branca. Console mostra erro 500.',
    'browser' => 'Chrome 120',
    'url' => url()->current(),
], $user);

// Unitop é notificado automaticamente (prioridade urgent)
```

### Cenário 2: Melhoria

```php
// Cliente cria melhoria
$ticket = $ticketService->createTicket([
    'category_id' => 2,
    'type' => TicketType::MELHORIA,
    'priority' => TicketPriority::MEDIA,
    'subject' => 'Adicionar filtro de data nos relatórios',
    'description' => 'Seria útil poder filtrar relatórios por período...',
], $cliente);

// Equipe Qualidade é notificada automaticamente

// Qualidade revisa e aprova
$ticketService->qualityReview($ticket, $qualityUser, true, 'Aprovado! Prioridade para Q2.');

// Equipe Unitop é notificada

// Unitop atribui desenvolvedor
$ticketService->assignTicket($ticket, $dev, $manager);

// Dev define estimativa
$ticketService->setEstimate($ticket, 16, $dev);

// Dev responde
$ticketService->addResponse($ticket, [
    'message' => 'Implementado! Por favor testar.',
    'is_solution' => true,
], $dev);

// Status muda automaticamente para RESOLVIDO

// Cliente avalia
$ticketService->addSatisfactionRating($ticket, 5, 'Perfeito!');

// Status muda automaticamente para FECHADO
```

---

**Criado em:** 2025-01-06
**Versão:** 1.0.0
