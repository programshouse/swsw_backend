@extends('admin.layouts.app')

@section('title', 'الوجبات')

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

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1000px;
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

        .badge {
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            display: inline-block;
        }

        .approved {
            background: #dcfce7;
            color: #166534;
        }

        .pending {
            background: #fef3c7;
            color: #92400e;
        }

        .rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 8px 14px;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
        }

        .btn-approve {
            background: #16a34a;
        }

        .btn-reject {
            background: #dc2626;
        }

        .meal-image {
            width: 65px;
            height: 65px;
            border-radius: 12px;
            object-fit: cover;
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
    </style>
@endpush

@section('content')

    <div class="page-title">الوجبات</div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">كل الوجبات</div>
        </div>

        @if ($meals->count())

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            {{-- <th>ID</th> --}}
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>الوصف</th>
                            <th>الوصفة</th>
                            <th>الكمية</th>
                            <th>السعر</th>
                            <th>متاح للتوصيل اليوم</th>
                            <th>اسم المطبخ</th>
                            <th>هاتف المطبخ</th>
                            <th>اسم الفئة</th>
                            <th>معرف الفئة</th>
                           
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($meals as $meal)
                            <tr>
                                {{-- <td>{{ $meal->id }}</td> --}}

                                <td>
                                    @php
                                        $mealImage = null;

                                        if (!empty($meal->image)) {
                                            $mealImage = str_starts_with($meal->image, 'public/')
                                                ? url($meal->image)
                                                : asset('storage/' . $meal->image);
                                        }
                                    @endphp

                                    @if ($mealImage)
                                        <img src="{{ $mealImage }}" class="meal-image" alt="meal">
                                    @else
                                        -
                                    @endif
                                </td>

                                <td>{{ $meal->name ?? '-' }}</td>

                                <td>{{ $meal->description ?? '-' }}</td>

                                <td>{{ $meal->recipe ?? '-' }}</td>

                                <td>{{ $meal->quantity ?? '-' }}</td>

                                <td>{{ $meal->price ?? '-' }}</td>

                                <td>
                                    {{ $meal->available_delivery_today ? 'نعم' : 'لا' }}
                                </td>

                                <td>{{ $meal->kitchen->name ?? ($meal->kitchenProfile->name ?? '-') }}</td>

                                <td>{{ $meal->kitchen->phone ?? ($meal->kitchenProfile->phone ?? '-') }}</td>

                                <td>{{ $meal->category->name ?? '-' }}</td>

                                <td>{{ $meal->category_id ?? '-' }}</td>

                                <td>
                                    @if ($meal->approved === 'approved' || $meal->approved == 1)
                                        <span class="badge approved">مقبولة</span>
                                    @elseif($meal->approved === 'rejected')
                                        <span class="badge rejected">مرفوضة</span>
                                    @else
                                        <span class="badge pending">قيد الانتظار</span>
                                    @endif
                                </td>

                                {{-- <td>{{ optional($meal->created_at)->format('Y-m-d H:i') }}</td> --}}

                                {{-- <td>{{ optional($meal->updated_at)->format('Y-m-d H:i') }}</td> --}}

                                <td>
                                    <div class="actions" style="flex-wrap: wrap;">

                                        <form action="{{ route('admin.meals.approve', $meal->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="approve" value="approved">
                                            <button class="btn btn-approve">قبول</button>
                                        </form>

                                        <form action="{{ route('admin.meals.approve', $meal->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="approve" value="rejected">
                                            <button class="btn btn-reject">رفض</button>
                                        </form>

                                        <form action="{{ route('admin.meals.update-image', $meal->id) }}" method="POST"
                                            enctype="multipart/form-data"
                                            style="display:flex; gap:8px; align-items:center;">
                                            @csrf

                                            <input type="file" name="image" accept="image/*" required>

                                            <button class="btn" style="background:#2563eb;">
                                                تغيير الصورة
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
                لا توجد وجبات
            </div>

        @endif

    </div>

@endsection
