<?php

namespace App\Modules\Multas\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Certificados\Models\Multa;
use App\Modules\Certificados\Models\DetalheMulta;
use App\Models\Veiculo;
use App\Modules\Pessoal\Models\Pessoal;
use App\Modules\Certificados\Models\ClassificacaoMulta;
use App\Models\TipoOrgaoSinistro;
use App\Models\Municipio;
use App\Modules\Configuracoes\Models\Filial;
use App\Modules\Configuracoes\Models\Departamento;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MultaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->normalizeSmartSelectParams($request);
        // Inicializar searchTerm com o valor da requisição ou null
        $searchTerm = $request->filled('search') ? $request->search : null;

        // Construir a query com filtros
        $query = Multa::query()
            ->with(['veiculo:id_veiculo,placa', 'condutor:id_pessoal,nome', 'classificacaoMulta:id_classificacao_multa,descricao_multa'])
            ->select('id_motivo_multa', 'auto_infracao', 'data_inclusao', 'data_infracao', 'valor_multa', 'id_veiculo', 'id_condutor', 'id_classificacao_multa', 'situacao', 'status_multa')
            ->orderBy('id_motivo_multa', 'desc');

        // Aplicar filtros da busca
        if ($request->filled('search')) {
            $searchTermLower = strtolower($searchTerm);
            $query->where(function ($query) use ($searchTermLower) {
                $query->whereRaw('LOWER(auto_infracao) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(id_motivo_multa AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(valor_multa AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%']);
            });
        }

        if ($request->filled('id_veiculo')) {
            $query->where('id_veiculo', $request->id_veiculo);
        }

        if ($request->filled('status_multa')) {
            $query->where('status_multa', $request->status_multa);
        }

        if ($request->filled('situacao')) {
            $query->where('situacao', $request->situacao);
        }

        // Contar registros para estatísticas
        $totalRegistros = $query->count();

        // Paginar resultados
        $multas = $query->paginate(15)
            ->appends($request->query());

        // Carregar dados para os selects do formulário de busca (com cache)
        $placasData = Cache::remember('veiculos_ativos_index', now()->addHour(), function () {
            return Veiculo::select('placa as label', 'id_veiculo as value')
                ->where('situacao_veiculo', true)
                ->orderBy('placa')
                ->limit(50)
                ->get();
        });

        // Retornar a view com os dados necessários
        return view('admin.multas.index', compact(
            'multas',
            'totalRegistros',
            'placasData',
            'searchTerm'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Usar cache para listas de seleção para melhorar o desempenho
        $placasData = Cache::remember('veiculos_ativos_form', now()->addHour(), function () {
            return Veiculo::select('placa as label', 'id_veiculo as value')
                ->where('situacao_veiculo', true)
                ->orderBy('placa')
                ->limit(500)
                ->get();
        });

        $classificacaoMultaData = Cache::remember('classificacao_multa', now()->addDay(), function () {
            return ClassificacaoMulta::selectRaw(
                "CONCAT('Pontuação: ', pontos, ' - ', descricao_multa) as label, id_classificacao_multa as value"
            )
                ->orderBy('pontos')
                ->get();
        });

        $tipoOrgaoSinistro = Cache::remember('tipo_orgao_sinistro', now()->addDay(), function () {
            return TipoOrgaoSinistro::select('descricao_tipo_orgao as label', 'id_tipo_orgao as value')
                ->orderBy('descricao_tipo_orgao')
                ->get();
        });

        $municipiosData = Cache::remember('municipios_populares', now()->addDay(), function () {
            return Municipio::select('nome_municipio as label', 'id_municipio as value')
                ->orderBy('nome_municipio')
                ->limit(20)
                ->get();
        });

        $condutorData = Cache::remember('condutores', now()->addHour(), function () {
            return Pessoal::select('nome as label', 'id_pessoal as value')
                ->orderBy('nome')
                ->limit(500)
                ->get();
        });

        $filialData = Cache::remember('filiais', now()->addDay(), function () {
            return Filial::select('name as label', 'id as value')
                ->orderBy('name')
                ->get();
        });

        $departamentoData = Cache::remember('departamentos', now()->addDay(), function () {
            return Departamento::select('descricao_departamento as label', 'id_departamento as value')
                ->orderBy('descricao_departamento')
                ->get();
        });

        return view('admin.multas.create', compact(
            'placasData',
            'classificacaoMultaData',
            'tipoOrgaoSinistro',
            'municipiosData',
            'condutorData',
            'filialData',
            'departamentoData'
        ));
    }

    /**
     * Fetch vehicle data for AJAX requests.
     */
    public function getVehicleData(Request $request)
    {
        try {
            $veiculo = Veiculo::select('id_veiculo', 'id_departamento', 'id_filial', 'id_base_veiculo')
                ->with([
                    'departamentoVeiculo:id_departamento,descricao_departamento',
                    'filial:id,name',
                    'baseVeiculo:id_base_veiculo,descricao_base'
                ])
                ->where('id_veiculo', $request->placa)
                ->firstOrFail();

            return response()->json([
                'departamento' => $veiculo->departamentoVeiculo->descricao_departamento ?? 'Não informado',
                'filial' => $veiculo->filial->name ?? 'Não informado',
                'locacao' => $veiculo->baseVeiculo->descricao_base ?? 'Não informado',
                'id_departamento' => $veiculo->id_departamento,
                'id_filial' => $veiculo->id_filial
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validar dados do formulário
            $validated = $this->validateMultaData($request);

            // Verificar itens de detalhe
            $detalhesMulta = json_decode($request->input('detalheMultainput', '[]'), true);

            DB::beginTransaction();

            // Processar assinatura se fornecida
            $fileName = null;
            if ($request->filled('assinatura')) {
                $signatureData = $request->input('assinatura');
                $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
                $signatureData = str_replace(' ', '+', $signatureData);

                $fileName = 'signatures/' . uniqid() . '.png';
                Storage::disk('public')->put($fileName, base64_decode($signatureData));
            }

            // Buscar dados complementares do veículo
            if ($request->filled('id_veiculo')) {
                $veiculo = Veiculo::select('id_veiculo', 'id_departamento', 'id_filial')
                    ->find($request->id_veiculo);

                if ($veiculo) {
                    if (!$request->filled('id_departamento')) {
                        $validated['id_departamento'] = $veiculo->id_departamento;
                    }
                    if (!$request->filled('id_filial')) {
                        $validated['id_filial'] = $veiculo->id_filial;
                    }
                }
            }

            // Processar arquivo_multa se fornecido (input no form é arquivo_multa mas campo no DB é aquivo_multa)
            $aquivoMulta = null;
            if ($request->hasFile('arquivo_multa')) {
                try {
                    $arquivo = $request->file('arquivo_multa');
                    $aquivoMulta = $arquivo->store('multas', 'public');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar arquivo da multa: ' . $e->getMessage());
                }
            }

            // Processar arquivo_boleto se fornecido
            $arquivoBoleto = null;
            if ($request->hasFile('arquivo_boleto')) {
                try {
                    $arquivo = $request->file('arquivo_boleto');
                    $arquivoBoleto = $arquivo->store('boletos', 'public');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar arquivo do boleto: ' . $e->getMessage());
                }
            }

            // Criar registro de multa - Corrigido o tratamento de campos nulos
            $multaData = [
                'data_inclusao' => now(),
                'descricao' => $validated['descricao'] ?? null,
                'id_orgao' => $validated['id_orgao'] ?? null,
                'id_classificacao_multa' => $validated['id_classificacao_multa'] ?? null,
                'valor_multa' => isset($validated['valor_multa']) ? $this->sanitizeToDouble($validated['valor_multa']) : null,
                'aquivo_multa' => $aquivoMulta, // Campo no DB é 'aquivo_multa' (sem 'r')
                'id_veiculo' => $validated['id_veiculo'] ?? null,
                'id_condutor' => $validated['id_condutor'] ?? null,
                'data_infracao' => isset($validated['data_infracao']) && $validated['data_infracao'] ? Carbon::parse($validated['data_infracao']) : null,
                'vencimento_multa' => isset($validated['vencimento_multa']) && $validated['vencimento_multa'] ? Carbon::parse($validated['vencimento_multa']) : null,
                'id_departamento' => $validated['id_departamento'] ?? null,
                'id_filial' => $validated['id_filial'] ?? null,
                'auto_infracao' => $validated['auto_infracao'] ?? null,
                'notificacao' => $validated['notificacao'] ?? null,
                'situacao' => $validated['situacao'] ?? null,
                'responsabilidade' => $validated['responsabilidade'] ?? null,
                'localizacao' => $validated['localizacao'] ?? null,
                'id_municipio' => $validated['id_municipio'] ?? null,
                'debitar_condutor' => isset($validated['debitar_condutor']) ? (bool) $validated['debitar_condutor'] : null,
                'parcelas' => $validated['parcelas'] ?? null,
                'data_envio_departamento' => isset($validated['data_envio_departamento']) && $validated['data_envio_departamento'] ? Carbon::parse($validated['data_envio_departamento']) : null,
                'status_multa' => $validated['status_multa'] ?? null,
                'data_envio_financeiro' => isset($validated['data_envio_financeiro']) && $validated['data_envio_financeiro'] ? Carbon::parse($validated['data_envio_financeiro']) : null,
                'id_departamento_responsavel' => $validated['id_departamento_responsavel'] ?? null,
                'id_filial_responsavel' => $validated['id_filial_responsavel'] ?? null,
                'data_prazo_ident' => isset($validated['data_prazo_ident']) && $validated['data_prazo_ident'] ? Carbon::parse($validated['data_prazo_ident']) : null,
                'arquivo_boleto' => $arquivoBoleto,
                'is_assinado' => isset($validated['is_assinado']) ? (bool) $validated['is_assinado'] : null,
                'assinatura' => $fileName
            ];

            $multaId = DB::connection('pgsql')->table('motivo_multa')->insertGetId($multaData, 'id_motivo_multa');

            // Inserir detalhes da multa
            foreach ($detalhesMulta as $detalhe) {
                $detalheData = [
                    'data_inclusao' => now(),
                    'id_motivo_multa' => $multaId,
                    'prazo_indicacao_condutor' => $detalhe['prazo_indicacao_condutor'] ?? null,
                    'prazo_para_pagamento' => null,
                    'prazo_para_recurso' => null,
                    'data_envio_financeiro' => isset($detalhe['data_envio_financeiro']) && $detalhe['data_envio_financeiro'] ? Carbon::parse($detalhe['data_envio_financeiro']) : null,
                    'data_pagamento' => isset($detalhe['data_pagamento']) && $detalhe['data_pagamento'] ? Carbon::parse($detalhe['data_pagamento']) : null,
                    'data_recebimento_notificacao' => isset($detalhe['data_recebimento_notificacao']) && $detalhe['data_recebimento_notificacao'] ? Carbon::parse($detalhe['data_recebimento_notificacao']) : null,
                    'data_envio_departamento' => isset($detalhe['data_envio_departamento']) && $detalhe['data_envio_departamento'] ? Carbon::parse($detalhe['data_envio_departamento']) : null,
                    'data_indeferimento_recurso' => isset($detalhe['data_indeferimento_recurso']) && $detalhe['data_indeferimento_recurso'] ? Carbon::parse($detalhe['data_indeferimento_recurso']) : null,
                    'data_inicio_recurso' => isset($detalhe['data_inicio_recurso']) && $detalhe['data_inicio_recurso'] ? Carbon::parse($detalhe['data_inicio_recurso']) : null,
                    'responsavel_recurso' => $detalhe['responsavel_recurso'] ?? null
                ];

                DB::connection('pgsql')->table('detalhe_multa')->insert($detalheData);
            }

            DB::commit();

            // Limpar cache após alterações
            $this->clearRelatedCache();

            return redirect()->route('admin.multas.index')
                ->with('success', 'Multa cadastrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERRO AO CADASTRAR MULTA: ' . $e->getMessage());
            Log::error('Detalhes do erro: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar multa: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $multas = Multa::with([
            'veiculo:id_veiculo,placa',
            'condutor:id_pessoal,nome',
            'classificacaoMulta:id_classificacao_multa,descricao_multa',
            'detalheMulta'
        ])->findOrFail($id);

        return view('admin.multas.show', compact('multas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // Carrega o registro da multa com seus relacionamentos
        $multas = Multa::with([
            'veiculo:id_veiculo,placa',
            'condutor:id_pessoal,nome',
            'classificacaoMulta:id_classificacao_multa,descricao_multa',
            'detalheMulta'
        ])->findOrFail($id);

        // Usar cache para listas de seleção para melhorar o desempenho
        $placasData = Cache::remember('veiculos_ativos_form', now()->addHour(), function () {
            return Veiculo::select('placa as label', 'id_veiculo as value')
                ->where('situacao_veiculo', true)
                ->orderBy('placa')
                ->limit(500)
                ->get();
        });

        $classificacaoMultaData = Cache::remember('classificacao_multa', now()->addDay(), function () {
            return ClassificacaoMulta::selectRaw(
                "CONCAT('Pontuação: ', pontos, ' - ', descricao_multa) as label, id_classificacao_multa as value"
            )
                ->orderBy('pontos')
                ->get();
        });

        $tipoOrgaoSinistro = Cache::remember('tipo_orgao_sinistro', now()->addDay(), function () {
            return TipoOrgaoSinistro::select('descricao_tipo_orgao as label', 'id_tipo_orgao as value')
                ->orderBy('descricao_tipo_orgao')
                ->get();
        });

        $municipiosData = Cache::remember('municipios', now()->addDay(), function () {
            return Municipio::select('nome_municipio as label', 'id_municipio as value')
                ->orderBy('nome_municipio')
                ->limit(500)
                ->get();
        });

        $condutorData = Cache::remember('condutores', now()->addHour(), function () {
            return Pessoal::select('nome as label', 'id_pessoal as value')
                ->orderBy('nome')
                ->limit(500)
                ->get();
        });

        $filialData = Cache::remember('filiais', now()->addDay(), function () {
            return Filial::select('name as label', 'id as value')
                ->orderBy('name')
                ->get();
        });

        $departamentoData = Cache::remember('departamentos', now()->addDay(), function () {
            return Departamento::select('descricao_departamento as label', 'id_departamento as value')
                ->orderBy('descricao_departamento')
                ->get();
        });

        // Carregar os detalhes da multa
        $detalheMultaTab = DetalheMulta::where('id_motivo_multa', $id)
            ->select(
                'data_inclusao as datainclusao',
                'data_alteracao',
                'prazo_indicacao_condutor',
                'data_envio_financeiro',
                'data_pagamento',
                'data_recebimento_notificacao',
                'data_envio_departamento',
                'data_indeferimento_recurso',
                'data_inicio_recurso',
                'responsavel_recurso'
            )
            ->get()
            ->toArray();

        return view('admin.multas.edit', compact(
            'multas',
            'placasData',
            'classificacaoMultaData',
            'tipoOrgaoSinistro',
            'municipiosData',
            'condutorData',
            'filialData',
            'departamentoData',
            'detalheMultaTab'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validar dados do formulário
            $validated = $this->validateMultaData($request);

            // Verificar itens de detalhe
            $detalhesMulta = json_decode($request->input('detalheMultainput', '[]'), true);

            DB::beginTransaction();

            $multa = Multa::findOrFail($id);

            // Processar assinatura se fornecida
            $fileName = $multa->assinatura;
            if ($request->filled('assinatura') && strpos($request->input('assinatura'), 'data:image/png;base64,') === 0) {
                $signatureData = $request->input('assinatura');
                $signatureData = str_replace('data:image/png;base64,', '', $signatureData);
                $signatureData = str_replace(' ', '+', $signatureData);

                // Remover assinatura antiga se existir
                if ($multa->assinatura) {
                    try {
                        if (Storage::disk('public')->exists($multa->assinatura)) {
                            Storage::disk('public')->delete($multa->assinatura);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao excluir assinatura antiga: ' . $e->getMessage());
                    }
                }

                $fileName = 'signatures/' . uniqid() . '.png';
                Storage::disk('public')->put($fileName, base64_decode($signatureData));
            }

            // Buscar dados complementares do veículo
            if ($request->filled('id_veiculo') && $request->id_veiculo != $multa->id_veiculo) {
                $veiculo = Veiculo::select('id_veiculo', 'id_departamento', 'id_filial')
                    ->find($request->id_veiculo);

                if ($veiculo) {
                    if (!$request->filled('id_departamento')) {
                        $validated['id_departamento'] = $veiculo->id_departamento;
                    }
                    if (!$request->filled('id_filial')) {
                        $validated['id_filial'] = $veiculo->id_filial;
                    }
                }
            }

            // Criar array com dados da multa a ser atualizada
            $multaData = [
                'data_alteracao' => now(),
                'descricao' => $validated['descricao'] ?? null,
                'id_orgao' => $validated['id_orgao'] ?? null,
                'id_classificacao_multa' => $validated['id_classificacao_multa'] ?? null,
                'valor_multa' => isset($validated['valor_multa']) ? $this->sanitizeToDouble($validated['valor_multa']) : null,
                'id_veiculo' => $validated['id_veiculo'] ?? null,
                'id_condutor' => $validated['id_condutor'] ?? null,
                'data_infracao' => isset($validated['data_infracao']) && $validated['data_infracao'] ? Carbon::parse($validated['data_infracao']) : null,
                'vencimento_multa' => isset($validated['vencimento_multa']) && $validated['vencimento_multa'] ? Carbon::parse($validated['vencimento_multa']) : null,
                'id_departamento' => $validated['id_departamento'] ?? null,
                'id_filial' => $validated['id_filial'] ?? null,
                'auto_infracao' => $validated['auto_infracao'] ?? null,
                'notificacao' => $validated['notificacao'] ?? null,
                'situacao' => $validated['situacao'] ?? null,
                'responsabilidade' => $validated['responsabilidade'] ?? null,
                'localizacao' => $validated['localizacao'] ?? null,
                'id_municipio' => $validated['id_municipio'] ?? null,
                'debitar_condutor' => isset($validated['debitar_condutor']) ? (bool) $validated['debitar_condutor'] : null,
                'parcelas' => $validated['parcelas'] ?? null,
                'data_envio_departamento' => isset($validated['data_envio_departamento']) && $validated['data_envio_departamento'] ? Carbon::parse($validated['data_envio_departamento']) : null,
                'status_multa' => $validated['status_multa'] ?? null,
                'data_envio_financeiro' => isset($validated['data_envio_financeiro']) && $validated['data_envio_financeiro'] ? Carbon::parse($validated['data_envio_financeiro']) : null,
                'id_departamento_responsavel' => $validated['id_departamento_responsavel'] ?? null,
                'id_filial_responsavel' => $validated['id_filial_responsavel'] ?? null,
                'data_prazo_ident' => isset($validated['data_prazo_ident']) && $validated['data_prazo_ident'] ? Carbon::parse($validated['data_prazo_ident']) : null,
                'is_assinado' => isset($validated['is_assinado']) ? (bool) $validated['is_assinado'] : null,
                'assinatura' => $fileName
            ];

            // Adicionar arquivo_multa apenas se fornecido um novo arquivo
            if ($request->hasFile('arquivo_multa')) {
                // Remover arquivo antigo se existir
                if ($multa->aquivo_multa) {
                    try {
                        if (Storage::disk('public')->exists($multa->aquivo_multa)) {
                            Storage::disk('public')->delete($multa->aquivo_multa);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao excluir arquivo antigo da multa: ' . $e->getMessage());
                    }
                }

                try {
                    $arquivo = $request->file('arquivo_multa');
                    $multaData['aquivo_multa'] = $arquivo->store('multas', 'public');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novo arquivo da multa: ' . $e->getMessage());
                }
            }

            // Adicionar arquivo_boleto apenas se fornecido um novo arquivo
            if ($request->hasFile('arquivo_boleto')) {
                // Remover arquivo antigo se existir
                if ($multa->arquivo_boleto) {
                    try {
                        if (Storage::disk('public')->exists($multa->arquivo_boleto)) {
                            Storage::disk('public')->delete($multa->arquivo_boleto);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao excluir arquivo antigo do boleto: ' . $e->getMessage());
                    }
                }

                try {
                    $arquivo = $request->file('arquivo_boleto');
                    $multaData['arquivo_boleto'] = $arquivo->store('boletos', 'public');
                } catch (\Exception $e) {
                    Log::error('Erro ao processar novo arquivo do boleto: ' . $e->getMessage());
                }
            }

            // Atualizar registro da multa
            DB::connection('pgsql')->table('motivo_multa')
                ->where('id_motivo_multa', $id)
                ->update($multaData);

            // Excluir detalhes antigos
            DB::connection('pgsql')->table('detalhe_multa')
                ->where('id_motivo_multa', $id)
                ->delete();

            // Inserir novos detalhes
            foreach ($detalhesMulta as $detalhe) {
                $detalheData = [
                    'data_inclusao' => now(),
                    'id_motivo_multa' => $id,
                    'prazo_indicacao_condutor' => $detalhe['prazo_indicacao_condutor'] ?? null,
                    'prazo_para_pagamento' => null,
                    'prazo_para_recurso' => null,
                    'data_envio_financeiro' => isset($detalhe['data_envio_financeiro']) && $detalhe['data_envio_financeiro'] ? Carbon::parse($detalhe['data_envio_financeiro']) : null,
                    'data_pagamento' => isset($detalhe['data_pagamento']) && $detalhe['data_pagamento'] ? Carbon::parse($detalhe['data_pagamento']) : null,
                    'data_recebimento_notificacao' => isset($detalhe['data_recebimento_notificacao']) && $detalhe['data_recebimento_notificacao'] ? Carbon::parse($detalhe['data_recebimento_notificacao']) : null,
                    'data_envio_departamento' => isset($detalhe['data_envio_departamento']) && $detalhe['data_envio_departamento'] ? Carbon::parse($detalhe['data_envio_departamento']) : null,
                    'data_indeferimento_recurso' => isset($detalhe['data_indeferimento_recurso']) && $detalhe['data_indeferimento_recurso'] ? Carbon::parse($detalhe['data_indeferimento_recurso']) : null,
                    'data_inicio_recurso' => isset($detalhe['data_inicio_recurso']) && $detalhe['data_inicio_recurso'] ? Carbon::parse($detalhe['data_inicio_recurso']) : null,
                    'responsavel_recurso' => $detalhe['responsavel_recurso'] ?? null
                ];

                DB::connection('pgsql')->table('detalhe_multa')->insert($detalheData);
            }

            DB::commit();

            // Limpar cache após alterações
            $this->clearRelatedCache();

            return redirect()->route('admin.multas.index')
                ->with('success', 'Multa atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ERRO AO ATUALIZAR MULTA: ' . $e->getMessage());
            Log::error('Detalhes do erro: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar multa: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Excluir detalhes da multa
            DB::connection('pgsql')->table('detalhe_multa')
                ->where('id_motivo_multa', $id)
                ->delete();

            // Obter dados da multa para exclusão dos arquivos
            $multa = Multa::select('id_motivo_multa', 'assinatura', 'aquivo_multa', 'arquivo_boleto')->find($id);
            if ($multa) {
                // Remover arquivos associados
                if ($multa->assinatura) {
                    try {
                        if (Storage::disk('public')->exists($multa->assinatura)) {
                            Storage::disk('public')->delete($multa->assinatura);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao excluir assinatura: ' . $e->getMessage());
                    }
                }

                if ($multa->aquivo_multa) {
                    try {
                        if (Storage::disk('public')->exists($multa->aquivo_multa)) {
                            Storage::disk('public')->delete($multa->aquivo_multa);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao excluir arquivo da multa: ' . $e->getMessage());
                    }
                }

                if ($multa->arquivo_boleto) {
                    try {
                        if (Storage::disk('public')->exists($multa->arquivo_boleto)) {
                            Storage::disk('public')->delete($multa->arquivo_boleto);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Erro ao excluir arquivo do boleto: ' . $e->getMessage());
                    }
                }
            }

            // Excluir multa
            DB::connection('pgsql')->table('motivo_multa')
                ->where('id_motivo_multa', $id)
                ->delete();

            DB::commit();

            // Limpar cache após exclusão
            $this->clearRelatedCache();

            return response()->json([
                'notification' => [
                    'title' => 'Multa excluída',
                    'type' => 'success',
                    'message' => 'Multa excluída com sucesso!'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possível excluir a Multa, pois existem registros dependentes';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $mensagem
                ]
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Validate the multa data.
     */
    protected function validateMultaData(Request $request)
    {
        return $request->validate([
            'descricao' => 'nullable|string',
            'id_orgao' => 'required',
            'id_classificacao_multa' => 'required',
            'valor_multa' => 'required',
            'arquivo_multa' => 'nullable|file',
            'id_veiculo' => 'required',
            'id_condutor' => 'nullable',
            'data_infracao' => 'nullable|date',
            'vencimento_multa' => 'nullable|date',
            'id_departamento' => 'nullable',
            'id_filial' => 'nullable',
            'auto_infracao' => 'nullable|string|max:50',
            'notificacao' => 'nullable|string',
            'situacao' => 'required|string',
            'responsabilidade' => 'nullable|string',
            'localizacao' => 'nullable|string|max:300',
            'id_municipio' => 'nullable',
            'debitar_condutor' => 'nullable|boolean',
            'parcelas' => 'nullable|integer',
            'data_envio_departamento' => 'nullable|date',
            'status_multa' => 'required|string',
            'data_envio_financeiro' => 'nullable|date',
            'id_departamento_responsaval' => 'nullable',
            'id_filial_responsaval' => 'nullable',
            'data_prazo_ident' => 'nullable|date',
            'arquivo_boleto' => 'nullable|file',
            'is_assinado' => 'nullable|boolean',
            'assinatura' => 'nullable|string',
            'detalheMultainput' => 'nullable|json'
        ]);
    }

    /**
     * Limpar todos os caches relacionados ao módulo
     */
    protected function clearRelatedCache()
    {
        Cache::forget('veiculos_ativos_index');
        Cache::forget('veiculos_ativos_form');
        Cache::forget('classificacao_multa');
        Cache::forget('tipo_orgao_sinistro');
        Cache::forget('municipios');
        Cache::forget('condutores');
        Cache::forget('filiais');
        Cache::forget('departamentos');
    }

    /**
     * Sanitize a string to a double.
     */
    protected function sanitizeToDouble($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remover qualquer caractere que não seja número, vírgula ou ponto
        $value = preg_replace('/[^\d,.]/', '', $value);

        // Substituir vírgula por ponto
        $value = str_replace(',', '.', $value);

        // Converter para double
        return (float) $value;
    }
}
