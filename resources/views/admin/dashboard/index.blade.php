@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@section('content')

<div class="dashboard-hero">
    <div>
        <h1>لوحة التحكم</h1>
        <p>نظرة عامة على أداء النظام والإحصائيات اليومية</p>
    </div>
</div>
<style>.dashboard-title {
    font-size: 28px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 6px;
}

.dashboard-subtitle {
    color: #6b7280;
    margin-bottom: 24px;
    font-size: 14px;
}

.section-title {
    font-size: 18px;
    font-weight: 800;
    color: #111827;
    margin: 24px 0 14px;
    padding-right: 12px;
    border-right: 4px solid #2563eb;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.stat-card {
    background: #fff;
    border-radius: 14px;
    padding: 16px 18px;
    border: 1px solid #e5e7eb;
    min-height: 95px;
    transition: .2s;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,.06);
}

.stat-label {
    color: #6b7280;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 8px;
}

.stat-value {
    color: #111827;
    font-size: 30px;
    font-weight: 900;
    line-height: 1;
}

.charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-top: 24px;
}

.chart-card {
    background: #fff;
    border-radius: 16px;
    padding: 18px;
    border: 1px solid #e5e7eb;
}

.chart-title {
    font-size: 16px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 14px;
}

.chart-card canvas {
    max-height: 280px !important;
}

@media (max-width: 1200px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .stat-card {
        min-height: 85px;
    }

    .stat-value {
        font-size: 26px;
    }
}</style>
@php
    $sections = [
        [
            'title' => 'المطابخ',
            'items' => [
                ['label' => 'إجمالي المطابخ', 'value' => $data['kitchens']['total'], 'icon' => '🍽️'],
                ['label' => 'مطابخ مقبولة', 'value' => $data['kitchens']['approved'], 'icon' => '✅'],
                ['label' => 'قيد الانتظار', 'value' => $data['kitchens']['pending'], 'icon' => '⏳'],
                ['label' => 'مفتوحة الآن', 'value' => $data['kitchens']['open_now'], 'icon' => '🟢'],
            ],
        ],
        [
            'title' => 'المستخدمين',
            'items' => [
                ['label' => 'إجمالي المستخدمين', 'value' => $data['users']['total'], 'icon' => '👥'],
                ['label' => 'العملاء', 'value' => $data['users']['clients'], 'icon' => '🧑'],
                ['label' => 'أصحاب المطابخ', 'value' => $data['users']['kitchens'], 'icon' => '👨‍🍳'],
                ['label' => 'النشطين', 'value' => $data['users']['active'], 'icon' => '⚡'],
            ],
        ],
        [
            'title' => 'الوجبات',
            'items' => [
                ['label' => 'إجمالي الوجبات', 'value' => $data['meals']['total'], 'icon' => '🍔'],
                ['label' => 'وجبات مقبولة', 'value' => $data['meals']['approved'], 'icon' => '✅'],
                ['label' => 'قيد الانتظار', 'value' => $data['meals']['pending'], 'icon' => '⏳'],
                ['label' => 'متاحة الآن', 'value' => $data['meals']['available_now'], 'icon' => '🟢'],
            ],
        ],
        [
            'title' => 'الطلبات',
            'items' => [
                ['label' => 'إجمالي الطلبات', 'value' => $data['orders']['total'], 'icon' => '📦'],
                ['label' => 'طلبات اليوم', 'value' => $data['orders']['today'], 'icon' => '📅'],
                ['label' => 'تم توصيلها اليوم', 'value' => $data['orders']['delivered_today'], 'icon' => '🚚'],
                ['label' => 'إيرادات اليوم', 'value' => number_format($data['orders']['today_revenue'], 2), 'icon' => '💰'],
            ],
        ],
    ];
@endphp

@foreach($sections as $section)
    <div class="dashboard-section">
        <div class="dashboard-section-title">
            {{ $section['title'] }}
        </div>

        <div class="dashboard-stats-grid">
            @foreach($section['items'] as $item)
                <div class="dashboard-stat-card">
                    <div class="dashboard-stat-icon">
                        {{ $item['icon'] }}
                    </div>

                    <div class="dashboard-stat-content">
                        <div class="dashboard-stat-label">
                            {{ $item['label'] }}
                        </div>

                        <div class="dashboard-stat-value">
                            {{ $item['value'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

{{-- <div class="dashboard-charts-grid">

    <div class="dashboard-chart-card">
        <div class="dashboard-chart-header">
            <div>
                <h3>الطلبات آخر 7 أيام</h3>
                <p>عدد الطلبات اليومية</p>
            </div>
        </div>

        <canvas id="ordersChart" height="130"></canvas>
    </div>

    <div class="dashboard-chart-card">
        <div class="dashboard-chart-header">
            <div>
                <h3>الإيرادات آخر 7 أيام</h3>
                <p>إجمالي الإيرادات اليومية</p>
            </div>
        </div>

        <canvas id="revenueChart" height="130"></canvas>
    </div>

</div> --}}

@endsection

@push('styles')
<style>
    .dashboard-hero {
        background: linear-gradient(135deg, #111827, #2563eb);
        color: #fff;
        border-radius: 24px;
        padding: 32px;
        margin-bottom: 28px;
        box-shadow: 0 14px 32px rgba(37, 99, 235, .22);
    }

    .dashboard-hero h1 {
        margin: 0 0 8px;
        font-size: 32px;
        font-weight: 900;
    }

    .dashboard-hero p {
        margin: 0;
        color: #dbeafe;
        font-weight: 700;
    }

    .dashboard-section {
        margin-bottom: 28px;
    }

    .dashboard-section-title {
        font-size: 20px;
        font-weight: 900;
        color: #111827;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .dashboard-section-title::before {
        content: '';
        width: 5px;
        height: 24px;
        background: #2563eb;
        border-radius: 999px;
    }

    .dashboard-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .dashboard-stat-card {
        background: #fff;
        border-radius: 20px;
        padding: 22px;
        box-shadow: 0 8px 24px rgba(0,0,0,.07);
        border: 1px solid #eef2f7;
        display: flex;
        align-items: center;
        gap: 16px;
        min-height: 125px;
    }

    .dashboard-stat-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .dashboard-stat-label {
        color: #64748b;
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .dashboard-stat-value {
        color: #0f172a;
        font-size: 32px;
        font-weight: 900;
        line-height: 1;
    }

    .dashboard-charts-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-top: 30px;
    }

    .dashboard-chart-card {
        background: #fff;
        border-radius: 22px;
        padding: 24px;
        box-shadow: 0 8px 24px rgba(0,0,0,.07);
        border: 1px solid #eef2f7;
    }

    .dashboard-chart-header {
        margin-bottom: 18px;
    }

    .dashboard-chart-header h3 {
        margin: 0 0 6px;
        font-size: 20px;
        font-weight: 900;
        color: #111827;
    }

    .dashboard-chart-header p {
        margin: 0;
        color: #64748b;
        font-weight: 700;
    }

    @media (max-width: 1200px) {
        .dashboard-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .dashboard-stats-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-hero {
            padding: 24px;
        }

        .dashboard-hero h1 {
            font-size: 26px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const trend = @json($data['orders']['last_7_days_trend']);

    const labels = trend.map(item => item.date);
    const orders = trend.map(item => item.orders);
    const revenue = trend.map(item => item.revenue);

    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    };

    new Chart(document.getElementById('ordersChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'عدد الطلبات',
                data: orders,
                tension: 0.4,
                fill: true
            }]
        },
        options: chartOptions
    });

    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'الإيرادات',
                data: revenue
            }]
        },
        options: chartOptions
    });
</script>
@endpush