# Guia de Deploy do Laravel Reverb no Servidor Debian

Este guia fornece instruções passo a passo para configurar e executar o Laravel Reverb (WebSocket Server) em produção no servidor Debian.

---

## 📋 Pré-requisitos

- Servidor Debian com acesso root
- Laravel já instalado e funcionando
- Redis instalado e rodando
- Nginx ou Apache configurado
- Certificado SSL (recomendado para produção)
- Supervisor instalado para gerenciar processos

---

## 🔧 Passo 1: Instalar Redis (se ainda não estiver instalado)

```bash
# Atualizar repositórios
apt update

# Instalar Redis
apt install redis-server -y

# Iniciar e habilitar Redis
systemctl start redis-server
systemctl enable redis-server

# Verificar se está rodando
systemctl status redis-server

# Testar conexão
redis-cli ping
# Deve retornar: PONG
```

---

## 🔧 Passo 2: Instalar Supervisor

O Supervisor manterá o processo do Reverb rodando continuamente, reiniciando-o automaticamente em caso de falhas.

```bash
# Instalar Supervisor
apt install supervisor -y

# Iniciar e habilitar Supervisor
systemctl start supervisor
systemctl enable supervisor

# Verificar status
systemctl status supervisor
```

---

## ⚙️ Passo 3: Configurar o Arquivo .env de Produção

Edite o arquivo `.env` no servidor com as configurações de produção:

```bash
# Navegar até o diretório do projeto
cd /caminho/do/projeto

# Editar .env
nano .env
```

Atualize as seguintes variáveis:

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb - Configurações de Produção
REVERB_APP_ID=gestaofrota
REVERB_APP_KEY=gestaofrota_reverb_key_2024
REVERB_APP_SECRET=gestaofrota_reverb_secret_very_secure_2024

# IMPORTANTE: Substituir pelo domínio do servidor (SEM https:// e SEM porta!)
REVERB_HOST=seu-dominio.com.br
REVERB_PORT=8081
REVERB_SCHEME=https

# Servidor Reverb
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8081

# Redis (necessário para Reverb)
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0

# Frontend (Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**⚠️ Importante:**
- Substitua `REVERB_HOST` pelo seu domínio real (ex: `gestaofrota.com.br`)
- Use `https` em produção se tiver certificado SSL configurado
- Gere valores seguros para `REVERB_APP_KEY` e `REVERB_APP_SECRET`

---

## 📝 Passo 4: Criar Configuração do Supervisor para o Reverb

Crie um arquivo de configuração do Supervisor:

```bash
nano /etc/supervisor/conf.d/reverb.conf
```

Adicione o seguinte conteúdo (ajuste os caminhos conforme necessário):

```ini
[program:reverb]
process_name=%(program_name)s
command=php /var/www/html/gestao_frota/artisan reverb:start --host=0.0.0.0 --port=8081
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/html/gestao_frota/storage/logs/reverb.log
stopwaitsecs=3600
```

**Configurações importantes:**
- `command`: Caminho absoluto para o arquivo artisan do projeto
- `user`: Usuário que executará o processo (geralmente `www-data`)
- `stdout_logfile`: Local onde os logs serão salvos

Salve e saia (Ctrl+X, Y, Enter).

---

## 🚀 Passo 5: Iniciar o Reverb via Supervisor

```bash
# Recarregar configurações do Supervisor
supervisorctl reread

# Atualizar Supervisor com as novas configurações
supervisorctl update

# Iniciar o processo do Reverb
supervisorctl start reverb

# Verificar status
supervisorctl status reverb
```

O output deve mostrar algo como:
```
reverb                           RUNNING   pid 12345, uptime 0:00:05
```

---

## 🔥 Passo 6: Configurar Firewall

Se estiver usando `ufw`, libere a porta do Reverb:

```bash
# Permitir conexões na porta 8081
ufw allow 8081/tcp

# Recarregar firewall
ufw reload

# Verificar status
ufw status
```

---

## 🌐 Passo 7: Configurar Nginx como Proxy Reverso (Recomendado)

Para usar SSL/TLS e melhorar a segurança, configure o Nginx como proxy reverso para o Reverb.

Edite o arquivo de configuração do seu site no Nginx:

```bash
nano /etc/nginx/sites-available/seu-site
```

Adicione a seguinte configuração dentro do bloco `server`:

```nginx
# Configuração do Reverb WebSocket
location /app {
    proxy_pass http://127.0.0.1:8081;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_cache_bypass $http_upgrade;

    # Timeouts
    proxy_connect_timeout 7d;
    proxy_send_timeout 7d;
    proxy_read_timeout 7d;
}
```

