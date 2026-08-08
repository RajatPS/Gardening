<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\Subscription;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueService
{
    public function getRevenueByPeriod(string $period): float
    {
        return $this->buildRevenueQuery($period)->sum('amount') ?? 0;
    }

    public function getRevenueForDate(Carbon $date): float
    {
        return $this->buildRevenueQueryForRange($date->copy()->startOfDay(), $date->copy()->endOfDay())->sum('amount') ?? 0;
    }

    public function getRevenueChartData(): array
    {
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $data[$date->format('M d')] = $this->getRevenueForDate($date);
        }

        return $data;
    }

    protected function buildRevenueQuery(string $period)
    {
        $query = DB::query();

        [$start, $end] = match ($period) {
            'daily' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'yearly' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
        };

        $amounts = $this->getAllCompletedRevenueSources($start, $end);

        return $query->fromSub($amounts, 'revenue_totals')->selectRaw('SUM(amount) as amount');
    }

    protected function buildRevenueQueryForRange(Carbon $start, Carbon $end)
    {
        $amounts = $this->getAllCompletedRevenueSources($start, $end);

        return DB::query()->fromSub($amounts, 'revenue_totals')->selectRaw('SUM(amount) as amount');
    }

    protected function getAllCompletedRevenueSources(Carbon $start, Carbon $end)
    {
        $orderRevenue = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('CAST(total_amount AS DECIMAL(10,2)) as amount');

        $subscriptionRevenue = Transaction::query()
            ->whereNotNull('subscription_id')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('CAST(amount AS DECIMAL(10,2)) as amount');

        $appointmentRevenue = ServiceBooking::query()
            ->where('status', 'completed')
            ->whereNotNull('booking_date')
            ->whereBetween('booking_date', [$start, $end])
            ->selectRaw('CAST(0 AS DECIMAL(10,2)) as amount');

        return $orderRevenue
            ->unionAll($subscriptionRevenue)
            ->unionAll($appointmentRevenue);
    }
}
