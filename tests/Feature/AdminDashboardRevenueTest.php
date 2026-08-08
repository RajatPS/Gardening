<?php

namespace Tests\Feature;

use App\Models\GardenSetup;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardRevenueTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_combines_order_subscription_and_completed_appointment_revenue(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'status' => 'active',
        ]);

        Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-REV-001',
            'total_amount' => 1200.00,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'shipping_address' => 'Test address',
        ]);

        $subscription = Subscription::create([
            'user_id' => $customer->id,
            'subscription_plan_id' => null,
            'plan_name' => 'Basic',
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'amount' => 299,
            'status' => 'active',
            'renewal_count' => 0,
        ]);

        Transaction::create([
            'user_id' => $customer->id,
            'subscription_id' => $subscription->id,
            'order_id' => null,
            'transaction_id' => 'txn-sub-001',
            'amount' => 299.00,
            'payment_method' => 'razorpay',
            'payment_gateway' => 'razorpay',
            'status' => 'completed',
        ]);

        ServiceBooking::create([
            'user_id' => $customer->id,
            'service_type' => 'garden-setup',
            'status' => 'completed',
            'booking_date' => now(),
            'time_slot' => '10:00 AM',
        ]);

        GardenSetup::create([
            'name' => 'Test Customer',
            'phone' => '9999999999',
            'address' => 'Test address',
            'budget' => 450.00,
            'notes' => 'Budget is a planning preference only.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $this->assertSame(1499.0, round((float) $response->viewData('dailyRevenue'), 2));
        $this->assertSame(1499.0, round((float) $response->viewData('weeklyRevenue'), 2));
        $this->assertSame(1499.0, round((float) $response->viewData('monthlyRevenue'), 2));
        $this->assertSame(1499.0, round((float) $response->viewData('yearlyRevenue'), 2));
    }
}
