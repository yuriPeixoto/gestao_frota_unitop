# 🔧 SOLUÇÃO: Aumentar max_input_vars do PHP

## Problema Identificado
O PHP está limitado a **1000 campos** em formulários (`max_input_vars = 1000`).
Como o formulário de permissões tem **1728 checkboxes**, o PHP descarta tudo após 1000.

## Solução

### 1. Abrir o arquivo php.ini
**Caminho:** `C:\php\php.ini`

### 2. Procurar por "max_input_vars"
Use Ctrl+F e busque por: `max_input_vars`

### 3. Alterar o valor
Se estiver comentado (com ;), remova o comentário e altere:

**ANTES:**
```ini
;max_input_vars = 1000
```

**DEPOIS:**
```ini
max_input_vars = 3000
```

**OU se já estiver descomentado:**

**ANTES:**
```ini
max_input_vars = 1000
```

**DEPOIS:**
```ini
max_input_vars = 3000
```

### 4. Reiniciar o servidor web
Se estiver usando:

#### Laragon:
- Clique em "Stop All"
- Clique em "Start All"

#### XAMPP:
- Pare o Apache
- Inicie o Apache novamente

#### Servidor PHP Artisan:
```powershell
# Parar o servidor (Ctrl+C no terminal onde está rodando)
# Depois iniciar novamente:
php artisan serve
```

### 5. Verificar se funcionou
Execute no terminal:
```powershell
php -i | Select-String "max_input_vars"
```

Deve retornar:
```
max_input_vars => 3000 => 3000
```

## Valores Recomendados

Para este sistema, recomendo:
- **max_input_vars = 3000** (para suportar até 3000 campos)
- **post_max_size = 50M** (se ainda não estiver)
- **upload_max_filesize = 50M** (se ainda não estiver)

## Após Ajustar

1. Teste novamente no sistema
2. Marque todas as 1728 permissões
3. Salve
4. Recarregue e verifique se todas foram salvas

---

**Executado em:** 15/10/2025
**Problema:** max_input_vars limitado a 1000
**Solução:** Aumentar para 3000
