<?php

namespace App\Modules\Configuracoes\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasPermissions;

class Branch extends Model
{
    use SoftDeletes;
    use LogsActivity;
    use HasPermissions;

    protected $fillable = ['name', 'is_headquarter'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_branches')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_branches')
            ->withTimestamps();
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
