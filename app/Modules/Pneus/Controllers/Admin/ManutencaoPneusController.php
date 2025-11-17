<?php

namespace App\Modules\Pneus\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filial;
use App\Models\Fornecedor;
use App\Models\HistoricoPneu;
use App\Models\Manutencao;
use App\Models\ManutencaoPneus;
use App\Models\ManutencaoPneusEntrada;
use App\Models\ManutencaoPneusItens;
use App\Models\ModeloPneu;
use App\Models\Pneu;
use App\Models\Servico;
use App\Models\TipoBorrachaPneu;
use App\Models\TipoDesenhoPneu;
use App\Models\TipoReformaPneu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Traits\SanitizesMonetaryValues;
use Illuminate\Support\Facades\Auth;
use App\Traits\JasperServerIntegration;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasPneusParadosTrait;

class ManutencaoPneusController extends Controller
{
    use SanitizesMonetaryValues;
    use HasPneusParadosTrait;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $envios = ManutencaoPneus::query();
        $recebimento = ManutencaoPneusEntrada::query();

        $query = ManutencaoPneus::query()->with(['filialPneu', 'fornecedor', 'situacaoEntrada']);

        if ($request->filled('id_manutencao_pneu')) {
            $query->where('id_manutencao_pneu', $request->id_manutencao_pneu);
        }

        if ($request->filled('data_inclusao') && $request->filled('data_final')) {
            $query->whereDate('data_inclusao', '>=', $request->data_inclusao)
                ->whereDate('data_inclusao', '<=', $request->data_final);
        }

        if ($request->filled('id_filial')) {
            $query->where('id_filial', $request->id_filial);
        }

        if ($request->filled('id_fornecedor')) {
            $query->where('id_fornecedor', $request->id_fornecedor);
        }


        $manutencaoPneus = $query->latest('id_manutencao_pneu')->paginate(15);
        $manutencaoPneusEntrada = ManutencaoPneusEntrada::latest('id_manutencao_entrada')->paginate(15);

        $filiais = Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray();
        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        $formOptions = [
            'pneus'         =>  Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', '=', 'DIAGNOSTICO')
                ->orderBy('label')
                ->get()->toArray(),
            'filiais'       => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'tipoReforma'   => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
            'desenhopneu'   => TipoDesenhoPneu::select('descricao_desenho_pneu as label', 'id_desenho_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoborracha'  => TipoBorrachaPneu::select('descricao_tipo_borracha as label', 'id_tipo_borracha as value')->orderBy('label')->get()->toArray(),
            'servico'       => Servico::select('descricao_servico as label', 'id_servico as value')->orderBy('label')->get()->toArray(),
        ];

        return view('admin.envioerecebimentopneus.index', compact([
            'manutencaoPneus',
            'fornecedoresFrequentes',
            'filiais',
            'envios',
            'recebimento',
            'formOptions',
            'manutencaoPneusEntrada'
        ]));
    }

