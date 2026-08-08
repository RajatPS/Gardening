<?php

namespace App\Http\Controllers\Admin;

use App\Services\RevenueService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct(private readonly RevenueService $revenueService)
    {
    }
    public function revenue()
    {
        $data = $this->getRevenueReportData();
        return view('admin.reports.revenue', compact('data'));
    }

    public function sales()
    {
        $data = $this->getSalesReportData();
        return view('admin.reports.sales', compact('data'));
    }

    public function users()
    {
        $data = $this->getUsersReportData();
        return view('admin.reports.users', compact('data'));
    }

    public function subscriptions()
    {
        $data = $this->getSubscriptionsReportData();
        return view('admin.reports.subscriptions', compact('data'));
    }

    public function products()
    {
        $data = $this->getProductsReportData();
        return view('admin.reports.products', compact('data'));
    }

    public function staffPerformance()
    {
        $data = $this->getStaffPerformanceData();
        return view('admin.reports.staff-performance', compact('data'));
    }

    private function getRevenueReportData()
    {
        $dailyRevenue = [];

        foreach (range(29, 0) as $dayOffset) {
            $date = Carbon::today()->subDays($dayOffset);
            $dailyRevenue[] = [
                'date' => $date->toDateString(),
                'total' => $this->revenueService->getRevenueForDate($date),
            ];
        }

        return collect($dailyRevenue)->paginate(30);
    }

    private function getSalesReportData()
    {
        return DB::table('orders')
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('status')
            ->get();
    }

    private function getUsersReportData()
    {
        return [
            'total' => DB::table('users')->count(),
            'active' => DB::table('users')->where('status', 'active')->count(),
            'suspended' => DB::table('users')->where('status', 'suspended')->count(),
            'monthly_growth' => $this->getMonthlyUserGrowth()
        ];
    }

    private function getSubscriptionsReportData()
    {
        return [
            'active' => DB::table('subscription_plans')->where('status', 'active')->count(),
            'expiring' => DB::table('subscription_plans')->where('end_date', '<=', Carbon::now()->addDays(7))->count(),
            'cancelled' => DB::table('subscription_plans')->where('status', 'cancelled')->count(),
        ];
    }

    private function getProductsReportData()
    {
        return [
            'total' => DB::table('products')->count(),
            'active' => DB::table('products')->where('is_active', true)->count(),
            'low_stock' => DB::table('products')->whereBetween('stock', [1, 10])->count(),
            'out_of_stock' => DB::table('products')->where('stock', 0)->count(),
        ];
    }

    private function getStaffPerformanceData()
    {
        return DB::table('users')
            ->where('role', 'staff')
            ->selectRaw('users.id, users.name, COUNT(service_bookings.id) as appointments, COUNT(DISTINCT orders.id) as orders')
            ->leftJoin('service_bookings', 'users.id', '=', 'service_bookings.assigned_staff_id')
            ->leftJoin('orders', 'users.id', '=', 'orders.assigned_staff_id')
            ->groupBy('users.id', 'users.name')
            ->get();
    }

    private function getMonthlyUserGrowth()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i)->format('Y-m-01');
            $count = DB::table('users')->whereDate('created_at', '<=', $date)->count();
            $data[Carbon::parse($date)->format('M Y')] = $count;
        }
        return $data;
    }
}
