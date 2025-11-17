<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;


class NotaFiscalPneu extends Model
{
    use LogsActivity;

    protected $table = 'nota_fiscal_pneu';

    protected $primaryKey = 'id_nota_fiscal_pneu';

    public $timestamps = false;

    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'numero_nf',
        'serie',
        'valor_unitario',
        'data_nf',
        'valor_total',
        'id_pneu',
        'id_fornecedor',
        'id_nota_fiscal_produtos'
    ];

    protected $appends = ['valor_formatado', 'valor_total_formatado'];

    public function getValorFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_unitario, 2, ',', '.');
    }

    public function getValorTotalFormatadoAttribute()
    {
        return 'R$ ' . number_format($this->valor_total, 2, ',', '.');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }
}
