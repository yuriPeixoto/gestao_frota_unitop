<?php

namespace App\Models;

use App\Traits\LogsActivity;
use App\Traits\ToggleIsActiveOnSoftDelete;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Veiculo extends Model
{
    use LogsActivity;
    use SoftDeletes;
    use ToggleIsActiveOnSoftDelete;

    // protected $connection = 'carvalima_production';

    // necessário para o soft delete identificar o campo
    protected $activeField = 'situacao_veiculo';

    protected $table = 'veiculo';

    protected $primaryKey = 'id_veiculo';

    public $timestamps = false;

    protected $fillable = [
        'placa',
        'data_inclusao',
        'data_alteracao',
        'id_pessoal',
        'id_municipio',
        'id_filial',
        'id_departamento',
        'id_tipo_combustivel',
        'id_categoria',
        'id_fornecedor',
        'chassi',
        'renavam',
        'ano_fabricacao',
        'ano_modelo',
        'cor_veiculo',
        'marca_veiculo',
        'data_compra',
        'is_terceiro',
        'is_possui_tracao',
        'is_marcador_quilometragem',
        'is_horas',
        'km_inicial',
        'valor_venal',
        'capacidade_tanque_principal',
        'capacidade_tanque_secundario',
        'capacidade_arla',
        'id_sascar',
        'id_tipo_equipamento',
        'id_veiculo_cliente',
        'situacao_veiculo',
        'id_modelo_veiculo',
        'horas_iniciais',
        'uf',
        'id_uf',
        'base',
        'veiculo_baixado',
        'descricao_equipamento',
        'telemetria',
        'empresa',
        'possui_placa_tk',
        'capacidade_carregamento_real',
        'rota_1',
        'rota_2',
        'id_base_veiculo',
        'contrato_manutencao',
        'capacidade_carregamento_cubado',
        'capacidade_carregamento_m3',
        'id_user_alteracao',
        'id_subcategoria_veiculo',
        'numero_frota',
        'id_operacao',
        'id_tipo_veiculo',
        'id_tipo_rastreador',
        'imagem_veiculo',
        'id_user_cadastro',
        'id_cadastro_imobilizado',
        'status_cadastro_imobilizado',
        'id_fornecedor_comodato',
        'data_comodato',
    ];

    /**
     * Casts de atributos
     */
    protected $casts = [
        'data_comodato' => 'date',
        'data_compra' => 'date',
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'is_terceiro' => 'boolean',
        'is_possui_tracao' => 'boolean',
        'is_marcador_quilometragem' => 'boolean',
        'is_horas' => 'boolean',
        'telemetria' => 'boolean',
        'possui_placa_tk' => 'boolean',
        'contrato_manutencao' => 'boolean',
        'veiculo_baixado' => 'boolean',
        'situacao_veiculo' => 'boolean',
    ];

    public function categoriaVeiculo(): BelongsTo
    {
        return $this->belongsTo(TipoCategoria::class, 'id_categoria');
    }

    public function combustivelVeiculo(): BelongsTo
    {
        return $this->belongsTo(TipoCombustivel::class, 'id_tipo_combustivel');
    }

    public function departamentoVeiculo(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function baseVeiculo(): BelongsTo
    {
        return $this->belongsTo(BaseVeiculo::class, 'id_base_veiculo');
    }

    public function ufVeiculo(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_uf');
    }

    public function municipioVeiculo(): BelongsTo
    {
        return $this->belongsTo(Municipio::class, 'id_municipio');
    }

    public function status()
    {
        return $this->belongsTo(StatusCadastroImobilizado::class, 'status_cadastro_imobilizado', 'id');
    }

    public function modeloVeiculo(): BelongsTo
    {
        return $this->belongsTo(ModeloVeiculo::class, 'id_modelo_veiculo');
    }

    public function filialVeiculo(): BelongsTo
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    public function transferenciaVeiculo(): HasMany
    {
        return $this->hasMany(TransferenciaVeiculo::class, 'id_veiculo');
    }

    public function multa(): HasMany
    {
        return $this->hasMany(Multa::class, 'id_veiculo');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial');
    }

    public function condutor()
    {
        return $this->belongsTo(Pessoal::class, 'id_pessoal');
    }

    public function tipoEquipamento()
    {
        return $this->belongsTo(TipoEquipamento::class, 'id_tipo_equipamento');
    }

    public function licenciamento(): HasMany
    {
        return $this->hasMany(LicenciamentoVeiculo::class, 'id_licenciamento');
    }

    public function ipva(): HasMany
    {
        return $this->hasMany(IpvaVeiculo::class, 'id_ipva_veiculo');
    }

    public function seguroobrigatorio(): HasMany
    {
        return $this->hasMany(SeguroObrigatorio::class, 'id_veiculo');
    }

    // public function afericaobomba(): HasMany
    // {
    //     return $this->hasMany(AfericaoBomba::class, 'id_veiculo_unitop');
    // }

    public function certificadoveiculos(): HasMany
    {
        return $this->hasMany(CertificadoVeiculos::class, 'id_veiculo');
    }

    public function testeFumaca(): HasMany
    {
        return $this->hasMany(TesteFumaca::class, 'id_veiculo');
    }

    public function ordemServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class, 'id_veiculo');
    }

    public function ordemServicoAuxiliar(): HasMany
    {
        return $this->hasMany(GerarOSVeiculosAuxiliar::class, 'id_veiculo');
    }

    // public function ipvaVeiculo(): HasMany
    // {
    //     return $this->hasMany(IpvaVeiculo::class, 'id_veiculo');
    // }

    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um pessoal for modificado
        static::saved(function ($veiculo) {
            Cache::forget('veiculos_' . $veiculo->id_veiculo);

            // Limpar o cache dos pessoales frequentes
            Cache::forget('veiculos_frequentes');
        });

        static::deleted(function ($veiculo) {
            Cache::forget('veiculos_' . $veiculo->id_veiculo);
            Cache::forget('veiculos_frequentes');
        });
    }

    public function scopeAtivos($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInativos($query)
    {
        return $query->onlyTrashed();
    }

    public function scopeTodos($query)
    {
        return $query->withTrashed();
    }

    public function calibragens()
    {
        return $this->hasMany(CalibragemPneus::class, 'id_veiculo');
    }

    public function restricoes()
    {
        return $this->hasMany(RestricoesBloqueios::class, 'placa', 'placa');
    }
}
