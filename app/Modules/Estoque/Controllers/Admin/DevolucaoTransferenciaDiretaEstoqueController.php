<?php

namespace App\Modules\Estoque\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Estoque\Models\TransferenciaDiretaEstoque;
use App\Models\Filial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DevolucaoTransferenciaDiretaEstoqueController extends Controller
{
    protected $transferenciaDiretaEstoque;
    protected $filial;
    public function __construct(TransferenciaDiretaEstoque $transferenciaDiretaEstoque, Filial $filial)
    {
        $this->transferenciaDiretaEstoque = $transferenciaDiretaEstoque;
        $this->filial = $filial;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->queryTransferencia();

        if ($request->has('search')) {
            $query->when($request->has('search'), function ($query) use ($request) {
                return $query->whereRaw('LOWER(filial.descricao_filial) LIKE LOWER(?)', ['%' . $request->search . '%']);
            });
        }

        $results = $query->paginate()
            ->through(function ($item) {
                $item->filial =  DB::connection('pgsql')->table('filial')->where('id_filial', $item->filial)->select('descricao_filial')->first();
                $item->data_inclusao = \Carbon\Carbon::parse($item->data_inclusao)->format('d/m/Y H:i');
                $item->data_alteracao = \Carbon\Carbon::parse($item->data_alteracao)->format('d/m/Y H:i');
                return $item;
            });

        return view('admin.devolucaoTransferenciaDiretaEstoque.index', compact('results'));
    }

    public function queryTransferencia()
    {
        return $this->transferenciaDiretaEstoque->select(
            'usuarios_carvalima.name',
            'filial.descricao_filial',
            'departamento.descricao_departamento',
            'transferencia_direta_estoque.*'
        )
            ->join('departamento', 'departamento.id_departamento', '=', 'transferencia_direta_estoque.id_departamento')
            ->join('usuarios_carvalima', 'transferencia_direta_estoque.id_usuario', '=', 'usuarios_carvalima.id')
            ->join('filial', 'transferencia_direta_estoque.filial_solicita', '=', 'filial.id_filial');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function gerarCSV()
    {
        $result = $this->queryTransferencia()->get();

        $csvHeader = [
            'Cód. Tansferência',
            'Status',
            'Usuário',
            'Departamento',
            'Observação',
            'Filial',
            'Filial Solicitante',
            'Data inclusao'
        ];

        $csvData = fopen('php://temp', 'w');

        fputcsv($csvData, $csvHeader);
        foreach ($result as $item) {
            fputcsv($csvData, $item->toArray());
        }
        rewind($csvData);
        $csvContent = stream_get_contents($csvData);
        fclose($csvData);

        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="ajustes.csv"',
        ]);
    }

    public function gerarPDF()
    {
        $pdf = PDF::loadView('PDFS.tranferenciaDiretaEstoque');
        return $pdf->download('tranferenciaDiretaEstoque.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
