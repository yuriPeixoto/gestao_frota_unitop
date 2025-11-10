<?php

namespace App\Modules\Checklist\Controllers\Admin;
ini_set('memory_limit', '512M');


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Checklist\Models\TipoChecklist;
use App\Modules\Checklist\Models\CheckList;
use App\Modules\Checklist\Models\ColunaCheckList;
use App\Charts\ChecklistChart;
use Carbon\Carbon;

class ChecklistController extends Controller
{
    protected $tipoChecklist;
    protected $checklist;
    protected $coluna_checkList;
    protected $checklist_resposta;

    public function __construct(TipoChecklist $tipoChecklist, CheckList $checklist, ColunaCheckList $colunaCheckList) {
        $this->tipoChecklist = $tipoChecklist;
        $this->checklist = $checklist;
        $this->colunaCheckList = $colunaCheckList;
    }

    public function biChecklist(Request $request)
    {
        $checklists = $this->checklist->all();
        $tipoChecklist = $this->tipoChecklist->orderBy('nome', 'desc')->get()
        ->map(function($tipo) { 
            return [
                'label' => $tipo->nome, 
                'value' => $tipo->id
            ];
        });;

        $nomesMeses = [
            '01' => 'Janeiro',
            '02' => 'Fevereiro',
            '03' => 'Março',
            '04' => 'Abril',
            '05' => 'Maio',
            '06' => 'Junho',
            '07' => 'Julho',
            '08' => 'Agosto',
            '09' => 'Setembro',
            '10' => 'Outubro',
            '11' => 'Novembro',
            '12' => 'Dezembro'
        ];
    
        $anos = $checklists->map(function($checkList) { 
            $anoFormatado = $checkList->created_at->format('Y'); // Corrigido o nome da variável
            return [
                'label' => $anoFormatado, 
                'value' => $anoFormatado
            ];
        });

        $meses = $checklists->map(function($checkList) use ($nomesMeses) {
            $mesNumero = $checkList->created_at->format('m'); 
            return [
                'label' => $nomesMeses[$mesNumero], 
                'value' =>  $checkList->created_at->format('m')
            ];
        });

        
        $chart->labels(['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun']);
        $chart->dataset('', 'pie', [
            Checklist::whereMonth('created_at', 1)->count(),
        ])->backgroundColor(['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0', '#9966ff', '#ff9f40']);

        return view('admin.checklist.biChecklist', compact('chart','anos', 'meses','tipoChecklist'));
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $checklist = $this->checklist
        ->with(['tipoChecklist:id,nome'])
        ->paginate();

       
        return view('admin.checklist.index', compact('checklist'));
    }

    public function getTipoChecklist() {
        $tipoChecklist = $this->tipoChecklist->orderBy('created_at', 'desc')->get();
        $tipoChecklist = $tipoChecklist->map(function ($tipoChecklist) {
            return [
                'label' => $tipoChecklist->nome, 
                'value' => $tipoChecklist->id
            ];
        });
        return $tipoChecklist;
    }

    public function getPerguntas(){
        return  $this->colunaCheckList->distinct()->orderBy('created_at', 'desc')->paginate();
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $perguntas = $this->getPerguntas();
        $tipoChecklist = $this->getTipoChecklist();
        return view('admin.checklist.create', compact('tipoChecklist', 'perguntas' ));
    }

    public function store(Request $request)
    {

        $request->validate([
            'nome' => 'required|max:255',
            'descricao' => 'required|max:255',
            'tipo_checklist_id' => 'required',
        ]);

        $checklist = $this->checklist->create($request->all());
       
        if($request->perguntas){
            $perguntas = $this->colunaCheckList->whereIn('id', $request->perguntas)->get();

            foreach($perguntas as $pergunta){
                $this->colunaCheckList->create([
                    'tipo'=>              $pergunta->tipo,
                    'descricao'=>         $pergunta->descricao,
                    'checklist_id'=>      $checklist->id,
                    'relacionado_a_tabela'=>' veiculo',
                    'chave_estrangeira_relacionado'=> 1
                ]);

            }
        
        }
      
        return redirect('/admin/checklist');
    }

