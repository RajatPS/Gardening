<?php

namespace App\Http\Controllers\Admin;

use App\Services\RevenueService;
use Illuminate\Routing\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ServiceBooking;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __construct(private readonly RevenueService $revenueService)
    {
    }

    public function index()
    {
        // Revenue Statistics
        $dailyRevenue = $this->revenueService->getRevenueByPeriod('daily');
        $weeklyRevenue = $this->revenueService->getRevenueByPeriod('weekly');
        $monthlyRevenue = $this->revenueService->getRevenueByPeriod('monthly');
        $yearlyRevenue = $this->revenueService->getRevenueByPeriod('yearly');

        // Sales Statistics
        $totalOrders = DB::table('orders')->count();
        $pendingOrders = DB::table('orders')->where('status', 'pending')->count();
        $completedOrders = DB::table('orders')->where('status', 'completed')->count();
        $cancelledOrders = DB::table('orders')->where('status', 'cancelled')->count();

        // User Statistics
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $suspendedUsers = User::where('status', 'suspended')->count();

        // Product Statistics
        $totalProducts = Product::count();
        $lowStockProducts = Product::where('stock', '<=', 10)->where('stock', '>', 0)->count();
        $outOfStockProducts = Product::where('stock', 0)->count();

        // Appointment Statistics
        $upcomingVisits = ServiceBooking::where('booking_date', '>', Carbon::now())->count();
        $todaysVisits = ServiceBooking::whereDate('booking_date', Carbon::today())->count();
        $completedVisits = ServiceBooking::where('status', 'completed')->count();

        // Subscription Statistics
        $activePlans = SubscriptionPlan::where('status', 'active')->count();
        $expiringPlans = SubscriptionPlan::where('end_date', '<=', Carbon::now()->addDays(7))->count();

        // Chart Data
        $revenueChart = $this->revenueService->getRevenueChartData();
        $salesChart = $this->getSalesChartData();
        $userGrowthChart = $this->getUserGrowthChartData();
        $subscriptionGrowthChart = $this->getSubscriptionGrowthChartData();

        return view('admin.dashboard', compact(
            'dailyRevenue',
            'weeklyRevenue',
            'monthlyRevenue',
            'yearlyRevenue',
            'totalOrders',
            'pendingOrders',
            'completedOrders',
            'cancelledOrders',
            'totalUsers',
            'activeUsers',
            'suspendedUsers',
            'totalProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'upcomingVisits',
            'todaysVisits',
            'completedVisits',
            'activePlans',
            'expiringPlans',
            'revenueChart',
            'salesChart',
            'userGrowthChart',
            'subscriptionGrowthChart'
        ));
    }


    private function getSalesChartData()
    {
        return DB::table('orders')
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    private function getUserGrowthChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = User::whereDate('created_at', '<=', $date)->count();
            $data[Carbon::parse($date)->format('M d')] = $count;
        }
        return $data;
    }

    private function getSubscriptionGrowthChartData()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = SubscriptionPlan::whereDate('created_at', '<=', $date)->where('status', 'active')->count();
            $data[Carbon::parse($date)->format('M d')] = $count;
        }
        return $data;
    }
}
