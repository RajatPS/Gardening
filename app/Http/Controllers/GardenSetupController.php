<?php

namespace App\Http\Controllers;

use App\Models\GardenSetup;
use Illuminate\Http\Request;

class GardenSetupController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'budget' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]);

        GardenSetup::create($validated);

        return redirect()->back()->with('success','Garden setup request received. Our team will call you.');
    }
}
