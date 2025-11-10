<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Departamento extends Model
{
    use LogsActivity;
    use HasPermissions;

    protected $connection = 'pgsql';
    protected $table = 'departamento';
    protected $primaryKey = 'id_departamento';
    public $timestamps = false;
    protected $fillable = ['descricao_departamento', 'data_inclusao', 'data_alteracao', 'ativo', 'sigla', 'id_filial'];

    public function getDescricaoDepartamentoAttribute($value)
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public function veiculo()
    {
        return  $this->hasMany(Veiculo::class, 'id_departamento');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class, 'id_filial', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'departamento_id', 'id_departamento');
    }

    public function pessoal()
    {
        return $this->hasMany(Pessoal::class, 'id_departamento', 'id_departamento');
    }

    /*
    |--------------------------------------------------------------------------
    | Relações do Módulo de Compras
    |--------------------------------------------------------------------------
    */

    /**
     * Solicitações de compra deste departamento
     */
    public function solicitacoesCompra(): HasMany
    {
        return $this->hasMany(SolicitacaoCompra::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Obtém os usuários aprovadores do departamento
     * Assume-se que existe uma tabela de relacionamento entre usuários e departamentos
     * onde a coluna is_aprovador indica se o usuário é aprovador daquele departamento
     */
    public function aprovadores()
    {
        return $this->belongsToMany(User::class, 'usuario_departamento', 'id_departamento', 'id_user')
            ->where('is_aprovador', true)
            ->withTimestamps();
    }

    /**
     * Verifica se o departamento possui orçamento disponível
     *
     * @param float $valor Valor a ser verificado
     * @param int|null $ano Ano de referência, se null usa o ano atual
     * @param int|null $mes Mês de referência, se null considera o orçamento anual
     * @return bool
     */
    public function possuiOrcamentoDisponivel(float $valor, ?int $ano = null, ?int $mes = null): bool
    {
        $ano = $ano ?? date('Y');

        // Implementação básica, deve ser adaptada conforme a estrutura de orçamento da aplicação
        // Assumindo que existe um model de Orçamento relacionado ao departamento

        // Busca o orçamento do departamento para o ano/mês
        $orcamento = $this->orcamentos()
            ->where('ano', $ano)
            ->when($mes, function ($query) use ($mes) {
                return $query->where('mes', $mes);
            })
            ->first();

        if (!$orcamento) {
            return false;
        }

        // Calculando o valor já comprometido
        $valorComprometido = $this->calcularValorComprometido($ano, $mes);

        // Verifica se tem saldo disponível
        return ($orcamento->valor_orcamento - $valorComprometido) >= $valor;
    }

    /**
     * Calcula o valor comprometido do orçamento
     *
     * @param int $ano Ano de referência
     * @param int|null $mes Mês de referência, se null considera o ano inteiro
     * @return float
     */
    protected function calcularValorComprometido(int $ano, ?int $mes = null): float
    {
        // Lógica para calcular o valor já comprometido em solicitações e pedidos
        // Esta é uma implementação básica e deve ser adaptada conforme necessidade

        // Soma os valores de solicitações aprovadas
        $valorSolicitacoes = $this->solicitacoesCompra()
            ->whereYear('data_solicitacao', $ano)
            ->when($mes, function ($query) use ($mes) {
                return $query->whereMonth('data_solicitacao', $mes);
            })
            ->where('status', 'aprovada')
            ->sum('valor_total');

        // Soma os valores de pedidos de compra confirmados
        $valorPedidos = PedidoCompra::where('id_departamento', $this->id_departamento)
            ->whereYear('data_pedido', $ano)
            ->when($mes, function ($query) use ($mes) {
                return $query->whereMonth('data_pedido', $mes);
            })
            ->whereIn('status', ['aprovado', 'enviado', 'confirmado'])
            ->sum('valor_total');

        return $valorSolicitacoes + $valorPedidos;
    }

    /**
     * Relação com orçamentos do departamento
     */
    public function orcamentos()
    {
        // Assumindo que existe um model Orcamento com a relação ao departamento
        return $this->hasMany(Orcamento::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Escopo para departamentos ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Obtém as solicitações de compra pendentes de aprovação deste departamento
     */
    public function solicitacoesPendentes()
    {
        return $this->solicitacoesCompra()
            ->where('status', 'aguardando_aprovacao')
            ->orderBy('data_solicitacao');
    }

    public function OSAuxiliarDepto(): HasMany
    {
        return $this->hasMany(GerarOrdemServicoAuxiliar::class, 'id_departamento');
    }
}
