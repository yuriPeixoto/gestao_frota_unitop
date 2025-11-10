<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $fillable = [
        'street',
        'number',
        'complement',
        'district',
        'city',
        'state',
        'zip_code',
    ];

    public function branches()
    {
        return $this->hasOne(Branch::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
