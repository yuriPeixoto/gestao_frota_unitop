<?php

namespace App\Modules\Configuracoes\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    public function index()
    {
        $filiais = Branch::with('address')->get();

        $filiaisData = $filiais->map(function ($filial) {
            return [
                'id'     => $filial->id,
                'nome'   => $filial->name,
                'código' => $filial->code,
                'status' => $filial->status
            ];
        })->toArray();

        return view('admin.filiais.index', compact('filiaisData'));
    }

    public function create()
    {
        return view('admin.filiais.create');
    }

    public function store(Request $request)
    {
        $addressData = $request->validate([
            'street'     => 'required|string|max:255',
            'number'     => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'district'   => 'required|string|max:255',
            'city'       => 'required|string|max:255',
            'state'      => 'required|size:2',
            'zip_code'   => 'required'
        ]);



        $branchData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|unique:branches,code,NULL,id,deleted_at,NULL',

        ]);



        try {

            $zipCode = str_replace('-', '', $addressData['zip_code']);

            // Create address first
            $address = Address::create([
                'street'     => $addressData['street'],
                'number'     => $addressData['number'],
                'complement' => $addressData['complement'] ?? null,
                'district'   => $addressData['district'],
                'city'       => $addressData['city'],
                'state'      => strtoupper($addressData['state']),
                'zip_code'   => $zipCode,
            ]);
            $dataInclusao = date('Y-m-d H:i:s');
            $address2 = DB::connection('pgsql')->table('addresses')->where('id', $address->id);


            // Create branch with address
            $branch = new Branch();
            $branch->name = $branchData['name'];
            $branch->code = $branchData['code'];
            $branch->is_headquarter = $request->has('is_headquarter');
            $branch->address_id = $address->id;
            $branch->save();

            return redirect()
                ->route('admin.filiais.index')
                ->withNotification([
                    'title'   => 'Filial criada',
                    'type'    => 'success',
                    'message' => 'Filial criada com sucesso!'
                ]);
        } catch (\Exception $e) {
            dd($e);

            Log::error('Erro ao criar filial: ' . $e->getMessage());
            return back()
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Ocorreu um erro ao criar filial: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        $branchId = json_decode($branch, true)['id'];
        $filial = Branch::where('id', $branchId)->first();
        $filial->load('address');
        return view('admin.filiais.edit', compact('filial'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $branchId = json_decode($branch, true)['id'];
        $filial = Branch::where('id', $branchId)->first();

        $addressData = $request->validate([
            'street'     => 'required|string|max:255',
            'number'     => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'district'   => 'required|string|max:255',
            'city'       => 'required|string|max:255',
            'state'      => 'required|size:2',
            'zip_code'   => 'required'
        ]);

        $branchData = $request->validate([
            'name' => 'required|string|max:255',
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'code')->ignore($branchId),
            ],
        ]);

        $branchData['is_headquarter'] = $request->has('is_headquarter');

        try {
            // Update or create address
            if ($filial->address) {
                $filial->address->update($addressData);
            } else {
                $address = Address::create($addressData);
                $filial->address_id = $address->id;
                $filial->save();
            }

            // Update branch
            $filial->name = $branchData['name'];
            $filial->code = $branchData['code'];
            $filial->is_headquarter = $request->has('is_headquarter');
            $filial->save();

            return redirect()
                ->route('admin.filiais.index')
                ->withNotification([
                    'title'   => 'Filial atualizada',
                    'type'    => 'success',
                    'message' => 'Filial atualizada com sucesso!'
                ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar filial: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withNotification([
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Ocorreu um erro ao atualizar filial: ' . $e->getMessage()
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        try {
            $filial = Branch::findOrFail($id);
            $filial->delete();

            return response()->json([
                'notification' => [
                    'title'   => 'Filial excluída',
                    'type'    => 'success',
                    'message' => 'Filial excluída com sucesso'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'notification' => [
                    'title'   => 'Erro',
                    'type'    => 'error',
                    'message' => 'Não foi possível excluir a filial: ' . $e->getMessage()
                ]
            ], 500);
        }
    }
}
