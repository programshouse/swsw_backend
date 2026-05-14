@extends('admin.layouts.app')

@section('title', 'وجبات المطبخ')

@push('styles')
<style>

    .page-title{
        font-size:28px;
        font-weight:800;
        margin-bottom:20px;
        color:#0f172a;
    }

    .top-card{
        background:#fff;
        border-radius:18px;
        padding:24px;
        margin-bottom:20px;
        box-shadow:0 10px 24px rgba(15,23,42,.06);
        border:1px solid #eef2f7;
    }

    .kitchen-name{
        font-size:24px;
        font-weight:800;
        color:#0f172a;
        margin-bottom:10px;
    }

    .kitchen-info{
        color:#64748b;
        font-size:15px;
        margin-bottom:6px;
    }

    .table-card{
        background:#fff;
        border-radius:18px;
        overflow:hidden;
        box-shadow:0 10px 24px rgba(15,23,42,.06);
        border:1px solid #eef2f7;
    }

    .table-wrapper{
        overflow-x:auto;
    }

    table{
        width:100%;
        border-collapse:collapse;
        min-width:1300px;
    }

    th{
        background:#f8fafc;
        color:#334155;
        padding:16px;
        text-align:right;
        font-size:14px;
        border-bottom:1px solid #eef2f7;
        white-space:nowrap;
    }

    td{
        padding:16px;
        color:#475569;
        font-size:14px;
        border-bottom:1px solid #eef2f7;
        white-space:nowrap;
        vertical-align:middle;
    }

    tr:hover{
        background:#f8fafc;
    }

    .meal-image{
        width:70px;
        height:70px;
        border-radius:14px;
        object-fit:cover;
        border:1px solid #e2e8f0;
    }

    .badge{
        display:inline-block;
        padding:6px 12px;
        border-radius:999px;
        font-size:13px;
        font-weight:800;
    }

    .badge-success{
        background:#dcfce7;
        color:#166534;
    }

    .badge-danger{
        background:#fee2e2;
        color:#991b1b;
    }

    .badge-warning{
        background:#fef3c7;
        color:#92400e;
    }

    .btn{
        border:none;
        border-radius:10px;
        padding:10px 14px;
        font-size:14px;
        font-weight:700;
        cursor:pointer;
        text-decoration:none;
        display:inline-block;
    }

    .btn-back{
        background:#e2e8f0;
        color:#0f172a;
        margin-top:20px;
    }

</style>
@endpush

@section('content')

<div class="page-title">
    وجبات المطبخ
</div>

<div class="top-card">

    <div class="kitchen-name">
        {{ $kitchen->name ?? '-' }}
    </div>

    <div class="kitchen-info">
        الهاتف:
        {{ $kitchen->phone ?? '-' }}
    </div>

    <div class="kitchen-info">
        المحافظة:
        {{ $kitchen->government->name ?? '-' }}
    </div>

    <div class="kitchen-info">
        المنطقة:
        {{ $kitchen->area->name ?? '-' }}
    </div>

</div>

<div class="table-card">

    <div class="table-wrapper">

        <table>

            <thead>
            <tr>
                <th>ID</th>
                <th>الصورة</th>
                <th>الاسم</th>
                <th>الوصف</th>
                <th>السعر</th>
                <th>الكمية</th>
                <th>الفئة</th>
                <th>وقت التحضير</th>
                <th>التوصيل اليوم</th>
                <th>التوفر</th>
                <th>الحالة</th>
                <th>تاريخ الإنشاء</th>
            </tr>
            </thead>

            <tbody>

            @forelse($meals as $meal)

                <tr>

                    <td>{{ $meal->id }}</td>

                    <td>
                        @if($meal->image)
                            <img
                                src="{{ asset('storage/' . $meal->image) }}"
                                class="meal-image"
                            >
                        @else
                            -
                        @endif
                    </td>

                    <td>{{ $meal->name ?? '-' }}</td>

                    <td style="max-width:260px;white-space:normal">
                        {{ $meal->description ?? '-' }}
                    </td>

                    <td>
                        {{ $meal->price ?? 0 }}
                        ج.م
                    </td>

                    <td>{{ $meal->quantity ?? 0 }}</td>

                    <td>
                        {{ $meal->category->name ?? '-' }}
                    </td>

                    <td>
                        {{ $meal->preparation_time ?? '-' }}
                    </td>

                    <td>
                        @if($meal->available_delivery_today)
                            <span class="badge badge-success">
                                متاح
                            </span>
                        @else
                            <span class="badge badge-danger">
                                غير متاح
                            </span>
                        @endif
                    </td>

                    <td>
                        @if($meal->availability)
                            <span class="badge badge-success">
                                متوفر
                            </span>
                        @else
                            <span class="badge badge-danger">
                                غير متوفر
                            </span>
                        @endif
                    </td>

                    <td>

                        @if($meal->approved === 'approved')
                            <span class="badge badge-success">
                                مقبول
                            </span>

                        @elseif($meal->approved === 'rejected')
                            <span class="badge badge-danger">
                                مرفوض
                            </span>

                        @else
                            <span class="badge badge-warning">
                                {{ $meal->approved }}
                            </span>
                        @endif

                    </td>

                    <td>
                        {{ optional($meal->created_at)->format('Y-m-d H:i') }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="12" style="text-align:center;padding:30px">
                        لا توجد وجبات
                    </td>
                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

</div>

<a
    href="{{ route('admin.kitchens.show', $kitchen->user_id) }}"
    class="btn btn-back"
>
    رجوع
</a>

@endsection