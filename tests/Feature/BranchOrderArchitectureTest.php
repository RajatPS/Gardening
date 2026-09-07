<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BranchOrderArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_staff_can_only_list_orders_for_their_branch(): void
    {
        [$madharihat, $kolkata] = $this->branches();
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $madharihat->id, 'status' => 'active']);
        $allowed = $this->order($madharihat, 'Allowed Order');
        $this->order($kolkata, 'Hidden Order');

        $response = $this->actingAs($staff)->get(route('staff.orders.index'));

        $response->assertOk();
        $response->assertSee($allowed->order_number);
        $response->assertDontSee('Hidden Order');
    }

    public function test_staff_cannot_view_or_update_another_branch_order_directly(): void
    {
        [$madharihat, $kolkata] = $this->branches();
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $madharihat->id, 'status' => 'active']);
        $order = $this->order($kolkata, 'Protected Order');

        $this->actingAs($staff)->get(route('staff.orders.show', $order))->assertForbidden();
        $this->actingAs($staff)->post(route('staff.orders.status', $order), ['status' => 'processing'])->assertForbidden();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_staff_cannot_enter_administrator_order_routes(): void
    {
        [$branch] = $this->branches();
        $staff = User::factory()->create(['role' => 'staff', 'branch_id' => $branch->id, 'status' => 'active']);

        $this->actingAs($staff)->get(route('admin.orders.index'))->assertForbidden();
    }

    public function test_admin_can_list_all_orders_and_filter_by_selected_branch(): void
    {
        [$madharihat, $kolkata] = $this->branches();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $madharihatOrder = $this->order($madharihat, 'Madharihat Order');
        $kolkataOrder = $this->order($kolkata, 'Kolkata Order');

        $all = $this->actingAs($admin)->get(route('admin.orders.index'));
        $all->assertSee($madharihatOrder->order_number)->assertSee($kolkataOrder->order_number);

        session(['admin.selected_branch_id' => $madharihat->id]);
        $filtered = $this->actingAs($admin)->get(route('admin.orders.index'));
        $filtered->assertSee($madharihatOrder->order_number)->assertDontSee($kolkataOrder->order_number);
    }

    public function test_checkout_branch_resolver_selects_nearest_branch(): void
    {
        [$madharihat, $kolkata] = $this->branches();
        Http::fake([
            'https://nominatim.openstreetmap.org/*' => Http::response([['lat' => '26.5013', 'lon' => '89.3485']], 200),
        ]);

        $resolver = new \App\Services\BranchResolverService();
        $location = $resolver->resolveCustomerLocation('Madharihat Market', 'Madharihat', '736135');
        $nearest = $resolver->resolveNearestBranchToCoordinates($location);

        $this->assertSame($madharihat->id, $nearest?->id);
        $this->assertNotSame($kolkata->id, $nearest?->id);
    }

    private function branches(): array
    {
        return [
            Branch::create(['name' => 'Madharihat', 'address' => 'Madharihat, India', 'latitude' => 26.5013, 'longitude' => 89.3485]),
            Branch::create(['name' => 'Kolkata', 'address' => 'Kolkata, India', 'latitude' => 22.5726, 'longitude' => 88.3639]),
        ];
    }

    private function order(Branch $branch, string $name): Order
    {
        return Order::create([
            'user_id' => User::factory()->create(['role' => 'customer'])->id,
            'branch_id' => $branch->id,
            'order_number' => 'ORD-' . strtoupper(substr(md5($name), 0, 10)),
            'total_amount' => 100,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'cash',
            'shipping_address' => $name,
            'contact_phone' => '9876543210',
        ]);
    }
}