<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriaServico;
use App\Models\GrupoServico;
use App\Models\Manutencao;
use App\Models\PecasServicos;
use App\Models\Produto;
use App\Models\Servico;
use App\Models\TipoCategoria;
use App\Models\Filial;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManutencaoServicoController extends Controller
{
    public function index(Request $request)
    {
        $query = Servico::query();


        if ($request->filled('id_servico')) {
            $query->where('id_servico', $request->id_servico);
        }


        $manutencaoServico = $query->latest('id_servico')
            ->paginate(10)
            ->withQueryString();

        // dd($referenceDatas);
        return view('admin.manutencaoservicos.index', array_merge(
            [
                'manutencaoServico'         => $manutencaoServico,
            ]
        ));
    }

    public function create()
    {
        $filiais = Filial::orderBy('name')->get();
        $grupoServico = GrupoServico::orderBy('descricao_grupo')->get();
        $manutencoes = Manutencao::orderBy('id_manutencao')->get();
        $tipoCategoria = TipoCategoria::orderBy('descricao_categoria')->get();
        $produtos = Produto::orderBy('descricao_produto')->limit(100)->get();

        return view('admin.manutencaoservicos.create', compact(
            'filiais',
            'grupoServico',
            'manutencoes',
            'tipoCategoria',
            'produtos',
        ));
    }

    public function edit($id)
    {
        $filiais = Filial::orderBy('name')->get();
        $grupoServico = GrupoServico::orderBy('descricao_grupo')->get();
        $manutencoes = Manutencao::orderBy('id_manutencao')->get();
        $tipoCategoria = TipoCategoria::select('id_categoria as value', 'descricao_categoria as label')->orderBy('descricao_categoria')->get();
        $produtos = Produto::select('id_produto as value', 'descricao_produto as label')->orderBy('descricao_produto')->limit(100)->get();

        $manutencaoConfig = Servico::with(['categorias', 'pecas.produto'])
            ->where('id_servico', $id)
            ->firstOrFail();

        // Mapeia as peças já formatadas para o front
        $pecasFormatadas = $manutencaoConfig->pecas->map(function ($p) {
            return [
                'id_produto' => $p->produto->id_produto ?? '',
                'descricao_produto' => $p->produto->descricao_produto ?? '',
                'data_inclusao' => $p->data_inclusao ?? '',
                'data_alteracao' => $p->data_alteracao ?? '',
            ];
        });


        return view('admin.manutencaoservicos.edit', compact(
            'filiais',
            'grupoServico',
            'manutencoes',
            'tipoCategoria',
            'produtos',
            'manutencaoConfig',
            'pecasFormatadas'

        ));
    }

    public function store(Request $request)
    {
        $servicoCategoria = $request->validate([
            'id_filial'         => 'required|string',
            'id_grupo'          => 'required|string',
            'descricao_servico' => 'required|string',
            'ativo_servico'     => 'required|string',
            'id_manutencao'     => 'nullable|string',
            'hora_servico'      => 'nullable|string',
            'auxiliar'          => 'nullable|string',
        ]);

        $servicoCategoria['data_inclusao'] = now();

        $servicos = json_decode($request->input('servicos'), true);
        $pecas = json_decode($request->input('pecas'), true);

        try {
            DB::beginTransaction();

            // Se tiver categorias, define a primeira como a principal
            if (!empty($servicos)) {
                $servicoCategoria['id_categoria'] = $servicos[0]['id_categoria'];
            }

            // Cria o serviço principal
            $servicoCreate = Servico::create($servicoCategoria);

            // Cria as categorias associadas
            foreach ($servicos as $servico) {
                CategoriaServico::create([
                    'data_inclusao'         => now(),
                    'id_categoria'          => $servico['id_categoria'],
                    'descricao_categoria'   => $servico['descricao_categoria'],
                    'id_servico'            => $servicoCreate->id_servico,
                ]);
            }

            // Cria as peças associadas
            foreach ($pecas as $peca) {
                PecasServicos::create([
                    'data_inclusao'         => now(),
                    'id_peca'               => $peca['id_produto'],
                    'id_servico'            => $servicoCreate->id_servico,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.manutencaoservicos.index')
                ->with('success', 'Manutenção de Serviço Criado com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar serviço', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.manutencaoservicos.index')
                ->with('error', 'Não foi possível criar Manutenção!');
        }
    }


    public function update(Request $request, $id)
    {
        $servicoCategoria = $request->validate([
            'id_filial'         => 'required|string',
            'id_grupo'          => 'required|string',
            'descricao_servico' => 'required|string',
            'ativo_servico'     => 'required|string',
            'id_manutencao'     => 'nullable|string',
            'hora_servico'      => 'nullable|string',
            'auxiliar'          => 'nullable|string',
        ]);
        $servicoCategoria['data_alteracao'] = now();

        $servicos = json_decode($request->input('servicos'), true);
        $pecas = json_decode($request->input('pecas'), true);

        try {
            DB::beginTransaction();

            $servicoCreate = Servico::where('id_servico', $id)->firstOrFail();

            // Atualiza o id_categoria com base na primeira categoria enviada
            if (!empty($servicos)) {
                $servicoCategoria['id_categoria'] = $servicos[0]['id_categoria'];
            }

            $servicoCreate->update($servicoCategoria);

            CategoriaServico::where('id_servico', $servicoCreate->id_servico)->delete();
            PecasServicos::where('id_servico', $servicoCreate->id_servico)->delete();

            // Recria as categorias
            foreach ($servicos as $servico) {
                CategoriaServico::create([
                    'data_inclusao'       => now(),
                    'id_categoria'        => $servico['id_categoria'],
                    'descricao_categoria' => $servico['descricao_categoria'],
                    'id_servico'          => $servicoCreate->id_servico,
                ]);
            }

            // Recria as peças
            foreach ($pecas as $peca) {
                PecasServicos::create([
                    'data_inclusao' => now(),
                    'id_peca'       => $peca['id_produto'],
                    'id_servico'    => $servicoCreate->id_servico,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.manutencaoservicos.index')
                ->with('success', 'Manutenção de Serviço atualizada com sucesso!');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Erro ao atualizar serviço', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('admin.manutencaoservicos.index')
                ->with('error', 'Não foi possível atualizar Manutenção!');
        }
    }
}
