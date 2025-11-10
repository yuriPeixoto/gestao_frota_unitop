# 📬 Sistema de Notificações - Guia Completo

## 📋 Índice
1. [Visão Geral](#visão-geral)
2. [Arquitetura do Sistema](#arquitetura-do-sistema)
3. [Tipos de Notificações](#tipos-de-notificações)
4. [Como Enviar Notificações](#como-enviar-notificações)
5. [Integrando em Controllers](#integrando-em-controllers)
6. [API Externa (Checklist/Lumen)](#api-externa-checklistlumen)
7. [Opções Avançadas](#opções-avançadas)
8. [Exemplos Práticos](#exemplos-práticos)
9. [Boas Práticas](#boas-práticas)

---

## 🎯 Visão Geral

O Sistema de Notificações do Gestão Frota permite enviar notificações em tempo real para usuários através de diferentes canais e segmentações organizacionais.

### ✨ Principais Recursos

- ✅ **Notificações Segmentadas**: Envie para usuários específicos, roles, departamentos, cargos, filiais ou todos
- ✅ **Sistema em Tempo Real**: Notificações instantâneas via WebSocket/Broadcasting
- ✅ **Prioridades**: Urgente, Alta, Normal, Baixa
- ✅ **Histórico Completo**: Usuários podem revisar notificações antigas
- ✅ **API Externa**: Sistema externo (Checklist) pode enviar notificações
- ✅ **Painel Robusto**: Interface rica com filtros, badges e estatísticas
- ✅ **Mobile Support**: API dedicada para aplicativo mobile

---

## 🏗️ Arquitetura do Sistema

### Componentes Principais

```
┌─────────────────────────────────────────────────────────────┐
│                    NotificationService                      │
│  (Serviço Central - app/Services/NotificationService.php)  │
└─────────────────────┬───────────────────────────────────────┘
                      │
        ┌─────────────┴─────────────┐
        │                           │
        ▼                           ▼
┌───────────────────┐     ┌──────────────────┐
│ NotificationTarget│     │   Notifications  │
│   (Broadcast)     │     │ (Laravel Direct) │
└─────────┬─────────┘     └────────┬─────────┘
          │                        │
          ▼                        ▼
┌───────────────────────────────────────────┐
│        notification_reads                 │
│   (Controle de leitura por usuário)      │
└───────────────────────────────────────────┘
```

### Tabelas do Banco de Dados

1. **`notification_targets`**: Notificações broadcast (para grupos de usuários)
2. **`notification_reads`**: Controla quais usuários leram cada notificação
3. **`notifications`**: Notificações diretas do Laravel (para usuário específico)

---

## 📦 Tipos de Notificações

### 1️⃣ Por Destinatário

| Tipo | Método | Descrição |
|------|--------|-----------|
| **Usuários Específicos** | `sendToUsers()` | Envia para lista de IDs de usuários |
| **Roles/Funções** | `sendToRoles()` | Envia para usuários com roles específicas |
| **Departamentos** | `sendToDepartments()` | Envia para todos de um departamento |
| **Cargos** | `sendToCargos()` | Envia para todos de um cargo |
| **Filiais** | `sendToFiliais()` | Envia para todos de filiais específicas |
| **Todos** | `sendToAll()` | Broadcast para todos os usuários |

### 2️⃣ Por Prioridade

| Prioridade | Valor | Cor Visual | Uso Recomendado |
|------------|-------|------------|-----------------|
| **Urgente** | `urgent` | 🔴 Vermelho | Problemas críticos, falhas graves |
| **Alta** | `high` | 🟠 Laranja | Tarefas importantes, prazos próximos |
| **Normal** | `normal` | 🔵 Azul | Notificações padrão, atualizações |
| **Baixa** | `low` | ⚪ Cinza | Informações opcionais, lembretes |

### 3️⃣ Por Tipo de Conteúdo

Você pode criar seus próprios tipos usando notação ponto. Exemplos:

- `tickets.nova_melhoria`
- `tickets.nova_resposta`
- `tickets.atribuicao`
- `manutencao.preventiva_vencida`
- `checklist.pendente`
- `system.update`

---

## 🚀 Como Enviar Notificações

### Passo 1: Injetar o Serviço

No seu Controller ou Service, injete o `NotificationService`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;

class MeuController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
}
```

### Passo 2: Enviar Notificação

Escolha o método apropriado baseado no destinatário:

#### 📨 Para Usuários Específicos

```php
$this->notificationService->sendToUsers(
    userIds: [1, 2, 3],
    type: 'order.completed',
    title: 'Pedido Finalizado',
    message: 'Seu pedido #12345 foi concluído com sucesso!',
    data: [
        'order_id' => 12345,
        'url' => route('orders.show', 12345),
    ],
    priority: 'high',
    icon: 'check-circle',
    color: 'green'
);
```

#### 👥 Para uma Role Específica

```php
// Notificar todos os administradores
$adminRoleId = 1;

$this->notificationService->sendToRoles(
    roleIds: [$adminRoleId],
    type: 'system.alert',
    title: 'Novo Usuário Cadastrado',
    message: 'Um novo usuário se cadastrou e aguarda aprovação.',
    priority: 'normal',
    icon: 'user-plus',
    color: 'blue'
);
```

#### 🏢 Para um Departamento

```php
$departamentoTI = 5;

$this->notificationService->sendToDepartments(
    departmentIds: [$departamentoTI],
    type: 'maintenance.scheduled',
    title: 'Manutenção Programada',
    message: 'O sistema ficará offline das 02h às 04h para manutenção.',
    priority: 'urgent',
    icon: 'exclamation-triangle',
    color: 'red'
);
```

#### 🏪 Para uma Filial

```php
$filialId = 10;

$this->notificationService->sendToFiliais(
    filialIds: [$filialId],
    type: 'announcement.filial',
    title: 'Reunião de Equipe',
    message: 'Reunião geral amanhã às 10h no auditório.',
    priority: 'normal',
    icon: 'calendar',
    color: 'purple'
);
```

#### 📢 Para TODOS os Usuários

```php
$this->notificationService->sendToAll(
    type: 'system.update',
    title: 'Nova Versão Disponível',
    message: 'O sistema foi atualizado para a versão 2.5.0 com novos recursos!',
    priority: 'low',
    icon: 'rocket',
    color: 'indigo'
);
```

---

## 🎨 Parâmetros Disponíveis

### Parâmetros Obrigatórios

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `type` | `string` | Identificador do tipo de notificação |
| `title` | `string` | Título da notificação (máx. 255 caracteres) |
| `message` | `string` | Mensagem completa da notificação |

### Parâmetros Opcionais

| Parâmetro | Tipo | Padrão | Descrição |
|-----------|------|--------|-----------|
| `data` | `array` | `[]` | Dados adicionais (JSON) - útil para URLs, IDs, etc. |
| `priority` | `string` | `'normal'` | Prioridade: `urgent`, `high`, `normal`, `low` |
| `icon` | `string` | `'bell'` | Ícone Font Awesome (sem prefixo `fa-`) |
| `color` | `string` | `'blue'` | Cor visual: `red`, `orange`, `yellow`, `green`, `blue`, `indigo`, `purple`, `gray` |

### Exemplo com Todos os Parâmetros

```php
$this->notificationService->sendToUsers(
    userIds: [42],
    type: 'manutencao.veiculo.criada',
    title: 'Manutenção Preventiva Agendada',
    message: 'O veículo ABC-1234 está agendado para manutenção preventiva em 15/12/2024.',
    data: [
        'veiculo_id' => 100,
        'placa' => 'ABC-1234',
        'data_manutencao' => '2024-12-15',
        'url' => route('manutencao.show', 100),
        'tipo' => 'preventiva',
    ],
    priority: 'high',
    icon: 'wrench',
    color: 'orange'
);
```

---

## 💻 Integrando em Controllers

### Exemplo Real: Sistema de Tickets

Este é o exemplo real do nosso `TicketService.php`:

```php
<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\User;

class TicketService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Notificar criação de novo ticket
     */
    protected function notifyTicketCreated(SupportTicket $ticket): void
    {
        // Se for melhoria, notificar equipe de qualidade
        if ($ticket->type === 'melhoria') {
            $qualityUsers = User::role('Equipe Qualidade')->pluck('id')->toArray();

            if (!empty($qualityUsers)) {
                $this->notificationService->sendToUsers(
                    userIds: $qualityUsers,
                    type: 'tickets.nova_melhoria',
                    title: 'Nova Melhoria para Revisar',
                    message: "#{$ticket->ticket_number}: {$ticket->subject}",
                    data: [
                        'url' => route('tickets.show', $ticket->id),
                        'ticket_id' => $ticket->id,
                    ],
                    priority: 'high',
                    icon: 'lightbulb',
                    color: 'yellow'
                );
            }
        }
    }

    /**
     * Notificar nova resposta
     */
    protected function notifyNewResponse(SupportTicket $ticket, User $author): void
    {
        // Notificar criador do ticket (se não for ele quem respondeu)
        if ($ticket->user_id !== $author->id) {
            $this->notificationService->sendToUsers(
                userIds: [$ticket->user_id],
                type: 'tickets.nova_resposta',
                title: 'Nova Resposta no seu Chamado',
                message: "#{$ticket->ticket_number}: {$author->name} respondeu",
                data: ['url' => route('tickets.show', $ticket->id)],
                priority: 'normal',
                icon: 'comment',
                color: 'blue'
            );
        }
    }

    /**
     * Notificar atribuição de ticket
     */
    protected function notifyTicketAssigned(SupportTicket $ticket, User $assignee): void
    {
        $this->notificationService->sendToUsers(
            userIds: [$assignee->id],
            type: 'tickets.atribuicao',
            title: 'Novo Chamado Atribuído',
            message: "#{$ticket->ticket_number}: {$ticket->subject}",
            data: ['url' => route('tickets.show', $ticket->id)],
            priority: $ticket->priority === 'urgente' ? 'urgent' : 'high',
            icon: 'user-check',
            color: 'green'
        );
    }
}
```

### Exemplo 2: Sistema de Manutenção

```php
<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Services\NotificationService;

class ManutencaoService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Criar ordem de serviço e notificar responsáveis
     */
    public function criarOrdemServico(array $data): OrdemServico
    {
        $os = OrdemServico::create($data);

        // Notificar mecânicos da filial
        $this->notificationService->sendToFiliais(
            filialIds: [$os->filial_id],
            type: 'manutencao.nova_os',
            title: 'Nova Ordem de Serviço',
            message: "OS #{$os->numero} criada para veículo {$os->veiculo->placa}",
            data: [
                'os_id' => $os->id,
                'url' => route('manutencao.show', $os->id),
            ],
            priority: $os->urgente ? 'urgent' : 'normal',
            icon: 'tools',
            color: 'orange'
        );

        return $os;
    }

    /**
     * Alertar sobre manutenção preventiva vencida
     */
    public function alertarManutencoesVencidas(): void
    {
        $veiculosVencidos = $this->getVeiculosComManutencaoVencida();

        foreach ($veiculosVencidos as $veiculo) {
            // Notificar gestor da frota
            $this->notificationService->sendToRoles(
                roleIds: [config('roles.gestor_frota')],
                type: 'manutencao.preventiva_vencida',
                title: 'Manutenção Preventiva Vencida',
                message: "Veículo {$veiculo->placa} está com manutenção atrasada há {$veiculo->dias_atraso} dias",
                data: [
                    'veiculo_id' => $veiculo->id,
                    'url' => route('veiculos.show', $veiculo->id),
                ],
                priority: 'urgent',
                icon: 'exclamation-circle',
                color: 'red'
            );
        }
    }
}
```

---

## 🌐 API Externa (Checklist/Lumen)

### Como Sistemas Externos Enviam Notificações

O sistema permite que aplicações externas (como o Checklist em Lumen) enviem notificações.

### Configuração

1. **Defina o Token de API** no `.env`:

```env
EXTERNAL_API_TOKEN=seu_token_super_secreto_aqui_123abc
```

2. **Endpoint Disponível**:

```
POST /api/notifications/send
```

### Autenticação

Envie o token no header:

```
X-API-Token: seu_token_super_secreto_aqui_123abc
```

### Payload JSON

```json
{
  "user_ids": [1, 2, 3],
  "type": "checklist.pendente",
  "title": "Checklist Pendente",
  "message": "Você tem 3 checklists pendentes para hoje.",
  "data": {
    "checklist_ids": [10, 11, 12],
    "url": "https://checklist.exemplo.com/pendentes"
  },
  "priority": "high",
  "icon": "clipboard-check",
  "color": "blue"
}
```

### Exemplo com cURL

```bash
curl -X POST https://gestao-frota.exemplo.com/api/notifications/send \
  -H "Content-Type: application/json" \
  -H "X-API-Token: seu_token_super_secreto_aqui_123abc" \
  -d '{
    "user_ids": [42],
    "type": "checklist.concluido",
    "title": "Checklist Concluído",
    "message": "O checklist #1234 foi concluído com sucesso!",
    "data": {
      "checklist_id": 1234
    },
    "priority": "normal",
    "icon": "check",
    "color": "green"
  }'
```

### Exemplo com PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client();

$response = $client->post('https://gestao-frota.exemplo.com/api/notifications/send', [
    'headers' => [
        'Content-Type' => 'application/json',
        'X-API-Token' => env('GESTAO_FROTA_API_TOKEN'),
    ],
    'json' => [
        'user_ids' => [42, 55],
        'type' => 'checklist.alerta',
        'title' => 'Checklist Atrasado',
        'message' => 'Checklist #9876 está atrasado há 3 dias',
        'data' => [
            'checklist_id' => 9876,
            'dias_atraso' => 3,
        ],
        'priority' => 'urgent',
        'icon' => 'exclamation-triangle',
        'color' => 'red',
    ],
]);
```

### Resposta da API

**Sucesso (200):**

```json
{
  "success": true,
  "message": "Notificação enviada com sucesso",
  "notification_id": 12345
}
```

**Erro (401 - Token inválido):**

```json
{
  "success": false,
  "message": "Token de API inválido ou ausente"
}
```

**Erro (422 - Validação):**

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "user_ids": ["The user ids field is required."]
  }
}
```

---

## ⚙️ Opções Avançadas

### 1. Notificações Agendadas

Embora não exposto diretamente na API pública, você pode agendar notificações:

```php
$this->notificationService->createNotification(
    type: 'reminder.meeting',
    title: 'Reunião em 1 hora',
    message: 'Lembre-se: reunião de equipe às 14h',
    targetType: 'user',
    targetIds: [42],
    scheduledAt: now()->addHour()
);
```

### 2. Notificações com Expiração

```php
$this->notificationService->createNotification(
    type: 'offer.limited',
    title: 'Oferta Relâmpago',
    message: 'Desconto de 50% válido até amanhã!',
    targetType: 'all',
    targetIds: [],
    expiresAt: now()->addDay()
);
```

### 3. Verificar se Usuário Pode Receber

```php
$notification = NotificationTarget::find(123);
$user = User::find(42);

if ($notification->shouldReceiveNotification($user)) {
    // Usuário deve receber esta notificação
}
```

### 4. Marcar Como Lida Programaticamente

```php
$this->notificationService->markAsRead(
    notificationId: 123,
    userId: 42
);
```

### 5. Obter Notificações Não Lidas

```php
$unreadNotifications = $this->notificationService->getUnreadNotifications(
    userId: 42,
    limit: 50
);
```

---

## 📚 Exemplos Práticos por Cenário

### Cenário 1: Sistema de Aprovação de Despesas

```php
public function solicitarAprovacao(Despesa $despesa)
{
    // Notificar aprovadores da filial
    $aprovadores = User::role('Aprovador')
        ->whereHas('filiais', fn($q) => $q->where('filiais.id', $despesa->filial_id))
        ->pluck('id')
        ->toArray();

    $this->notificationService->sendToUsers(
        userIds: $aprovadores,
        type: 'despesas.aprovacao_pendente',
        title: 'Nova Despesa para Aprovar',
        message: "Despesa de R$ {$despesa->valor} aguardando sua aprovação",
        data: [
            'despesa_id' => $despesa->id,
            'url' => route('despesas.aprovar', $despesa->id),
        ],
        priority: $despesa->valor > 5000 ? 'high' : 'normal',
        icon: 'file-invoice-dollar',
        color: 'green'
    );
}
```

### Cenário 2: Alerta de Vencimento de CNH

```php
public function alertarVencimentoCNH()
{
    $motoristas = Motorista::where('cnh_vencimento', '<=', now()->addDays(30))
        ->get();

    foreach ($motoristas as $motorista) {
        $this->notificationService->sendToUsers(
            userIds: [$motorista->user_id],
            type: 'motorista.cnh_vencendo',
            title: 'CNH Vencendo em Breve',
            message: "Sua CNH vence em {$motorista->cnh_vencimento->diffForHumans()}. Renove com antecedência!",
            data: [
                'motorista_id' => $motorista->id,
                'url' => route('profile.edit'),
            ],
            priority: 'urgent',
            icon: 'id-card',
            color: 'red'
        );
    }
}
```

### Cenário 3: Notificação de Multa Recebida

```php
public function registrarMulta(Multa $multa)
{
    // Notificar o motorista
    if ($multa->motorista) {
        $this->notificationService->sendToUsers(
            userIds: [$multa->motorista->user_id],
            type: 'multa.recebida',
            title: 'Nova Multa Registrada',
            message: "Multa no valor de R$ {$multa->valor} registrada para o veículo {$multa->veiculo->placa}",
            data: [
                'multa_id' => $multa->id,
                'url' => route('multas.show', $multa->id),
            ],
            priority: 'high',
            icon: 'exclamation-triangle',
            color: 'red'
        );
    }

    // Notificar gestor de frota
    $this->notificationService->sendToRoles(
        roleIds: [Role::where('name', 'Gestor de Frota')->first()->id],
        type: 'multa.nova',
        title: 'Nova Multa Registrada',
        message: "Veículo {$multa->veiculo->placa} recebeu multa de R$ {$multa->valor}",
        data: [
            'multa_id' => $multa->id,
            'url' => route('multas.show', $multa->id),
        ],
        priority: 'normal',
        icon: 'file-invoice',
        color: 'orange'
    );
}
```

### Cenário 4: Conclusão de Abastecimento

```php
public function concluirAbastecimento(Abastecimento $abastecimento)
{
    // Notificar motorista
    $this->notificationService->sendToUsers(
        userIds: [$abastecimento->motorista->user_id],
        type: 'abastecimento.concluido',
        title: 'Abastecimento Confirmado',
        message: "Abastecimento de {$abastecimento->litros}L confirmado para {$abastecimento->veiculo->placa}",
        data: [
            'abastecimento_id' => $abastecimento->id,
            'url' => route('abastecimentos.show', $abastecimento->id),
        ],
        priority: 'low',
        icon: 'gas-pump',
        color: 'blue'
    );
}
```

---

## ✅ Boas Práticas

### 1. Use Tipos Descritivos

```php
// ✅ BOM - Clara e organizada
'tickets.nova_resposta'
'manutencao.preventiva_vencida'
'usuario.senha_alterada'

// ❌ RUIM - Genérico demais
'notification'
'alert'
'message'
```

### 2. Sempre Inclua URLs no Data

```php
// ✅ BOM - Permite navegação direta
data: [
    'url' => route('tickets.show', $ticket->id),
    'ticket_id' => $ticket->id,
]

// ❌ RUIM - Usuário não sabe onde clicar
data: [
    'ticket_id' => $ticket->id,
]
```

### 3. Escolha Prioridades Adequadas

```php
// ✅ BOM - Prioridades fazem sentido
- Sistema caiu: 'urgent'
- Prazo vencendo: 'high'
- Nova mensagem: 'normal'
- Dica de uso: 'low'

// ❌ RUIM - Tudo urgente perde o significado
- Nova mensagem: 'urgent'
- Dica de uso: 'urgent'
```

### 4. Mensagens Claras e Acionáveis

```php
// ✅ BOM - Clara e específica
"O veículo ABC-1234 precisa de manutenção preventiva em 5 dias"

// ❌ RUIM - Vaga e sem contexto
"Veículo precisa de atenção"
```

### 5. Não Abuse do Broadcast Global

```php
// ✅ BOM - Segmentado para quem importa
$this->notificationService->sendToRoles([Role::ADMIN], ...);

// ❌ RUIM - Spam para todos
$this->notificationService->sendToAll(...);
```

### 6. Limpe Notificações Antigas

Configure um job para limpar notificações antigas:

```php
// No seu scheduler (app/Console/Kernel.php)
$schedule->call(function () {
    app(NotificationService::class)->cleanupOldNotifications(90);
})->daily();
```

---

## 🎨 Ícones Font Awesome Sugeridos

Aqui estão alguns ícones úteis (sem o prefixo `fa-`):

| Categoria | Ícones |
|-----------|--------|
| **Alertas** | `exclamation-triangle`, `exclamation-circle`, `exclamation` |
| **Sucesso** | `check`, `check-circle`, `check-double` |
| **Informação** | `info-circle`, `info`, `bell` |
| **Usuários** | `user`, `user-plus`, `user-check`, `users` |
| **Documentos** | `file`, `file-alt`, `file-invoice`, `clipboard` |
| **Veículos** | `car`, `truck`, `bus`, `motorcycle` |
| **Ferramentas** | `tools`, `wrench`, `screwdriver`, `cog` |
| **Tempo** | `clock`, `calendar`, `calendar-check`, `hourglass` |
| **Comunicação** | `comment`, `comments`, `envelope`, `paper-plane` |
| **Financeiro** | `dollar-sign`, `money-bill`, `credit-card`, `coins` |

Veja todos em: https://fontawesome.com/icons

---

## 🐛 Troubleshooting

### Notificações não aparecem em tempo real

1. Verifique se o broadcasting está configurado (Laravel Echo + Pusher/Socket.io)
2. Confira se `BROADCAST_DRIVER` está definido no `.env`
3. Verifique logs em `storage/logs/laravel.log`

### API Externa retorna 401

- Verifique se o token no header `X-API-Token` está correto
- Confirme que `EXTERNAL_API_TOKEN` está configurado no `.env`

### Usuário não recebe notificação

- Verifique se o usuário está ativo (`is_ativo = true`)
- Confirme que o usuário pertence ao segmento correto (role, departamento, etc.)
- Verifique se a notificação não está expirada

---

## 📖 Referências

- **NotificationService**: `app/Services/NotificationService.php`
- **NotificationTarget Model**: `app/Models/NotificationTarget.php`
- **NotificationController**: `app/Http/Controllers/NotificationController.php`
- **Rotas**: `routes/notifications.php`
- **View do Painel**: `resources/views/notifications/index.blade.php`

---

## 📞 Suporte

Para dúvidas ou problemas, consulte:
- A equipe de desenvolvimento
- Documentação do Laravel: https://laravel.com/docs/notifications
- Issues do projeto

---

**Última atualização**: 27/10/2024
**Versão do Sistema**: 2.5.0