<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SubscriptionPaymentController extends Controller
{
    public function checkout(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to continue with a plan.');
        }

        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:basic,standard,premium'],
        ]);

        $planName = ucfirst($validated['plan']);
        $planData = $this->planDetails($planName);
        $currentSubscription = Subscription::active()->where('user_id', auth()->id())->latest('end_date')->first();

        if ($currentSubscription && $currentSubscription->plan_name === $planName) {
            return redirect()->route('subscriptions')->with('error', 'You already have the selected plan active.');
        }

        if ($currentSubscription) {
            $currentPrice = $currentSubscription->amount;
            if ($planData['amount'] <= $currentPrice) {
                return redirect()->route('subscriptions')->with('error', 'Downgrades and same-plan purchases are not allowed while your current plan is active.');
            }

            $upgradeAmount = $planData['amount'] - $currentPrice;
        } else {
            $upgradeAmount = $planData['amount'];
        }

        return view('pages.subscriptions-payment', [
            'plan' => $planName,
            'amount' => $upgradeAmount,
            'currency' => config('app.currency', env('APP_CURRENCY', 'INR')),
            'paymentGateway' => $this->paymentGatewayEnabled() ? 'razorpay' : 'mock',
            'paymentMethod' => null,
            'currentPlanPrice' => $currentSubscription?->amount,
            'currentPlanName' => $currentSubscription?->plan_name,
            'currentPlanAmountFormatted' => $currentSubscription ? '₹' . number_format($currentSubscription->amount, 0) : null,
        ]);
    }

    public function pay(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to continue with a plan.');
        }

        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:basic,standard,premium'],
            'payment_method' => ['required', 'string', 'in:upi,qr,card,netbanking,wallet,cash'],
        ]);

        $planName = ucfirst($validated['plan']);
        $planData = $this->planDetails($planName);
        $currency = config('app.currency', env('APP_CURRENCY', 'INR'));
        $paymentMethod = $validated['payment_method'];
        $currentSubscription = Subscription::active()->where('user_id', auth()->id())->latest('end_date')->first();

        if ($currentSubscription && $currentSubscription->plan_name === $planName) {
            return redirect()->route('subscriptions')->with('error', 'You already have the selected plan active.');
        }

        if ($currentSubscription && $planData['amount'] <= $currentSubscription->amount) {
            return redirect()->route('subscriptions')->with('error', 'Downgrades and same-plan purchases are not allowed while your current plan is active.');
        }

        $amountToPay = $currentSubscription ? $planData['amount'] - $currentSubscription->amount : $planData['amount'];

        if ($this->paymentGatewayEnabled()) {
            $order = $this->createRazorpayOrder($amountToPay, $currency, $planName, $paymentMethod);

            if ($order['success']) {
                $subscription = $this->createSubscriptionRecord($planName, $planData['amount'], 'pending', $paymentMethod, [
                    'gateway' => 'razorpay',
                    'order_id' => $order['data']['id'],
                    'upgrade_from' => $currentSubscription?->plan_name,
                ]);

                $transaction = $this->createTransactionRecord($subscription, $order['data']['id'], $amountToPay, $paymentMethod, 'pending', [
                    'gateway' => 'razorpay',
                    'order_id' => $order['data']['id'],
                    'upgrade_from' => $currentSubscription?->plan_name,
                ], null);

                return view('pages.subscriptions-payment', [
                    'plan' => $planName,
                    'amount' => $amountToPay,
                    'currency' => $currency,
                    'paymentGateway' => 'razorpay',
                    'paymentMethod' => $paymentMethod,
                    'order' => $order['data'],
                    'transactionId' => $transaction->id,
                    'currentPlanName' => $currentSubscription?->plan_name,
                    'currentPlanPrice' => $currentSubscription?->amount,
                ]);
            }
        }

        return $this->completeSubscription($planName, $planData['amount'], 'cash', 'completed', [
            'gateway' => 'mock',
            'message' => 'Payment completed using the local fallback flow.',
            'upgrade_from' => $currentSubscription?->plan_name,
        ]);
    }

    public function verify(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('customer.login')->with('error', 'Please log in to continue with the plan.');
        }

        $validated = $request->validate([
            'plan' => ['required', 'string', 'in:basic,standard,premium'],
            'payment_method' => ['required', 'string', 'in:upi,qr,card,netbanking,wallet,cash'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $planName = ucfirst($validated['plan']);
        $planData = $this->planDetails($planName);
        $signature = $this->buildSignature($validated['razorpay_order_id'], $validated['razorpay_payment_id'], $validated['razorpay_signature']);

        $transaction = Transaction::where('transaction_id', $validated['razorpay_order_id'])->latest()->first();

        if (! $transaction) {
            return redirect()->route('subscriptions')->with('error', 'We could not find the payment record for this subscription.');
        }

        if ($signature['valid']) {
            $transaction->update([
                'status' => 'completed',
                'payment_method' => $validated['payment_method'],
                'response' => 'Payment verified successfully.',
                'payment_details' => [
                    'gateway' => 'razorpay',
                    'payment_id' => $validated['razorpay_payment_id'],
                    'order_id' => $validated['razorpay_order_id'],
                    'signature' => $validated['razorpay_signature'],
                ],
            ]);

            if ($transaction->subscription_id) {
                Subscription::find($transaction->subscription_id)?->update([
                    'status' => 'active',
                    'start_date' => Carbon::now(),
                    'end_date' => Carbon::now()->addMonth(),
                    'amount' => $planData['amount'],
                    'plan_name' => $planName,
                    'payment_gateway' => 'razorpay',
                ]);
            }

            return redirect()->route('subscriptions')->with('success', 'Plan activated successfully. Payment completed.');
        }

        $transaction->update([
            'status' => 'failed',
            'response' => 'Signature verification failed.',
            'payment_details' => [
                'gateway' => 'razorpay',
                'payment_id' => $validated['razorpay_payment_id'] ?? null,
                'order_id' => $validated['razorpay_order_id'] ?? null,
            ],
        ]);

        return redirect()->route('subscriptions')->with('error', 'Payment could not be verified. Please try again.');
    }

    private function createRazorpayOrder(int $amount, string $currency, string $planName, string $paymentMethod): array
    {
        $keyId = env('RAZORPAY_KEY_ID');
        $secret = env('RAZORPAY_KEY_SECRET');

        if (empty($keyId) || empty($secret)) {
            return ['success' => false, 'message' => 'Razorpay credentials are not configured.'];
        }

        $response = Http::withBasicAuth($keyId, $secret)
            ->asForm()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amount * 100,
                'currency' => $currency,
                'receipt' => 'plan-' . Str::slug($planName) . '-' . time(),
                'notes' => [
                    'plan' => $planName,
                    'payment_method' => $paymentMethod,
                    'user_id' => auth()->id(),
                ],
            ]);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'message' => $response->body()];
    }

    private function completeSubscription(string $planName, int $amount, string $paymentMethod, string $status, array $gatewayResponse)
    {
        $subscription = $this->createSubscriptionRecord($planName, $amount, $status, $paymentMethod, $gatewayResponse);
        $this->createTransactionRecord($subscription, 'local-' . Str::uuid()->toString(), $amount, $paymentMethod, $status, $gatewayResponse, $gatewayResponse['message'] ?? null);

        return redirect()->route('subscriptions')->with('success', 'Your plan has been activated successfully.');
    }

    private function createSubscriptionRecord(string $planName, int $amount, string $status, string $paymentMethod, array $gatewayResponse): Subscription
    {
        $plan = $this->ensurePlan($this->planDetails($planName));

        return Subscription::create([
            'user_id' => auth()->id(),
            'subscription_plan_id' => $plan->id,
            'plan_name' => $planName,
            'start_date' => Carbon::now(),
            'end_date' => Carbon::now()->addMonth(),
            'amount' => $amount,
            'status' => $status,
            'payment_gateway' => $gatewayResponse['gateway'] ?? 'mock',
        ]);
    }

    private function createTransactionRecord(Subscription $subscription, string $transactionId, int $amount, string $paymentMethod, string $status, array $gatewayResponse, ?string $message): Transaction
    {
        return Transaction::create([
            'user_id' => auth()->id(),
            'subscription_id' => $subscription->id,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'payment_details' => $gatewayResponse,
            'payment_gateway' => $gatewayResponse['gateway'] ?? 'mock',
            'gateway_response' => $gatewayResponse,
            'response' => $message,
        ]);
    }

    private function ensurePlan(array $planData): SubscriptionPlan
    {
        $plan = SubscriptionPlan::where('name', $planData['name'])->first();

        if ($plan) {
            return $plan;
        }

        return SubscriptionPlan::create([
            'name' => $planData['name'],
            'status' => 'active',
            'monthly_price' => $planData['amount'],
            'visit_cadence' => $planData['cadence'],
            'end_date' => Carbon::now()->addYear()->toDateString(),
            'features' => $planData['features'],
            'priority_support' => true,
            'emergency_assistance' => $planData['name'] === 'Premium',
        ]);
    }

    private function planDetails(string $planName): array
    {
        $planMap = [
            'Basic' => [
                'name' => 'Basic',
                'amount' => 100,
                'cadence' => 'Monthly visit',
                'features' => ['Health inspection', 'Care checklist', 'Reminder setup'],
            ],
            'Standard' => [
                'name' => 'Standard',
                'amount' => 300,
                'cadence' => 'Two visits per month',
                'features' => ['Fertilizer application', 'Plant care reminders', 'Priority booking'],
            ],
            'Premium' => [
                'name' => 'Premium',
                'amount' => 500,
                'cadence' => 'Weekly visit',
                'features' => ['Emergency assistance', 'Detailed health reports', 'Priority support'],
            ],
        ];

        return $planMap[$planName] ?? $planMap['Basic'];
    }

    private function buildSignature(string $orderId, string $paymentId, string $signature): array
    {
        $secret = env('RAZORPAY_KEY_SECRET');

        if (empty($secret)) {
            return ['valid' => false];
        }

        $body = $orderId . '|' . $paymentId;
        $expected = hash_hmac('sha256', $body, $secret);

        return ['valid' => hash_equals($expected, $signature)];
    }

    private function paymentGatewayEnabled(): bool
    {
        return ! empty(env('RAZORPAY_KEY_ID')) && ! empty(env('RAZORPAY_KEY_SECRET')) && str_contains(strtolower((string) env('PAYMENT_GATEWAY', 'razorpay')), 'razorpay');
    }
}
