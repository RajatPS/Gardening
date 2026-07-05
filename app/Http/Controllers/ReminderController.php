<?php

namespace App\Http\Controllers;

use App\Services\PlantReminderScheduler;
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
            'frequency' => 'nullable|string|in:daily,weekly,monthly,every_3_months',
            'plant_care' => 'nullable|boolean',
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

        Reminder::create($data);

        if (! empty($v['plant_care'])) {
            $scheduler = new PlantReminderScheduler();
            $tasks = [
                ['title' => 'Morning watering', 'frequency' => $v['frequency'] ?? 'daily', 'type' => 'Watering', 'hour' => 7, 'minute' => 0],
                ['title' => 'Evening watering', 'frequency' => $v['frequency'] ?? 'daily', 'type' => 'Watering', 'hour' => 19, 'minute' => 0],
                ['title' => 'Fertilize plants', 'frequency' => 'every_3_months', 'type' => 'Fertilizer', 'hour' => 8, 'minute' => 0],
            ];

            foreach ($scheduler->buildPlantCareReminders(['start_at' => $v['remind_at'], 'tasks' => $tasks]) as $task) {
                Reminder::create([
                    'user_id' => $data['user_id'] ?? null,
                    'task' => $task['task'],
                    'remind_at' => $task['remind_at'],
                    'type' => $task['type'],
                    'payload' => $task['payload'],
                    'notified' => false,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Reminder scheduled.');
    }
}
