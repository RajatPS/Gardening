<?php

namespace Tests\Unit;

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_create_a_plan_with_status_and_end_date(): void
    {
        $endDate = now()->addYear()->toDateString();

        $plan = SubscriptionPlan::create([
            'name' => 'Premium',
            'status' => 'active',
            'monthly_price' => 250,
            'visit_cadence' => 'Monthly',
            'end_date' => $endDate,
            'features' => ['Priority support'],
            'priority_support' => true,
            'emergency_assistance' => false,
        ]);

        $this->assertSame('active', $plan->status);
        $this->assertSame($endDate, $plan->end_date->toDateString());
        $this->assertDatabaseHas('subscription_plans', [
            'name' => 'Premium',
            'status' => 'active',
            'end_date' => $endDate,
        ]);
    }
}
