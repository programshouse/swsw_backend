@extends('admin.layouts.app')

@section('title', 'التقرير المالي للطلبات')

@section('content')
    <div dir="rtl">

        <div class="page-head">
            <div>
                <h1 class="page-title" style="margin-bottom: 6px;">
                    التقرير المالي للطلبات
                </h1>

                <p style="margin: 0; color: #6b7280;">
                    تقرير يومي يوضح ما دفعه العميل ومستحقات المطبخ والدليفري.
                </p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="section-card">
            <div class="section-header">
                <h3>فلترة التقرير</h3>
            </div>

            <form
                method="GET"
                action="{{ route(
                    'admin.order-financial-reports.index'
                ) }}"
            >
                <div
                    style="
                        display: grid;
                        grid-template-columns:
                            repeat(3, minmax(0, 1fr));
                        gap: 18px;
                    "
                    class="report-filters-grid"
                >
                    <div class="form-group">
                        <label class="form-label" for="date">
                            اليوم
                        </label>

                        <input
                            type="date"
                            id="date"
                            name="date"
                            class="form-input"
                            value="{{ $date }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="status">
                            حالة الطلب
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-input"
                        >
                            <option value="">
                                جميع الحالات
                            </option>

                            @foreach($statuses as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    {{ $status === $value
                                        ? 'selected'
                                        : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="keyword">
                            البحث
                        </label>

                        <input
                            type="text"
                            id="keyword"
                            name="keyword"
                            class="form-input"
                            value="{{ $keyword }}"
                            placeholder="رقم الطلب، العميل أو المطبخ"
                        >
                    </div>
                </div>

                <div
                    style="
                        display: flex;
                        gap: 10px;
                        margin-top: 20px;
                    "
                >
                    <button type="submit" class="save-btn">
                        عرض التقرير
                    </button>

                    <a
                        href="{{ route(
                            'admin.order-financial-reports.index'
                        ) }}"
                        class="cancel-btn"
                        style="text-decoration: none;"
                    >
                        إعادة ضبط
                    </a>
                </div>
            </form>
        </div>

        {{-- Summary --}}
        <div
            class="stats-grid"
            style="
                grid-template-columns:
                    repeat(4, minmax(0, 1fr));
            "
        >
            <div class="stat-card">
                <div class="stat-label">
                    عدد الطلبات
                </div>

                <div class="stat-value">
                    {{ $summary['orders_count'] }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    ما دفعه العملاء
                </div>

                <div class="stat-value">
                    {{ number_format(
                        $summary['users_paid'],
                        2
                    ) }}
                </div>

                <div style="color: #6b7280; margin-top: 5px;">
                    جنيه
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    صافي المطابخ
                </div>

                <div class="stat-value">
                    {{ number_format(
                        $summary['kitchens_net'],
                        2
                    ) }}
                </div>

                <div style="color: #6b7280; margin-top: 5px;">
                    جنيه
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    مستحقات الدليفري
                </div>

                <div class="stat-value">
                    {{ number_format(
                        $summary['deliveries_amount'],
                        2
                    ) }}
                </div>

                <div style="color: #6b7280; margin-top: 5px;">
                    جنيه
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    رسوم خدمات التطبيق
                </div>

                <div class="stat-value">
                    {{ number_format(
                        $summary['application_service_revenue'],
                        2
                    ) }}
                </div>

                <div style="color: #6b7280; margin-top: 5px;">
                    جنيه
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    إجمالي القيمة المضافة
                </div>

                <div class="stat-value">
                    {{ number_format(
                        $summary['vat_total'],
                        2
                    ) }}
                </div>

                <div style="color: #6b7280; margin-top: 5px;">
                    جنيه
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    إجمالي الخصومات
                </div>

                <div class="stat-value">
                    {{ number_format(
                        $summary['discounts_total'],
                        2
                    ) }}
                </div>

                <div style="color: #6b7280; margin-top: 5px;">
                    جنيه
                </div>
            </div>
        </div>

        {{-- Orders table --}}
        <div class="table-card">
            <div class="table-header">
                <div>
                    <div class="table-title">
                        طلبات يوم {{ $date }}
                    </div>

                    <div
                        style="
                            color: #6b7280;
                            margin-top: 6px;
                        "
                    >
                        إجمالي النتائج:
                        {{ $orders->total() }}
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>العميل</th>
                            <th>المطبخ</th>
                            <th>الدليفري</th>
                            <th>قيمة الوجبات</th>
                            <th>VAT</th>
                            <th>التوصيل</th>
                            <th>خدمة العميل</th>
                            <th>خدمة المطبخ</th>
                            <th>صافي المطبخ</th>
                            <th>العميل دفع</th>
                            <th>الدليفري استلم</th>
                            <th>الحالة</th>
                            <th>التفاصيل</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($orders as $order)
                            @php
                                $deliveryOrder =
                                    $order->deliveryOrders->first();

                                $deliveryName =
                                    $deliveryOrder?->deliveryUser?->name
                                    ?? 'لم يتم التعيين';

                                $subtotal =
                                    (float) ($order->subtotal ?? 0);

                                $vat =
                                    (float) ($order->vat_value ?? 0);

                                $deliveryPrice =
                                    (float) (
                                        $order->delivery_price ?? 0
                                    );

                                $clientFee =
                                    (float) (
                                        $order->client_service_fee ?? 0
                                    );

                                $kitchenFee =
                                    (float) (
                                        $order->kitchen_service_fee ?? 0
                                    );

                                $kitchenNet =
                                    (float) (
                                        $order->kitchen_net_amount
                                        ?? max(
                                            $subtotal - $kitchenFee,
                                            0
                                        )
                                    );

                                $userPaid =
                                    (float) ($order->total ?? 0);
                            @endphp

                            <tr>
                                <td>
                                    <strong>
                                        {{ $order->number }}
                                    </strong>
                                </td>

                                <td>
                                    <div>
                                        {{ $order->user?->name ?? '-' }}
                                    </div>

                                    <small style="color: #6b7280;">
                                        {{ $order->user?->phone }}
                                    </small>
                                </td>

                                <td>
                                    {{ $order->kitchen?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ $deliveryName }}
                                </td>

                                <td>
                                    {{ number_format($subtotal, 2) }}
                                </td>

                                <td>
                                    {{ number_format($vat, 2) }}
                                </td>

                                <td>
                                    {{ number_format(
                                        $deliveryPrice,
                                        2
                                    ) }}
                                </td>

                                <td>
                                    <span
                                        style="
                                            background: #dbeafe;
                                            color: #1d4ed8;
                                            padding: 6px 10px;
                                            border-radius: 999px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ number_format(
                                            $clientFee,
                                            2
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    <span
                                        style="
                                            background: #f3e8ff;
                                            color: #7e22ce;
                                            padding: 6px 10px;
                                            border-radius: 999px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ number_format(
                                            $kitchenFee,
                                            2
                                        ) }}
                                    </span>
                                </td>

                                <td>
                                    <strong style="color: #166534;">
                                        {{ number_format(
                                            $kitchenNet,
                                            2
                                        ) }}
                                    </strong>
                                </td>

                                <td>
                                    <strong style="color: #1d4ed8;">
                                        {{ number_format(
                                            $userPaid,
                                            2
                                        ) }}
                                    </strong>
                                </td>

                                <td>
                                    <strong style="color: #b45309;">
                                        {{ number_format(
                                            $deliveryPrice,
                                            2
                                        ) }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="status-badge">
                                        {{ $statuses[$order->status]
                                            ?? $order->status }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ route(
                                            'admin.order-financial-reports.show',
                                            $order
                                        ) }}"
                                        class="view-btn"
                                    >
                                        عرض التفاصيل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="empty">
                                    لا توجد طلبات في هذا اليوم.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div style="margin-top: 22px;">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media(max-width: 1100px) {
            .stats-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr)) !important;
            }

            .report-filters-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media(max-width: 650px) {
            .stats-grid,
            .report-filters-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush