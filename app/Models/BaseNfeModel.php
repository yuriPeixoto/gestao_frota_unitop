<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseNfeModel extends Model
{
    protected $connection = 'pgsql';

    public $timestamps   = true;

    public const CREATED_AT = 'data_inclusao';
    public const UPDATED_AT = 'data_alteracao';
}
