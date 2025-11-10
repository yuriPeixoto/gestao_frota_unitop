<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PermissaoKmManual extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'permissaokmmanual';
    protected $primaryKey = 'id_permissao_km_manual';
    public $timestamps = false;
    protected $fillable = [
        'data_inclusao',
        'data_alteracao',
        'id_permissao_km_manual',
        'id_filial',
        'id_categoria',
        'id_departamento',
        'id_veiculo'
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
    ];

    /**
     * Retorna a filial associada a esta permissão
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    /**
     * Retorna o veículo associado a esta permissão
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function veiculo()
    {
        return $this->belongsTo(Veiculo::class, 'id_veiculo', 'id_veiculo');
    }

    /**
     * Retorna o departamento associado a esta permissão
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Retorna a categoria do veículo associada a esta permissão
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function categoria()
    {
        return $this->belongsTo(CategoriaVeiculo::class, 'id_categoria', 'id_categoria');
    }

    /**
     * Scope para filtrar por filial
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $filialId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilial($query, $filialId)
    {
        return $query->where('id_filial', $filialId);
    }

    /**
     * Scope para filtrar por departamento
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $departamentoId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDepartamento($query, $departamentoId)
    {
        return $query->where('id_departamento', $departamentoId);
    }

    /**
     * Scope para filtrar por categoria
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $categoriaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategoria($query, $categoriaId)
    {
        return $query->where('id_categoria', $categoriaId);
    }

    /**
     * Scope para filtrar por veículo
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $veiculoId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVeiculo($query, $veiculoId)
    {
        return $query->where('id_veiculo', $veiculoId);
    }

    /**
     * Scope para filtrar por período de inclusão
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $dataInicial
     * @param string|null $dataFinal
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePorPeriodo($query, $dataInicial, $dataFinal = null)
    {
        $query->whereDate('data_inclusao', '>=', $dataInicial);

        if ($dataFinal) {
            $query->whereDate('data_inclusao', '<=', $dataFinal);
        }

        return $query;
    }
}
