@extends('admin.layouts.app')

@section('title', 'تفاصيل المستخدم')

@push('styles')
<style>
    .page-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
    }

    .back-btn {
        background: #e2e8f0;
        color: #0f172a;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 700;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    .info-card {
        background: #fff;
        border-radius: 18px;
        padding: 20px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
    }

    .label {
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .value {
        color: #0f172a;
        font-size: 18px;
        font-weight: 800;
    }

    .section-card {
        background: #fff;
        border-radius: 18px;
        padding: 22px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
        margin-bottom: 24px;
    }

    .section-title {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 18px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .table-wrapper {
        overflow-x: auto;
    }

    th {
        background: #f8fafc;
        color: #334155;
        padding: 14px;
        text-align: right;
        border-bottom: 1px solid #eef2f7;
    }

    td {
        padding: 14px;
        color: #475569;
        border-bottom: 1px solid #eef2f7;
    }

    .empty {
        color: #64748b;
        padding: 16px 0;
    }

    @media (max-width: 1100px) {
        .details-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 600px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-head">
    <div class="page-title">تفاصيل المستخدم</div>

    <a href="{{ route('admin.clients.index') }}" class="back-btn">
        رجوع
    </a>
</div>

<div class="details-grid">
    <div class="info-card">
        <div class="label">ID</div>
        <div class="value">{{ $user->id }}</div>
    </div>

    <div class="info-card">
        <div class="label">الاسم</div>
        <div class="value">{{ $user->name ?? '-' }}</div>
    </div>

    <div class="info-card">
        <div class="label">البريد الإلكتروني</div>
        <div class="value">{{ $user->email ?? '-' }}</div>
    </div>

    <div class="info-card">
        <div class="label">الهاتف</div>
        <div class="value">{{ $user->phone ?? '-' }}</div>
    </div>
</div>

<div class="section-card">
    <div class="section-title">العناوين</div>

    @if($user->address && $user->address->count())
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>العنوان</th>
                    <th>المنطقة</th>
                    <th>المحافظة</th>
                </tr>
                </thead>
                <tbody>
                @foreach($user->address as $address)
                    <tr>
                        <td>{{ $address->id }}</td>
                        <td>{{ $address->address ?? $address->details ?? '-' }}</td>
                        <td>{{ $address->area->name ?? '-' }}</td>
                        <td>{{ $address->government->name ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">لا توجد عناوين</div>
    @endif
</div>

<div class="section-card">
    <div class="section-title">الطلبات</div>

    @if($user->orders && $user->orders->count())
        <div class="table-wrapper">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>الحالة</th>
                    <th>الإجمالي</th>
                    <th>تاريخ الإنشاء</th>
                </tr>
                </thead>
                <tbody>
                @foreach($user->orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->status ?? '-' }}</td>
                        <td>{{ $order->total ?? 0 }}</td>
                        <td>{{ optional($order->created_at)->format('Y-m-d H:i') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty">لا توجد طلبات</div>
    @endif
</div>

@endsection