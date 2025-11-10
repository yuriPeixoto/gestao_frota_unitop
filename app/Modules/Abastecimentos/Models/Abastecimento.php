<?php

namespace App\Modules\Abastecimentos\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Abastecimento extends Model
{
    use LogsActivity;

    protected $table = 'abastecimento';
    protected $primaryKey = 'id_abastecimento';
    public $timestamps = false;
    protected $fillable = ['data_inclusao', 'data_alteracao', 'id_veiculo', 'id_fornecedor', 'numero_nota_fiscal', 'id_pessoal', 'id_filial', 'id_departamento', 'tratado', 'data_abastecimento', 'chave_nf', 'id_user', 'data_fechamento'];

    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function pessoal()
    {
        return $this->belongsTo(Pessoal::class, 'id_motorista', 'id_pessoal');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
