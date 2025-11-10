# Sistema de Notificações - Guia de Uso

## 📋 Visão Geral

Sistema completo de notificações em tempo real usando Laravel Reverb (WebSocket) com suporte a:

- ✅ Notificações pessoais (usuário específico)
- ✅ Notificações por departamento
- ✅ Notificações por cargo/tipo pessoal
- ✅ Notificações por role (permissão)
- ✅ Notificações por filial
- ✅ Notificações globais (todos os usuários)
- ✅ Broadcasting em tempo real via WebSocket
- ✅ Níveis de prioridade (low, normal, high, urgent)
- ✅ Configurações personalizadas por usuário
- ✅ Horário de silêncio (quiet hours)

## 🚀 Inicialização

### 1. Executar o SQL de criação das tabelas

Execute o arquivo `database/sql/create_notifications_system.sql` no DBeaver ou seu cliente PostgreSQL.

### 2. Compilar assets frontend

```bash
npm install
npm run dev
```

### 3. Iniciar o servidor Reverb

```bash
php artisan reverb:start
```

Ou em modo debug:
```bash
php artisan reverb:start --debug
```

### 4. Iniciar workers de fila (se usar notificações por email)

```bash
php artisan queue:work
```

## 📤 Enviando Notificações

### Exemplo 1: Notificação para usuários específicos

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

$notificationService->sendToUsers(
    userIds: [1, 2, 3],
    type: 'sistema.alerta',
    title: 'Manutenção Programada',
    message: 'O sistema ficará indisponível das 22h às 23h para manutenção.',
    data: [
        'url' => '/admin/manutencao',
        'start_time' => '2024-01-15 22:00:00',
        'end_time' => '2024-01-15 23:00:00',
    ],
    priority: 'high',
    icon: 'wrench',
    color: 'orange'
);
```

### Exemplo 2: Notificação para um departamento

```php
$notificationService->sendToDepartments(
    departmentIds: [5], // ID do departamento de manutenção
    type: 'manutencao.ordem_servico',
    title: 'Nova Ordem de Serviço',
    message: 'Uma nova OS (#12345) foi criada e aguarda atendimento.',
    data: [
        'url' => '/admin/ordemservico/12345',
        'os_id' => 12345,
        'veiculo' => 'ABC-1234',
    ],
    priority: 'normal',
    icon: 'clipboard-list',
    color: 'blue'
);
```

### Exemplo 3: Notificação para uma role específica

```php
$notificationService->sendToRoles(
    roleIds: [2], // ID da role "Gerente"
    type: 'compras.aprovacao_pendente',
    title: 'Pedido de Compra Aguardando Aprovação',
    message: 'Pedido #9876 no valor de R$ 15.000,00 aguarda sua aprovação.',
    data: [
        'url' => '/admin/pedidos/9876',
        'pedido_id' => 9876,
        'valor' => 15000.00,
    ],
    priority: 'urgent',
    icon: 'file-invoice-dollar',
    color: 'red'
);
```

### Exemplo 4: Notificação para cargos/tipos pessoais

```php
$notificationService->sendToCargos(
    cargoIds: [3, 4], // Mecânicos e Auxiliares
    type: 'manutencao.alerta_estoque',
    title: 'Estoque Baixo de Peças',
    message: 'O estoque de filtros de óleo está abaixo do mínimo.',
    data: [
        'url' => '/admin/estoque/filtros',
        'produto_id' => 456,
        'quantidade_atual' => 5,
        'quantidade_minima' => 20,
    ],
    priority: 'high',
    icon: 'boxes',
    color: 'yellow'
);
```

### Exemplo 5: Notificação para filiais

```php
$notificationService->sendToFiliais(
    filialIds: [1, 2], // Matriz e Filial 2
    type: 'sistema.comunicado',
    title: 'Novo Procedimento Operacional',
    message: 'Foi publicado um novo procedimento para gestão de combustível.',
    data: [
        'url' => '/admin/procedimentos/123',
        'documento_id' => 123,
    ],
    priority: 'normal',
    icon: 'file-alt',
    color: 'blue'
);
```

### Exemplo 6: Notificação global (todos os usuários)

```php
$notificationService->sendToAll(
    type: 'sistema.comunicado',
    title: 'Bem-vindo ao Sistema Atualizado',
    message: 'O sistema foi atualizado com novas funcionalidades. Confira!',
    data: [
        'url' => '/admin/novidades',
        'versao' => '2.0.0',
    ],
    priority: 'normal',
    icon: 'rocket',
    color: 'green'
);
```

## 🎯 Exemplos de Uso em Contextos Reais

### Alerta de Vencimento de CNH

```php
use App\Services\NotificationService;

// No comando/job que verifica CNHs vencidas
$service = app(NotificationService::class);

foreach ($motoristasComCnhVencendo as $motorista) {
    $service->sendToUsers(
        userIds: [$motorista->id],
        type: 'vencimentarios.cnh',
        title: 'CNH Próxima ao Vencimento',
        message: "Sua CNH vence em {$motorista->diasAteVencimento} dias. Renove com antecedência!",
        data: [
            'url' => '/admin/perfil',
            'dias_restantes' => $motorista->diasAteVencimento,
            'data_vencimento' => $motorista->validade_cnh->format('d/m/Y'),
        ],
        priority: $motorista->diasAteVencimento <= 7 ? 'urgent' : 'high',
        icon: 'id-card',
        color: $motorista->diasAteVencimento <= 7 ? 'red' : 'orange'
    );
}
```

### Notificação de Sinistro Registrado

```php
// No controller de sinistros, após criar um sinistro
$service = app(NotificationService::class);

