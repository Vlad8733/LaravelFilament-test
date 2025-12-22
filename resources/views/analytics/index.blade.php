@extends('layouts.app')

@section('title', __('analytics.title'))

@push('styles')
    @vite('resources/css/analytics/analytics.css')
@endpush

@section('content')
<div class="analytics-page">
    <div class="analytics-container">
        <!-- Back Link -->
        <a href="{{ route('settings.index') }}" class="analytics-back">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            {{ __('analytics.back_to_settings') }}
        </a>

        <!-- Header -->
        <div class="analytics-header">
            <div class="analytics-header-content">
                <h1 class="analytics-title">
                    <svg class="analytics-title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    {{ __('analytics.title') }}
                </h1>
                <p class="analytics-subtitle">{{ __('analytics.subtitle') }}</p>
            </div>
            
            <!-- Period Selector -->
            <div class="analytics-period-selector">
                <button type="button" class="period-btn active" data-period="7days">{{ __('analytics.7_days') }}</button>
                <button type="button" class="period-btn" data-period="30days">{{ __('analytics.30_days') }}</button>
                <button type="button" class="period-btn" data-period="year">{{ __('analytics.year') }}</button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="analytics-stats-grid">
            <div class="stats-card">
                <div class="stats-card-icon revenue">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="stats-card-content">
                    <span class="stats-card-label">{{ __('analytics.total_revenue') }}</span>
                    <span class="stats-card-value" id="stat-revenue">${{ number_format($stats['revenue']['value'], 2) }}</span>
                    <span class="stats-card-change {{ $stats['revenue']['trend'] }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($stats['revenue']['trend'] === 'up')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                        <span id="stat-revenue-change">{{ $stats['revenue']['change'] }}%</span>
                    </span>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="stats-card-icon orders">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="stats-card-content">
                    <span class="stats-card-label">{{ __('analytics.total_orders') }}</span>
                    <span class="stats-card-value" id="stat-orders">{{ number_format($stats['orders']['value']) }}</span>
                    <span class="stats-card-change {{ $stats['orders']['trend'] }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($stats['orders']['trend'] === 'up')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                        <span id="stat-orders-change">{{ $stats['orders']['change'] }}%</span>
                    </span>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="stats-card-icon customers">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="stats-card-content">
                    <span class="stats-card-label">{{ __('analytics.new_customers') }}</span>
                    <span class="stats-card-value" id="stat-customers">{{ number_format($stats['customers']['value']) }}</span>
                    <span class="stats-card-change {{ $stats['customers']['trend'] }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($stats['customers']['trend'] === 'up')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                        <span id="stat-customers-change">{{ $stats['customers']['change'] }}%</span>
                    </span>
                </div>
            </div>
            
            <div class="stats-card">
                <div class="stats-card-icon avg-order">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="stats-card-content">
                    <span class="stats-card-label">{{ __('analytics.avg_order_value') }}</span>
                    <span class="stats-card-value" id="stat-avg-order">${{ number_format($stats['avgOrder']['value'], 2) }}</span>
                    <span class="stats-card-change {{ $stats['avgOrder']['trend'] }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($stats['avgOrder']['trend'] === 'up')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            @endif
                        </svg>
                        <span id="stat-avg-order-change">{{ $stats['avgOrder']['change'] }}%</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="analytics-charts-grid">
            <!-- Sales Chart -->
            <div class="chart-card chart-card-large">
                <div class="chart-header">
                    <h3 class="chart-title">{{ __('analytics.sales_overview') }}</h3>
                    <div class="chart-legend">
                        <span class="legend-item revenue"><span class="legend-dot"></span>{{ __('analytics.revenue') }}</span>
                        <span class="legend-item orders"><span class="legend-dot"></span>{{ __('analytics.orders') }}</span>
                    </div>
                </div>
                <div class="chart-body">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            
            <!-- Category Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">{{ __('analytics.products_by_category') }}</h3>
                </div>
                <div class="chart-body chart-body-centered">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
            
            <!-- Order Status Distribution -->
            <div class="chart-card">
                <div class="chart-header">
                    <h3 class="chart-title">{{ __('analytics.order_status') }}</h3>
                </div>
                <div class="chart-body chart-body-centered">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Grid -->
        <div class="analytics-bottom-grid">
            <!-- Top Products -->
            <div class="analytics-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        {{ __('analytics.top_products') }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="top-products-list">
                        @forelse($topProducts as $index => $product)
                            <div class="top-product-item">
                                <span class="product-rank">#{{ $index + 1 }}</span>
                                <div class="product-info">
                                    <span class="product-name">{{ $product['name'] }}</span>
                                    <span class="product-stats">{{ $product['sold'] }} {{ __('analytics.sold') }} • ${{ number_format($product['revenue'], 2) }}</span>
                                </div>
                                <div class="product-bar-wrapper">
                                    @php
                                        $maxSold = $topProducts->max('sold') ?? 1;
                                        $percentage = ($product['sold'] / $maxSold) * 100;
                                    @endphp
                                    <div class="product-bar" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <span>{{ __('analytics.no_sales_yet') }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="analytics-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('analytics.recent_orders') }}
                    </h3>
                    <a href="{{ route('orders.tracking.search') }}" class="card-action">{{ __('analytics.view_all') }}</a>
                </div>
                <div class="card-body">
                    <div class="recent-orders-list">
                        @forelse($recentOrders as $order)
                            <div class="recent-order-item">
                                <div class="order-info">
                                    <span class="order-number">{{ $order['order_number'] }}</span>
                                    <span class="order-customer">{{ $order['customer'] }}</span>
                                </div>
                                <div class="order-meta">
                                    <span class="order-total">${{ number_format($order['total'], 2) }}</span>
                                    <span class="order-status status-{{ $order['status_color'] }}">{{ $order['status'] }}</span>
                                </div>
                                <span class="order-time">{{ $order['created_at'] }}</span>
                            </div>
                        @empty
                            <div class="empty-state">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                                <span>{{ __('analytics.no_orders_yet') }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Footer -->
        <div class="analytics-footer">
            <div class="quick-stat">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>{{ $stats['totalProducts'] }} {{ __('analytics.active_products') }}</span>
            </div>
            <div class="quick-stat">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span>{{ $stats['totalCategories'] }} {{ __('analytics.categories') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart.js default settings
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
    Chart.defaults.font.family = "'Inter', sans-serif";
    
    // Initial data from server
    const salesData = @json($salesData);
    const categoryData = @json($categoryData);
    const orderStatusData = @json($orderStatusData);
    
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [
                {
                    label: '{{ __("analytics.revenue") }}',
                    data: salesData.revenue,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#1a1a1a',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: '{{ __("analytics.orders") }}',
                    data: salesData.orders,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#1a1a1a',
                    pointBorderWidth: 2,
                    pointHoverRadius: 6,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#fff',
                    bodyColor: '#9ca3af',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        maxRotation: 0,
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(255, 255, 255, 0.05)',
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        stepSize: 1,
                    }
                },
            }
        }
    });
    
    // Category Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.labels,
            datasets: [{
                data: categoryData.data,
                backgroundColor: categoryData.colors,
                borderColor: '#1a1a1a',
                borderWidth: 3,
                hoverOffset: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#fff',
                    bodyColor: '#9ca3af',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                }
            }
        }
    });
    
    // Order Status Chart
    const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    const orderStatusChart = new Chart(orderStatusCtx, {
        type: 'pie',
        data: {
            labels: orderStatusData.labels,
            datasets: [{
                data: orderStatusData.data,
                backgroundColor: orderStatusData.colors,
                borderColor: '#1a1a1a',
                borderWidth: 3,
                hoverOffset: 10,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                    }
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#fff',
                    bodyColor: '#9ca3af',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                }
            }
        }
    });
    
    // Period selector
    const periodButtons = document.querySelectorAll('.period-btn');
    periodButtons.forEach(btn => {
        btn.addEventListener('click', async function() {
            periodButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const period = this.dataset.period;
            
            try {
                const response = await fetch(`/analytics/data?period=${period}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                
                // Update sales chart
                salesChart.data.labels = data.sales.labels;
                salesChart.data.datasets[0].data = data.sales.revenue;
                salesChart.data.datasets[1].data = data.sales.orders;
                salesChart.update();
                
                // Update stats
                document.getElementById('stat-revenue').textContent = '$' + parseFloat(data.stats.revenue.value).toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('stat-revenue-change').textContent = data.stats.revenue.change + '%';
                
                document.getElementById('stat-orders').textContent = data.stats.orders.value.toLocaleString();
                document.getElementById('stat-orders-change').textContent = data.stats.orders.change + '%';
                
                document.getElementById('stat-customers').textContent = data.stats.customers.value.toLocaleString();
                document.getElementById('stat-customers-change').textContent = data.stats.customers.change + '%';
                
                document.getElementById('stat-avg-order').textContent = '$' + parseFloat(data.stats.avgOrder.value).toLocaleString('en-US', {minimumFractionDigits: 2});
                document.getElementById('stat-avg-order-change').textContent = data.stats.avgOrder.change + '%';
                
            } catch (error) {
                console.error('Failed to fetch analytics data:', error);
            }
        });
    });
});
</script>
@endsection