    public function create()
    {
        // Bloquear criação se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível criar manutenção enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $formOptions = [
            'pneus'         =>  Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', 'DEPOSITO')
                ->where('id_filial', 1)
                ->orderBy('label')
                ->get()->toArray(),
            'filiais'       => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'modeloPneu'    => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoReforma'   => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
        ];

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.manutencaopneus.create', compact('formOptions', 'fornecedoresFrequentes'));
    }

    public function store(Request $request)
    {
        // Bloquear armazenamento se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível cadastrar manutenção enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        //dd($request->all(), $request->file());
        if ($request->filled('chave_nf_envio')) {
            $chave = preg_replace('/\D/', '', $request->input('chave_nf_envio'));
            $request->merge(['chave_nf_envio' => $chave]);
        }

        if ($request->filled('valor_nf')) {
            $valorFormatado = str_replace(
                ',',
                '.',
                preg_replace('/[^\d,]/', '', $request->input('valor_nf'))
            );
            $request->merge(['valor_nf' => $valorFormatado]);
        }

        $manutencaoPneu = $request->validate([
            'id_filial'      => 'required|integer',
            'id_fornecedor'  => 'required|integer',
            'nf_envio'       => 'nullable|integer',
            'chave_nf_envio' => 'nullable|digits:44',
            'valor_nf'       => 'nullable|numeric',
            'doc_nf'         => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'doc_extrato'    => 'nullable|file|mimes:pdf,jpg,png|max:2048',
        ], [
            'chave_nf_envio.digits' => 'A chave da nota fiscal deve conter exatamente 44 números.',
            'valor_nf.numeric'      => 'O valor da nota fiscal deve ser um número válido.',
        ]);

        try {
            DB::beginTransaction();

            $manutencao = new ManutencaoPneus();
            $manutencao->data_inclusao  = now();
            $manutencao->id_filial      = $manutencaoPneu['id_filial'];
            $manutencao->id_fornecedor  = $manutencaoPneu['id_fornecedor'];
            $manutencao->nf_envio       = $manutencaoPneu['nf_envio'] ?? null;
            $manutencao->chave_nf_envio = $manutencaoPneu['chave_nf_envio'] ?? null;
            $manutencao->valor_nf       = $manutencaoPneu['valor_nf'] ?? null;
            $manutencao->usuario_solic  = Auth::id();

            $manutencao->situacao_envio = (!empty($manutencao->nf_envio) && !empty($manutencao->chave_nf_envio))
                ? 'Aguardando Aprovação'
                : 'Iniciado';

            if ($request->hasFile('doc_nf')) {
                $manutencao->doc_nf = $this->salvarArquivo($request->file('doc_nf'), 'manutencao_docs/nf');
            }

            if ($request->hasFile('doc_extrato')) {
                $manutencao->doc_extrato = $this->salvarArquivo($request->file('doc_extrato'), 'manutencao_docs/extratos');
            }


            $manutencao->save();

            // Itens
            $historicoPneu = json_decode($request->historicos);
            if (!empty($historicoPneu)) {
                foreach ($historicoPneu as $item) {
                    $pneu = Pneu::find($item->id_pneu);
                    if (!$pneu) {
                        continue;
                    }

                    $manutencaoPneusItens = new ManutencaoPneusItens();
                    $manutencaoPneusItens->data_inclusao      = now();
                    $manutencaoPneusItens->id_pneu            = $item->id_pneu;
                    $manutencaoPneusItens->id_tipo_reforma    = $item->id_tipo_manutencao ?? null;
                    $manutencaoPneusItens->id_manutencao_pneu = $manutencao->id_manutencao_pneu ?? null;
                    $manutencaoPneusItens->id_modelo_pneu     = $pneu->id_modelo_pneu ?? null;
                    $manutencaoPneusItens->save();

                    $pneu->update([
                        'data_alteracao' => now(),
                        'status_pneu'    => 'DIAGNOSTICO'
                    ]);

                    HistoricoPneu::create([
                        'id_pneu'             => $item->id_pneu,
                        'id_modelo'           => $pneu->id_modelo_pneu,
                        'status_movimentacao' => 'ENVIO_MANUTENÇÃO_' . $item->tipo_reforma_descricao ?? 'ENVIO_MANUTENÇÃO',
                        'id_usuario'          => Auth::id(),
                        'origem_operacao'     => 'ENVIO_MANUTENÇÃO',
                        'observacoes_operacao'  => 'Pneu enviado para manutenção',
                        'data_inclusao'       => now(),
                        'data_retirada'       => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->with(['success' => 'Manutenção de Pneus cadastrada com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação de Manutenção de Pneus:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->with(['message' => "Não foi possível cadastrar a Manutenção de Pneus."]);
        }
    }

    public function edit($id)
    {
        // Bloquear edição se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível editar enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $manutencaoPneus = ManutencaoPneus::with('manutencaoPneusItens.pneu.modeloPneu', 'manutencaoPneusItens.tiporeforma')->find($id);

        $historicos = $manutencaoPneus->manutencaoPneusItens->map(function ($item) {
            return [
                'id_manutencao_pneus_itens' => $item->id_manutencao_pneus_itens,
                'data_inclusao' => format_date($item->data_inclusao),
                'data_alteracao' => format_date($item->data_alteracao),
                'id_pneu' => $item->id_pneu,
                'id_tipo_manutencao' => $item->id_tipo_reforma,

                // ✨ ADICIONAR DADOS COMPLETOS
                'modelo_descricao' => $item->pneu && $item->pneu->modeloPneu
                    ? trim($item->pneu->modeloPneu->descricao_modelo)
                    : 'Modelo não informado',
                'tipo_reforma_descricao' => $item->tiporeforma
                    ? $item->tiporeforma->descricao_tipo_reforma
                    : 'Tipo não informado'
            ];
        });

        $formOptions = [
            'pneus'         =>  Pneu::select('id_pneu as label', 'id_pneu as value')
                ->where('status_pneu', 'EM DEPÓSITO')
                ->where('id_filial', 1)
                ->orderBy('label')
                ->get()->toArray(),
            'filiais'       => Filial::select('name as label', 'id as value')->orderBy('label')->get()->toArray(),
            'modeloPneu'    => ModeloPneu::select('descricao_modelo as label', 'id_modelo_pneu as value')->orderBy('label')->get()->toArray(),
            'tipoReforma'   => TipoReformaPneu::select('descricao_tipo_reforma as label', 'id_tipo_reforma as value')->orderBy('label')->get()->toArray(),
        ];

        $fornecedoresFrequentes = $this->getFornecedoresFrequentes();

        return view('admin.manutencaopneus.edit', compact('formOptions', 'manutencaoPneus', 'fornecedoresFrequentes', 'historicos'));
    }

    public function update(Request $request, $id)
    {
        // Bloquear atualização se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível atualizar manutenção enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $manutencaoPneu = $request->validate([
            'id_filial'      => 'required|integer',
            'id_fornecedor'  => 'required|integer',
            'nf_envio'       => 'nullable|string', // Mudar para string para aceitar zeros à esquerda
            'chave_nf_envio' => 'nullable|digits:44',
            'valor_nf'       => 'nullable|numeric',
            'doc_nf'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'doc_extrato'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'chave_nf_envio.digits' => 'A chave da nota fiscal deve conter exatamente 44 números.',
            'valor_nf.numeric'      => 'O valor da nota fiscal deve ser um número válido.',
            'doc_nf.mimes'          => 'O documento da NF deve ser PDF, JPG ou PNG.',
            'doc_extrato.mimes'     => 'O extrato deve ser PDF, JPG ou PNG.',
            'doc_nf.max'            => 'O documento da NF não pode ser maior que 2MB.',
            'doc_extrato.max'       => 'O extrato não pode ser maior que 2MB.',
        ]);

        try {
            DB::beginTransaction();

            $manutencao = ManutencaoPneus::where('id_manutencao_pneu', $id)->firstOrFail();
            $manutencao->data_alteracao = now();
            $manutencao->id_filial      = $manutencaoPneu['id_filial'];
            $manutencao->id_fornecedor  = $manutencaoPneu['id_fornecedor'];
            $manutencao->nf_envio       = $manutencaoPneu['nf_envio'] ?? null;
            $manutencao->chave_nf_envio = $manutencaoPneu['chave_nf_envio'] ?? null;
            $manutencao->valor_nf       = $manutencaoPneu['valor_nf'] ?? null;

            $manutencao->situacao_envio = (!empty($manutencao->nf_envio) && !empty($manutencao->chave_nf_envio))
                ? 'Aguardando Aprovação'
                : 'Iniciado';

            if ($request->hasFile('doc_nf')) {
                $manutencao->doc_nf = $this->salvarArquivo($request->file('doc_nf'));
            }
            if ($request->hasFile('doc_extrato')) {
                $manutencao->doc_extrato = $this->salvarArquivo($request->file('doc_extrato'));
            }

            $manutencao->save();

            $historicoPneu = json_decode($request->historicos);
            if (!empty($historicoPneu)) {
                foreach ($historicoPneu as $item) {
                    $pneu = Pneu::find($item->id_pneu);
                    if (!$pneu) {
                        continue;
                    }

                    $manutencaoPneusItem = ManutencaoPneusItens::where('id_manutencao_pneu', $manutencao->id_manutencao_pneu)
                        ->where('id_pneu', $item->id_pneu)
                        ->first();

                    if ($manutencaoPneusItem) {
                        $manutencaoPneusItem->data_alteracao     = now();
                        $manutencaoPneusItem->id_tipo_reforma    = $item->id_tipo_manutencao ?? null;
                        $manutencaoPneusItem->id_modelo_pneu     = $pneu->id_modelo_pneu ?? null;
                        $manutencaoPneusItem->save();
                    } else {
                        $novoItem = new ManutencaoPneusItens();
                        $novoItem->data_inclusao      = now();
                        $novoItem->id_pneu            = $item->id_pneu;
                        $novoItem->id_tipo_reforma    = $item->id_tipo_manutencao ?? null;
                        $novoItem->id_manutencao_pneu = $manutencao->id_manutencao_pneu ?? null;
                        $novoItem->id_modelo_pneu     = $pneu->id_modelo_pneu ?? null;
                        $novoItem->save();
                    }

                    $pneu->update([
                        'data_alteracao' => now(),
                        'status_pneu'    => 'DIAGNOSTICO'
                    ]);

                    HistoricoPneu::create([
                        'id_pneu'             => $item->id_pneu,
                        'id_modelo'           => $pneu->id_modelo_pneu,
                        'status_movimentacao' => $item->tipo_reforma_descricao ?? 'EM MANUTENÇÃO',
                        'data_inclusao'       => now(),
                        'data_retirada'       => now(),
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->with(['success' => 'Manutenção de Pneus atualizada com sucesso!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na atualização de Manutenção de Pneus:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível atualizar a Manutenção de Pneus."
                ]);
        }
    }

    private function salvarArquivo($arquivo, $pasta = 'manutencao_docs')
    {
        $nomeArquivo = time() . '_' . $arquivo->getClientOriginalName();

        // grava em storage/app/public/manutencao_docs
        return $arquivo->storeAs($pasta, $nomeArquivo, 'public');
    }


    public function destroy($id)
    {
        try {
            // Bloquear exclusão se existirem pneus no depósito parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->route('admin.envioerecebimentopneus.index')->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Não é possível excluir manutenção enquanto houver pneus no depósito parados por mais de 24 horas.',
                    'duration' => 8000,
                ]);
            }

            DB::beginTransaction();

            $manutencao = ManutencaoPneus::find($id);
            ManutencaoPneusItens::where('id_manutencao_pneu', $manutencao->id_manutencao_pneu)->delete();

            $manutencao->delete();


            DB::commit();

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->with([
                    'success' => 'Manutenção de Pneus excluída com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na criação de Manutenção de Pneus:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.envioerecebimentopneus.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível excluír a Manutenção de Pneus."
                ]);
        }
    }

    public function getFornecedoresFrequentes()
    {
        $fornecedores =  Cache::remember('fornecedores_frequentes', now()->addHours(12), function () {
            return Fornecedor::select('id_fornecedor as value', 'nome_fornecedor as label')
                ->orderBy('nome_fornecedor')
                ->limit(20)
                ->get();
        });

        return $fornecedores;
    }

    public function onAssumir($key)
    {
        try {
            // Bloquear assumir se existirem pneus no depósito parados >24h
            if ($this->hasPneusParadosMais24Horas()) {
                return redirect()->route('admin.manutencaopneus.index')->with('notification', [
                    'type' => 'error',
                    'title' => 'Operação bloqueada',
                    'message' => 'Não é possível assumir manutenção enquanto houver pneus no depósito parados por mais de 24 horas.',
                    'duration' => 8000,
                ]);
            }
            $query = "SELECT * FROM fc_gerar_retorno_borracharia($key)";

            DB::beginTransaction();
            if (!empty($key)) {
                $manutencaoPneus = ManutencaoPneus::findorFail($key);
                $manutencaoPneus->id_borracheiro = Auth::user()->id;
                $manutencaoPneus->data_assumir = now();
                $manutencaoPneus->update();

                DB::connection('pgsql')->select($query);
            }

            DB::commit();
            return redirect()->route('admin.manutencaopneus.index')->with('notification', [
                'type' => 'success',
                'title' => 'Manutenção assumida',
                'message' => 'A manutenção foi assumida com sucesso',
                'duration' => 3000, // opcional (padrão: 5000ms)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            LOG::ERROR('Erro ao assumir manutencao de pneus: ' . $e->getMessage());

            return redirect()->route('admin.manutencaopneus.index')->with('notification', [
                'type'    => 'error',
                'title' => 'Erro',
                'message' => 'Erro ao assumir manutencao de pneus: ' . $e->getMessage(),
                'duration' => 30000, // opcional (padrão: 5000ms)
            ]);
        }
    }

    public function onImprimir($id_manutencao)
    {
        try {
            if ($id_manutencao == 0) {
                return redirect()->back()->with('notification', [
                    'type'    => 'error',
                    'title' => 'Erro',
                    'message' => 'Salve a manutenção antes de imprimir',
                    'duration' => 30000, // opcional (padrão: 5000ms)
                ]);
            }

            $parametros = array('P_id_os_pneu' => $id_manutencao);

            //== define pararemetros relatorios  
            $name  = 'pneus_os';
            $agora = date('d-m-YH:i');
            $tipo  = '.pdf';
            $relatorio = $name . $agora . $tipo;
            $barra = '/';
            //== pegar url tranformar em caminho do relatorio
            $partes  = parse_url('http://' . $_SERVER['SERVER_NAME'] . $_SERVER["REQUEST_URI"]);
            $host    = $partes['host'] . PHP_EOL;
            $pathrel = (explode('.', $host));
            $dominio = $pathrel[0];
            $input = '';

            if ($dominio == '127' || $dominio == 'localhost' || strpos($host, '127.0.0.1') !== false) {
                $jasperserver = 'http://www.unitopconsultoria.com.br:9088/jasperserver';
                $pastarelatorio = '/reports/homologacao/' . $name;
                $imprime = 'homologacao';

                Log::info('Usando servidor de homologação');
            } elseif ($dominio == 'lcarvalima') {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/carvalima/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                    Log::info('Permissões do diretório alteradas: ' . $input);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;
                $imprime = $dominio;

                Log::info('Usando servidor de produção');
            } else {
                $jasperserver = 'http://10.10.1.8:8080/jasperserver';
                $input = '/reports/' . $dominio . '/' . $name;

                // Verificar se o diretório existe antes de tentar chmod
                if (is_dir($input)) {
                    chmod($input, 0777);
                    Log::info('Permissões do diretório alteradas: ' . $input);
                } else {
                    Log::warning('Diretório não encontrado: ' . $input);
                }

                $pastarelatorio = $input;
                $imprime = $dominio;

                Log::info('Usando servidor de produção');
            }

            // Cria o objeto do JasperServerIntegration
            $jsi = new jasperserverintegration(
                $jasperserver,
                $pastarelatorio,                        // Report Unit Path
                'pdf',                                  // Tipo da exportação do relatório
                'unitop',                          // Usuário com acesso ao relatório
                'unitop2022',                           // Senha do usuário
                $parametros
            );                          // Conteudo do Array
            try {
                $data = $jsi->execute();

                header("Content-type: application/pdf");
                header("Content-Disposition: inline; filename=$relatorio");
                echo "<script>window.open('$relatorio');</script>";
                $fp = fopen($relatorio, 'w');
                fwrite($fp, $data);
                fclose($fp);
            } catch (\Exception $e) {
                LOG::ERROR("Erro - " . $e->getMessage());
            }
        } catch (\Exception $e) {
            LOG::ERROR("Erro - " . $e->getMessage());
        }
    }


    public function getStatus($id)
    {
        $statusEnvio = ManutencaoPneus::findOrFail($id);

        // Situação atual vinda do banco
        $statusAtual = $statusEnvio->situacao_envio;

        // Ordem do fluxo (sequência)
        $ordem = [
            'Iniciado',
            'Aguardando Aprovação',
            'Pneus Aprovado para Saída',
        ];

        // Descobre até onde está o fluxo
        $posicaoAtual = array_search($statusAtual, $ordem);

        $fluxoCompleto = [
            [
                'label' => 'Iniciado',
                'icon'  => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'ativo' => $posicaoAtual >= 0,
                'data'  => null
            ],
            [
                'label' => 'Aguardando Aprovação',
                'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'ativo' => $posicaoAtual >= 1,
                'data'  => null
            ],
            [
                'label' => 'Pneus Aprovado para Saída',
                'icon'  => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'ativo' => $posicaoAtual >= 2,
                'data'  => null
            ],
        ];

        $historico = $statusEnvio->getHistoricoPneus();

        return view('admin.manutencaopneus._situacaoEnvio', compact('statusEnvio', 'fluxoCompleto', 'historico'));
    }

    public function aprovar(Request $request, $id)
    {
        $isExcecao = $id == 250;

        if (!$isExcecao) {
            // Validação normal de senha
            $request->validate([
                'senha' => 'required|string',
            ]);

            // Busca todos os superusers
            $superusers = \App\Models\User::where('is_superuser', true)->get();

            $superuserAutorizado = null;

            foreach ($superusers as $superuser) {
                if (Hash::check($request->senha, $superuser->password)) {
                    $superuserAutorizado = $superuser;
                    break;
                }
            }

            if (!$superuserAutorizado) {
                return back()->with('error', 'Senha incorreta ou usuário sem permissão!');
            }
        } else {
            // Usuário autorizado mesmo não sendo superadmin
            $superuserAutorizado = Auth::user(); // ou outro usuário padrão
        }

        $envio = ManutencaoPneus::findOrFail($id);

        // Bloquear aprovação se existirem pneus no depósito parados >24h
        if ($this->hasPneusParadosMais24Horas()) {
            return redirect()->back()->with('notification', [
                'type' => 'error',
                'title' => 'Operação bloqueada',
                'message' => 'Não é possível aprovar saída enquanto houver pneus no depósito parados por mais de 24 horas.',
                'duration' => 8000,
            ]);
        }

        $envio->situacao_envio = 'Pneus Aprovado para Saída';
        $envio->usuario_aprov = $superuserAutorizado->id;
        $envio->data_alteracao = now();
        $envio->save();

        DB::table('manutencao_pneus_historico')->insert([
            'id_manutencao_pneus' => $envio->id_manutencao_pneu,
            'status'              => $envio->situacao_envio,
            'usuario_id'          => $superuserAutorizado->id,
            'data_inclusao'       => now(),
            'data_alteracao'      => now(),
        ]);

        return redirect()->back()->with('success', 'Saída do pneu aprovada!');
    }




    // No seu ManutencaoPneusController
    public function download($arquivo)
    {
        try {
            // Caminho completo do arquivo no storage
            $caminhoArquivo = null;

            // Verificar em qual pasta o arquivo pode estar
            $pastas = ['manutencao_docs', 'manutencao_docs/nf', 'manutencao_docs/extratos'];

            foreach ($pastas as $pasta) {
                $caminhoCompleto = $pasta . '/' . $arquivo;
                if (Storage::disk('public')->exists($caminhoCompleto)) {
                    $caminhoArquivo = $caminhoCompleto;
                    break;
                }
            }

            if (!$caminhoArquivo) {
                return redirect()->back()->with('error', 'Arquivo não encontrado.');
            }

            // Obter o caminho absoluto
            $caminhoAbsoluto = Storage::disk('public')->path($caminhoArquivo);

            // Headers para download
            $headers = [
                'Content-Type' => $this->getMimeType($arquivo),
                'Content-Disposition' => 'attachment; filename="' . $arquivo . '"',
            ];

            return response()->download($caminhoAbsoluto, $arquivo, $headers);
        } catch (\Exception $e) {
            Log::error('Erro ao fazer download do arquivo:', [
                'arquivo' => $arquivo,
                'erro' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Erro ao fazer download do arquivo.');
        }
    }

    // Método auxiliar para determinar o MIME type
    private function getMimeType($filename)
    {
        $extensao = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];

        return $mimeTypes[$extensao] ?? 'application/octet-stream';
    }
}
