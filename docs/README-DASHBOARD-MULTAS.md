# Painel de Controle de Multas

Este projeto implementa um painel de controle completo para gestão de multas de frota em duas versões: **Laravel (Blade + PHP)** e **JavaScript puro**.

## 📋 Funcionalidades

### Indicadores de Veículos

-   **Veículos Ativos**: Total de veículos na frota
-   **Licenciados**: Veículos com licenciamento em dia
-   **Não Licenciados**: Veículos sem licenciamento válido
-   **Restrições**: Veículos com algum tipo de restrição
-   **IPVA**: Valor total do IPVA da frota
-   **Licenciamentos**: Valor total dos licenciamentos

### Indicadores de Notificações/Multas

-   **Notificações**: Total de notificações recebidas
-   **R$ Notificações**: Valor total das notificações
-   **Multas**: Total de multas aplicadas
-   **R$ Multas**: Valor total das multas
-   **ANTT**: Total de multas da ANTT
-   **R$ ANTT**: Valor total das multas ANTT
-   **Multas Vencidas**: Valor das multas vencidas
-   **Desconto Perdido**: Valor dos descontos perdidos
-   **Multa a Vencer**: Valor das multas a vencer
-   **Multas com Desconto**: Valor das multas com desconto disponível

### Gráficos e Análises (Chart.js)

-   **Multas por Placa**: Gráfico de barras horizontal com Top 10 veículos
-   **Notificações por Órgão**: Gráfico de pizza com distribuição por órgão autuador
-   **Notificações por Gravidade**: Gráfico donut com distribuição por tipo de infração
-   **Multas por Veículo**: Gráfico de barras vertical com Top 10 veículos com mais multas

Todos os gráficos são interativos, responsivos e incluem tooltips informativos.

## 📊 Tipos de Gráficos Implementados

### Laravel Version

-   **Multas por Placa**: Barra horizontal com valores em R$
-   **Notificações por Órgão**: Gráfico de pizza
-   **Notificações por Gravidade**: Gráfico donut
-   **Multas por Veículo**: Barras verticais com quantidade

### JavaScript Version

-   Mesmos tipos de gráficos com Chart.js
-   Interatividade completa (hover, tooltips)
-   Responsivo para mobile
-   Cores personalizadas por categoria

### Recursos dos Gráficos

✅ **Interativos**: Hover effects e tooltips
✅ **Responsivos**: Adaptam ao tamanho da tela
✅ **Acessíveis**: Suporte a leitores de tela
✅ **Personalizáveis**: Cores e estilos customizáveis
✅ **Performance**: Otimizados para grandes datasets

## 🚀 Versão Laravel (Blade + PHP)

### Arquivos Criados

```
app/Http/Controllers/Admin/DashboardMultasController.php
resources/views/admin/dashboard-multas/index.blade.php
routes/web.php (nova rota adicionada)
```

### Instalação

1. O controller já está criado e integrado ao sistema Laravel existente
2. A rota está configurada: `/admin/dashboard-multas`
3. Acesse via: `{{ route('admin.dashboard-multas.index') }}`

### Características

-   ✅ Integração completa com Laravel
-   ✅ Usa models existentes (SmartecVeiculo, etc.)
-   ✅ Sistema de autenticação integrado
-   ✅ Blade templates com componentes reutilizáveis
-   ✅ Responsivo com Tailwind CSS
-   ✅ Auto-refresh a cada 5 minutos

### Uso

```php
// Acessar via rota
Route::get('/admin/dashboard-multas', [DashboardMultasController::class, 'index']);

// Ou incluir em menus
<a href="{{ route('admin.dashboard-multas.index') }}">Dashboard Multas</a>
```

## 🌐 Versão JavaScript Pura

### Arquivos Criados

```
public/dashboard-multas.html          # Versão completa em um arquivo
public/dashboard-multas-js.html       # Versão modular
public/js/dashboard-multas.js         # Classe JavaScript
public/css/dashboard-multas.css       # Estilos CSS
```

### Instalação

1. Acesse diretamente: `/dashboard-multas.html` ou `/dashboard-multas-js.html`
2. Para integração com API, configure a URL base no JavaScript

### Características

-   ✅ Independente de framework
-   ✅ API REST compatível
-   ✅ Dados simulados para demonstração
-   ✅ Classe JavaScript reutilizável
-   ✅ CSS modular e responsivo
-   ✅ Auto-refresh configurável
-   ✅ Suporte a modo escuro
-   ✅ Acessibilidade (WCAG)

