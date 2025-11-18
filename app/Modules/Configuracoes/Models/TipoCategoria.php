<?php

namespace App\Modules\Configuracoes\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TipoCategoria extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'categoria_veiculo';
    protected $primaryKey = 'id_categoria';
    public $timestamps = false;
    protected $fillable = ['descricao_categoria', 'data_inclusao', 'data_alteracao', 'ativo'];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function veiculo()
    {
        return $this->hasMany(Veiculo::class, 'id_categoria');
    }

    public function contarVeiculos()
    {
        return DB::connection('pgsql')->table('veiculo as v')
            ->join('categoria_veiculo as cv', 'cv.id_categoria', '=', 'v.id_categoria')
            ->where('cv.id_categoria', $this->id_categoria)
            ->selectRaw('COUNT(*) as quantidade')
            ->groupBy('cv.id_categoria', 'cv.descricao_categoria')
            ->first()
            ->quantidade ?? 0;
    }

    /**
     * Escopo para filtrar apenas categorias ativas
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeAtivas(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->where('ativo', true)
                ->orWhereNull('ativo'); // Considerar também registros onde 'ativo' é NULL como ativos
        });
    }

    /**
     * Retorna as categorias ativas formatadas para uso em selects
     *
     * @return array
     */
    public static function getActiveForSelect(): array
    {
        return self::ativas()
            ->orderBy('descricao_categoria')
            ->get()
            ->map(function ($categoria) {
                return [
                    'value' => $categoria->id_categoria,
                    'label' => $categoria->descricao_categoria
                ];
            })->toArray();
    }

    /**
     * Retorna as categorias ativas com paginação (opcional) para selects com muitos registros
     *
     * @param int|null $limit
     * @param string|null $search
     * @return array
     */
    public static function getActiveForSelectPaginated(?int $limit = 100, ?string $search = null): array
    {
        $query = self::ativas()->orderBy('descricao_categoria');

        if ($search) {
            $query->where('descricao_categoria', 'ILIKE', "%{$search}%");
        }

        return $query->limit($limit)
            ->get()
            ->map(function ($categoria) {
                return [
                    'value' => $categoria->id_categoria,
                    'label' => $categoria->descricao_categoria
                ];
            })->toArray();
    }
}
