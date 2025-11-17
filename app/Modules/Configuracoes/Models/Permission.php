<?php

namespace App\Modules\Configuracoes\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    public function getGroupAttribute()
    {
        $parts = explode('_', $this->name);
        return count($parts) > 1 ? implode('_', array_slice($parts, 1)) : $this->name;
    }

    public function getActionAttribute()
    {
        $parts = explode('_', $this->name);
        return $parts[0] ?? '';
    }
}
