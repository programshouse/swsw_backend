@extends('admin.layouts.app')

@section('title', 'تفاصيل الطلب')

@section('content')

    <div class="page-head">
        <div>
            <div class="page-title">تفاصيل الطلب</div>
            <div class="page-subtitle">{{ $order->number ?? '-' }}</div>
        </div>

        <a href="{{ url()->previous() }}" class="back-btn">
            ← رجوع
        </a>
    </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="order-hero">

        <div>
            <div class="order-number">{{ $order->number ?? '-' }}</div>
            <span class="status-badge">{{ $order->status }}</span>
        </div>

        <div class="order-total">
            {{ number_format($order->total, 2) }}
            <span>جنيه</span>
        </div>

    </div>

    <div class="details-grid">

        <div class="info-card">
            <h3>بيانات الطلب</h3>

            <div class="info-row">
                <span>رقم الطلب</span>
                <strong>{{ $order->number ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>طريقة الدفع</span>
                <strong>{{ $order->payment_method ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>تاريخ الإنشاء</span>
                <strong>{{ optional($order->created_at)->format('Y-m-d H:i') }}</strong>
            </div>

            <div class="info-row">
                <span>تاريخ الاستلام</span>
                <strong>{{ $order->receive_date ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>وقت الاستلام</span>
                <strong>{{ $order->receive_time ?? '-' }}</strong>
            </div>
        </div>

        <div class="info-card">
            <h3>بيانات العميل</h3>

            <div class="info-row">
                <span>الاسم</span>
                <strong>{{ $order->user->name ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>الهاتف</span>
                <strong>{{ $order->user->phone ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>البريد</span>
                <strong>{{ $order->user->email ?? '-' }}</strong>
            </div>
        </div>

        <div class="info-card">
            <h3>بيانات المطبخ</h3>

            <div class="info-row">
                <span>الاسم</span>
                <strong>{{ $order->kitchen->name ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>الهاتف</span>
                <strong>{{ $order->kitchen->phone ?? ($order->kitchen->user->phone ?? '-') }}</strong>
            </div>
        </div>

        <div class="info-card">
            <h3>العنوان</h3>

            <div class="info-row">
                <span>العنوان</span>
                <strong>{{ $order->userAddress->full_address ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>المنطقة</span>
                <strong>{{ $order->userAddress->area->name_ar ?? '-' }}</strong>
            </div>

            <div class="info-row">
                <span>المحافظة</span>
                <strong>{{ $order->userAddress->government->name_ar ?? '-' }}</strong>
            </div>
        </div>

    </div>

    <div class="section-card">
        <div class="section-header">
            <h3>عناصر الطلب</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الوجبة</th>
                        <th>الكمية</th>
                        <th>السعر</th>
                        <th>الإجمالي</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>{{ $item->meal->name_ar ?? ($item->meal->name ?? '-') }}</td>
                            <td>{{ $item->quantity ?? 1 }}</td>
                            <td>{{ number_format($item->price ?? 0, 2) }}</td>
                            <td>{{ number_format(($item->price ?? 0) * ($item->quantity ?? 1), 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">لا توجد عناصر لهذا الطلب</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-card">
        <div class="section-header">
            <h3>الدليفري</h3>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الدليفري</th>
                        <th>الحالة</th>
                        <th>تم التسوية؟</th>
                        <th>التاريخ</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($order->deliveryOrders as $deliveryOrder)
                        <tr>
                            <td>{{ $deliveryOrder->id }}</td>
                            <td>{{ $deliveryOrder->deliveryUser->name ?? '-' }}</td>
                            <td>
                                <span class="status-badge">
                                    {{ $deliveryOrder->status }}
                                </span>
                            </td>
                            <td>{{ $deliveryOrder->cash_settled ? 'نعم' : 'لا' }}</td>
                            <td>{{ optional($deliveryOrder->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">لم يتم تعيين دليفري بعد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="actions-card">

        @if (!in_array($order->status, ['delivered', 'cancelled', 'cancelled_by_client', 'cancelled_by_kitchen']))
            <form method="POST" action="{{ route('admin.orders.cancel', $order->id) }}"
                onsubmit="return confirm('هل أنت متأكد من إلغاء الطلب؟')">
                @csrf

                <button type="submit" class="cancel-btn">
                    إلغاء الطلب
                </button>
            </form>
        @else
            <div class="empty-state">
                لا يمكن إلغاء هذا الطلب
            </div>
        @endif

    </div>

@endsection

@push('styles')
    <style>
        .page-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            gap: 15px;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            color: #0f172a;
        }

        .page-subtitle {
            margin-top: 6px;
            color: #64748b;
            font-size: 14px;
        }

        .back-btn {
            background: #f1f5f9;
            color: #0f172a;
            padding: 11px 18px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 14px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        .alert.success {
            background: #dcfce7;
            color: #166534;
        }

        .alert.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .order-hero,
        .info-card,
        .section-card,
        .actions-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
            border: 1px solid #eef2f7;
        }

        .order-hero {
            padding: 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .order-number {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 10px;
        }

        .order-total {
            font-size: 30px;
            font-weight: 900;
            color: #4f46e5;
        }

        .order-total span {
            font-size: 14px;
            color: #64748b;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .info-card {
            padding: 22px;
        }

        .info-card h3,
        .section-header h3 {
            margin: 0 0 18px;
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: 0;
        }

        .info-row span {
            color: #64748b;
            font-size: 14px;
        }

        .info-row strong {
            color: #0f172a;
            font-size: 14px;
            text-align: left;
        }

        .section-card {
            padding: 22px;
            margin-bottom: 24px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: #334155;
            font-size: 14px;
            padding: 14px;
            text-align: right;
            border-bottom: 1px solid #e2e8f0;
        }

        td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
            font-size: 14px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 7px 14px;
            border-radius: 999px;
            background: #dcfce7;
            color: #166534;
            font-weight: 800;
            font-size: 13px;
        }

        .actions-card {
            padding: 22px;
            margin-bottom: 24px;
        }

        .cancel-btn {
            background: #dc2626;
            color: #fff;
            border: 0;
            padding: 13px 24px;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
        }

        .empty-state {
            background: #f8fafc;
            color: #64748b;
            padding: 18px;
            border-radius: 14px;
            text-align: center;
            font-weight: 700;
        }

        .order-link {
            color: #4f46e5;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .order-hero,
            .page-head {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
@endpush
