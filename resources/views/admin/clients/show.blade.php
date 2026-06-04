@extends('admin.layouts.app')

@section('title', 'تفاصيل المستخدم')



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