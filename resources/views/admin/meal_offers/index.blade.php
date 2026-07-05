@extends('admin.layouts.app')

@section('title', 'عروض الوجبات')

@push('styles')
<style>
    .page-title {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 24px;
    }

    .table-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.06);
        border: 1px solid #eef2f7;
        overflow: hidden;
    }

    .table-header {
        padding: 20px 24px;
        border-bottom: 1px solid #eef2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-title {
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
    }

    .add-btn,
    .save-btn,
    .edit-btn {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 9px 14px;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .delete-btn {
        background: #dc2626;
        color: #fff;
        border: none;
        padding: 9px 14px;
        border-radius: 10px;
        font-weight: 700;
        cursor: pointer;
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
        padding: 16px;
        text-align: right;
        border-bottom: 1px solid #eef2f7;
        font-size: 14px;
    }

    td {
        padding: 16px;
        color: #475569;
        border-bottom: 1px solid #eef2f7;
        font-size: 14px;
    }

    tr:hover {
        background: #f8fafc;
    }

    .success-alert {
        background: #dcfce7;
        color: #166534;
        padding: 14px 18px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .empty {
        padding: 40px;
        text-align: center;
        color: #64748b;
    }

    .badge-active {
        background:#dcfce7;
        color:#166534;
        padding:6px 12px;
        border-radius:999px;
        font-weight:700;
    }

    .badge-inactive {
        background:#fee2e2;
        color:#991b1b;
        padding:6px 12px;
        border-radius:999px;
        font-weight:700;
    }

    .actions {
        display: flex;
        gap: 8px;
        align-items: center;
    }
</style>
@endpush

@section('content')

<div class="page-title">عروض الوجبات</div>

@if (session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">قائمة عروض الوجبات</div>

        <a href="{{ route('admin.meal-offers.create') }}" class="add-btn">
            إضافة عرض جديد
        </a>
    </div>

    @if ($offers->count())

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>المطبخ</th>
                        <th>الوجبة</th>
                        <th>السعر الأساسي</th>
                        <th>نسبة الخصم</th>
                        <th>السعر بعد الخصم</th>
                        <th>من تاريخ</th>
                        <th>إلى تاريخ</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($offers as $offer)
                        @php
                            $price = $offer->meal->price ?? 0;
                            $finalPrice = $price - (($price * $offer->percentage) / 100);
                        @endphp

                        <tr>
                            <td>{{ $offer->kitchen->user->name ?? '-' }}</td>

                            <td>{{ $offer->meal->name ?? '-' }}</td>

                            <td>{{ number_format($price, 2) }}</td>

                            <td>{{ $offer->percentage }}%</td>

                            <td>{{ number_format($finalPrice, 2) }}</td>

                            <td>{{ $offer->start_date ?? '-' }}</td>

                            <td>{{ $offer->end_date ?? '-' }}</td>

                            <td>
                                @if($offer->status)
                                    <span class="badge-active">نشط</span>
                                @else
                                    <span class="badge-inactive">معطل</span>
                                @endif
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="{{ route('admin.meal-offers.edit', $offer->id) }}" class="edit-btn">
                                        تعديل
                                    </a>

                                    <form action="{{ route('admin.meal-offers.destroy', $offer->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('هل أنت متأكد من حذف العرض؟')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="delete-btn">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>

    @else
        <div class="empty">
            لا توجد عروض
        </div>
    @endif

</div>

@endsection