// Notificar departamento de gestão de riscos
$service->sendToDepartments(
    departmentIds: [$sinistro->departamentoResponsavel->id],
    type: 'sinistros.novo',
    title: 'Novo Sinistro Registrado',
    message: "Sinistro #{$sinistro->id} - {$sinistro->tipo} - Veículo {$sinistro->veiculo->placa}",
    data: [
        'url' => route('sinistros.show', $sinistro->id),
        'sinistro_id' => $sinistro->id,
        'tipo' => $sinistro->tipo,
        'gravidade' => $sinistro->gravidade,
    ],
    priority: $sinistro->gravidade === 'alta' ? 'urgent' : 'high',
    icon: 'car-crash',
    color: 'red'
);
```

### Notificação de Estoque Baixo

```php
// No observer ou job de verificação de estoque
$service = app(NotificationService::class);

$service->sendToRoles(
    roleIds: [Role::where('name', 'Gerente de Estoque')->first()->id],
    type: 'estoque.alerta_minimo',
    title: 'Alerta de Estoque Mínimo',
    message: "{$produto->nome} está com {$produto->quantidade} unidades (mínimo: {$produto->estoque_minimo})",
    data: [
        'url' => route('produtos.show', $produto->id),
        'produto_id' => $produto->id,
        'quantidade_atual' => $produto->quantidade,
        'estoque_minimo' => $produto->estoque_minimo,
    ],
    priority: 'high',
    icon: 'exclamation-triangle',
    color: 'orange'
);
```

## ⚙️ Configurações de Notificação do Usuário

Os usuários podem configurar suas preferências de notificação através da interface em `/notifications/settings`.

### Obter configurações do usuário via código:

```php
$user = auth()->user();
$settings = $user->getNotificationSettings();

// Verificar se um tipo de notificação está habilitado
if ($settings->isNotificationTypeEnabled('manutencao')) {
    // Enviar notificação
}

// Verificar se está em horário de silêncio
if (!$settings->isInQuietHours()) {
    // Enviar notificação
}

// Obter canais habilitados para um tipo
$channels = $settings->getChannelsForNotificationType('sinistros');
// Retorna algo como: ['database', 'broadcast', 'email']
```

## 🔔 Tipos de Notificação Disponíveis

Configure em `.env`:

```env
# Tipos principais
- sistema.* (comunicados, alertas, manutenção)
- veiculos.* (alertas, vencimentos, manutenção)
- manutencao.* (ordens de serviço, peças, agendamentos)
- sinistros.* (novos, atualizações, aprovações)
- estoque.* (baixo, reposição, transferências)
- vencimentarios.* (CNH, documentos, licenças)
- compras.* (solicitações, aprovações, pedidos)
```

## 📊 Prioridades

```php
'low'    => Informativo, não urgente
'normal' => Padrão, requer atenção
'high'   => Importante, requer ação em breve
'urgent' => Crítico, requer ação imediata
```

## 🎨 Ícones Disponíveis (Font Awesome)

Exemplos comuns:
- `bell` - Notificação genérica
- `exclamation-triangle` - Alerta
- `info-circle` - Informação
- `check-circle` - Sucesso
- `times-circle` - Erro
- `car` - Veículos
- `wrench` - Manutenção
- `file-invoice-dollar` - Financeiro/Compras
- `clipboard-list` - Ordem de serviço
- `boxes` - Estoque
- `id-card` - Documentos
- `car-crash` - Sinistro

## 🎨 Cores Disponíveis

```php
'blue'   => Informação
'green'  => Sucesso
'yellow' => Atenção
'orange' => Importante
'red'    => Urgente/Erro
'gray'   => Neutro
'purple' => Especial
```

## 🧹 Limpeza de Notificações Antigas

Execute periodicamente via comando:

```php
$service = app(NotificationService::class);
$deletedCount = $service->cleanupOldNotifications(90); // Mantém 90 dias
```

Ou crie um comando agendado em `app/Console/Kernel.php`:

```php
$schedule->call(function () {
    app(NotificationService::class)->cleanupOldNotifications(90);
})->monthly();
```

## 🔧 Troubleshooting

### Notificações não aparecem em tempo real

1. Verificar se o Reverb está rodando: `php artisan reverb:start`
2. Verificar se as variáveis de ambiente estão corretas no `.env`
3. Verificar o console do navegador para erros de WebSocket
4. Verificar se `npm run dev` está rodando

### Badge de contagem não atualiza

1. Verificar se o `meta[name="user-id"]` está no layout
2. Verificar se `notifications.js` está sendo carregado
3. Abrir o console e verificar se `window.notificationManager` existe
4. Verificar se há erros no console

### Notificações não são enviadas

1. Verificar se a fila está rodando: `php artisan queue:work`
2. Verificar logs em `storage/logs/laravel.log`
3. Verificar se o usuário tem configurações que bloqueiam notificações
4. Verificar se está em horário de silêncio

## 📚 Referências

- [Laravel Notifications](https://laravel.com/docs/11.x/notifications)
- [Laravel Broadcasting](https://laravel.com/docs/11.x/broadcasting)
- [Laravel Reverb](https://laravel.com/docs/11.x/reverb)
- [Laravel Echo](https://github.com/laravel/echo)

---

**Criado em:** 2025-01-06
**Versão do Sistema:** 2.0.0
