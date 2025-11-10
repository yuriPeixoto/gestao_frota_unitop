# 🏗️ Build do Frontend no Servidor (Reverb + Echo)

## ❌ Problema Atual

O Echo não está disponível no navegador porque os **assets não foram compilados** no servidor.

**Erro no console:**
```javascript
typeof Echo !== 'undefined'  // retorna false ❌
```

---

## ✅ Solução: Compilar Assets no Servidor

Execute estes comandos **no servidor staging**:

### Opção 1: Build Completo (Recomendado)

```bash
cd /var/www/html/gestao_frota

# 1. Instalar dependências do Node.js (se ainda não instalou)
npm install

# 2. Build de produção
npm run build

# 3. Limpar caches do Laravel
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan config:cache

# 4. Verificar permissões
chown -R www-data:www-data public/build
chmod -R 755 public/build

# 5. Reiniciar Reverb
supervisorctl restart reverb
```

---

### Opção 2: Build Local + Upload (Se o servidor não tiver Node.js)

Se o servidor **não tiver Node.js instalado**, você pode compilar localmente e fazer upload:

**No Windows (Local):**

```powershell
# No seu projeto local
cd C:\projects\gestao_frota

# Criar build de produção
npm run build

# Os arquivos serão gerados em: public/build/
```

**Depois, faça upload da pasta `public/build/` para o servidor:**

Usando SCP/SFTP, copie toda a pasta:
- **De:** `C:\projects\gestao_frota\public\build\`
- **Para:** `/var/www/html/gestao_frota/public/build/`

**No Servidor (após upload):**

```bash
cd /var/www/html/gestao_frota

# Ajustar permissões
chown -R www-data:www-data public/build
chmod -R 755 public/build

# Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🔍 Verificar se Funcionou

### 1. Verificar arquivos compilados no servidor

```bash
ls -lah /var/www/html/gestao_frota/public/build/assets/
```

Deve mostrar arquivos `.js` e `.css` com nomes tipo:
- `app-a1b2c3d4.js`
- `notifications-e5f6g7h8.js`
- etc.

### 2. Testar no navegador

Recarregue a página (Ctrl+Shift+R para hard refresh) e teste no console:

```javascript
// Deve retornar true
typeof Echo !== 'undefined'

// Deve mostrar o objeto Echo
console.log(Echo);

// Deve mostrar a conexão
console.log(Echo.connector.pusher.connection.state);
```

Se aparecer `"connected"`, está funcionando! ✅

---

## 📦 Verificar Dependências (package.json)

Certifique-se de que o `package.json` tem estas dependências:

```json
{
  "dependencies": {
    "laravel-echo": "^1.16.1",
    "pusher-js": "^8.4.0-rc2"
  }
}
```

Se não tiver, adicione:

```bash
npm install laravel-echo pusher-js --save
npm run build
```

---

## 🔧 Instalar Node.js no Servidor (se necessário)

Se o servidor **não tiver Node.js**:

```bash
# Atualizar repositórios
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -

# Instalar Node.js
apt install -y nodejs

# Verificar instalação
node -v
npm -v
```

---

## 🐛 Troubleshooting

### ❌ Erro: "npm: command not found"

**Solução:** Instale o Node.js no servidor (veja acima)

### ❌ Erro: "EACCES: permission denied"

**Solução:** Execute com sudo ou ajuste permissões

```bash
sudo chown -R $USER:$USER /var/www/html/gestao_frota/node_modules
sudo chown -R $USER:$USER /var/www/html/gestao_frota/public/build
```

### ❌ Erro: "Vite manifest not found"

**Solução:** O build não foi concluído. Execute `npm run build` novamente.

### ❌ Echo ainda undefined após build

**Causa:** Cache do navegador

**Solução:**
1. Limpar cache do navegador (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+Shift+R)
3. Ou abrir em aba anônima

---

## 📊 Verificação Final

Execute este script no servidor para verificar tudo:

```bash
#!/bin/bash

echo "=== VERIFICAÇÃO COMPLETA DO FRONTEND ==="
echo ""

echo "1. Node.js instalado?"
node -v 2>/dev/null || echo "❌ Node.js NÃO instalado"
npm -v 2>/dev/null || echo "❌ NPM NÃO instalado"
echo ""

echo "2. Arquivos build existem?"
if [ -d "/var/www/html/gestao_frota/public/build" ]; then
    echo "✅ Pasta public/build existe"
    echo "Quantidade de arquivos:"
    ls -1 /var/www/html/gestao_frota/public/build/assets/ | wc -l
else
    echo "❌ Pasta public/build NÃO existe"
fi
echo ""

echo "3. Manifest do Vite existe?"
if [ -f "/var/www/html/gestao_frota/public/build/manifest.json" ]; then
    echo "✅ manifest.json existe"
else
    echo "❌ manifest.json NÃO existe - Execute npm run build!"
fi
echo ""

echo "4. Permissões da pasta build:"
ls -ld /var/www/html/gestao_frota/public/build/
echo ""

echo "5. .env configurado?"
grep "^VITE_REVERB" /var/www/html/gestao_frota/.env
echo ""

echo "6. Reverb rodando?"
supervisorctl status reverb
echo ""
```

---

## ✅ Checklist

Após executar os passos acima, verifique:

- [ ] Node.js instalado no servidor (ou build feito localmente)
- [ ] `npm install` executado
- [ ] `npm run build` executado com sucesso
- [ ] Pasta `public/build/` existe e tem arquivos `.js`
- [ ] Arquivo `public/build/manifest.json` existe
- [ ] Permissões corretas (www-data)
- [ ] Caches do Laravel limpos
- [ ] Reverb reiniciado
- [ ] Navegador com cache limpo (hard refresh)
- [ ] `typeof Echo !== 'undefined'` retorna `true` ✅
- [ ] Notificações funcionando em tempo real ✅

---

## 🎯 Ordem de Execução Recomendada

1. **Corrigir .env** (REVERB_HOST sem https://)
2. **Build do frontend** (npm run build)
3. **Limpar caches** (config:clear, cache:clear)
4. **Reiniciar Reverb** (supervisorctl restart reverb)
5. **Testar no navegador** (hard refresh + console)

---

**Boa sorte! 🚀**