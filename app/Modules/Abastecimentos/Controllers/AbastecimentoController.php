<?php

namespace App\Modules\Abastecimentos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Abastecimentos\Models\Abastecimento;
use App\Modules\Abastecimentos\Models\Bomba;
use App\Modules\Sinistros\Models\DadosPessoalSinistro;
use App\Models\Departamento;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Modules\Sinistros\Models\FotosDocumentosSinistros;
use App\Modules\Sinistros\Models\HistoricoEventosSinistro;
use App\Models\Pessoal;
use App\Modules\Sinistros\Models\Sinistro;
use App\Models\TipoCategoria;
use App\Modules\Abastecimentos\Models\TipoCombustivel;
use App\Models\TipoMotivoSinistro;
use App\Models\TipoOcorrencia;
use App\Models\TipoOrgaoSinistro;
use App\Models\Veiculo;
use App\Traits\SanitizesMonetaryValues;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AbastecimentoController extends Controller
{
    use SanitizesMonetaryValues;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        ini_set('memory_limit', '512M');
        // $startTime = microtime(true); //código para ver a performance da tela e retorno do back

        $dadoabastecimentos = Abastecimento::with(['veiculo:id_veiculo,placa', 'filial:id,name', 'pessoal:id_pessoal,nome', 'fornecedor:id_fornecedor,nome_fornecedor', 'departamento:id_departamento,descricao_departamento', 'user:id,name', 'veiculo.tipoequipamento'])  // Carrega somente id_veiculo e nome_veiculo
            ->select(
                'id_abastecimento',
                'id_veiculo',
                'data_inclusao',
                'data_abastecimento',
                'id_filial',
                'numero_nota_fiscal',
                'id_fornecedor',
                'id_departamento'
            )->get();

        $abastecimentos = $dadoabastecimentos->map(function ($dadoabastecimentos) {

            return [
                'idabastecimento' => $dadoabastecimentos->id_abastecimento,
                'placa' => $dadoabastecimentos->id_veiculo ? $dadoabastecimentos->veiculo->placa : '',
                'datainclusao' => $dadoabastecimentos->data_inclusao ? format_date($dadoabastecimentos->data_inclusao, 'd/m/Y H:i') : '',
                'dataabastecimento' => $dadoabastecimentos->data_abastecimento ? format_date($dadoabastecimentos->data_abastecimento, 'd/m/Y H:i') : '',
                'numeronotafiscal' => $dadoabastecimentos->numero_nota_fiscal ? $dadoabastecimentos->numero_nota_fiscal : '',
                'fornecedor' => $dadoabastecimentos->id_fornecedor ? $dadoabastecimentos->fornecedor->nome_fornecedor : '',
                'departamento' => $dadoabastecimentos->id_departamento ? $dadoabastecimentos->departamento->descricao_departamento : '',
                'tipoequipamento' => $dadoabastecimentos->veiculo->id_tipo_equipamento ? $dadoabastecimentos->veiculo->tipoequipamento->descricao_tipo : '',
            ];
        })->toArray();

        // Captura o tempo final (após a execução do código)
        /*$endTime = microtime(true);

            // Calcula o tempo de execução
            $executionTime = $endTime - $startTime;

            dd($executionTime);*/ // código para ver a performance da tela e retorno do back

        return view('admin.abastecimentos.index', compact('dadoabastecimentos', 'abastecimentos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $formOptions = [
            'placas' => Veiculo::select('placa as label', 'id_veiculo as value')->where('situacao_veiculo', '!=', 'false')->orderBy('label')->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'pessoas' => Pessoal::select('nome as label', 'id_pessoal as value')->where('ativo', '!=', 'false')->orderBy('label')->get()->toArray(),
            'fornecedores' => Fornecedor::select('nome_fornecedor as label', 'id_fornecedor as value')->where('is_ativo', '!=', 'false')->orderBy('label')->get()->toArray(),
            'departamentos' => Departamento::select('descricao_departamento as label', 'id_departamento as value')->orderBy('label')->get()->toArray(),
            'tipocombustiveis' => TipoCombustivel::select('descricao as label', 'id_tipo_combustivel as value')->orderBy('label')->get()->toArray(),
            'bombas' => Bomba::select('descricao_bomba as label', 'id_bomba as value')->orderBy('descricao_bomba')->get()->toArray(),
        ];

        return view('admin.abastecimentos.create', compact('formOptions'));
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

        $sinistros_insert = $request->validate([
            'id_veiculo' => 'required|int',
            'id_filial' => 'required|int',
            'id_motorista' => 'required|int',
            'data_sinistro' => 'required|date',
            'situacao_sinistro_processo' => 'nullable|string|max:100',
            'responsabilidade_sinistro' => 'nullable|string|max:500',
            'id_tipo_orgao' => 'nullable|int',
            'numero_processo' => 'nullable|int',
            'local_ocorrencia' => 'nullable|string',
            'descricao_ocorrencia' => 'nullable|string',
            'observacao_ocorrencia' => 'nullable|string',
            'valor_apagar' => 'numeric',
            'valor_pago' => 'numeric',
            'id_tipo_ocorrencia' => 'nullable|int',
            'id_motivo' => 'nullable|int',
            'id_categoria_veiculo' => 'nullable|int',
            'valorpagoseguradora' => 'numeric',
            'valorpagofrota' => 'numeric',
            'situacao_pista' => 'nullable|string',
            'estados_pista' => 'nullable|string',
            'topografica' => 'nullable|string',
            'sinalizacao' => 'nullable|boolean',
            'status' => 'nullable|string',
            'setor' => 'nullable|string',
            'prazo_em_dias' => 'required|int',
            'valor_pago_terceiro' => 'numeric',
        ]);

        // Instância do modelo
        $sinistros = new Sinistro;

        // Atribuição dos campos ao modelo
        $sinistros->id_veiculo = $sinistros_insert['id_veiculo'];
        $sinistros->data_inclusao = now();
        $sinistros->id_filial = $sinistros_insert['id_filial'] ?? null;
        $sinistros->id_motorista = $sinistros_insert['id_motorista'] ?? null;
        $sinistros->data_sinistro = $sinistros_insert['data_sinistro'] ?? null;
        $sinistros->situacao_sinistro_processo = $sinistros_insert['situacao_sinistro_processo'];
        $sinistros->responsabilidade_sinistro = $sinistros_insert['responsabilidade_sinistro'] ?? null;
        $sinistros->id_tipo_orgao = $sinistros_insert['id_tipo_orgao'] ?? null;
        $sinistros->numero_processo = $sinistros_insert['numero_processo'] ?? null;
        $sinistros->local_ocorrencia = $sinistros_insert['local_ocorrencia'] ?? null;
        $sinistros->descricao_ocorrencia = $sinistros_insert['descricao_ocorrencia'] ?? null;
        $sinistros->observacao_ocorrencia = $sinistros_insert['observacao_ocorrencia'] ?? null;
        $sinistros->valor_apagar = $sinistros_insert['valor_apagar'] ?? null;
        $sinistros->valor_pago = $sinistros_insert['valor_pago'] ?? null;
        $sinistros->id_tipo_ocorrencia = $sinistros_insert['id_tipo_ocorrencia'] ?? null;
        $sinistros->id_motivo = $sinistros_insert['id_motivo'] ?? null;
        $sinistros->id_categoria_veiculo = $sinistros_insert['id_categoria_veiculo'] ?? null;
        $sinistros->valorpagoseguradora = $sinistros_insert['valorpagoseguradora'] ?? null;
        $sinistros->valorpagofrota = $sinistros_insert['valorpagofrota'] ?? null;
        $sinistros->situacao_pista = $sinistros_insert['situacao_pista'] ?? null;
        $sinistros->estados_pista = $sinistros_insert['estados_pista'] ?? null;
        $sinistros->topografica = $sinistros_insert['topografica'] ?? null;
        $sinistros->sinalizacao = $sinistros_insert['sinalizacao'] ?? null;
        $sinistros->status = $sinistros_insert['status'] ?? null;
        $sinistros->setor = $sinistros_insert['setor'] ?? null;
        $sinistros->prazo_em_dias = $sinistros_insert['prazo_em_dias'] ?? null;
        $sinistros->valor_pago_terceiro = $sinistros_insert['valor_pago_terceiro'] ?? null;

        // Salvar no banco
        $sinistros->save();

        $historico_insert = $request->validate([
            'data_evento' => 'required|date',
            'descricao_situacao' => 'required|string',
            'observacao' => 'nullable|string',
            'id_usuario' => 'nullable|int',
        ]);

        // $historico = new HistoricoEventosSinistro();

        //     $historico->data_inclusao              = now();
        //     $historico->id_sinistro                = $sinistros->id_sinistro;
        //     $historico->data_evento                = $historico_insert['data_evento'];
        //     $historico->id_usuario                 = Auth::user()->id;
        //     $historico->descricao_situacao         = $historico_insert['descricao_situacao'] ?? null;
        //     $historico->observacao                 = $historico_insert['observacao'] ?? null;

        // $historico->save();

        $documentos_insert = $request->validate([
            'documento' => 'required|string',
        ]);

        $documentos = new FotosDocumentosSinistros;

        $documentos->data_inclusao = now();
        $documentos->documento = $documentos_insert['documento'];
        $documentos->id_sinistro = $sinistros->id_sinistro;

        $documentos->save();

        $dados_envolvidos_insert = $request->validate([

            'nome_pessoal' => 'nullable|string',
            'telefone' => 'nullable|string',
            'cpf' => 'nullable|string',
        ]);

        $dadosenvolvidos = new DadosPessoalSinistro;

        $dadosenvolvidos->data_inclusao = now();
        $dadosenvolvidos->nome_pessoal = $dados_envolvidos_insert['nome_pessoal'];
        $dadosenvolvidos->telefone = $dados_envolvidos_insert['telefone'];
        $dadosenvolvidos->cpf = $dados_envolvidos_insert['cpf'];
        $dadosenvolvidos->id_sinistro = $sinistros->id_sinistro;

        $dadosenvolvidos->save();

        return redirect()
            ->route('admin.sinistros.index')
            ->withNotification([
                'title' => 'Sinistro criado',
                'type' => 'success',
                'message' => 'Sinistro criado com sucesso!',
            ]);
    }

    public function storeHistorico(Request $request)
    {
        try {
            DB::beginTransaction();

            $registros = $request->input('registros');

            foreach ($registros as $registro) {
                $historico = new HistoricoEventosSinistro;

                $historico->data_inclusao = now();
                $historico->id_sinistro = $registro['id_sinistro'];
                $historico->data_evento = $registro['data_evento'];
                $historico->id_usuario = $registro['id_usuario'];
                $historico->descricao_situacao = $registro['descricao_situacao'] ?? null;
                $historico->observacao = $registro['observacao'] ?? null;

                $historico->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'Registros salvos com sucesso!',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Erro ao salvar os registros: '.$e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sinistro $sinistro)
    {
        $sinistro = Sinistro::findOrFail($sinistro);

        return view('admin.sinistros.show', compact('sinistros'));
    }

    public function edit(Sinistro $sinistro)
    {
        $formOptions = [
            'placas' => Veiculo::select('placa as label', 'id_veiculo as value')->where('situacao_veiculo', '!=', 'false')->orderBy('label')->get()->toArray(),
            'filiais' => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'categorias' => TipoCategoria::select('descricao_categoria as label', 'id_categoria as value')->orderBy('label')->get()->toArray(),
            'tiposOrgaos' => TipoOrgaoSinistro::select('descricao_tipo_orgao as label', 'id_tipo_orgao as value')->orderBy('label')->get()->toArray(),
            'pessoas' => Pessoal::select('nome as label', 'id_pessoal as value')->where('ativo', '!=', 'false')->orderBy('label')->get()->toArray(),
            'tipoocorrencias' => TipoOcorrencia::select('descricao_ocorrencia as label', 'id_tipo_ocorrencia as value')->orderBy('label')->get()->toArray(),
            'tipomotivos' => TipoMotivoSinistro::select('descricao_motivo as label', 'id_motivo_cinistro as value')->orderBy('label')->get()->toArray(),
        ];

        $historicosinistro = HistoricoEventosSinistro::where('id_sinistro', $sinistro->id_sinistro)->first();
        $fotosSinistro = FotosDocumentosSinistros::where('id_sinistro', $sinistro->id_sinistro)->first();
        $dadosSinistros = DadosPessoalSinistro::where('id_sinistro', $sinistro->id_sinistro)->first();

        return view('admin.sinistros.edit', compact('sinistro', 'historicosinistro', 'fotosSinistro', 'dadosSinistros', 'formOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sinistro $sinistro)
    {
        try {
            $this->sanitizeMonetaryValues($request, [
                'valor_apagar',
                'valor_pago',
                'valorpagoseguradora',
                'valorpagofrota',
                'valor_pago_terceiro',
            ]);

            $sinistros_insert = $request->validate([
                'id_veiculo' => 'nullable|int',
                'id_filial' => 'nullable|int',
                'id_motorista' => 'nullable|int',
                'data_sinistro' => 'nullable|date',
                'situacao_sinistro_processo' => 'nullable|string|max:100',
                'responsabilidade_sinistro' => 'nullable|string|max:500',
                'id_tipo_orgao' => 'nullable|int',
                'numero_processo' => 'nullable|int',
                'local_ocorrencia' => 'nullable|string',
                'descricao_ocorrencia' => 'nullable|string',
                'observacao_ocorrencia' => 'nullable|string',
                'valor_apagar' => 'numeric',
                'valor_pago' => 'numeric',
                'id_tipo_ocorrencia' => 'nullable|int',
                'id_motivo' => 'nullable|int',
                'id_categoria_veiculo' => 'nullable|int',
                'valorpagoseguradora' => 'numeric',
                'valorpagofrota' => 'numeric',
                'situacao_pista' => 'nullable|string',
                'estados_pista' => 'nullable|string',
                'topografica' => 'nullable|string',
                'sinalizacao' => 'nullable|boolean',
                'status' => 'nullable|string',
                'setor' => 'nullable|string',
                'prazo_em_dias' => 'nullable|int',
                'valor_pago_terceiro' => 'numeric',
            ]);

            $sinistros_insert['data_alteracao'] = now();

            $historico_insert = $request->validate([
                'data_evento' => 'required|date',
                'descricao_situacao' => 'required|string',
                'observacao' => 'nullable|string',
                'id_usuario' => 'nullable|int',
            ]);

            $historico_insert['data_alteracao'] = now();

            $historico = HistoricoEventosSinistro::where('id_sinistro', $sinistro->id_sinistro)->first();

            $dados_envolvidos_insert = $request->validate([
                'nome_pessoal' => 'nullable|string',
                'telefone' => 'nullable|string',
                'cpf' => 'nullable|string',
            ]);

            $dados_envolvidos_insert['data_alteracao'] = now();

            $dadosenvolvidos = DadosPessoalSinistro::where('id_sinistro', $sinistro->id_sinistro)->first();

            $documentos_insert = $request->validate([
                'documento' => 'required|string',
            ]);

            $documentos_insert['data_alteracao'] = now();

            $fotos = FotosDocumentosSinistros::where('id_sinistro', $sinistro->id_sinistro)->first();

            $sinistro->update($sinistros_insert);

            if (isset($historico)) {
                $historico->update($historico_insert);
            }
            if (isset($dadosenvolvidos)) {
                $dadosenvolvidos->update($dados_envolvidos_insert);
            }
            if (isset($fotos)) {
                $fotos->update($documentos_insert);
            }

            return redirect()->route('admin.sinistros.index')
                ->with('success', 'Sinistro atualizado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possivel editar o sinistro';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $mensagem,
                ],
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function destroy(Sinistro $sinistro)
    {
        try {
            DB::beginTransaction();
            $historico = HistoricoEventosSinistro::where('id_sinistro', $sinistro)->first();
            $historico->delete();

            $fotos = FotosDocumentosSinistros::where('id_sinistro', $sinistro)->first();
            $fotos->delete();

            $dadospessoal = DadosPessoalSinistro::where('id_sinistro', $sinistro);
            $dadospessoal->delete();

            $sinistrosdelet = Sinistro::findOrFail($sinistro);
            $sinistrosdelet->delete();
            DB::commit();

            return response()->json([
                'notification' => [
                    'title' => 'Sinistro excluído',
                    'type' => 'success',
                    'message' => 'Sinistro excluído com sucesso',
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $errorCode = $e->getCode();
            $mensagem = $e->getMessage();

            if ($errorCode == 23503) {
                $mensagem = 'Não foi possivel excluir o sinistro, pois ele possui dependentes';
            }

            return response()->json([
                'notification' => [
                    'title' => 'Erro',
                    'type' => 'error',
                    'message' => $mensagem,
                ],
            ], 500, [], JSON_UNESCAPED_UNICODE); // JSON_UNESCAPED_UNICODE para retorntar os acentos
        }
    }
}
