<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class FotosDocumentosSinistros extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'fotosdocumentossinistros';
    protected $primaryKey = 'id_fotos_documentos';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'documento', 'id_sinistro', 'nome_documento'];

    public function sinistro()
    {
        return $this->belongsTo(Sinistro::class, 'id_sinistro');
    }

    /*public function getAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }*/
}
