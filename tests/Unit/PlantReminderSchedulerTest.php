<?php

namespace Tests\Unit;

use App\Services\PlantReminderScheduler;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PlantReminderSchedulerTest extends TestCase
{
    public function test_daily_recurrence_advances_by_one_day(): void
    {
        $scheduler = new PlantReminderScheduler();
        $start = Carbon::parse('2026-07-05 07:00:00');

        $next = $scheduler->calculateNextReminderAt($start, 'daily');

        $this->assertSame('2026-07-06 07:00:00', $next->format('Y-m-d H:i:s'));
    }

    public function test_three_month_recurrence_advances_by_three_months(): void
    {
        $scheduler = new PlantReminderScheduler();
        $start = Carbon::parse('2026-07-05 07:00:00');

        $next = $scheduler->calculateNextReminderAt($start, 'every_3_months');

        $this->assertSame('2026-10-05 07:00:00', $next->format('Y-m-d H:i:s'));
    }
}
