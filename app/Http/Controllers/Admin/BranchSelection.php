<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BranchSelection extends Controller
{
    public function select()
    {
        $branches = auth()->user()->getAllowedBranches();
        return view('branch.select', compact('branches'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id'
        ]);

        $user = auth()->user();

        if (!$user->getAllowedBranches()->contains($validated['branch_id'])) {
            return back()->with('error', 'Você não tem acesso a esta filial.');
        }

        $user->update(['current_branch_id' => $validated['branch_id']]);
        return redirect()->intended(route('dashboard'));
    }
}
