<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Общая статистика
        $stats = $this->getGeneralStats();

        // Данные для графиков
        $salesData = $this->getSalesData();
        $categoryData = $this->getCategoryData();
        $topProducts = $this->getTopProducts();
        $recentOrders = $this->getRecentOrders();
        $orderStatusData = $this->getOrderStatusData();

        return view('analytics.index', compact(
            'stats',
            'salesData',
            'categoryData',
            'topProducts',
            'recentOrders',
            'orderStatusData'
        ));
    }

    /**
     * API для обновления графиков
     */
    public function getData(Request $request)
    {
        $period = $request->get('period', '7days');

        return response()->json([
            'sales' => $this->getSalesData($period),
            'categories' => $this->getCategoryData(),
            'orderStatus' => $this->getOrderStatusData(),
            'stats' => $this->getGeneralStats($period),
        ]);
    }

    /**
     * Общая статистика
     */
    private function getGeneralStats($period = '30days')
    {
        $days = $period === '7days' ? 7 : ($period === '30days' ? 30 : 365);
        $startDate = Carbon::now()->subDays($days);
        $prevStartDate = Carbon::now()->subDays($days * 2);

        // Текущий период
        $currentRevenue = Order::where('created_at', '>=', $startDate)->sum('total');
        $currentOrders = Order::where('created_at', '>=', $startDate)->count();
        $currentCustomers = User::where('created_at', '>=', $startDate)->where('role', 'user')->count();

        // Предыдущий период для сравнения
        $prevRevenue = Order::whereBetween('created_at', [$prevStartDate, $startDate])->sum('total');
        $prevOrders = Order::whereBetween('created_at', [$prevStartDate, $startDate])->count();
        $prevCustomers = User::whereBetween('created_at', [$prevStartDate, $startDate])->where('role', 'user')->count();

        // Расчет процентного изменения
        $revenueChange = $prevRevenue > 0 ? round((($currentRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;
        $ordersChange = $prevOrders > 0 ? round((($currentOrders - $prevOrders) / $prevOrders) * 100, 1) : 0;
        $customersChange = $prevCustomers > 0 ? round((($currentCustomers - $prevCustomers) / $prevCustomers) * 100, 1) : 0;

        // Средний чек
        $avgOrderValue = $currentOrders > 0 ? round($currentRevenue / $currentOrders, 2) : 0;
        $prevAvgOrderValue = $prevOrders > 0 ? round($prevRevenue / $prevOrders, 2) : 0;
        $avgOrderChange = $prevAvgOrderValue > 0 ? round((($avgOrderValue - $prevAvgOrderValue) / $prevAvgOrderValue) * 100, 1) : 0;

        return [
            'revenue' => [
                'value' => $currentRevenue,
                'change' => $revenueChange,
                'trend' => $revenueChange >= 0 ? 'up' : 'down',
            ],
            'orders' => [
                'value' => $currentOrders,
                'change' => $ordersChange,
                'trend' => $ordersChange >= 0 ? 'up' : 'down',
            ],
            'customers' => [
                'value' => $currentCustomers,
                'change' => $customersChange,
                'trend' => $customersChange >= 0 ? 'up' : 'down',
            ],
            'avgOrder' => [
                'value' => $avgOrderValue,
                'change' => $avgOrderChange,
                'trend' => $avgOrderChange >= 0 ? 'up' : 'down',
            ],
            'totalProducts' => Product::where('is_active', true)->count(),
            'totalCategories' => Category::where('is_active', true)->count(),
        ];
    }

    /**
     * Данные о продажах для графика
     */
    private function getSalesData($period = '7days')
    {
        $days = $period === '7days' ? 7 : ($period === '30days' ? 30 : 365);
        $labels = [];
        $revenue = [];
        $orders = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format($days > 30 ? 'M Y' : 'M d');

            $dayRevenue = Order::whereDate('created_at', $date->toDateString())->sum('total');
            $dayOrders = Order::whereDate('created_at', $date->toDateString())->count();

            $revenue[] = round($dayRevenue, 2);
            $orders[] = $dayOrders;
        }

        return [
            'labels' => $labels,
            'revenue' => $revenue,
            'orders' => $orders,
        ];
    }

    /**
     * Данные по категориям для круговой диаграммы
     */
    private function getCategoryData()
    {
        $categories = Category::withCount(['products' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->orderByDesc('products_count')
            ->limit(6)
            ->get();

        $labels = [];
        $data = [];
        $colors = ['#f59e0b', '#3b82f6', '#10b981', '#8b5cf6', '#ef4444', '#06b6d4'];

        foreach ($categories as $index => $category) {
            $labels[] = $category->name;
            $data[] = $category->products_count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_slice($colors, 0, count($labels)),
        ];
    }

    /**
     * Топ продуктов по продажам
     */
    private function getTopProducts($limit = 5)
    {
        return OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total) as total_revenue'))
            ->with('product:id,name,slug')
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product->name ?? 'Unknown',
                    'slug' => $item->product->slug ?? '',
                    'sold' => $item->total_sold,
                    'revenue' => round($item->total_revenue, 2),
                ];
            });
    }

    /**
     * Последние заказы
     */
    private function getRecentOrders($limit = 5)
    {
        return Order::with(['user:id,name,email', 'status:id,name,color'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->user->name ?? $order->customer_name,
                    'total' => $order->total,
                    'status' => $order->status->name ?? 'Pending',
                    'status_color' => $order->status->color ?? 'gray',
                    'created_at' => $order->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Распределение статусов заказов
     */
    private function getOrderStatusData()
    {
        $statuses = Order::select('order_status_id', DB::raw('COUNT(*) as count'))
            ->with('status:id,name,color')
            ->groupBy('order_status_id')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        $colorMap = [
            'gray' => '#6b7280',
            'yellow' => '#f59e0b',
            'blue' => '#3b82f6',
            'green' => '#10b981',
            'red' => '#ef4444',
            'indigo' => '#6366f1',
            'purple' => '#8b5cf6',
        ];

        foreach ($statuses as $status) {
            $labels[] = $status->status->name ?? 'Unknown';
            $data[] = $status->count;
            $colors[] = $colorMap[$status->status->color ?? 'gray'] ?? '#6b7280';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }
}
