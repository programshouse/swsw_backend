@extends('admin.layouts.app')

@section('title', 'تفاصيل المستخدم')

@section('content')

<div class="page-head">

    <div class="page-title">
        تفاصيل المستخدم
    </div>

    <a href="{{ route('admin.clients.index') }}" class="back-btn">
        ← رجوع للمستخدمين
    </a>

</div>

<div class="user-profile-card">

    <div class="avatar">
        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
    </div>

    <div class="user-info">

        <h2>
            {{ $user->name }}
        </h2>

        <div class="user-meta">

            <span>#{{ $user->id }}</span>

            <span>{{ $user->email }}</span>

            <span>{{ $user->phone }}</span>

        </div>

    </div>

</div>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-label">عدد العناوين</div>
        <div class="stat-value">
            {{ $user->address?->count() ?? 0 }}
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">عدد الطلبات</div>
        <div class="stat-value">
            {{ $user->orders?->count() ?? 0 }}
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">تاريخ التسجيل</div>
        <div class="stat-value">
            {{ optional($user->created_at)->format('Y-m-d') }}
        </div>
    </div>

</div>

<div class="section-card">

    <div class="section-header">
        <h3>العناوين</h3>
    </div>

    @if($user->address && $user->address->count())

        <div class="table-wrapper">

            <table>

                <thead>
                    <tr>
                        <th>#</th>
                        <th>العنوان</th>
                        <th>المنطقة</th>
                        <th>المحافظة</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($user->address as $address)

                        <tr>

                            <td>{{ $address->id }}</td>

                            <td>
                                {{ $address->full_address ?? '-' }}
                            </td>

                            <td>
                                {{ $address->area->name_ar ?? '-' }}
                            </td>

                            <td>
                                {{ $address->government->name_ar ?? '-' }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="empty-state">
            لا توجد عناوين لهذا المستخدم
        </div>

    @endif

</div>

<div class="section-card">

    <div class="section-header">
        <h3>الطلبات</h3>
    </div>

    @if($user->orders && $user->orders->count())

        <div class="table-wrapper">

            <table>

                <thead>

                    <tr>
                        <th>#</th>
                        <th>رقم الطلب</th>
                        <th>الحالة</th>
                        <th>الإجمالي</th>
                        <th>التاريخ</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($user->orders as $order)

                        <tr>

                            <td>{{ $order->id }}</td>

                            <td>
                                {{ $order->number ?? '-' }}
                            </td>

                            <td>

                                <span class="status-badge">

                                    {{ $order->status }}

                                </span>

                            </td>

                            <td>

                                {{ number_format($order->total,2) }}

                            </td>

                            <td>

                                {{ optional($order->created_at)->format('Y-m-d H:i') }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    @else

        <div class="empty-state">
            لا توجد طلبات لهذا المستخدم
        </div>

    @endif

</div>

@endsection


