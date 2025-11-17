<?php

namespace App\Modules\Compras\Models;

use App\Traits\LogsActivity;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SolicitacaoCompra extends Model
{
    use LogsActivity;

    protected $connection = 'pgsql';

    protected $table = 'solicitacoescompras';

    protected $primaryKey = 'id_solicitacoes_compras';

    public $timestamps = false;

    // Constantes de status da solicitação
    const STATUS_SEM_STATUS = 'INCLUIDA';

    const STATUS_AGUARDANDO_APROVACAO_GESTOR = 'AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO';

    const STATUS_AGUARDANDO_INICIO_COMPRAS = 'AGUARDANDO INÍCIO DE COMPRAS';

    const STATUS_INICIADA = 'INICIADA';

    const STATUS_AGUARDANDO_VALIDACAO_SOLICITANTE = 'AGUARDANDO VALIDAÇÃO DO SOLICITANTE';

    const STATUS_COTACOES_RECUSADAS = 'COTAÇÕES RECUSADAS PELO GESTOR';

    const STATUS_SOLICITACAO_VALIDADA = 'SOLICITAÇÃO VALIDADA PELO SOLICITANTE';

    const STATUS_AGUARDANDO_APROVACAO = 'AGUARDANDO APROVAÇÃO'; // Status específico para aprovação final de serviços

    const STATUS_FINALIZADO = 'FINALIZADO';

    const STATUS_CANCELADA = 'CANCELADA';

    const STATUS_REPROVADO_GESTOR = 'REPROVADO GESTOR DEPARTAMENTO';


    // Constantes de tipo de aplicação
    const APLICACAO_DIRETA = 1;

    protected $fillable = [
        'id_solicitacao_pecas',
        'id_departamento',
        'prioridade',
        'id_comprador',
        'id_filial',
        'id_ordem_servico',
        'situacao_compra',
        'observacao',
        'id_solicitante',
        'observacaocomprador',
        'aprovado_reprovado',
        'observacao_aprovador',
        'id_grupo_despesas',
        'data_finalizada',
        'justificativa_edit_or_delete',
        'tipo_solicitacao',
        'data_aprovacao',
        'is_cancelada',
        'filial_faturamento',
        'filial_entrega',
        'id_aprovador',
        'id_fornecedor',
        'is_contrato',
        'is_aplicacao_direta',
        'id_solicitacao_original',
        'id_user_adiado',
        'data_adiado',
        'is_adiado',
        'justificativa_adiado',
        'is_unificado',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'data_alteracao' => 'datetime',
        'data_finalizada' => 'datetime',
        'data_aprovacao' => 'datetime',
        'data_adiado' => 'date',
        'aprovado_reprovado' => 'boolean',
        'is_cancelada' => 'boolean',
        'is_contrato' => 'boolean',
        'is_aplicacao_direta' => 'integer',
        'is_adiado' => 'boolean',
    ];

    protected $dates = [
        'data_inclusao',
        'data_alteracao',
        'data_finalizada',
        'data_aprovacao',
        'data_adiado',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            $model->data_inclusao = now();
        });

        static::updating(function ($model) {
            $model->data_alteracao = now();
        });
    }

    /**
     * Get the solicitante (usuário que fez a solicitação)
     */
    public function solicitante()
    {
        return $this->belongsTo(User::class, 'id_solicitante');
    }

    /**
     * Get the aprovador (usuário que aprovou/rejeitou a solicitação)
     */
    public function aprovador()
    {
        return $this->belongsTo(User::class, 'id_aprovador');
    }

    /**
     * Get the comprador assigned to the solicitação
     */
    public function comprador()
    {
        return $this->belongsTo(User::class, 'id_comprador');
    }

    /**
     * Get the usuário que adiou a solicitação
     */
    public function userAdiado()
    {
        return $this->belongsTo(User::class, 'id_user_adiado');
    }

    /**
     * Get the departamento
     */
    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'id_departamento', 'id_departamento');
    }

    /**
     * Get the filial da solicitação
     */
    public function filial()
    {
        return $this->belongsTo(VFilial::class, 'id_filial', 'id');
    }

    /**
     * Get the filial de faturamento
     */
    public function filialFaturamento()
    {
        return $this->belongsTo(VFilial::class, 'filial_faturamento', 'id');
    }

    /**
     * Get the filial de entrega
     */
    public function filialEntrega()
    {
        return $this->belongsTo(VFilial::class, 'filial_entrega', 'id');
    }

    /**
     * Get the ordem de serviço relacionada
     */
    public function ordemServico()
    {
        return $this->belongsTo(OrdemServico::class, 'id_ordem_servico', 'id_ordem_servico');
    }

    /**
     * Get the fornecedor
     */
    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class, 'id_fornecedor', 'id_fornecedor');
    }

    /**
     * Get the grupo de despesas
     */
    public function grupoDespesa()
    {
        return $this->belongsTo(GrupoDespesa::class, 'id_grupo_despesas', 'id_grupo_despesa');
    }

    /**
     * Get the itens da solicitação
     */
    public function itens()
    {
        return $this->hasMany(ItemSolicitacaoCompra::class, 'id_solicitacao_compra', 'id_solicitacoes_compras');
    }

    /**
     * Get the solicitação original (caso seja uma solicitação desmembrada)
     */
    public function solicitacaoOriginal()
    {
        return $this->belongsTo(SolicitacaoCompra::class, 'id_solicitacao_original', 'id_solicitacoes_compras');
    }

    /**
     * Get as solicitações desmembradas a partir desta
     */
    public function solicitacoesDesmembradas()
    {
        return $this->hasMany(SolicitacaoCompra::class, 'id_solicitacao_original', 'id_solicitacoes_compras');
    }

    /**
     * Get the logs de status da solicitação
     */
    public function logs()
    {
        return $this->hasMany(LogsSolicitacoesCompras::class, 'id_solicitacoes_compras', 'id_solicitacoes_compras');
    }

    /**
     * Registrar mudança de status no log
     */
    public function registrarLog($status, $usuarioId, $observacoes = null): void
    {
        // Criar uma nova entrada de log para esta mudança de status
        LogsSolicitacoesCompras::create([
            'id_solicitacoes_compras' => $this->id_solicitacoes_compras,
            'situacao_compra' => $status,
            'user_id' => $usuarioId,
            'observacao' => $observacoes
        ]);

        Log::info("Log registrado para solicitação {$this->id_solicitacoes_compras}: {$status}");
    }

    /**
     * Scope para solicitações pendentes
     */
    public function scopePendentes($query)
    {
        // Pendentes: ainda sem decisão (aprovado_reprovado NULL), sem data de aprovação e não canceladas
        return $query->whereNull('aprovado_reprovado')
            ->whereYear('data_inclusao', now()->year)
            ->whereNull('data_aprovacao')
            ->where(function ($q) {
                $q->whereNull('is_cancelada')->orWhere('is_cancelada', false);
            });
    }

    /**
     * Scope para solicitações aprovadas
     */
    public function scopeAprovadas($query)
    {
        return $query->where('aprovado_reprovado', true)
            ->whereNotNull('data_aprovacao')
            ->where('is_cancelada', false);
    }

    /**
     * Scope para solicitações reprovadas
     */
    public function scopeReprovadas($query)
    {
        return $query->where('aprovado_reprovado', false)
            ->whereNotNull('data_aprovacao')
            ->where('is_cancelada', false);
    }

    /**
     * Scope para solicitações canceladas
     */
    public function scopeCanceladas($query)
    {
        return $query->where('is_cancelada', true);
    }

    public function scopeSemSituacoesIndesejadas($query)
    {
        return $query->whereRaw("TRIM(LOWER(situacao_compra)) NOT LIKE '%REPROVADO%'")
            ->whereRaw("TRIM(LOWER(situacao_compra)) NOT LIKE '%CANCELADO%'")
            ->whereRaw("TRIM(LOWER(situacao_compra)) NOT LIKE '%REJEITADA%'")
            ->whereRaw("TRIM(LOWER(situacao_compra)) NOT LIKE '%APROVADA%'");
    }

    /**
     * Scope para solicitações finalizadas
     */
    public function scopeFinalizadas($query)
    {
        return $query->whereNotNull('data_finalizada');
    }

    /**
     * Scope para solicitações em processamento (aprovadas mas não finalizadas)
     */
    public function scopeEmProcessamento($query)
    {
        return $query->where('aprovado_reprovado', true)
            ->whereNotNull('data_aprovacao')
            ->whereNull('data_finalizada')
            ->where('is_cancelada', false);
    }

    /**
     * Obter o status formatado para display
     */
    public function getStatusAttribute()
    {
        if ($this->is_cancelada) {
            return self::STATUS_CANCELADA;
        }

        if ($this->data_finalizada) {
            return self::STATUS_FINALIZADO;
        }

        // Retorna o status atual baseado na situacao_compra
        return $this->situacao_compra; // Não forçar 'Iniciada' quando é null
    }

    /**
     * Obter a classe CSS para a situação da compra
     */
    public function getStatusClassAttribute()
    {
        $situacao = $this->situacao_compra;

        return match ($situacao) {
            self::STATUS_AGUARDANDO_APROVACAO_GESTOR => 'bg-yellow-100 text-yellow-800',
            self::STATUS_AGUARDANDO_INICIO_COMPRAS => 'bg-blue-100 text-blue-800',
            self::STATUS_INICIADA => 'bg-indigo-100 text-indigo-800',
            self::STATUS_AGUARDANDO_VALIDACAO_SOLICITANTE => 'bg-purple-100 text-purple-800',
            self::STATUS_COTACOES_RECUSADAS => 'bg-orange-100 text-orange-800',
            self::STATUS_SOLICITACAO_VALIDADA => 'bg-green-100 text-green-800',
            self::STATUS_AGUARDANDO_APROVACAO => 'bg-yellow-200 text-yellow-900', // Cor diferente para aprovação final
            self::STATUS_FINALIZADO => 'bg-green-100 text-green-800',
            self::STATUS_CANCELADA => 'bg-red-100 text-red-800',
            self::STATUS_REPROVADO_GESTOR => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Obter a situação da compra formatada para exibição
     */
    public function getSituacaoFormatadaAttribute()
    {

        $situacoesCompra = [
            self::STATUS_AGUARDANDO_APROVACAO_GESTOR => 'Aguardando Aprovação do Gestor de Departamento',
            self::STATUS_AGUARDANDO_INICIO_COMPRAS => 'Aguardando Início',
            self::STATUS_INICIADA => 'Iniciada',
            self::STATUS_AGUARDANDO_VALIDACAO_SOLICITANTE => 'Aguardando Validação',
            self::STATUS_COTACOES_RECUSADAS => 'Cotações Recusadas',
            self::STATUS_SOLICITACAO_VALIDADA => 'Validada',
            self::STATUS_AGUARDANDO_APROVACAO => 'Aguardando Aprovação Final', // Label específica para diferenciação
            self::STATUS_FINALIZADO => 'Finalizada',
            self::STATUS_CANCELADA => 'Cancelada',
            self::STATUS_REPROVADO_GESTOR => 'Reprovada',
        ];

        return $situacoesCompra[$this->situacao_compra] ?? '';
    }

    /**
     * Verificar se a solicitação pode ser editada
     */
    public function podeSerEditada()
    {
        // Pode ser editada se não tem status ou se está aguardando aprovação e não foi cancelada/finalizada
        return (
            is_null($this->situacao_compra) ||
            $this->situacao_compra === self::STATUS_AGUARDANDO_APROVACAO_GESTOR
        ) && ! $this->is_cancelada && ! $this->data_finalizada;
    }

    /**
     * Verificar se a solicitação pode ser aprovada pelo gestor
     */
    public function podeSerAprovada()
    {
        return $this->situacao_compra === self::STATUS_AGUARDANDO_APROVACAO_GESTOR &&
            ! $this->is_cancelada &&
            ! $this->data_finalizada;
    }

    /**
     * Verificar se a solicitação pode ser cancelada
     */
    public function podeSerCancelada()
    {
        return ! $this->is_cancelada &&
            ! $this->data_finalizada &&
            ! in_array($this->situacao_compra, [self::STATUS_FINALIZADO]);
    }

    /**
     * Verificar se a solicitação pode ser desmembrada
     */
    public function podeSerDesmembrada()
    {
        return in_array($this->situacao_compra, [
            self::STATUS_AGUARDANDO_INICIO_COMPRAS,
            self::STATUS_INICIADA,
        ]) && ! $this->is_cancelada && ! $this->data_finalizada;
    }

    /**
     * Verificar se a solicitação pode ser enviada para aprovação
     */
    public function podeSerEnviadaParaAprovacao()
    {
        return is_null($this->situacao_compra) &&
            !$this->is_cancelada &&
            !$this->data_finalizada;
    }

    /**
     * Verificar se a solicitação pode ser assumida por um comprador
     */
    public function podeSerAssumida()
    {
        return $this->situacao_compra === self::STATUS_AGUARDANDO_INICIO_COMPRAS &&
            ! $this->is_cancelada &&
            ! $this->data_finalizada &&
            is_null($this->id_comprador);
    }

    /**
     * Verificar se a solicitação pode ser devolvida para aprovação
     */
    public function podeSerDevolvida()
    {
        return in_array($this->situacao_compra, [
            self::STATUS_AGUARDANDO_INICIO_COMPRAS,
            self::STATUS_INICIADA,
        ]) && ! $this->is_cancelada && ! $this->data_finalizada;
    }

    /**
     * Verificar se está em processo de cotação
     */
    public function estaEmProcessoCotacao()
    {
        return $this->situacao_compra === self::STATUS_INICIADA;
    }

    /**
     * Verificar se está aguardando aprovação departamental
     */
    public function estaAguardandoAprovacaoDepartamental()
    {
        return $this->situacao_compra === self::STATUS_AGUARDANDO_APROVACAO_GESTOR;
    }

    /**
     * Verificar se está aguardando comprador assumir
     */
    public function estaAguardandoCompradorAssumir()
    {
        return $this->situacao_compra === self::STATUS_AGUARDANDO_INICIO_COMPRAS;
    }

    /**
     * Verificar se foi aprovada (mantido para compatibilidade)
     * Agora todas seguem fluxo comum de aprovação departamental
     */
    public function foiAprovadaAutomaticamente()
    {
        // Como não há mais aprovação automática, sempre retorna false
        // Mantido o método para compatibilidade com o sistema
        return false;
    }

    /**
     * Retorna todas as situações/status possíveis da solicitação de compra
     *
     * @return array Associativo com código => descrição e condição
     */
    public static function getAllSituacoes()
    {
        return [
            self::STATUS_SEM_STATUS => [
                'descricao' => 'Sem status definido',
                'condicao' => 'Estado inicial antes de definir qualquer situação',
            ],
            self::STATUS_AGUARDANDO_APROVACAO_GESTOR => [
                'descricao' => 'Aguardando aprovação do gestor departamento',
                'condicao' => 'Todas as solicitações precisam de aprovação departamental',
            ],
            self::STATUS_AGUARDANDO_INICIO_COMPRAS => [
                'descricao' => 'Aguardando início de compras',
                'condicao' => 'Aprovada pelo gestor departamental - pronta para comprador assumir',
            ],
            self::STATUS_INICIADA => [
                'descricao' => 'Iniciada',
                'condicao' => 'Comprador assumiu a solicitação e iniciou processo de cotação',
            ],
            self::STATUS_AGUARDANDO_VALIDACAO_SOLICITANTE => [
                'descricao' => 'Aguardando validação do solicitante',
                'condicao' => 'Comprador enviou cotações para validação do solicitante',
            ],
            self::STATUS_COTACOES_RECUSADAS => [
                'descricao' => 'Cotações recusadas pelo gestor',
                'condicao' => 'Gestor recusou as cotações apresentadas - volta para comprador',
            ],
            self::STATUS_SOLICITACAO_VALIDADA => [
                'descricao' => 'Solicitação validada pelo solicitante',
                'condicao' => 'Solicitante aprovou as cotações - pronta para finalização',
            ],
            self::STATUS_AGUARDANDO_APROVACAO => [
                'descricao' => 'Aguardando aprovação',
                'condicao' => 'Status específico para aprovação final de serviços',
            ],
            self::STATUS_REPROVADO_GESTOR => [
                'descricao' => 'Reprovado pelo gestor departamento',
                'condicao' => 'Gestor departamental rejeitou a solicitação',
            ],
        ];
    }

    /**
     * Retorna apenas as situações para uso em filtros/dropdowns
     *
     * @return array Associativo com código => descrição
     */
    public static function getSituacoesParaFiltro()
    {
        $situacoes = self::getAllSituacoes();
        $filtros = [];

        foreach ($situacoes as $codigo => $info) {
            if ($codigo !== null) { // Exclui STATUS_SEM_STATUS
                $filtros[$codigo] = $info['descricao'];
            }
        }

        return $filtros;
    }

    /**
     * Retorna a situação inicial baseada no fluxo comum
     *
     * @param  int|null  $cargoId  ID do cargo do usuário (não utilizado mais)
     * @return string Situação inicial
     */
    public static function getSituacaoInicial($cargoId = null)
    {
        // Todas as solicitações seguem o fluxo comum: aprovação departamental primeiro
        return self::STATUS_AGUARDANDO_APROVACAO_GESTOR;
    }

    /**
     * Método mantido para compatibilidade - sempre retorna false
     *
     * @param  int|null  $cargoId  ID do cargo do usuário
     * @return bool Sempre false (fluxo comum para todos)
     */
    public static function usuarioTemCargoAutorizado($cargoId)
    {
        // Todos os usuários seguem o fluxo comum de aprovação
        return false;
    }

    /**
     * Validar campos obrigatórios (método mantido para compatibilidade)
     */
    public function validarCamposCargoAutorizado()
    {
        // Não há mais validação especial para cargos autorizados
        // Todos seguem o fluxo padrão
        return true;
    }

    /**
     * Enviar solicitação para aprovação
     *
     * FLUXO COMUM PARA TODOS OS USUÁRIOS:
     *
     * - Todas as solicitações vão para "AGUARDANDO APROVAÇÃO DO GESTOR DEPARTAMENTO"
     * - aprovado_reprovado = NULL (aguarda decisão do gestor)
     * - Não há mais pulo de aprovação baseado em cargo
     */
    public function enviarParaAprovacao($userId, $cargoId = null)
    {
        if (! $this->podeSerEnviadaParaAprovacao()) {
            throw new \Exception('Solicitação não pode ser enviada para aprovação no status atual.');
        }

        // Usar data_alteracao em vez de data_enviada_aprovacao
        $this->data_alteracao = now();

        // Todas as solicitações seguem o fluxo padrão de aprovação departamental
        $this->situacao_compra = self::STATUS_AGUARDANDO_APROVACAO_GESTOR;
        // aprovado_reprovado fica null para aguardar decisão do gestor

        $this->save();

        // Registrar log da mudança de status
        $this->registrarLog($this->situacao_compra, $userId, 'Solicitação enviada para aprovação');

        return $this;
    }

    /**
     * Aprovar solicitação (gestor departamental)
     */
    public function aprovarGestor($userId, $observacao = null)
    {
        if (! $this->podeSerAprovada()) {
            throw new \Exception('Solicitação não pode ser aprovada no status atual.');
        }

        $this->situacao_compra = self::STATUS_AGUARDANDO_INICIO_COMPRAS;
        // $this->aprovado_gestor = true; // Campo não existe no banco
        $this->aprovado_reprovado = true;
        $this->id_aprovador = $userId;
        $this->data_aprovacao = now();
        $this->observacao_aprovador = $observacao;

        $this->save();

        // Registrar log da mudança de status
        $this->registrarLog($this->situacao_compra, $userId, $observacao);

        return $this;
    }

    /**
     * Reprovar solicitação (gestor departamental)
     */
    public function reprovarGestor($userId, $observacao)
    {
        if (! $this->podeSerAprovada()) {
            throw new \Exception('Solicitação não pode ser reprovada no status atual.');
        }

        $this->situacao_compra = self::STATUS_REPROVADO_GESTOR;
        // $this->aprovado_gestor = false; // Campo não existe no banco
        $this->aprovado_reprovado = false;
        $this->id_aprovador = $userId;
        $this->data_aprovacao = now();
        $this->observacao_aprovador = $observacao;

        $this->save();

        // Registrar log da mudança de status
        $this->registrarLog($this->situacao_compra, $userId, $observacao);

        return $this;
    }


    /**
     * Obter histórico da solicitação
     */
    public function getHistorico()
    {
        $historico = [];

        // Adicionar histórico das datas importantes
        if ($this->data_inclusao) {
            $solicitante = $this->solicitante;
            $historico[] = [
                'data' => $this->data_inclusao,
                'acao' => 'Solicitação Criada',
                'usuario' => $solicitante ? $solicitante->name : 'Sistema',
                'observacao' => 'Solicitação de compra criada',
            ];
        }

        if ($this->data_aprovacao && $this->aprovado_reprovado !== null) {
            $aprovador = $this->aprovador;
            $historico[] = [
                'data' => $this->data_aprovacao,
                'acao' => $this->aprovado_reprovado ? 'Aprovada' : 'Reprovada',
                'usuario' => $aprovador ? $aprovador->name : 'Sistema',
                'observacao' => $this->observacao_aprovador ?? '',
            ];
        }

        if ($this->data_finalizada) {
            $historico[] = [
                'data' => $this->data_finalizada,
                'acao' => 'Finalizada',
                'usuario' => 'Sistema',
                'observacao' => 'Solicitação finalizada',
            ];
        }

        // Ordenar por data - garantindo que todas as datas sejam objetos Carbon
        usort($historico, function ($a, $b) {
            // Converter para Carbon se necessário
            $dataA = $a['data'] instanceof Carbon ? $a['data'] : Carbon::parse($a['data']);
            $dataB = $b['data'] instanceof Carbon ? $b['data'] : Carbon::parse($b['data']);

            return $dataA->timestamp <=> $dataB->timestamp;
        });

        return $historico;
    }

    /**
     * Obter histórico completo baseado no log
     */
    public function getHistoricoCompleto()
    {
        $historico = [];

        // Sempre adicionar a criação da solicitação primeiro
        if ($this->data_inclusao) {
            $solicitante = $this->solicitante;
            $historico[] = [
                'data' => $this->data_inclusao,
                'acao' => 'Solicitação Criada',
                'usuario' => $solicitante ? $solicitante->name : 'Sistema',
                'observacao' => 'Solicitação de compra criada',
                'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'color' => 'bg-blue-500',
            ];
        }

        // Buscar todos os logs desta solicitação ordenados por data de criação
        $logs = $this->logs()->with('user')->orderBy('data_inclusao')->get();

        // Mapear status para configurações de exibição
        $statusConfig = [
            self::STATUS_AGUARDANDO_APROVACAO_GESTOR => [
                'acao' => 'Enviada para Aprovação',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'bg-yellow-500',
            ],
            self::STATUS_AGUARDANDO_INICIO_COMPRAS => [
                'acao' => 'Aguardando Início',
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'color' => 'bg-indigo-500',
            ],
            self::STATUS_INICIADA => [
                'acao' => 'Iniciada',
                'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0v6a2 2 0 01-2 2H10a2 2 0 01-2-2V6m8 0H8',
                'color' => 'bg-blue-600',
            ],
            self::STATUS_AGUARDANDO_VALIDACAO_SOLICITANTE => [
                'acao' => 'Aguardando Validação',
                'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'bg-purple-500',
            ],
            self::STATUS_SOLICITACAO_VALIDADA => [
                'acao' => 'Validada',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'bg-green-500',
            ],
            self::STATUS_COTACOES_RECUSADAS => [
                'acao' => 'Cotações Recusadas',
                'icon' => 'M6 18L18 6M6 6l12 12',
                'color' => 'bg-orange-500',
            ],
            self::STATUS_AGUARDANDO_APROVACAO => [
                'acao' => 'Aguardando Aprovação Final',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'bg-yellow-600',
            ],
            self::STATUS_REPROVADO_GESTOR => [
                'acao' => 'Reprovada',
                'icon' => 'M6 18L18 6M6 6l12 12',
                'color' => 'bg-red-500',
            ],
            self::STATUS_CANCELADA => [
                'acao' => 'Cancelada',
                'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728',
                'color' => 'bg-red-600',
            ],
            self::STATUS_FINALIZADO => [
                'acao' => 'Finalizada',
                'icon' => 'M5 13l4 4L19 7',
                'color' => 'bg-emerald-500',
            ],
        ];

        // Adicionar cada entrada do log ao histórico
        foreach ($logs as $log) {
            $config = $statusConfig[$log->situacao_compra] ?? [
                'acao' => $log->situacao_compra,
                'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'color' => 'bg-gray-500',
            ];

            $historico[] = [
                'data' => $log->data_inclusao,
                'acao' => $config['acao'],
                'usuario' => $log->user ? $log->user->name : 'Sistema',
                'observacao' => $log->observacao ?? '',
                'icon' => $config['icon'],
                'color' => $config['color'],
            ];
        }

        // Ordenar por data
        usort($historico, function ($a, $b) {
            $dataA = $a['data'] instanceof Carbon ? $a['data'] : Carbon::parse($a['data']);
            $dataB = $b['data'] instanceof Carbon ? $b['data'] : Carbon::parse($b['data']);

            return $dataA->timestamp <=> $dataB->timestamp;
        });

        return $historico;
    }
}
