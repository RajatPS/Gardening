<?php

namespace Tests\Feature;

use App\Http\Controllers\CustomerAccountController;
use Tests\TestCase;

class CustomerCheckoutAmountTest extends TestCase
{
    public function test_build_checkout_summary_uses_grand_total_for_razorpay_amount(): void
    {
        $controller = new CustomerAccountController();
        $method = new \ReflectionMethod($controller, 'buildCheckoutSummary');
        $method->setAccessible(true);

        $items = collect([
            (object) ['price' => 250, 'quantity' => 1],
            (object) ['price' => 250, 'quantity' => 1],
        ]);

        $summary = $method->invoke($controller, $items);

        $this->assertSame(500.0, $summary['subtotal']);
        $this->assertSame(20.0, $summary['delivery']);
        $this->assertSame(90.0, $summary['gst']);
        $this->assertSame(610.0, $summary['grand_total']);
        $this->assertSame(61000, $summary['amount_in_paise']);
    }
}
