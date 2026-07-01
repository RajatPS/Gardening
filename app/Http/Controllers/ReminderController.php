<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reminder;

class ReminderController extends Controller
{
    public function store(Request $request)
    {
        $v = $request->validate([
            'task' => 'required|string|max:255',
            'remind_at' => 'required|date',
            'type' => 'nullable|string',
        ]);

        $data = [
            'task' => $v['task'],
            'remind_at' => $v['remind_at'],
            'type' => $v['type'] ?? null,
            'payload' => null,
        ];

        if (auth()->check()) {
            $data['user_id'] = auth()->id();
        }

        $reminder = Reminder::create($data);

        return redirect()->back()->with('success', 'Reminder scheduled.');
    }
}
