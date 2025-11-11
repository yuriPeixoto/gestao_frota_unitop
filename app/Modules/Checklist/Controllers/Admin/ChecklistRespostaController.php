<?php

namespace App\Modules\Checklist\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Modules\Checklist\Models\ChecklistResposta;
use App\Modules\Checklist\Models\CheckList;
use Illuminate\Support\Facades\DB;

enum Status: string
{
    case Pendente = 'pendente';
    case Aprovado = 'aprovado';
    case Reprovado = 'reprovado';
}

class ChecklistRespostaController extends Controller
{
    protected $checklistResposta;
    protected $checklist;

    public function __construct(ChecklistResposta $colunaCheckListResposta, Checklist $checklist)
    {
        $this->checklistResposta = $colunaCheckListResposta;
        $this->checklist = $checklist;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $respostas = $this->checklist
            ->whereHas('checklistRespostas')
            ->with(['checklistRespostas.user', 'checklistRespostas'])
            ->paginate();

        return view('admin.checklistresposta.index', compact('respostas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $res = $request->data;

            foreach ($res as $value) {
                $body = [
                    'checklist_id' => $value['checklist_id'],
                    'coluna_id' => $value['coluna_id'],
                    'relacionado_id' => 'teste',
                    'valor_resposta' => $value['valor_resposta'] ?? null,
                    'user_id' => $request->user()->id,
                    'passo' => intval($value['passo']),
                    'status' => Status::tryFrom('pendente')
                ];

                if (isset($value['assinatura']) && $value['assinatura'] instanceof \Illuminate\Http\UploadedFile) {
                    $body['assinatura'] = $value['assinatura']->store('uploads/assinaturas', 'public');
                }

                if (isset($value['foto']) && $value['foto'] instanceof \Illuminate\Http\UploadedFile) {
                    $body['foto'] = $value['foto']->store('uploads/fotos', 'public');
                }

                $this->checklistResposta->create($body);
            }

            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['error' => true]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $query = $this->checklist
            ->whereHas('checklistRespostas')
            ->with(['checklistRespostas.colunaChecklist', 'checklistRespostas.user', 'checklistRespostas.user.departamento'])
            ->where('id', $id)
            ->get();

        $resposta1 = $query->map(function ($checklist) {
            return (object)[
                'id' => $checklist->id,
                'nome' => $checklist->nome,
                'checklistRespostas' => $checklist->checklistRespostas->filter(function ($resposta) {
                    return $resposta->passo === 1;
                })->map(function ($resposta) {
                    return (object)[
                        'id' => $resposta->id,
                        'colunaChecklist' => $resposta->colunaChecklist->descricao ?? null,
                        'passo' => $resposta->passo ?? null,
                        'simOuNao' => $resposta->valor_resposta ?? null,
                        'foto' => $resposta->foto ?? null,
                        'assinatura' => $resposta->assinatura ?? null,
                        'user' => $resposta->user,
                    ];
                }),
            ];
        });

        $resposta2 = $query->map(function ($checklist) {
            return (object)[
                'id' => $checklist->id,
                'nome' => $checklist->nome,
                'checklistRespostas' => $checklist->checklistRespostas->filter(function ($resposta) {
                    return $resposta->passo === 2;
                })->map(function ($resposta) {
                    return (object)[
                        'id' => $resposta->id,
                        'colunaChecklist' => $resposta->colunaChecklist->descricao ?? null,
                        'passo' => $resposta->passo ?? null,
                        'simOuNao' => $resposta->valor_resposta ?? null,
                        'foto' => $resposta->foto ?? null,
                        'assinatura' => $resposta->assinatura ?? null,
                        'user' => $resposta->user,
                        'status' => $resposta->status
                    ];
                }),
            ];
        });

        $user = $query->map(function ($checklist) {
            return $checklist->checklistRespostas->first(function ($resposta) {
                return $resposta->user;
            });
        })->filter(function ($resposta) {
            return $resposta !== null;
        })->map(function ($resposta) {
            return (object)[
                'id' => $resposta->user->id ?? null,
                'name' => $resposta->user->name ?? null,
                'email' => $resposta->user->email ?? null,
                'departamento' => $resposta->user->departamento->nome ?? null,
            ];
        });

        return view('admin.checklistresposta.show', compact('resposta1', 'resposta2', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $statusEnum = Status::tryFrom($request->status);

        $resposta = $this->checklist
            ->whereHas('checklistRespostas')
            ->with(['checklistRespostas.colunaChecklist', 'checklistRespostas.user', 'checklistRespostas.user.departamento'])
            ->where('id', $id)
            ->get()
            ->map(function ($checklist) use ($statusEnum) {
                $checklist->checklistRespostas->each(function ($resposta) use ($statusEnum) {
                    $resposta->update([
                        'status' => $statusEnum
                    ]);
                });
                return (object)[
                    'id' => $checklist->id,
                    'nome' => $checklist->nome,
                    'checklistRespostas' => $checklist->checklistRespostas->map(function ($resposta) {
                        return (object)[
                            'id' => $resposta->id,
                            'colunaChecklist' => $resposta->colunaChecklist->descricao ?? null,
                            'passo' => $resposta->passo ?? null,
                            'simOuNao' => $resposta->valor_resposta ?? null,
                            'foto' => $resposta->foto ?? null,
                            'assinatura' => $resposta->assinatura ?? null,
                            'status' => $resposta->status,
                            'user' => $resposta->user ?? null,
                        ];
                    }),
                ];
            });

        return redirect('/admin/checklistresposta');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
