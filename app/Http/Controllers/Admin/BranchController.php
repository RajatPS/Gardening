<?php

namespace App\Http\Controllers\Admin;

use App\Models\Branch;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function select(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        if (empty($validated['branch_id'])) {
            $request->session()->forget('admin.selected_branch_id');
        } else {
            $request->session()->put('admin.selected_branch_id', $validated['branch_id']);
        }

        return redirect()->back();
    }
}
