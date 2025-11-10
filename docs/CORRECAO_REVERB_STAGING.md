# 🔧 Correção do Reverb no Servidor Staging

## ❌ Problema Identificado

O `.env` do servidor staging está com configurações **incorretas**:

```env
REVERB_HOST=https://lcarvalima.unitopconsultoria.com.br:8443  ❌ ERRADO
```

O `REVERB_HOST` **NÃO pode** conter `https://` nem porta. Deve ser **apenas o domínio**.

---

## ✅ Solução Rápida

### Opção 1: Executar Script Automático

1. Faça upload do arquivo `fix_reverb_staging.sh` para o servidor
2. Execute no servidor:

```bash
cd /var/www/html/gestao_frota
chmod +x fix_reverb_staging.sh
./fix_reverb_staging.sh
```

Este script irá:
- ✅ Fazer backup do `.env`
- ✅ Corrigir `REVERB_HOST`
- ✅ Adicionar `CACHE_PREFIX`
- ✅ Limpar caches
- ✅ Reiniciar Reverb
- ✅ Verificar conectividade

---

### Opção 2: Correção Manual

Execute os comandos abaixo no servidor:

```bash
cd /var/www/html/gestao_frota

# 1. Fazer backup do .env
cp .env .env.backup.$(date +%Y%m%d_%H%M%S)

# 2. Editar .env
nano .env
```

**Altere estas linhas:**

```env
# DE:
REVERB_HOST=https://lcarvalima.unitopconsultoria.com.br:8443
CACHE_PREFIX=

# PARA:
REVERB_HOST=lcarvalima.unitopconsultoria.com.br
CACHE_PREFIX=gestao_frota_staging_
```

**Salve** (Ctrl+X, Y, Enter)

```bash
# 3. Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan config:cache

# 4. Reiniciar Reverb
supervisorctl restart reverb

# 5. Verificar se está rodando
supervisorctl status reverb

# 6. Ver logs
tail -f storage/logs/reverb.log
```

---

## 🧪 Testar a Conexão WebSocket

### Método 1: No Console do Navegador

1. Acesse o sistema staging: `https://lcarvalima.unitopconsultoria.com.br:8443`
2. Abra o Console (F12)
3. Cole este código:

```javascript
// Verificar se Echo está carregado
console.log('Echo disponível?', typeof Echo !== 'undefined');

// Tentar conectar
if (typeof Echo !== 'undefined' && Echo.connector) {
    console.log('Connector:', Echo.connector.pusher.connection.state);
}
```

Se aparecer `connected`, está funcionando! ✅

### Método 2: Arquivo HTML de Teste

1. Faça upload do arquivo `test_websocket_connection.html` para o servidor
2. Acesse via navegador: `https://lcarvalima.unitopconsultoria.com.br:8443/test_websocket_connection.html`
3. Clique em **"Testar Conexão"**
4. Observe os logs na página

### Método 3: Criar um Ticket

1. Entre no sistema staging
2. Vá em **Suporte → Novo Ticket**
3. Crie um ticket de teste
4. Verifique se a notificação aparece em tempo real

---

## 🔍 Diagnóstico Completo

Execute no servidor para verificar tudo:

```bash
echo "=== VERIFICAÇÃO REVERB ==="
echo ""
echo "1. Status Reverb:"
supervisorctl status reverb
echo ""

echo "2. Porta 8081 escutando?"
netstat -tlnp | grep 8081 || echo "❌ Porta 8081 NÃO está escutando"
echo ""

echo "3. Redis funcionando?"
redis-cli ping || echo "❌ Redis não está respondendo"
echo ""

echo "4. Configurações .env:"
grep "^REVERB" /var/www/html/gestao_frota/.env
echo ""

echo "5. Últimas linhas do log do Reverb:"
tail -20 /var/www/html/gestao_frota/storage/logs/reverb.log
echo ""

echo "6. Processos Reverb rodando:"
ps aux | grep reverb | grep -v grep
```

---

## 📊 Configuração Correta Esperada

Após a correção, o `.env` deve ter:

```env
# ✅ CORRETO
REVERB_HOST=lcarvalima.unitopconsultoria.com.br
REVERB_PORT=8081
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081

# Frontend (Vite) - estas variáveis usam interpolação
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

## 🐛 Possíveis Problemas e Soluções

### ❌ Erro: "WebSocket connection failed"

**Causa:** Porta 8081 bloqueada ou Reverb não está rodando

**Solução:**
```bash
# Verificar se Reverb está rodando
supervisorctl status reverb

# Se não estiver, iniciar
supervisorctl start reverb

# Verificar logs
tail -f /var/www/html/gestao_frota/storage/logs/reverb.log
```

### ❌ Erro: "Failed to load resource: net::ERR_CONNECTION_REFUSED"

**Causa:** Firewall bloqueando porta 8081 ou Apache não está fazendo proxy

**Solução:**
```bash
# Se usar firewall (ufw)
ufw allow 8081/tcp
ufw reload
```

### ❌ Erro: "Connection timeout"

**Causa:** Configuração de timeout muito curta

**Solução:** Verificar configuração do Apache/Nginx para WebSocket

---

## ✅ Como Saber se Está Funcionando

Você saberá que está funcionando quando:

1. ✅ Reverb mostra status `RUNNING` no Supervisor
2. ✅ Porta 8081 aparece no `netstat`
3. ✅ Console do navegador mostra `Echo.connector.pusher.connection.state = "connected"`
4. ✅ Ao criar um ticket, a notificação aparece **instantaneamente** sem precisar recarregar a página
5. ✅ Badge de notificações atualiza em tempo real

---

## 📞 Próximos Passos

Após aplicar a correção:

1. **Teste criando um ticket** no sistema staging
2. **Verifique se a notificação chega** em tempo real
3. **Monitore os logs** por alguns minutos:
   ```bash
   tail -f /var/www/html/gestao_frota/storage/logs/reverb.log
   ```
4. **Se ainda não funcionar**, verifique:
   - Logs do Apache: `tail -f /var/log/apache2/error.log`
   - Logs do Laravel: `tail -f /var/www/html/gestao_frota/storage/logs/laravel.log`

---

## 🚀 Após Funcionar

Quando tudo estiver funcionando em staging, você poderá:

1. Replicar a mesma configuração para **produção**
2. Monitorar o uso de recursos (memória/CPU)
3. Configurar alertas para quedas do Reverb
4. Implementar mais notificações em tempo real no sistema

---

**Boa sorte! 🎯**