@extends('admin.layouts.app')

@section('title','طلبات الدليفري')

@section('content')

<div class="page-title">
    @if(!empty($delivery))
        طلبات {{ $delivery->name }}
    @else
        طلبات الدليفري
    @endif
</div>

@if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            جميع الطلبات
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>رقم الأوردر</th>
                    <th>الدليفري</th>
                    <th>العميل</th>
                    <th>المطبخ</th>
                    <th>الإجمالي</th>
                    <th>الحالة</th>
                    <th>تاريخ الطلب</th>
                    <th>إضافة نقاط</th>
                </tr>
            </thead>

            <tbody>
                @forelse($orders as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $item->order?->number ?? '-' }}</td>

                        <td>{{ $item->delivery?->name ?? '-' }}</td>

                        <td>{{ $item->order?->user?->name ?? '-' }}</td>

                        <td>{{ $item->order?->kitchen?->name ?? '-' }}</td>

                        <td>{{ number_format($item->order?->total ?? 0, 2) }}</td>

                        <td>
                            @switch($item->status)
                                @case('accepted')
                                    <span class="status-badge status-primary">تم القبول</span>
                                    @break

                                @case('picked_up')
                                    <span class="status-badge status-warning">تم الاستلام</span>
                                    @break

                                @case('on_the_way')
                                    <span class="status-badge status-info">في الطريق</span>
                                    @break

                                @case('delivered')
                                    <span class="status-badge status-success">تم التوصيل</span>
                                    @break

                                @case('cancelled')
                                    <span class="status-badge status-danger">ملغي</span>
                                    @break

                                @default
                                    <span class="status-badge status-secondary">
                                        {{ $item->status ?? '-' }}
                                    </span>
                            @endswitch
                        </td>

                        <td>
                            {{ optional($item->created_at ?? $item->order?->created_at)->format('Y-m-d H:i') ?? '-' }}
                        </td>

                        <td>
                            @if($item->delivery_user_id && $item->order_id)
                                <form method="POST" action="{{ route('admin.delivery.orders.add-points') }}" class="points-form">
                                    @csrf

                                    <input type="hidden" name="delivery_user_id" value="{{ $item->delivery_user_id }}">
                                    <input type="hidden" name="order_id" value="{{ $item->order_id }}">

                                    <select name="point_id" class="form-input points-select" required>
                                        <option value="">اختر النقاط</option>

                                        @foreach($points as $point)
                                            <option value="{{ $point->id }}">
                                                {{ $point->name }} - {{ $point->number }} نقطة
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="submit" class="add-btn small-btn">
                                        إضافة
                                    </button>
                                </form>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">
                            لا توجد طلبات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection

@push('styles')
<style>
.table-wrapper {
    overflow-x: auto;
}

.points-form {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 270px;
}

.points-select {
    min-width: 160px;
    height: 38px;
}

.small-btn {
    height: 38px;
    padding: 8px 14px;
    white-space: nowrap;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 6px 10px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.status-primary {
    background: #dbeafe;
    color: #2563eb;
}

.status-warning {
    background: #fef3c7;
    color: #d97706;
}

.status-info {
    background: #e0f2fe;
    color: #0284c7;
}

.status-success {
    background: #dcfce7;
    color: #16a34a;
}

.status-danger {
    background: #fee2e2;
    color: #dc2626;
}

.status-secondary {
    background: #f1f5f9;
    color: #475569;
}
</style>
@endpush