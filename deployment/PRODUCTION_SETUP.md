# 🚀 Configuração de Produção - Reverb & Notificações

## 📋 Visão Geral

Este guia mostra como configurar o Laravel Reverb para rodar continuamente em produção usando diferentes métodos.

---

## 🐧 Opção 1: Supervisor (Linux - RECOMENDADO)

### 1. Instalar Supervisor

```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor
```

### 2. Configurar Reverb

Copie o arquivo de configuração:

```bash
sudo cp deployment/supervisor/reverb.conf /etc/supervisor/conf.d/reverb.conf
```

**Edite o arquivo** para ajustar os caminhos:

```bash
sudo nano /etc/supervisor/conf.d/reverb.conf
```

Ajuste:
- `command=php /var/www/gestao_frota/artisan reverb:start` (caminho correto)
- `user=www-data` (usuário do seu servidor web)
- `stdout_logfile=/var/www/gestao_frota/storage/logs/reverb.log`

### 3. Configurar Queue Worker (Opcional, mas recomendado)

```bash
sudo cp deployment/supervisor/queue-worker.conf /etc/supervisor/conf.d/queue-worker.conf
sudo nano /etc/supervisor/conf.d/queue-worker.conf
```

### 4. Recarregar Supervisor

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
sudo supervisorctl start queue-worker:*
```

### 5. Verificar Status

```bash
sudo supervisorctl status
```

Você deve ver:
```
reverb                           RUNNING   pid 12345, uptime 0:00:10
queue-worker:queue-worker_00     RUNNING   pid 12346, uptime 0:00:10
queue-worker:queue-worker_01     RUNNING   pid 12347, uptime 0:00:10
```

### 6. Comandos Úteis

```bash
# Ver logs em tempo real
sudo tail -f /var/www/gestao_frota/storage/logs/reverb.log

# Parar o Reverb
sudo supervisorctl stop reverb

# Reiniciar o Reverb
sudo supervisorctl restart reverb

# Parar tudo
sudo supervisorctl stop all

# Iniciar tudo
sudo supervisorctl start all
```

---

## 🪟 Opção 2: Windows Service (usando NSSM)

### 1. Baixar NSSM

Download: https://nssm.cc/download

### 2. Instalar Reverb como Serviço

```cmd
nssm install LaravelReverb "C:\php\php.exe" "C:\inetpub\wwwroot\gestao_frota\artisan reverb:start"
nssm set LaravelReverb AppDirectory "C:\inetpub\wwwroot\gestao_frota"
nssm set LaravelReverb DisplayName "Laravel Reverb - Gestão Frota"
nssm set LaravelReverb Description "Servidor WebSocket para notificações em tempo real"
nssm set LaravelReverb Start SERVICE_AUTO_START
```

### 3. Gerenciar o Serviço

```cmd
# Iniciar
net start LaravelReverb

# Parar
net stop LaravelReverb

# Remover
nssm remove LaravelReverb confirm
```

---

## 🐳 Opção 3: Docker Compose

Se usar Docker, adicione ao seu `docker-compose.yml`:

```yaml
services:
  app:
    # ... configuração existente

  reverb:
    image: your-app-image
    command: php artisan reverb:start
    restart: unless-stopped
    environment:
      - REVERB_HOST=0.0.0.0
      - REVERB_PORT=8080
      - REVERB_SERVER_HOST=0.0.0.0
      - REVERB_SERVER_PORT=8080
    ports:
      - "8080:8080"
    depends_on:
      - redis
    networks:
      - app-network

  queue:
    image: your-app-image
    command: php artisan queue:work --tries=3
    restart: unless-stopped
    depends_on:
      - redis
    networks:
      - app-network
```

Iniciar:
```bash
docker-compose up -d reverb queue
```

---

## ☁️ Opção 4: Systemd (Linux Alternativo)

### 1. Criar arquivo de serviço

```bash
sudo nano /etc/systemd/system/reverb.service
```

Conteúdo:
```ini
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target redis.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/gestao_frota
ExecStart=/usr/bin/php artisan reverb:start
Restart=always
RestartSec=5
StandardOutput=append:/var/www/gestao_frota/storage/logs/reverb.log
StandardError=append:/var/www/gestao_frota/storage/logs/reverb-error.log

