@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@push('styles')
<style>
    .dashboard-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }

    .dashboard-subtitle {
        color: #64748b;
        margin-bottom: 28px;
    }

    .section-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin: 28px 0 14px;
        padding-right: 10px;
        border-right: 4px solid #2563eb;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .stat-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
        min-height: 120px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .stat-label {
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
    }

    .stat-value {
        color: #0f172a;
        font-size: 34px;
        font-weight: 900;
        margin-top: 14px;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-top: 24px;
    }

    .chart-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
    }

    .chart-title {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 18px;
    }

    @media (max-width: 1200px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="dashboard-title">لوحة التحكم</div>
<div class="dashboard-subtitle">نظرة عامة على أداء النظام</div>

<div class="section-title">المطابخ</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">إجمالي المطابخ</div>
        <div class="stat-value">{{ $data['kitchens']['total'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">مطابخ مقبولة</div>
        <div class="stat-value">{{ $data['kitchens']['approved'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">مطابخ قيد الانتظار</div>
        <div class="stat-value">{{ $data['kitchens']['pending'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">مطابخ مفتوحة الآن</div>
        <div class="stat-value">{{ $data['kitchens']['open_now'] }}</div>
    </div>
</div>

<div class="section-title">المستخدمين</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">إجمالي المستخدمين</div>
        <div class="stat-value">{{ $data['users']['total'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">العملاء</div>
        <div class="stat-value">{{ $data['users']['clients'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">أصحاب المطابخ</div>
        <div class="stat-value">{{ $data['users']['kitchens'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">المستخدمين النشطين</div>
        <div class="stat-value">{{ $data['users']['active'] }}</div>
    </div>
</div>

<div class="section-title">الوجبات</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">إجمالي الوجبات</div>
        <div class="stat-value">{{ $data['meals']['total'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">وجبات مقبولة</div>
        <div class="stat-value">{{ $data['meals']['approved'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">وجبات قيد الانتظار</div>
        <div class="stat-value">{{ $data['meals']['pending'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">متاحة الآن</div>
        <div class="stat-value">{{ $data['meals']['available_now'] }}</div>
    </div>
</div>

<div class="section-title">الطلبات</div>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">إجمالي الطلبات</div>
        <div class="stat-value">{{ $data['orders']['total'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">طلبات اليوم</div>
        <div class="stat-value">{{ $data['orders']['today'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">تم توصيلها اليوم</div>
        <div class="stat-value">{{ $data['orders']['delivered_today'] }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">إيرادات اليوم</div>
        <div class="stat-value">{{ number_format($data['orders']['today_revenue'], 2) }}</div>
    </div>
</div>

<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-title">الطلبات آخر 7 أيام</div>
        <canvas id="ordersChart" height="140"></canvas>
    </div>

    <div class="chart-card">
        <div class="chart-title">الإيرادات آخر 7 أيام</div>
        <canvas id="revenueChart" height="140"></canvas>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const trend = @json($data['orders']['last_7_days_trend']);

    const labels = trend.map(item => item.date);
    const orders = trend.map(item => item.orders);
    const revenue = trend.map(item => item.revenue);

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
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
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
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush