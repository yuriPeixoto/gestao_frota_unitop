<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Fornecedor extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';

    protected $table = 'fornecedor';

    protected $primaryKey = 'id_fornecedor';

    // Desativar timestamps para evitar created_at e updated_at
    public $timestamps = false;

    protected $fillable = [
        'nome_fornecedor',
        'apelido_fornecedor',
        'cnpj_fornecedor',
        'cpf_fornecedor',
        'id_tipo_fornecedor',
        'email',
        'id_filial',
        'id_uf',
        'id_sistema',
        'is_ativo',
        'inscricao_estadual',
        'percentual_compra',
        'possui_percentual',
        'is_juridico',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'is_ativo' => 'boolean',
        'percentual_compra' => 'float',
        'possui_percentual' => 'boolean',
        'is_juridico' => 'boolean',
    ];

    /**
     * Retorna um array formatado de fornecedores ativos para uso em selects
     *
     * @param  bool  $cache  Se deve utilizar cache na consulta
     * @param  int|null  $limit  Limite de resultados a retornar (null para todos)
     * @return array Array de fornecedores no formato ['value' => id, 'label' => nome]
     */
    public static function fornecedoresAtivosParaSelect($cache = true, $limit = null)
    {
        $cacheKey = 'fornecedores_ativos_select' . ($limit ? '_limit_' . $limit : '');

        if ($cache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $query = self::ativo()
            ->select('id_fornecedor as value', DB::raw("concat(cnpj_fornecedor, ' - ', nome_fornecedor) as label"))
            ->orderBy('nome_fornecedor');

        if ($limit) {
            $query->limit($limit);
        }

        $fornecedores = $query->get()->toArray();

        if ($cache) {
            Cache::put($cacheKey, $fornecedores, now()->addMinutes(15));
        }

        return $fornecedores;
    }

    /**
     * Busca fornecedores ativos por termo de pesquisa
     *
     * @param  string  $term  Termo de pesquisa
     * @param  int  $limit  Limite de resultados
     * @return array Array de fornecedores no formato ['value' => id, 'label' => nome]
     */
    public function scopeActive($query)
    {
        return $query->where('is_ativo', true);
    }

    public static function buscarFornecedoresAtivos($term, $limit = 20)
    {
        $cacheKey = 'fornecedores_busca_' . md5($term . $limit);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $fornecedores = self::ativo()
            ->filter($term)
            ->select('id_fornecedor as value', DB::raw("concat(cnpj_fornecedor, ' - ', nome_fornecedor) as label"))
            ->where('is_ativo', true)
            ->orderBy('nome_fornecedor')
            ->limit($limit)
            ->get()
            ->toArray();

        Cache::put($cacheKey, $fornecedores, now()->addMinutes(15));

        // Registrar chave de cache para limpeza posterior
        $cacheKeys = Cache::get('fornecedor_search_keys', []);
        $cacheKeys[] = $cacheKey;
        Cache::put('fornecedor_search_keys', $cacheKeys, now()->addDay());

        return $fornecedores;
    }

    /**
     * Get the tipo fornecedor that owns the Fornecedor
     */
    public function tipoFornecedor()
    {
        return $this->belongsTo(TipoFornecedor::class, 'id_tipo_fornecedor', 'id_tipo_fornecedor');
    }

    //
    public function mecanicos()
    {
        return $this->hasMany(FornecedorXMecanico::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the filial that owns the Fornecedor
     */
    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    /**
     * Get the pedidos for the Fornecedor
     */
    public function pedidosCompra()
    {
        return $this->hasMany(PedidoCompra::class, 'id_fornecedor', 'id_fornecedor');
    }

    public function endereco(): HasMany
    {
        return $this->hasMany(Endereco::class, 'id_fornecedor_endereco', 'id_fornecedor');
    }

    /**
     * Get the solicitacoes for the Fornecedor
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class, 'id_uf');
    }

    public function solicitacaoCompra(): HasMany
    {
        return $this->hasMany(SolicitacaoCompra::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the contratos for the Fornecedor
     */
    public function contratos()
    {
        return $this->hasMany(ContratoFornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the telefones for the Fornecedor
     */
    public function telefones()
    {
        return $this->hasMany(Telefone::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get formatted cnpj
     */
    public function getCnpjFormatadoAttribute()
    {
        $cnpj = $this->cnpj_fornecedor;
        if (empty($cnpj)) {
            return '';
        }

        // Remove qualquer caractere que não seja número
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Formata o CNPJ: XX.XXX.XXX/XXXX-XX
        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /**
     * Get formatted CPF
     */
    public function getCpfFormatadoAttribute()
    {
        $cpf = $this->cpf_fornecedor;
        if (empty($cpf)) {
            return '';
        }

        // Remove qualquer caractere que não seja número
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        // Formata o CPF: XXX.XXX.XXX-XX
        return preg_replace('/^(\d{3})(\d{3})(\d{3})(\d{2})$/', '$1.$2.$3-$4', $cpf);
    }

    /**
     * Get the display name for the fornecedor
     */
    public function getNomeExibicaoAttribute()
    {
        return $this->nome_fornecedor . ($this->apelido_fornecedor ? " ({$this->apelido_fornecedor})" : '');
    }

    /**
     * Filter active suppliers
     */
    public function scopeAtivo($query)
    {
        return $query->where('is_ativo', true);
    }

    /**
     * Filter by name or CNPJ/CPF
     */
    public function scopeFilter($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('nome_fornecedor', 'like', "%{$term}%")
                ->orWhere('apelido_fornecedor', 'like', "%{$term}%")
                ->orWhere('cnpj_fornecedor', 'like', "%{$term}%")
                ->orWhere('cpf_fornecedor', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function recCombustivel(): HasMany
    {
        return $this->hasMany(RecebimentoCombustivel::class, 'id_fornecedor');
    }

    protected static function boot()
    {
        parent::boot();

        // Limpar cache quando um fornecedor for modificado
        static::saved(function ($fornecedor) {
            // Remove o cache específico deste fornecedor
            Cache::forget('fornecedor_' . $fornecedor->id_fornecedor);

            // Remove o cache de fornecedores frequentes
            Cache::forget('fornecedores_frequentes');

            // Remover cache de fornecedores ativos
            Cache::forget('fornecedores_ativos_select');

            // Remover outros caches relacionados a fornecedores ativos com limite
            $cacheKeyPattern = 'fornecedores_ativos_select_limit_';
            foreach (Cache::getStore()->many([$cacheKeyPattern . '10', $cacheKeyPattern . '20', $cacheKeyPattern . '50']) as $key => $value) {
                if ($value) {
                    Cache::forget($key);
                }
            }

            // Uma abordagem para limpar caches de busca relacionados
            // (simplificada, mas funcional para a maioria dos casos)
            $cacheKeys = Cache::get('fornecedor_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            Cache::put('fornecedor_search_keys', [], now()->addDay());
        });

        static::deleted(function ($fornecedor) {
            Cache::forget('fornecedor_' . $fornecedor->id_fornecedor);
            Cache::forget('fornecedores_frequentes');
            Cache::forget('fornecedores_ativos_select');

            // Remover outros caches relacionados a fornecedores ativos com limite
            $cacheKeyPattern = 'fornecedores_ativos_select_limit_';
            foreach (Cache::getStore()->many([$cacheKeyPattern . '10', $cacheKeyPattern . '20', $cacheKeyPattern . '50']) as $key => $value) {
                if ($value) {
                    Cache::forget($key);
                }
            }

            // Mesma lógica para limpar caches de busca
            $cacheKeys = Cache::get('fornecedor_search_keys', []);
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
        });
    }
}
