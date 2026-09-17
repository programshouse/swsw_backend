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

        @if ($user->address && $user->address->count())

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

                        @foreach ($user->address as $address)
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

        <div class="section-header orders-header">

            <h3>الطلبات</h3>

            <form method="GET" action="{{ route('admin.clients.show', $user->id) }}" class="order-filter-form">

                <input type="text" name="id" value="{{ request('id') }}" placeholder="ابحث برقم الطلب ID"
                    class="order-filter-input">
                <button type="submit" class="order-filter-btn">
                    بحث
                </button>

                @if (request()->filled('order_number'))
                    <a href="{{ route('admin.clients.show', $user->id) }}" class="order-clear-btn">
                        إلغاء
                    </a>
                @endif

            </form>

        </div>

        @if ($user->orders && $user->orders->count())

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

                        @foreach ($user->orders as $order)
                            <tr>

                                <td>{{ $order->id }}</td>

                                <td>
                                    <a href="{{ route('admin.orders.show', $order->id) }}" class="order-link">
                                        {{ $order->number ?? '-' }}
                                    </a>
                                </td>

                                <td>

                                    <span class="status-badge">

                                        {{ $order->status }}

                                    </span>

                                </td>

                                <td>

                                    {{ number_format($order->total, 2) }}

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
                لا توجد طلبات بهذا الرقم
            </div>

        @endif

    </div>

@endsection

@push('styles')
    <style>
        .orders-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .order-filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .order-filter-input {
            width: 230px;
            height: 42px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            padding: 0 14px;
            outline: none;
            font-size: 14px;
        }

        .order-filter-input:focus {
            border-color: #4f46e5;
        }

        .order-filter-btn,
        .order-clear-btn {
            height: 42px;
            padding: 0 18px;
            border-radius: 10px;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .order-filter-btn {
            border: none;
            background: #4f46e5;
            color: #fff;
            cursor: pointer;
        }

        .order-clear-btn {
            background: #f3f4f6;
            color: #111827;
        }
    </style>
@endpush