**Para conexão direta WebSocket (wss://):**

```nginx
server {
    listen 8081 ssl http2;
    server_name seu-dominio.com.br;

    ssl_certificate /caminho/para/certificado.crt;
    ssl_certificate_key /caminho/para/chave.key;

    location / {
        proxy_pass http://127.0.0.1:8081;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;

        proxy_connect_timeout 7d;
        proxy_send_timeout 7d;
        proxy_read_timeout 7d;
    }
}
```

Testar e recarregar Nginx:

```bash
# Testar configuração
nginx -t

# Recarregar Nginx
systemctl reload nginx
```

---

## 🔍 Passo 8: Verificar se o Reverb está Funcionando

### Testar localmente no servidor:

```bash
# Verificar se está escutando na porta
netstat -tlnp | grep 8081
# ou
ss -tlnp | grep 8081

# Ver logs do Reverb
tail -f /var/www/gestao_frota/storage/logs/reverb.log
```

### Testar do navegador:

Acesse as ferramentas de desenvolvedor (F12) e no console digite:

```javascript
// Substitua pelos seus valores reais
Echo.channel('test')
    .listen('.test-event', (e) => {
        console.log('WebSocket funcionando!', e);
    });
```

---

## 🛠️ Comandos Úteis do Supervisor

```bash
# Ver status de todos os processos
supervisorctl status

# Parar o Reverb
supervisorctl stop reverb

# Iniciar o Reverb
supervisorctl start reverb

# Reiniciar o Reverb
supervisorctl restart reverb

# Ver logs em tempo real
tail -f /var/www/gestao_frota/storage/logs/reverb.log

# Recarregar todas as configurações
supervisorctl reread && supervisorctl update
```

---

## 🐛 Troubleshooting

### Reverb não inicia:

```bash
# Ver logs de erro
cat /var/www/gestao_frota/storage/logs/reverb.log

# Verificar permissões
chown -R www-data:www-data /var/www/gestao_frota/storage
chmod -R 775 /var/www/gestao_frota/storage

# Verificar se Redis está rodando
systemctl status redis-server
redis-cli ping
```

### Porta já em uso:

```bash
# Ver qual processo está usando a porta 8081
lsof -i :8081

# Matar o processo (substitua PID pelo número real)
kill -9 PID
```

### WebSocket não conecta:

1. Verifique o firewall
2. Verifique os logs do Nginx: `tail -f /var/log/nginx/error.log`
3. Verifique se o domínio está correto no `.env`
4. Confirme que o certificado SSL está válido
5. Teste a conexão direta: `telnet seu-dominio.com.br 8081`

---

## 🔄 Atualização do Frontend

Sempre que alterar configurações do Reverb no `.env`, execute:

```bash
# Limpar cache
php artisan config:clear
php artisan cache:clear

# Recompilar assets do Vite (se necessário)
npm run build

# Reiniciar Reverb
supervisorctl restart reverb
```

---

## 📊 Monitoramento

### Ver conexões ativas:

```bash
# Monitorar logs em tempo real
tail -f /var/www/gestao_frota/storage/logs/reverb.log

# Ver uso de memória
ps aux | grep reverb

# Ver conexões na porta 8081
netstat -an | grep 8081
```

---

## 🔐 Segurança

1. **Nunca exponha as credenciais do Reverb** (`REVERB_APP_KEY` e `REVERB_APP_SECRET`)
2. **Use sempre HTTPS/WSS em produção**
3. **Configure `allowed_origins`** no arquivo `config/reverb.php`:

```php
'allowed_origins' => [
    'https://seu-dominio.com.br',
],
```

4. **Mantenha o Redis seguro** (use senha se necessário)
5. **Monitore os logs regularmente**

---

## ✅ Checklist Final

- [ ] Redis instalado e rodando
- [ ] Supervisor instalado e configurado
- [ ] Arquivo `.env` atualizado com valores de produção
- [ ] Configuração do Supervisor criada em `/etc/supervisor/conf.d/reverb.conf`
- [ ] Reverb iniciado via Supervisor
- [ ] Firewall configurado (porta 8081)
- [ ] Nginx configurado como proxy reverso
- [ ] SSL/TLS configurado
- [ ] WebSocket testado e funcionando
- [ ] Logs monitorados

---

## 📚 Recursos Adicionais

- [Documentação oficial do Laravel Reverb](https://laravel.com/docs/11.x/reverb)
- [Documentação do Supervisor](http://supervisord.org/)
- [Configuração de WebSocket no Nginx](https://nginx.org/en/docs/http/websocket.html)

---

**Pronto!** Seu Laravel Reverb está configurado e rodando em produção. 🚀
