<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbastecimentoManual extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'v_abastecimento_manual';
    protected $primaryKey = 'id_abastecimento';
    public $timestamps    = false;

    protected $fillable = [
        'data_inclusao',
        'id_veiculo',
        'id_fornecedor',
        'numero_nota_fiscal',
        'id_filial',
        'id_departamento',
        'data_abastecimento'
    ];

    protected $casts = [
        'data_inclusao'      => 'datetime',
        'data_abastecimento' => 'datetime'
    ];

    // Relacionamentos
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo');
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function itens()
    {
        return $this->hasMany(AbastecimentoItem::class, 'id_abastecimento', 'id_abastecimento')
            ->orderBy('data_abastecimento', 'desc');
    }

    /**
     * Verificar se usuário tem autorização especial para ações específicas
     * Método centralizado para substituir IDs hardcoded
     */
    public static function usuarioTemAutorizacaoEspecial($userId, $acao = 'geral')
    {
        // IDs de usuários com autorização especial - centralizado para fácil manutenção
        $usuariosEspeciais = [
            'excluir_abastecimento' => [3, 4, 17, 25], // Antonio(3), Marcos(4), Marcelo(17) + ID 25
            'geral' => [3, 4, 17, 25],
            // Adicionar outras ações conforme necessário
        ];

        $idsAutorizados = $usuariosEspeciais[$acao] ?? $usuariosEspeciais['geral'];

        return in_array($userId, $idsAutorizados);
    }
}