    public function createQuestion(string $id)
    {
        $checklists = $this->checklist
        ->orderBy('created_at', 'asc')
        ->where('id', $id)->
        get();

        $tipoPergunta =[
            [
                'label'=>'Sim ou Não',
                'value'=>'sim_ou_nao'
            ],
            [
                'label'=>'Assinatura',
                'value'=>'assinatura'
            ],
            [
                'label'=>'Foto',
                'value'=>'foto'
            ],
            [
                'label'=>'Sim ou Nao e Foto',
                'value'=>'sim_ou_nao_e_foto'
            ],
        ];

        $colunaCheckLists = $this->colunaCheckList->orderBy('created_at', 'asc')->where('checklist_id',$id)->paginate();
        $data = $checklists->map(function($checkList){
            return [
                'label' => $checkList->nome, 
                'value' => $checkList->id
            ];
        });
        return view('admin.checklist.createQuestionnaire', compact('data','tipoPergunta', 'colunaCheckLists', 'id'));
    }
    /**
     * Display the specified resource.
     */
    public function storeQuestion(Request $request, string $id)
    {
        $request->validate([
            'tipo' => 'required|max:255',
            'descricao' => 'required|max:255',
        ]);

        $res = $this->colunaCheckList->create([
            'tipo'=>$request->tipo,
            'descricao'=>$request->descricao,
            'checklist_id'=> $id,
            'relacionado_a_tabela'=>'1',
            'chave_estrangeira_relacionado'=>1
        ]);

        return redirect("/admin/checklist/criar_questionario/$id");
    }
    public function edit(string $id)
    {
        $checklist =  $checkList = $this->checklist->where('id', $id)->first();
        $perguntas = $this->getPerguntas();
        $tipoChecklist = $this->getTipoChecklist();
        $pergunta = $this->colunaCheckList->select('id')->where('checklist_id', $id)->get();

        return view('admin.checklist.edit', compact('checklist','tipoChecklist', 'perguntas', 'pergunta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'nome' => 'required|max:255',
            'descricao' => 'required|max:255',
            'tipo_checklist_id' => 'required',
        ]);

        $result = $this->checklist->where('id', $id)
            ->update([
            "nome" => $request->nome,
            "descricao" => $request->descricao,
            "tipo_checklist_id" => $request->tipo_checklist_id
        ]);

        // $this->colunaCheckList->where('checklist_id', $id)->delete();

        if($request->pergunta){
            $perguntas = $this->colunaCheckList->whereIn('id', $request->perguntas)->get();
            foreach($perguntas as $pergunta){
                $this->colunaCheckList->create([
                    'tipo'=>              $pergunta->tipo,
                    'descricao'=>         $pergunta->descricao,
                    'checklist_id'=>      $checklist->id,
                    'relacionado_a_tabela'=>' veiculo',
                    'chave_estrangeira_relacionado'=> 1
                ]);
            }
        }
        return redirect('/admin/checklist');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $result = $this->checklist->where('id', $id);
        $result->delete();
        return redirect('/admin/checklist');
    }

    public function checklists()
    {
        $checklist = $this->checklist
        ->with(['tipoChecklist:id,departamento_id,cargo_id', 'colunaChecklist']) 
        ->whereHas('colunaChecklist') 
        ->get()
        ->map(function ($checklist) {
            unset($checklist->colunaChecklist);
            return $checklist;
        });

        return response()->json($checklist);
    }

    function colunaChecklist($checklist_id)
    {
        $res = $this->checklist
            ->with(['tipoChecklist:id,departamento_id,cargo_id,multiplas_etapas', 'colunaChecklist' => function($query) use ($checklist_id) {
                $query->where('checklist_id', $checklist_id); 
            }])
            ->whereHas('colunaChecklist', function($query) use ($checklist_id) {
                $query->where('checklist_id', $checklist_id);
            })->first();

        return response()->json($res);
    }
}
