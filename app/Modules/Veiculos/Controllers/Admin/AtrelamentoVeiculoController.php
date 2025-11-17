<?php

namespace App\Modules\Veiculos\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AtrelamentoVeiculo;
use App\Models\AtrelamentoItens;
use App\Models\Veiculo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function PHPUnit\Framework\throwException;

class AtrelamentoVeiculoController extends Controller
{
    public function index(Request $request)
    {
        $pageSize   = $request->input('pageSize', 10);
        $searchTerm = $request->input('search');

        $query = AtrelamentoVeiculo::query()
            ->select('id_atrelamento', 'status', 'data_atrelamento', 'data_inclusao', 'id_filial', 'id_usuario', 'id_cavalo')->distinct();

        if ($searchTerm) {
            $query->where(function ($query) use ($searchTerm) {
                $searchTermLower = strtolower($searchTerm);

                $query->whereRaw('LOWER(id_cavalo) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(status AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereRaw('LOWER(CAST(placa_cavalo AS TEXT)) LIKE ?', ['%' . $searchTermLower . '%'])
                    ->orWhereHas('filial', function ($query) use ($searchTermLower) {
                        $query->whereRaw('LOWER(name) LIKE ?', ['%' . $searchTermLower . '%']);
                    })
                    ->orWhereHas('baseVeiculo', function ($query) use ($searchTermLower) {
                        $query->whereRaw('LOWER(descricao_base) LIKE ?', ['%' . $searchTermLower . '%']);
                    });
                //mais filtros de datas e usuarios
            });
        }
        $query->orderBy('id_atrelamento', 'desc');
        $atrelamentoVeiculo = $query->paginate($pageSize);

        $atrelamentoVeiculoData = $atrelamentoVeiculo->map(function ($atrelamentoVeiculo) {
            return [
                'id' => $atrelamentoVeiculo->id_atrelamento,
                'status' => $atrelamentoVeiculo->status,
                'placa_cavalo' => $atrelamentoVeiculo->id_cavalo ? $atrelamentoVeiculo->veiculo->placa : '-',
                'data_atrelamento' => $atrelamentoVeiculo->data_atrelamento ? format_date($atrelamentoVeiculo->data_atrelamento, 'd/m/Y') : '-',
                'filial' => $atrelamentoVeiculo->id_filial ?  $atrelamentoVeiculo->filialAtrelamento->name : '-',
                'usuario' => $atrelamentoVeiculo->id_usuario ? $atrelamentoVeiculo->userAtrelamento->name : '-',
                'data_inclusao' => $atrelamentoVeiculo->data_inclusao ? format_date($atrelamentoVeiculo->data_inclusao, 'd/m/Y') : '-',
            ];
        })->toArray();

        $column_aliases = [
            'id'               => 'Cód. Atrelamento Cavalo',
            'status'           => 'Status',
            'placa_cavalo'     => 'Placa Cavalo',
            'data_atrelamento' => 'Data Atrelamento',
            'filial'           => 'Filial',
            'usuario'          => 'Usuário',
            'data_inclusao'    => 'Data Inclusão',
        ];
        $actionIcons = [
            // "icon:eye    | tip:Visualizar | color:blue|click:showAtrelamento({id})",
            "icon:pencil | tip:Editar  | click:editAtrelamento({id})",
            "icon:trash  | tip:Excluir | color:red | click:destroyAtrelamento({id}, '{id}')",
        ];

        return view('admin.atrelamentoveiculos.index', compact('atrelamentoVeiculo', 'actionIcons', 'searchTerm', 'column_aliases', 'atrelamentoVeiculoData'));
    }

    public function create(Request $request)
    {
        $veiculos = Veiculo::select('placa as label', 'id_veiculo as value', 'is_possui_tracao', 'id_sascar')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->where('is_possui_tracao', '=', 'true')
            ->orderBy('placa')->get()->toArray();

        $carretas = Veiculo::select('placa as label', 'id_veiculo as value', 'is_possui_tracao')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->where('is_possui_tracao', '=', 'false')
            ->orderBy('placa')->get()->toArray();

        $nomeUser = $request->user();

        return view('admin.atrelamentoveiculos.create', compact('veiculos', 'carretas', 'nomeUser'));
    }

    public function show()
    {
        return view('admin.atrelamentoveiculos.show');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        // Sanitizar os valores monetários
        // $this->sanitizeMonetaryValues($request, [
        //     'km_hr_inicial_cavalo'
        // ]);

        // Validações
        $cavalo = $request->validate([
            'id_cavalo' => 'required',
            'data_atrelamento' => 'required|date_format:Y-m-d',
            'km_hr_inicial_cavalo' => 'required|numeric',
            'km_hr_final_cavalo' => 'nullable|numeric',
            'id_usuario' => 'nullable|numeric',
            'id_filial' => 'nullable|numeric',
        ]);

        $carreta = $request->validate([
            'id_carreta' => 'required',
            'atrelamento_itens_atrelamento_km_hr_inicial_carreta' => 'nullable|numeric',
            'atrelamento_itens_atrelamento_km_hr_final_carreta' => 'nullable|numeric',
            'atrelamento_itens_atrelamento_km_hr_rodados' => 'nullable|numeric',
        ]);

        DB::beginTransaction();

        try {
            $cavalo = array_merge($cavalo, [
                'data_inclusao' => now(),
            ]);
            $atrelamentoVeiculo = AtrelamentoVeiculo::create($cavalo);


            $atrelamentoVeiculo = AtrelamentoItens::create([
                'id_atrelamento' => $atrelamentoVeiculo->id_atrelamento,
                'id_carreta' => $carreta['id_carreta'],
                // será trazido pela função a buscar dados pela placa
                // 'km_inicial_carreta' => $carreta['atrelamento_itens_atrelamento_km_hr_inicial_carreta'],
                // 'km_final_carreta' => $carreta['atrelamento_itens_atrelamento_km_hr_final_carreta'],
                // 'km_rodado_carreta' => $carreta['atrelamento_itens_atrelamento_km_hr_rodados'],
                'data_inclusao' => now(),
            ]);


            DB::commit();

            return redirect()
                ->route('admin.atrelamentoveiculos.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Veículo cadastrado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na criação de veículo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.atrelamentoveiculos.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível cadastrar o veículo."
                ]);
        }
    }

