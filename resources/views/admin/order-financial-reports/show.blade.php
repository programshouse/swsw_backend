@extends('admin.layouts.app')

@section('title', 'تفاصيل التقرير المالي للطلب')

@section('content')
    <div dir="rtl">

        <div class="page-head">
            <div>
                <h1 class="page-title" style="margin-bottom: 6px;">
                    تفاصيل الطلب {{ $order->number }}
                </h1>

                <p style="margin: 0; color: #6b7280;">
                    {{ $order->created_at?->format('Y-m-d h:i A') }}
                </p>
            </div>

            <a
                href="{{ route(
                    'admin.order-financial-reports.index',
                    [
                        'date' => $order->created_at
                            ?->toDateString()
                    ]
                ) }}"
                class="back-btn"
            >
                الرجوع للتقرير
            </a>
        </div>

        {{-- Parties --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">
                    العميل
                </div>

                <div
                    class="stat-value"
                    style="font-size: 20px;"
                >
                    {{ $order->user?->name ?? '-' }}
                </div>

                <div style="color: #6b7280; margin-top: 7px;">
                    {{ $order->user?->phone ?? '-' }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    المطبخ
                </div>

                <div
                    class="stat-value"
                    style="font-size: 20px;"
                >
                    {{ $order->kitchen?->name ?? '-' }}
                </div>

                <div style="color: #6b7280; margin-top: 7px;">
                    {{ $order->kitchen?->user?->phone ?? '-' }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">
                    الدليفري
                </div>

                <div
                    class="stat-value"
                    style="font-size: 20px;"
                >
                    {{
                        $deliveryOrder?->deliveryUser?->name
                        ?? 'لم يتم تعيين دليفري'
                    }}
                </div>

                <div style="color: #6b7280; margin-top: 7px;">
                    {{
                        $deliveryOrder?->deliveryUser?->phone
                        ?? '-'
                    }}
                </div>
            </div>
        </div>

        {{-- Client payment --}}
        <div class="section-card">
            <div class="section-header">
                <h3>تفاصيل ما دفعه العميل</h3>
            </div>

            <div class="table-wrapper">
                <table>
                    <tbody>
                        <tr>
                            <th>قيمة الوجبات</th>
                            <td>
                                {{ number_format(
                                    $financials['subtotal'],
                                    2
                                ) }}
                                جنيه
                            </td>
                        </tr>

                        <tr>
                            <th>القيمة المضافة</th>
                            <td>
                                {{ number_format(
                                    $financials['vat_value'],
                                    2
                                ) }}
                                جنيه
                            </td>
                        </tr>

                        <tr>
                            <th>رسوم التوصيل</th>
                            <td>
                                {{ number_format(
                                    $financials['delivery_price'],
                                    2
                                ) }}
                                جنيه
                            </td>
                        </tr>

                        <tr>
                            <th>رسوم خدمة العميل</th>
                            <td>
                                {{ number_format(
                                    $financials['client_service_fee'],
                                    2
                                ) }}
                                جنيه
                            </td>
                        </tr>

                        <tr>
                            <th>الإجمالي قبل الخصم</th>
                            <td>
                                <strong>
                                    {{ number_format(
                                        $financials[
                                            'total_before_discount'
                                        ],
                                        2
                                    ) }}
                                    جنيه
                                </strong>
                            </td>
                        </tr>

                        <tr>
                            <th>الخصم أو الكاش كود</th>
                            <td style="color: #dc2626;">
                                -
                                {{ number_format(
                                    $financials['discount_value'],
                                    2
                                ) }}
                                جنيه
                            </td>
                        </tr>

                        <tr>
                            <th>ما دفعه العميل فعليًا</th>
                            <td>
                                <strong
                                    style="
                                        color: #1d4ed8;
                                        font-size: 20px;
                                    "
                                >
                                    {{ number_format(
                                        $financials['user_paid'],
                                        2
                                    ) }}
                                    جنيه
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Distribution --}}
        <div class="section-card">
            <div class="section-header">
                <h3>توزيع قيمة الطلب</h3>
            </div>

            <div
                style="
                    display: grid;
                    grid-template-columns:
                        repeat(3, minmax(0, 1fr));
                    gap: 18px;
                "
                class="financial-distribution-grid"
            >
                <div
                    style="
                        background: #dcfce7;
                        border: 1px solid #bbf7d0;
                        border-radius: 16px;
                        padding: 22px;
                    "
                >
                    <div
                        style="
                            color: #166534;
                            font-weight: 700;
                            margin-bottom: 10px;
                        "
                    >
                        صافي المطبخ
                    </div>

                    <div
                        style="
                            color: #166534;
                            font-size: 27px;
                            font-weight: 800;
                        "
                    >
                        {{ number_format(
                            $financials['kitchen_net_amount'],
                            2
                        ) }}
                        جنيه
                    </div>

                    <div
                        style="
                            color: #166534;
                            margin-top: 10px;
                        "
                    >
                        قيمة الوجبات:
                        {{ number_format(
                            $financials['subtotal'],
                            2
                        ) }}

                        − رسوم المطبخ:
                        {{ number_format(
                            $financials['kitchen_service_fee'],
                            2
                        ) }}
                    </div>
                </div>

                <div
                    style="
                        background: #fffbeb;
                        border: 1px solid #fde68a;
                        border-radius: 16px;
                        padding: 22px;
                    "
                >
                    <div
                        style="
                            color: #92400e;
                            font-weight: 700;
                            margin-bottom: 10px;
                        "
                    >
                        مستحق الدليفري
                    </div>

                    <div
                        style="
                            color: #92400e;
                            font-size: 27px;
                            font-weight: 800;
                        "
                    >
                        {{ number_format(
                            $financials['delivery_amount'],
                            2
                        ) }}
                        جنيه
                    </div>

                    <div
                        style="
                            color: #92400e;
                            margin-top: 10px;
                        "
                    >
                        يساوي رسوم التوصيل المحفوظة في الطلب.
                    </div>
                </div>

                <div
                    style="
                        background: #eff6ff;
                        border: 1px solid #bfdbfe;
                        border-radius: 16px;
                        padding: 22px;
                    "
                >
                    <div
                        style="
                            color: #1d4ed8;
                            font-weight: 700;
                            margin-bottom: 10px;
                        "
                    >
                        رسوم خدمات التطبيق
                    </div>

                    <div
                        style="
                            color: #1d4ed8;
                            font-size: 27px;
                            font-weight: 800;
                        "
                    >
                        {{ number_format(
                            $financials[
                                'application_service_revenue'
                            ],
                            2
                        ) }}
                        جنيه
                    </div>

                    <div
                        style="
                            color: #1d4ed8;
                            margin-top: 10px;
                        "
                    >
                        خدمة العميل:
                        {{ number_format(
                            $financials['client_service_fee'],
                            2
                        ) }}

                        + خدمة المطبخ:
                        {{ number_format(
                            $financials['kitchen_service_fee'],
                            2
                        ) }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Order items --}}
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    عناصر الطلب
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الوجبة</th>
                            <th>سعر الوحدة</th>
                            <th>الكمية</th>
                            <th>الإجمالي</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $item->meal?->name ?? '-' }}
                                </td>

                                <td>
                                    {{ number_format(
                                        (float) $item->price,
                                        2
                                    ) }}
                                    جنيه
                                </td>

                                <td>
                                    {{ $item->quantity }}
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format(
                                            (float) $item->price
                                            * (int) $item->quantity,
                                            2
                                        ) }}
                                        جنيه
                                    </strong>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        @media(max-width: 900px) {
            .financial-distribution-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush