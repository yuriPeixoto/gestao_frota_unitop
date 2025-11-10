<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasPermissions;


class Role extends Model
{
    use LogsActivity;
    // use SoftDeletes;
    use HasPermissions;

    protected $fillable = ['name', 'description', 'guard_name', 'is_ativo'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'role_branches')
            ->withTimestamps();
    }
}
