<?php

namespace App\Services;

use Carbon\Carbon;

class PlantReminderScheduler
{
    public function calculateNextReminderAt(Carbon $startAt, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $startAt->copy()->addDay(),
            'weekly' => $startAt->copy()->addWeek(),
            'monthly' => $startAt->copy()->addMonth(),
            'every_3_months' => $startAt->copy()->addMonths(3),
            default => $startAt->copy()->addDay(),
        };
    }

    public function buildPlantCareReminders(array $data): array
    {
        $reminders = [];
        $start = Carbon::parse($data['start_at'] ?? now()->toDateTimeString());

        foreach ($data['tasks'] ?? [] as $task) {
            $frequency = $task['frequency'] ?? 'daily';
            $reminders[] = [
                'task' => $task['title'] ?? 'Plant care task',
                'remind_at' => $start->copy()->setTime($task['hour'] ?? 7, $task['minute'] ?? 0)->toDateTimeString(),
                'type' => $task['type'] ?? 'Watering',
                'payload' => [
                    'frequency' => $frequency,
                    'next_at' => $this->calculateNextReminderAt($start, $frequency)->toDateTimeString(),
                ],
            ];
        }

        return $reminders;
    }
}
