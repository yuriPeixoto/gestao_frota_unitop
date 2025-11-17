<?php

namespace App\Modules\Manutencao\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicoSolicitacaoCompra extends Model
{
   protected $connection = 'pgsql';
   protected $table = 'servicossolicitacoescompras';
   protected $primaryKey = 'id';
   public $timestamps = false;

   protected $fillable = [
      'id_solicitacao_compra',
      'id_servico',
      'quantidade',
      'valor_unitario',
      'valor_total',
      'observacao',
      'status'
   ];

   protected $casts = [
      'quantidade' => 'decimal:2',
      'valor_unitario' => 'decimal:2',
      'valor_total' => 'decimal:2',
   ];

   /**
    * Relacionamento com SolicitacaoCompra
    */
   public function solicitacaoCompra(): BelongsTo
   {
      return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacao_compra', 'id_solicitacoes_compras');
   }

   /**
    * Relacionamento com Servico (assumindo que existe um model Servico)
    */
   public function servico(): BelongsTo
   {
      return $this->belongsTo(Servico::class, 'id_servico', 'id_servicos');
   }
}
