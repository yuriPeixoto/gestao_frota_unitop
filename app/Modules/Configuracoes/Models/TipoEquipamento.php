<?php

namespace App\Modules\Configuracoes\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoEquipamento extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';
    protected $table = 'tipoequipamento';
    protected $primaryKey = 'id_tipo_equipamento';
    public $timestamps = false;
    protected $fillable = ['descricao_tipo', 'data_inclusao', 'data_alteracao', 'numero_eixos'];

    public function getDescricaoTipoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function metaTipoEquipamento()
    {
        return $this->hasMany(MetaPorTipoEquipamento::class, 'id_tipo_equipamento');
    }

    public function veiculos(): HasMany
    {
        return $this->hasMany(Veiculo::class, 'id_tipo_equipamento');
    }

    /**
     * Determina o tipo de checklist baseado no tipo de equipamento
     *
     * @return int ID do tipo de checklist
     */
    public function getChecklistTypeId(): int
    {
        $descricaoTipo = strtoupper(trim($this->descricao_tipo));

        // Concatena com o número de eixos se existir
        if (!empty($this->numero_eixos)) {
            $descricaoCompleta = $descricaoTipo . ' EIXO:' . $this->numero_eixos;
        } else {
            $descricaoCompleta = $descricaoTipo;
        }

        // Mapeamento dos tipos de equipamento para tipos de checklist
        $mappingEquipamentos = [
            // id: 1 - TOCO/TRUCK
            'TOCO EIXO:2' => 1,
            'TRUCK EIXO:3' => 1,
            'CAVALO TOCO EIXO:2' => 1,
            'CAVALO EIXO:2' => 1,
            'CAVALO EIXO:3' => 1,
            'EMPILHADEIRA' => 1,
            'EMPILHADEIRA 2,5T E 4T' => 1,
            'GUINDAUTO' => 1,
            'ÔNIBUS TRUCADO BI-DIRECIONAL' => 1,
            'CAMINHÃO TRUCADO BI-DIRECIONAL EIXO:4' => 1,
            '3/4 EIXO:2' => 1,
            'FURGÃO' => 1,
            'PRANCHA' => 1,
            'HR EIXO:2' => 1,
            'CAVALO TOCO RODOTREM - MERCEDES EIXO:2' => 1,
            'CAVALO  TOCO RODOTREM - SCANIA EIXO:2' => 1,
            'VAN EIXO:2' => 1,
            'DIVERSO/EQUIPAMENTO EIXO:0' => 1,

            // id: 5 - CARRETA BAÚ
            'SEMIRREBOQUE EIXO:3' => 5,
            'CARRETA 12T' => 5,
            'CARRETA 7T' => 5,
            'BAÚ EIXO:3' => 5,
            'SEMIRREBOQUE EIXO:2' => 5,
            'BAÚ EIXO:2' => 5,
            'BAÚ (RODOTREM) EIXO:3' => 5,
            'BAÚ (RODOTREM) EIXO:2' => 5,
            'BAÚ DESLIZANTE EIXO:3' => 5,
            'BAÚ MULTIUSO EIXO:3' => 5,
            'PORTA CONTAINER EIXO:3' => 5,
            'REBOQUE' => 5,

            // id: 6 - VEÍCULO LEVE
            'LEVE EIXO:2' => 6,
            'UTILITÁRIO EIXO:2' => 6,
            'MOTOCICLETA' => 6,

            // id: 3 - GRANELEIRO
            'DOLLY EIXO:2' => 3,
            'CAÇAMBA EIXO:2' => 3,
            'GRANELEIRA EIXO:3' => 3,
            'GRANELEIRA EIXO:2' => 3,
            'CAÇAMBA BASCULANTE EIXO:3' => 3,

            // id: 4 - SIDER
            'SIDER EIXO:3' => 4,
            'SIDER EIXO:4' => 4,

            // id: 2 - THERMOKING
            'THERMO KING EIXO:2' => 2,
            'SEMIRREBOQUE FRIGORÍFICO EIXO:3' => 2,
        ];

        // Retorna o tipo de checklist mapeado ou TOCO/TRUCK como padrão
        return $mappingEquipamentos[$descricaoCompleta] ?? 1;
    }

    /**
     * Retorna a descrição completa do tipo de equipamento (para debug)
     *
     * @return string Descrição completa concatenada
     */
    public function getDescricaoCompleta(): string
    {
        $descricaoTipo = strtoupper(trim($this->descricao_tipo));

        // Concatena com o número de eixos se existir
        if (!empty($this->numero_eixos)) {
            return $descricaoTipo . ' EIXO:' . $this->numero_eixos;
        } else {
            return $descricaoTipo;
        }
    }
}