[Install]
WantedBy=multi-user.target
```

### 2. Ativar e iniciar

```bash
sudo systemctl daemon-reload
sudo systemctl enable reverb
sudo systemctl start reverb
```

### 3. Comandos

```bash
# Status
sudo systemctl status reverb

# Parar
sudo systemctl stop reverb

# Reiniciar
sudo systemctl restart reverb

# Logs
sudo journalctl -u reverb -f
```

---

## 🔧 Configurações Adicionais para Produção

### 1. Nginx (Proxy Reverso para WebSocket)

Adicione ao seu arquivo de configuração do Nginx:

```nginx
# WebSocket para Reverb
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}
```

Reiniciar Nginx:
```bash
sudo nginx -t
sudo systemctl restart nginx
```

### 2. Variáveis de Ambiente (.env produção)

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Reverb - Produção
REVERB_APP_ID=gestaofrota_prod
REVERB_APP_KEY=sua_chave_segura_aqui
REVERB_APP_SECRET=seu_secret_muito_seguro_aqui
REVERB_HOST=seudominio.com.br
REVERB_PORT=443
REVERB_SCHEME=https

# Servidor Reverb (interno)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Frontend (Vite)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3. SSL/TLS (HTTPS)

Para usar WSS (WebSocket Secure):

1. Certifique-se de ter certificado SSL instalado
2. Configure `REVERB_SCHEME=https` e `REVERB_PORT=443`
3. Nginx fará o proxy reverso do WSS

### 4. Firewall

```bash
# Liberar porta 8080 apenas para localhost (Nginx fará proxy)
sudo ufw allow from 127.0.0.1 to any port 8080

# Liberar portas públicas
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

---

## 📊 Monitoramento

### 1. Logs

```bash
# Reverb
tail -f storage/logs/reverb.log

# Laravel
tail -f storage/logs/laravel.log

# Nginx
tail -f /var/log/nginx/error.log
```

### 2. Verificar se está rodando

```bash
# Verificar processo
ps aux | grep reverb

# Verificar porta
netstat -tulpn | grep 8080
# ou
ss -tulpn | grep 8080

# Testar conexão
curl http://localhost:8080
```

---

## 🔄 Deploy e Atualizações

Quando fizer deploy de novas versões:

```bash
# 1. Pull do código
git pull origin main

# 2. Instalar dependências
composer install --no-dev --optimize-autoloader
npm install
npm run build

# 3. Rodar migrations (se houver)
php artisan migrate --force

# 4. Limpar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Reiniciar serviços
sudo supervisorctl restart reverb
sudo supervisorctl restart queue-worker:*

# Ou com systemd:
# sudo systemctl restart reverb
```

---

## ⚠️ Troubleshooting

### Reverb não inicia

```bash
# Verificar logs
sudo supervisorctl tail -f reverb

# Verificar permissões
sudo chown -R www-data:www-data storage/
sudo chmod -R 775 storage/

# Verificar se a porta está em uso
sudo lsof -i :8080
```

### WebSocket não conecta

1. Verificar firewall
2. Verificar configuração do Nginx
3. Verificar variáveis de ambiente no frontend (`VITE_*`)
4. Verificar se Reverb está rodando: `sudo supervisorctl status reverb`

### Alta CPU/Memória

1. Limitar workers no `queue-worker.conf`: `numprocs=2`
2. Ajustar timeout: `stopwaitsecs=600`
3. Adicionar limite de memória: `environment=MEMORY_LIMIT=512M`

---

## 📚 Recursos

- [Laravel Reverb Docs](https://laravel.com/docs/11.x/reverb)
- [Supervisor Docs](http://supervisord.org/)
- [Systemd Docs](https://systemd.io/)

---

**Última atualização:** 2025-01-06
