<?php

namespace App\Modules\Sinistros\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sinistros\Models\DadosPessoalSinistro;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Sinistros\Models\FotosDocumentosSinistros;
use App\Modules\Sinistros\Models\HistoricoEventosSinistro;
use App\Models\Pessoal;
use App\Modules\Sinistros\Models\Sinistro;
use App\Modules\Configuracoes\Models\TipoCategoria;
use App\Models\TipoMotivoSinistro;
use App\Models\TipoOcorrencia;
use App\Models\TipoOrgaoSinistro;
use App\Models\Veiculo;
use App\Services\SinistroDocumentService;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SinistroController extends Controller
{
    use SanitizesMonetaryValues;

    /**
     * @var SinistroDocumentService
     */
    protected $documentService;

    /**
     * Construtor
     */
    public function __construct(SinistroDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Construir a query com eager loading otimizado
        $query = Sinistro::query()
            ->with([
                'veiculo:id_veiculo,placa',
                'filial:id,name',
                'pessoal:id_pessoal,nome',
                'orgao:id_tipo_orgao,descricao_tipo_orgao',
                'situacaoAtual:id_sinistro,descricao_situacao'
            ]);

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Executar a query com paginação
        $sinistros = $query->latest('data_inclusao')
            ->paginate(40)
            ->appends($request->query());

        // Se for uma requisição HTMX, retornar apenas a tabela
        if ($request->header('HX-Request')) {
            return view('admin.sinistros._table', compact('sinistros'));
        }

        return view('admin.sinistros.index', compact('sinistros'));
    }

    /**
     * Aplica filtros na consulta
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return void
     */
    protected function applyFilters($query, Request $request)
    {
        if ($request->filled('id_sinistro')) {
            $query->where('id_sinistro', $request->id_sinistro);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('placa')) {
            $query->whereHas('veiculo', function ($q) use ($request) {
                $q->where('placa', 'ilike', '%' . $request->placa . '%');
            });
        }

        if ($request->filled('filial')) {
            $query->whereHas('filial', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->filial . '%');
            });
        }

        if ($request->filled('motorista')) {
            $query->whereHas('pessoal', function ($q) use ($request) {
                $q->where('nome', 'ilike', '%' . $request->motorista . '%');
            });
        }

        if ($request->filled('data_sinistro')) {
            $query->whereDate('data_sinistro', '>=', $request->data_sinistro);
        }

        if ($request->filled('data_sinistro_fim')) {
            $query->whereDate('data_sinistro', '<=', $request->data_sinistro_fim);
        }

        if ($request->filled('responsabilidade')) {
            $query->where('responsabilidade_sinistro', 'ilike', '%' . $request->responsabilidade . '%');
        }

        if ($request->filled('orgao_sinistro')) {
            $query->whereHas('orgao', function ($q) use ($request) {
                $q->where('descricao_tipo_orgao', 'ilike', '%' . $request->orgao_sinistro . '%');
            });
        }

        if ($request->filled('data_inclusao')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao);
        }

        if ($request->filled('data_inclusao_fim')) {
            $query->whereDate('data_inclusao', '<=', $request->data_inclusao_fim);
        }
    }

    /**
     * Obtém e armazena em cache opções de formulário para evitar múltiplas consultas
     */
    protected function getFormOptions()
    {
        return Cache::remember('sinistros_form_options', now()->addHours(12), function () {
            return [
                'placas' => Veiculo::select('placa as label', 'id_veiculo as value')
                    ->where('situacao_veiculo', true)
                    ->orderBy('label')
                    ->get()
                    ->toArray(),

                'filiais' => Filial::select('name as label', 'id as value')
                    ->orderBy('label')
                    ->get()
                    ->toArray(),

                'tiposOrgaos' => TipoOrgaoSinistro::select('descricao_tipo_orgao as label', 'id_tipo_orgao as value')
                    ->orderBy('label')
                    ->get()
                    ->toArray(),

                'pessoas' => Pessoal::select('nome as label', 'id_pessoal as value')
                    ->where('ativo', true)
                    ->orderBy('label')
                    ->get()
                    ->toArray(),

                'tipoocorrencias' => TipoOcorrencia::select('descricao_ocorrencia as label', 'id_tipo_ocorrencia as value')
                    ->orderBy('label')
                    ->get()
                    ->toArray(),

                'tipomotivos' => TipoMotivoSinistro::select('descricao_motivo as label', 'id_motivo_cinistro as value')
                    ->orderBy('label')
                    ->get()
                    ->toArray(),

                'categoria_veiculo' => TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')
                    ->get()
                    ->toArray(),
            ];
        });
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formOptions = $this->getFormOptions();

        return view('admin.sinistros.create', compact('formOptions'));
    }

    public function getCategoria(Veiculo $veiculo)
    {
        $categoria = TipoCategoria::where('id_categoria', $veiculo->id_categoria)
            ->select('descricao_categoria as label', 'id_categoria as value')
            ->first();

        $dados = [
            'categoria' => [
                'label' => $categoria->label ?? '',
                'value' => $categoria->value ?? '',
            ],
            'filial' => [
                'value' => $veiculo->id_filial ?? '',
                'label' => $veiculo->filial->name ?? '',
            ],
        ];

        return response()->json($dados);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $this->sanitizeMonetaryValues($request, [
            'valor_apagar',
            'valor_pago',
            'valorpagoseguradora',
            'valorpagofrota',
            'valor_pago_terceiro',
        ]);

        try {
            $validacao = $request->validate([
                'id_veiculo' => 'required|int',
                'id_filial' => 'required|int',
                'id_motorista' => 'required|int',
                'data_sinistro' => 'required|date',
                'prazo_em_dias' => 'required|int',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            throw $ve;
        }

        try {
            DB::beginTransaction();

            // Instância do modelo
            $sinistros = new Sinistro;

            // Atribuição dos campos ao modelo
            $sinistros->id_veiculo                 = $validacao['id_veiculo'];
            $sinistros->data_inclusao              = now();
            $sinistros->id_filial                  = $validacao['id_filial'];
            $sinistros->id_motorista               = $validacao['id_motorista'];
            $sinistros->data_sinistro              = $validacao['data_sinistro'];
            $sinistros->prazo_em_dias              = $validacao['prazo_em_dias'];
            $sinistros->situacao_sinistro_processo = $request->situacao_sinistro_processo;
            $sinistros->responsabilidade_sinistro  = $request->responsabilidade_sinistro;
            $sinistros->id_tipo_orgao              = $request->id_tipo_orgao;
            $sinistros->numero_processo            = intval($request->numero_processo);
            $sinistros->local_ocorrencia           = $request->local_ocorrencia;
            $sinistros->descricao_ocorrencia       = $request->descricao_ocorrencia;
            $sinistros->observacao_ocorrencia      = $request->observacao_ocorrencia;
            $sinistros->valor_apagar               = $request->valor_apagar;
            $sinistros->valor_pago                 = $request->valor_pago;
            $sinistros->id_tipo_ocorrencia         = $request->id_tipo_ocorrencia;
            $sinistros->id_motivo                  = $request->id_motivo;
            $sinistros->id_categoria_veiculo       = $request->id_categoria_veiculo;
            $sinistros->valorpagoseguradora        = $request->valorpagoseguradora;
            $sinistros->valorpagofrota             = $request->valorpagofrota;
            $sinistros->situacao_pista             = $request->situacao_pista;

            // Ajuste: o campo no formulário é 'estado_pista'
            $sinistros->estados_pista       = $request->estado_pista;
            $sinistros->topografica         = $request->topografica;
            $sinistros->sinalizacao         = $request->sinalizacao;
            $sinistros->status              = $request->status;
            $sinistros->setor               = $request->setor;
            $sinistros->valor_pago_terceiro = $request->valor_pago_terceiro;

            // Salvar no banco
            $sinistros->save();


            // Processar históricos
            $this->processarHistoricos($sinistros->id_sinistro, $request);

            // Processar documentos
            $this->processarDocumentos($sinistros->id_sinistro, $request);

            // Processar envolvidos
            $this->processarEnvolvidos($sinistros->id_sinistro, $request);

            DB::commit();


            // Limpar cache de opções quando novos dados são adicionados
            Cache::forget('sinistros_form_options');

            return redirect()
                ->route('admin.sinistros.index')
                ->withNotification([
                    'title' => 'Sinistro criado',
                    'type' => 'success',
                    'message' => 'Sinistro criado com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.sinistros.index')
                ->withNotification([
                    'title' => 'Erro ao criar sinistro',
                    'type' => 'error',
                    'message' => 'Erro ao criar sinistro: ' . $e->getMessage(),
                ]);
        }
    }

    /**
     * Processa históricos do sinistro
     *
     * @param  int  $idSinistro
     * @return void
     */
    protected function processarHistoricos($idSinistro, Request $request)
    {
        $historicos = json_decode($request->input('historicos', '[]'), true);

        $possuiSituacaoFinalizado = false;

        foreach ($historicos as $historico_data) {
            $historico = new HistoricoEventosSinistro;

            $historico->data_inclusao      = now();
            $historico->id_sinistro        = $idSinistro;
            $historico->data_evento        = $historico_data['data_evento'];
            $historico->id_usuario         = Auth::user()->id;
            $historico->descricao_situacao = $historico_data['descricao_situacao'];
            $historico->observacao         = $historico_data['observacao'];

            $historico->save();

            // Verificar se a situação é "Finalizado"
            if (strtolower(trim($historico_data['descricao_situacao'])) === 'finalizado') {
                $possuiSituacaoFinalizado = true;
            }
        }

        // Se algum histórico tem situação "Finalizado", atualizar o status do sinistro
        if ($possuiSituacaoFinalizado) {
            $this->atualizarStatusSinistro($idSinistro, 'Finalizado');
        }
    }

    /**
     * Atualiza o status do sinistro
     *
     * @param  int  $idSinistro
     * @param  string  $novoStatus
     * @return void
     */
    protected function atualizarStatusSinistro($idSinistro, $novoStatus)
    {
        $sinistro = Sinistro::find($idSinistro);
        if ($sinistro) {
            $sinistro->status = $novoStatus;
            $sinistro->data_alteracao = now();
            $sinistro->save();
        }
    }

    /**
     * Processa documentos do sinistro
     *
     * @param  int  $idSinistro
     * @return void
     */
    protected function processarDocumentos($idSinistro, Request $request)
    {
        $documentos = json_decode($request->input('documentos', '[]'), true);

        foreach ($documentos as $documento) {
            // Criar novo registro no banco
            $docs = new FotosDocumentosSinistros;
            $docs->data_inclusao = now();
            $docs->id_sinistro = $idSinistro;

            // Verificar se é um caminho completo ou apenas nome de arquivo
            $fileName = isset($documento['documento']) ? $documento['documento'] : '';
            if (empty($fileName) && isset($documento['doc'])) {
                $fileName = $documento['doc']; // Compatibilidade com formato antigo
            }

            // Se for um caminho temporário, mover para a pasta do sinistro
            if (! empty($fileName) && strpos($fileName, 'temp/') === 0) {
                $tempFileName = str_replace('temp/', '', $fileName);
                $result = $this->documentService->moveFromTemp($tempFileName, $idSinistro);

                if ($result['success']) {
                    $docs->documento = $result['path'];
                } else {
                    $docs->documento = $fileName; // Salvar caminho original como fallback
                }
            } elseif (! empty($fileName) && strpos($fileName, $idSinistro . '/') !== false) {
                $docs->documento = $fileName;
            } else {
                $docs->documento = $fileName;
            }

            $docs->save();
        }
    }

    /**
     * Processa envolvidos do sinistro
     *
     * @param  int  $idSinistro
     * @return void
     */
    protected function processarEnvolvidos($idSinistro, Request $request)
    {
        $dadosenvolvidos = json_decode($request->input('envolvidos', '[]'), true);

        foreach ($dadosenvolvidos as $dadosEnv) {
            $envolvidos = new DadosPessoalSinistro;

            $envolvidos->data_inclusao = now();
            $envolvidos->nome_pessoal = $dadosEnv['nome'];
            $envolvidos->telefone = $dadosEnv['telefone'];
            $envolvidos->cpf = $dadosEnv['cpf'];
            $envolvidos->id_sinistro = $idSinistro;

            $envolvidos->save();
        }
    }

    public function edit(string $id)
    {
        // Armazenar em cache resultados específicos para este sinistro
        $cacheKey = "sinistro_edit_{$id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($id) {
            $sinistro = Sinistro::with([
                'veiculo:id_veiculo,placa,id_categoria',
                'filial:id,name',
                'pessoal:id_pessoal,nome',
                'orgao:id_tipo_orgao,descricao_tipo_orgao',
            ])->findOrFail($id);

            $formOptions = $this->getFormOptions();

            // Adicionar categoria_veiculo específica para este sinistro
            if ($sinistro->veiculo) {
                $formOptions['categoria_veiculo'] = TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')
                    ->where('id_categoria', $sinistro->veiculo->id_categoria)
                    ->get()
                    ->toArray();
            }

            $historicosinistro = HistoricoEventosSinistro::where('id_sinistro', $id)
                ->select('data_evento', 'descricao_situacao', 'observacao')
                ->get()
                ->toArray();

            $historicosinistroDocumentos = FotosDocumentosSinistros::where('id_sinistro', $id)
                ->get()
                ->toArray();

            $dadosEnvolvidos = DadosPessoalSinistro::where('id_sinistro', $id)
                ->get()
                ->toArray();

            return [
                'sinistro' => $sinistro,
                'formOptions' => $formOptions,
                'historicosinistro' => $historicosinistro,
                'historicosinistroDocumentos' => $historicosinistroDocumentos,
                'dadosEnvolvidos' => $dadosEnvolvidos,
            ];
        });

        return view('admin.sinistros.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->sanitizeMonetaryValues($request, [
            'valor_apagar',
            'valor_pago',
            'valorpagoseguradora',
            'valorpagofrota',
            'valor_pago_terceiro',
        ]);

        $validacao = $request->validate([
            'id_veiculo' => 'required|int',
            'id_filial' => 'required|int',
            'id_motorista' => 'required|int',
            'data_sinistro' => 'required|date',
            'prazo_em_dias' => 'required|int',
        ]);

        if (!is_numeric($request->input('id_motorista'))) {
            $motorista = Pessoal::where('nome', $request->input('id_motorista'))->first()->id_pessoal ?? null;
            $validacao['id_motorista'] = $motorista;
        }

        try {
            DB::beginTransaction();

            // Instância do modelo
            $sinistros = Sinistro::findOrFail($id);

            // Atribuição dos campos ao modelo
            $sinistros->id_veiculo                 = $validacao['id_veiculo'];
            $sinistros->data_alteracao             = now();
            $sinistros->id_filial                  = $validacao['id_filial'];
            $sinistros->id_motorista               = $validacao['id_motorista'];
            $sinistros->data_sinistro              = $validacao['data_sinistro'];
            $sinistros->prazo_em_dias              = $validacao['prazo_em_dias'];
            $sinistros->situacao_sinistro_processo = $request->situacao_sinistro_processo;
            $sinistros->responsabilidade_sinistro  = $request->responsabilidade_sinistro;
            $sinistros->id_tipo_orgao              = $request->id_tipo_orgao;
            $sinistros->numero_processo            = intval($request->numero_processo);
            $sinistros->local_ocorrencia           = $request->local_ocorrencia;
            $sinistros->descricao_ocorrencia       = $request->descricao_ocorrencia;
            $sinistros->observacao_ocorrencia      = $request->observacao_ocorrencia;
            $sinistros->valor_apagar               = $request->valor_apagar;
            $sinistros->valor_pago                 = $request->valor_pago;
            $sinistros->id_tipo_ocorrencia         = $request->id_tipo_ocorrencia;
            $sinistros->id_motivo                  = $request->id_motivo;
            $sinistros->id_categoria_veiculo       = $request->id_categoria_veiculo;
            $sinistros->valorpagoseguradora        = $request->valorpagoseguradora;
            $sinistros->valorpagofrota             = $request->valorpagofrota;
            $sinistros->situacao_pista             = $request->situacao_pista;

            // Ajuste: o campo no formulário é 'estado_pista'
            $sinistros->estados_pista       = $request->estado_pista;
            $sinistros->topografica         = $request->topografica;
            $sinistros->sinalizacao         = $request->sinalizacao;
            $sinistros->status              = $request->status;
            $sinistros->setor               = $request->setor;
            $sinistros->valor_pago_terceiro = $request->valor_pago_terceiro;

            // Salvar no banco
            $sinistros->save();

            // Remover históricos antigos
            HistoricoEventosSinistro::where('id_sinistro', $id)->delete();

            // Processar históricos novos
            $this->processarHistoricos($id, $request);

            // Remover registros de documentos antigos
            FotosDocumentosSinistros::where('id_sinistro', $id)->delete();

            // Processar documentos
            $this->processarDocumentos($id, $request);

            // Remover envolvidos antigos
            DadosPessoalSinistro::where('id_sinistro', $id)->delete();

            // Processar envolvidos
            $this->processarEnvolvidos($id, $request);

            DB::commit();

            // Limpar os caches relacionados
            $this->limparCaches($id);

            return redirect()
                ->route('admin.sinistros.index')
                ->withNotification([
                    'title' => 'Sinistro atualizado',
                    'type' => 'success',
                    'message' => 'Sinistro atualizado com sucesso!',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.sinistros.index')
                ->withNotification([
                    'title' => 'Erro ao atualizar sinistro',
                    'type' => 'error',
                    'message' => 'Erro ao atualizar sinistro: ' . $e->getMessage(),
                ]);
        }
    }

    /**
     * Limpa caches relacionados ao sinistro
     *
     * @param  int  $id
     * @return void
     */
    protected function limparCaches($id)
    {
        Cache::forget("sinistro_edit_{$id}");
        Cache::forget('sinistros_form_options');
    }

    public function destroy(int $id)
    {
        try {
            DB::beginTransaction();

            // Obter todos os documentos para excluir os arquivos físicos
            $documentos = FotosDocumentosSinistros::where('id_sinistro', $id)->get();
            foreach ($documentos as $documento) {
                $path = $documento->documento;
                if (! empty($path)) {
                    // Tentar remover o arquivo físico
                    $this->documentService->deleteFile($path);
                }
            }

            // Remover registros do banco
            DadosPessoalSinistro::where('id_sinistro', $id)->delete();
            FotosDocumentosSinistros::where('id_sinistro', $id)->delete();
            HistoricoEventosSinistro::where('id_sinistro', $id)->delete();
            Sinistro::where('id_sinistro', $id)->delete();

            DB::commit();

            // Limpar cache relacionado
            $this->limparCaches($id);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        $term = strtolower($request->get('term'));

        if (empty($term) || strlen($term) < 2) {
            return response()->json([]);
        }

        // Cache para melhorar performance
        $sinistros = Cache::remember('sinistro_search_' . $term, now()->addMinutes(30), function () use ($term) {
            return Sinistro::select('id_sinistro', 'descricao_ocorrencia', 'data_sinistro')
                ->whereRaw('CAST(id_sinistro AS TEXT) LIKE ?', ["%{$term}%"])
                ->orWhereRaw('LOWER(descricao_ocorrencia) LIKE ?', ["%{$term}%"])
                ->orderByDesc('data_sinistro')
                ->limit(30)
                ->get()
                ->map(function ($s) {
                    // Apenas o número no autocomplete
                    return [
                        'label' => (string) $s->id_sinistro,
                        'value' => $s->id_sinistro
                    ];

                    // 👉 se quiser exibir mais informações no label (descrição + data), pode usar:
                    /*
                    $label = "SIN #{$s->id_sinistro}";
                    if (!empty($s->descricao_ocorrencia)) {
                        $label .= ' - ' . mb_substr($s->descricao_ocorrencia, 0, 40);
                    }
                    if (!empty($s->data_sinistro)) {
                        $label .= ' (' . $s->data_sinistro->format('d/m/Y') . ')';
                    }

                    return [
                        'label' => $label,
                        'value' => $s->id_sinistro
                    ];
                    */
                })->toArray();
        });

        return response()->json($sinistros);
    }

    /**
     * Buscar sinistro pelo ID
     */
    public function getById($id)
    {
        $sinistro = Sinistro::where('id_sinistro', $id)->first();

        if (!$sinistro) {
            return response()->json([], 404);
        }

        return response()->json([
            'value' => $sinistro->id_sinistro,
            'label' => (string) $sinistro->id_sinistro,
            'data_sinistro' => optional($sinistro->data_sinistro)->format('d/m/Y'),
            'status' => $sinistro->status,
        ]);
    }

    /**
     * Armazena histórico de eventos do sinistro
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeHistorico(Request $request)
    {
        try {
            DB::beginTransaction();

            $registros = $request->input('registros');

            $possuiSituacaoFinalizado = false;
            $idSinistro = null;

            foreach ($registros as $registro) {
                $historico = new HistoricoEventosSinistro;

                $historico->data_inclusao = now();
                $historico->id_sinistro = $registro['id_sinistro'];
                $historico->data_evento = $registro['data_evento'];
                $historico->id_usuario = $registro['id_usuario'];
                $historico->descricao_situacao = $registro['descricao_situacao'] ?? null;
                $historico->observacao = $registro['observacao'] ?? null;

                $historico->save();

                // Verificar se a situação é "Finalizado"
                if (strtolower(trim($registro['descricao_situacao'] ?? '')) === 'finalizado') {
                    $possuiSituacaoFinalizado = true;
                    $idSinistro = $registro['id_sinistro'];
                }
            }

            // Se algum histórico tem situação "Finalizado", atualizar o status do sinistro
            if ($possuiSituacaoFinalizado && $idSinistro) {
                $this->atualizarStatusSinistro($idSinistro, 'Finalizado');
            }

            DB::commit();

            return response()->json([
                'message' => 'Registros salvos com sucesso!',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao salvar os registros: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }
}
