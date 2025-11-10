# Migração da Função onEnviarCotacoes para Laravel

## Resumo da Migração

A função `onEnviarCotacoes` no `CotacoesController` foi completamente migrada para usar o novo sistema de email Laravel que criamos.

## Principais Mudanças

### ✅ **Antes (Código Original)**

```php
public function onEnviarCotacoes(Request $request)
{
    // Usava classes antigas não-Laravel
    EmailSender::sendEmail($host, $port, $username, $password, $from, $object->email, $subject, $empresa, $enderecoEmpresa, $object->id_cotacoes, $object->nome_fornecedor);

    // Usava TMessage e TTransaction
    new TMessage('info', "Cotação Gerada com sucesso.");
    TTransaction::open('base_unitop');
}
```

### ✅ **Depois (Código Laravel)**

```php
public function onEnviarCotacoes(Request $request)
{
    // Usa injeção de dependência do Laravel
    $emailSenderService = app(\App\Services\EmailSenderService::class);

    // Usa o novo serviço Laravel
    $resultado = $emailSenderService->sendEmail(
        $host, $port, $username, $password, $from,
        $cotacao->email, $subject, $empresa,
        $enderecoEmpresa, $cotacao->id_cotacoes,
        $cotacao->nome_fornecedor
    );

    // Retorna JSON response para APIs
    return response()->json([
        'success' => true,
        'message' => 'Emails enviados com sucesso!'
    ]);
}
```

## Melhorias Implementadas

### 🔧 **1. Tratamento de Erros Aprimorado**

-   ✅ Logs detalhados para cada email enviado
-   ✅ Contabilização de sucessos e erros
-   ✅ Validação de emails antes do envio
-   ✅ Try-catch individual para cada email

### 🔧 **2. Validações Robustas**

-   ✅ Verifica se filiais estão preenchidas
-   ✅ Valida se existem cotações
-   ✅ Verifica se email do fornecedor existe
-   ✅ Valida ID da solicitação

### 🔧 **3. Resposta Estruturada**

-   ✅ JSON response para integração com frontend
-   ✅ Contadores de emails enviados/erros
-   ✅ Mensagens detalhadas de retorno
-   ✅ Status codes apropriados

### 🔧 **4. Logging Completo**

```php
Log::info('Email de cotação enviado com sucesso', [
    'id_cotacao' => $cotacao->id_cotacoes,
    'fornecedor' => $cotacao->nome_fornecedor,
    'email' => $cotacao->email
]);
```

## Como Usar a Nova Função

### **Chamada via AJAX/API**

```javascript
fetch("/admin/cotacoes/enviar", {
    method: "POST",
    headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    },
    body: JSON.stringify({
        id_solicitacoes_compras: 123,
        filial_entrega: "Filial Centro",
        filial_faturamento: "Filial Centro",
    }),
})
    .then((response) => response.json())
    .then((data) => {
        if (data.success) {
            alert(data.message);
            console.log("Emails enviados:", data.data.emails_enviados);
            console.log("Emails com erro:", data.data.emails_com_erro);
        } else {
            alert("Erro: " + data.message);
        }
    });
```

### **Parâmetros Esperados**

-   `id_solicitacoes_compras` (obrigatório): ID da solicitação
-   `filial_entrega` (obrigatório): Filial de entrega
-   `filial_faturamento` (obrigatório): Filial de faturamento

### **Resposta da API**

```json
{
    "success": true,
    "title": "Sucesso",
    "message": "Processo concluído: 3 email(s) enviado(s)",
    "data": {
        "emails_enviados": 3,
        "emails_com_erro": 0,
        "total_cotacoes": 3
    }
}
```

## Compatibilidade e Fallback

### **Versão Estática (Compatibilidade)**

Se preferir usar método estático em vez de injeção de dependência, descomente a função `onEnviarCotacoesLegacy` no controller:

```php
// Em vez de usar injeção de dependência
$emailSenderService = app(\App\Services\EmailSenderService::class);
$resultado = $emailSenderService->sendEmail(...);

// Use o método estático
$resultado = EmailSenderService::sendEmailStatic(...);
```

## Configuração Necessária

### **1. Service Provider Registrado**

✅ Já adicionado em `bootstrap/providers.php`:

```php
App\Providers\EmailServiceProvider::class,
```

### **2. Imports Necessários**

✅ Já adicionados no controller:

```php
use App\Services\EmailSenderService;
use Illuminate\Support\Facades\Auth;
```

## Configurações de Email

A função continua usando as mesmas configurações SMTP:

```php
$host = 'colaboracao.carvalima.com.br';
$port = 587;
$username = 'orcamento@carvalima.com.br';
$password = '3jYS%s74?yHtUL(Y';
$from = 'orcamento@carvalima.com.br';
```

## Exemplo de Rota

Adicione ao seu arquivo de rotas (`routes/web.php` ou similar):

```php
Route::post('/admin/cotacoes/enviar', [CotacoesController::class, 'onEnviarCotacoes'])
    ->name('admin.cotacoes.enviar')
    ->middleware('auth');
```

## Benefícios da Migração

1. **🎯 Laravel-Native**: Usa padrões e convenções do Laravel
2. **🔧 Testável**: Services podem ser facilmente mockados
3. **📊 Observável**: Logs detalhados e estruturados
4. **🛡️ Robusto**: Tratamento de erros aprimorado
5. **🔌 Reutilizável**: Services podem ser usados em outros lugares
6. **📱 API-Ready**: Resposta JSON para frontends modernos
7. **🔄 Compatível**: Mantém interface similar ao código original

## Próximos Passos

1. ✅ Atualizar frontend para usar nova resposta JSON
2. ✅ Configurar rota no arquivo de rotas
3. ✅ Testar envio de emails
4. ✅ Monitorar logs para identificar problemas
5. ✅ Considerar adicionar queue para emails em massa

A migração está completa e a função agora usa o sistema de email Laravel moderno e robusto!
