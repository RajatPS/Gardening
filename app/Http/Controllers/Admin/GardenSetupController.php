<?php

namespace App\Http\Controllers\Admin;

use App\Models\GardenSetup;
use Illuminate\Routing\Controller;

class GardenSetupController extends Controller
{
    public function index()
    {
        $items = GardenSetup::orderBy('created_at','desc')->paginate(20);
        return view('admin.garden-setups.index', compact('items'));
    }
}
