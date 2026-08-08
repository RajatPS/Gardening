<?php

namespace App\Http\Controllers\Admin;

use App\Models\GardenSetup;
use Illuminate\Routing\Controller;

class GardenSetupController extends Controller
{
    public function index()
    {
        $items = GardenSetup::orderBy('created_at','desc')->paginate(20);
        return view('admin.garden-setup-management', compact('items'));
    }

    public function destroy($id)
    {
        $item = GardenSetup::findOrFail($id);

        try {
            $item->delete();
            return redirect()->route('admin.garden-setups.index')->with('success', 'Garden setup request deleted successfully');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to delete this garden setup request.');
        }
    }
}
