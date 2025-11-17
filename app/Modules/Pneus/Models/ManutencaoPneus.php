<?php

namespace App\Modules\Pneus\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManutencaoPneus extends Model
{
    use LogsActivity;

    protected $table = 'manutencao_pneus';

    protected $primaryKey = 'id_manutencao_pneu';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_filial',
        'id_fornecedor',
        'nf_envio',
        'chave_nf_envio',
        'id_borracheiro',
        'data_assumir',
        'is_borracharia',
        'situacao_envio',
        'usuario_aprov',
        'usuario_solic',
        'doc_nf',
        'doc_extrato',
        'valor_nf'
    ];

    protected $casts = [
        'data_inclusao'  => 'datetime',
        'data_alteracao' => 'datetime'
    ];


    public function manutencaoPneusItens()
    {
        return $this->hasMany(ManutencaoPneusItens::class, 'id_manutencao_pneu', 'id_manutencao_pneu');
    }

    public function situacaoEntrada()
    {
        return $this->hasOne(ManutencaoPneusEntrada::class, 'id_manutencao', 'id_manutencao_pneu');
    }

    public function filialPneu(): BelongsTo
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function usuarioAprov(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'usuario_aprov', 'id');
    }

    public function usuarioSolic(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'usuario_solic', 'id');
    }

    public function borracheiro(): BelongsTo
    {
        return $this->belongsTo(Pessoal::class, 'is_borracharia', 'id_pessoal');
    }
    public function filial(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function getHistoricoPneus()
    {
        $historico = [];

        // Registro da solicitação
        if ($this->usuario_solic) {
            $historico[] = [
                'usuario'   => $this->usuarioSolic?->name, // pode ser ID, depois resolve o nome
                'acao'      => 'iniciou a solicitação de envio de pneus',
                'data'      => $this->data_inclusao,
                'observacao' => null,
                'icon'      => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'color'     => 'bg-blue-500',
            ];
        }

        if ($this->usuario_aprov) {
            $historico[] = [
                'usuario' => $this->usuarioAprov?->name ?? 'Usuário desconhecido',
                'acao'      => 'aprovou a saída dos pneus',
                'data'      => $this->data_alteracao ?? now(),
                'observacao' => null,
                'icon'       => 'M5 13l4 4L19 7', // ícone check
                'color'      => 'bg-green-500',
            ];
        }
        return $historico;
    }

    public function historicos()
    {
        return $this->hasMany(ManutencaoHistorico::class, 'id_manutencao_pneus', 'id_manutencao_pneu');
    }
}
