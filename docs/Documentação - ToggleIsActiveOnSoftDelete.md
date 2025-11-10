# 📘 Documentação: Trait `ToggleIsActiveOnSoftDelete`

## 📌 Objetivo

A trait `ToggleIsActiveOnSoftDelete` permite **alternar automaticamente o valor de um campo de status** (como `is_ativo`, `situacao`, `situacao_veiculo`) ao realizar um soft delete ou restaurar um registro.

---

## ⚙️ Como funciona

Ela escuta os eventos do Eloquent `SoftDeletes`:

-   `deleting`: altera o status quando o registro é "removido"
-   `restoring`: altera o status novamente ao restaurar

---

## 🧱 Código da Trait

```php
<?php

namespace App\Traits;

trait ToggleIsActiveOnSoftDelete
{
    public static function bootToggleIsActiveOnSoftDelete()
    {
        static::deleting(function ($model) {
            if (!$model->forceDeleting) {
                $model->toggleStatusField();
                $model->saveQuietly();
            }
        });

        static::restoring(function ($model) {
            $model->toggleStatusField();
            $model->saveQuietly();
        });
    }

    protected function toggleStatusField()
    {
        $field = $this->getActiveField();
        $map = $this->getStatusToggleMap();

        if (array_key_exists($this->$field, $map)) {
            $this->$field = $map[$this->$field];
        } else {
            $this->$field = !$this->$field; // Boolean toggle por padrão
        }
    }

    protected function getActiveField()
    {
        return property_exists($this, 'activeField') ? $this->activeField : 'is_ativo';
    }

    protected function getStatusToggleMap()
    {
        return property_exists($this, 'statusToggleMap') ? $this->statusToggleMap : [];
    }
}
```

---

## 🧩 Como usar em uma model

### ✅ Exemplo com campo booleano

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ToggleIsActiveOnSoftDelete;

class Produto extends Model
{
    use SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $activeField = 'is_ativo';
}
```

### ✅ Exemplo com campo string

```php
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ToggleIsActiveOnSoftDelete;

class Veiculo extends Model
{
    use SoftDeletes, ToggleIsActiveOnSoftDelete;

    protected $activeField = 'situacao_veiculo';

    protected $statusToggleMap = [
        'disponível' => 'indisponível',
        'indisponível' => 'disponível',
    ];
}
```

---

## 🧱 Como criar a migration para o `deleted_at`

### 1. Criar a migration

```bash
php artisan make:migration add_deleted_at_to_veiculo_table
```

### 2. Editar a migration

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('veiculo', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('veiculo', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
```

### 3. Rodar a migration

```bash
php artisan migrate --path=database/migrations/2025_05_08_XXXXXX_add_deleted_at_to_veiculo_table.php
```

> Substitua `XXXXXX` pelo timestamp correto ou simplesmente rode `php artisan migrate` para aplicar todas as migrations pendentes.

---

## ❗ Observações importantes

-   O uso da trait exige que o model implemente `SoftDeletes`.
-   A tabela **precisa conter** a coluna `deleted_at`.
-   Caso o campo de status seja string, defina a propriedade `statusToggleMap` na model.

---

## ✅ Benefícios

-   Código reutilizável
-   Funciona com qualquer campo de status (booleano ou string)
-   Integra-se perfeitamente com SoftDeletes do Laravel

---