### Configuração da API

```javascript
// Configurar URL da API
const dashboard = new DashboardMultas({
    apiBaseUrl: "/api", // URL da sua API
    refreshInterval: 300000, // 5 minutos
    autoRefresh: true,
});
```

### Estrutura da API Esperada

```json
{
    "indicadores": {
        "veiculos": 1250,
        "licenciados": 1180,
        "nao_licenciados": 70,
        "restricoes": 25,
        "ipva_total": 850000.5,
        "licenciamento_valor": 125000.75,
        "total_notificacoes": 342,
        "valor_notificacoes": 89500.25,
        "multas_total": 186,
        "valor_multas": 52750.8,
        "multa_antt": 15,
        "vlr_antt": 12500.0,
        "valor_vencidas": 25800.4,
        "desconto_perdido": 8900.15,
        "multa_avencer": 26950.4,
        "multa_desconto_a_vencer": 23850.35
    },
    "graficos": {
        "multas_por_placa": [{ "placa": "ABC-1234", "total": 8500.5 }],
        "notificacoes_por_orgao": [
            { "orgao_autuador": "DETRAN-SP", "total": 125 }
        ],
        "notificacoes_por_gravidade": [{ "gravidade": "Leve", "total": 156 }],
        "multas_por_veiculo": [{ "placa": "ABC-1234", "total": 12 }]
    }
}
```

## 🎨 Design e UX

### Cores e Temas

-   **Azul**: Indicadores de veículos
-   **Ciano**: Notificações e multas gerais
-   **Azul Escuro**: ANTT
-   **Vermelho**: Multas vencidas/problemas
-   **Verde**: Multas a vencer/positivo

### Responsividade

-   **Desktop**: Grid de 6 colunas para indicadores
-   **Tablet**: Grid de 3 colunas
-   **Mobile**: Coluna única

### Acessibilidade

-   Suporte a leitores de tela
-   Contraste adequado (WCAG AA)
-   Redução de movimento para usuários sensíveis
-   Suporte a modo escuro

## 🔧 Personalização

### Laravel Version

```php
// No controller, modificar queries:
$indicadores['custom'] = Model::where('campo', 'valor')->sum('campo');

// Na view, adicionar indicador:
<div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
    <!-- Seu indicador customizado -->
</div>
```

### JavaScript Version

```javascript
// Adicionar indicador customizado
dashboard.data.indicadores.custom = 12345;

// Renderizar indicador customizado
const customIndicator = dashboard.createIndicator(
    "Título",
    valor,
    "fas fa-icon",
    "purple-gradient"
);
```

## 📱 Mobile Support

Ambas as versões são totalmente responsivas:

-   Layout adaptável para diferentes tamanhos de tela
-   Touch gestures otimizados
-   Performance otimizada para dispositivos móveis
-   Modo offline (versão JavaScript com dados simulados)

## 🔄 Auto-refresh

### Laravel

-   Refresh automático via JavaScript (5 minutos)
-   Pode ser configurado por usuário/perfil

### JavaScript

-   Configurável via parâmetros da classe
-   Pode ser pausado/retomado dinamicamente

## 🐛 Debugging

### Laravel

```php
// Logs no controller
Log::info('Dashboard data:', $indicadores);

// Debug na view
@dd($indicadores)
```

### JavaScript

```javascript
// Console logs habilitados
console.log("Dashboard data:", dashboard.data);

// Modo debug
const dashboard = new DashboardMultas({
    debug: true,
});
```

## 📊 Performance

### Laravel

-   Otimização de queries com índices
-   Cache de resultados (Redis/Memcached)
-   Lazy loading de dados pesados

### JavaScript

-   Debounce em atualizações
-   Lazy loading de gráficos
-   Otimização de DOM

## 🔐 Segurança

### Laravel

-   Autenticação integrada
-   Middleware de permissões
-   Sanitização de dados

### JavaScript

-   Validação de dados da API
-   Escape de HTML
-   CSP headers recomendados

## 📈 Monitoramento

Ambas as versões suportam:

-   Google Analytics
-   Métricas de performance
-   Logs de erro
-   Tracking de uso

## 🤝 Contribuição

Para contribuir:

1. Fork o projeto
2. Crie uma branch para sua feature
3. Faça commit das mudanças
4. Abra um Pull Request

## 📄 Licença

Este projeto está sob licença MIT. Veja o arquivo LICENSE para mais detalhes.