    public function edit(int $id_atrelamento)
    {
        $registroAtrelamento = AtrelamentoVeiculo::where('id_atrelamento', $id_atrelamento)->first();

        $veiculos = Veiculo::select('placa as label', 'id_veiculo as value', 'is_possui_tracao', 'id_sascar')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->where('is_possui_tracao', '=', 'true')
            ->orderBy('placa')->get()->toArray();

        $carretas = Veiculo::select('placa as label', 'id_veiculo as value', 'is_possui_tracao')
            ->where('placa', '!=', null)
            ->where('situacao_veiculo', '=', 'True')
            ->where('is_possui_tracao', '=', 'false')
            ->orderBy('placa')->get()->toArray();

        return view('admin.atrelamentoveiculos.edit', compact('registroAtrelamento', 'veiculos', 'carretas'));
    }

    public function update(Request $request,  $id_atrelamento)
    {
        // $atrelamentoVeiculo = AtrelamentoVeiculo::find($id_atrelamento);
        // dd($atrelamentoVeiculo);
        // dd($request->all());
        // Validações
        $cavalo = $request->validate([
            'id_cavalo' => 'required',
            'data_atrelamento' => 'required|date_format:Y-m-d',
            'data_desatrelamento' => 'nullable|date_format:Y-m-d',
            'km_hr_inicial_cavalo' => 'required|numeric',
            'km_hr_final_cavalo' => 'nullable|numeric',
            'id_usuario' => 'nullable|numeric',
            'id_filial' => 'nullable|numeric',
        ]);


        $carreta = $request->validate([
            'id_carreta' => 'required',
            'atrelamento_itens_atrelamento_km_hr_inicial_carreta' => 'nullable|numeric',
            'atrelamento_itens_atrelamento_km_hr_final_carreta' => 'nullable|numeric',
            'atrelamento_itens_atrelamento_km_hr_rodados' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $cavalo = array_merge($cavalo, [
                'data_alteracao' => now(),
            ]);
            $atrelamentoVeiculo = AtrelamentoVeiculo::find($id_atrelamento);
            $atrelamentoVeiculo->update($cavalo);

            $carreta = array_merge($carreta, [
                'data_alteracao' => now(),
            ]);

            $atrelamentoVeiculo = AtrelamentoItens::where('id_atrelamento', $id_atrelamento)->first();
            $atrelamentoVeiculo->update($carreta);

            DB::commit();

            return redirect()
                ->route('admin.atrelamentoveiculos.index')
                ->withNotification([
                    'title'   => 'Sucesso!',
                    'type'    => 'success',
                    'message' => 'Veículo atualizado com sucesso!'
                ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro na atualização do cavalo:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->route('admin.atrelamentoveiculos.index')
                ->withNotification([
                    'title'   => 'Erro!',
                    'type'    => 'error',
                    'message' => "Não foi possível atrelar o cavalo."
                ]);
        }
    }

    public function destroy($id_atrelamento)
    {
        // dd($id_atrelamento);
        try {
            DB::beginTransaction();

            $atrelamentoItens = AtrelamentoItens::where('id_atrelamento', $id_atrelamento)->get();
            if (!empty($atrelamentoItens)) {
                foreach ($atrelamentoItens as $item) {
                    $item->delete();
                }
            }
            // $atrelamentoItens->delete();

            $atrelamentoVeiculo = AtrelamentoVeiculo::find($id_atrelamento);
            $atrelamentoVeiculo->delete();

            DB::commit();

            return response()->json([
                'notification' => [
                    'title'   => 'Atrelamento excluído!',
                    'type'    => 'success',
                    'message' => 'Atrelamento excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir atrelamento: ' . $e->getMessage());
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir o atrelamento: ' . $e->getMessage()
                ]
            ], 500);
        }
    }

    public function getInicialCavalo(Request $request)
    {
        try {
            $veiculo = Veiculo::where('id_veiculo', $request->id_veiculo)->first();
            $idsascar = $veiculo->id_sascar;
            $dataabertura = date('Y-m-d');

            $sql = "SELECT * FROM fc_km_retroativo_os(:idsascar, :dataabertura)";
            $veiculoKm = DB::connection('pgsql')->select($sql, [
                'idsascar' => $idsascar,
                'dataabertura' => $dataabertura,
            ]);
            return response()->json([
                'renavam' => $veiculoKm ?? 'Não informado'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar dados do veículo: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar dados do veículo'], 500);
        }
    }
}
