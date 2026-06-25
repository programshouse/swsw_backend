@extends('admin.layouts.app')

@section('title', 'وجبات المطبخ')

@section('content')

<div class="page-title">
    وجبات المطبخ
</div>

@if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="section-card">

    <div class="section-header">
        <h3>بيانات المطبخ</h3>
    </div>

   <div class="stats-grid" style="grid-template-columns: repeat(4,minmax(0,1fr));">
        <div class="stat-card">
            <div class="stat-label">اسم المطبخ</div>
            <div class="stat-value">{{ $kitchen->name ?? '-' }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">الهاتف</div>
            <div class="stat-value">{{ $kitchen->phone ?? '-' }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">المحافظة</div>
            <div class="stat-value">{{ $kitchen->government->name ?? '-' }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">المنطقة</div>
            <div class="stat-value">{{ $kitchen->area->name ?? '-' }}</div>
        </div>
    </div>

</div>

<div class="table-card">

    <div class="table-header">
        <div class="table-title">قائمة الوجبات</div>

        <a href="{{ route('admin.kitchens.show', $kitchen->user_id) }}" class="back-btn">
            رجوع
        </a>
    </div>

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
                    <th>الإجراءات</th>
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
                                    alt="meal"
                                    style="width:55px;height:55px;border-radius:12px;object-fit:cover;"
                                >
                            @else
                                -
                            @endif
                        </td>

                        <td>{{ $meal->name ?? '-' }}</td>

                        <td style="max-width:260px;white-space:normal;line-height:1.7;">
                            {{ $meal->description ?? '-' }}
                        </td>

                        <td>{{ $meal->price ?? 0 }} ج.م</td>

                        <td>{{ $meal->quantity ?? 0 }}</td>

                        <td>{{ $meal->category->name ?? '-' }}</td>

                        <td>{{ $meal->preparation_time ?? '-' }}</td>

                        <td>
                            @if($meal->available_delivery_today)
                                <span class="status-badge">متاح</span>
                            @else
                                <span class="status-badge" style="background:#fee2e2;color:#991b1b;">
                                    غير متاح
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($meal->availability)
                                <span class="status-badge">متوفر</span>
                            @else
                                <span class="status-badge" style="background:#fee2e2;color:#991b1b;">
                                    غير متوفر
                                </span>
                            @endif
                        </td>

                        <td>
                            @if($meal->approved === 'approved')
                                <span class="status-badge">مقبول</span>
                            @elseif($meal->approved === 'rejected')
                                <span class="status-badge" style="background:#fee2e2;color:#991b1b;">
                                    مرفوض
                                </span>
                            @else
                                <span class="status-badge" style="background:#fef3c7;color:#92400e;">
                                    قيد الانتظار
                                </span>
                            @endif
                        </td>

                        <td>{{ optional($meal->created_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">

                                <form method="POST" action="{{ route('admin.meals.approve', $meal->id) }}">
                                    @csrf
                                    <input type="hidden" name="approve" value="approved">

                                    <button type="submit" class="add-btn">
                                        قبول
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.meals.approve', $meal->id) }}">
                                    @csrf
                                    <input type="hidden" name="approve" value="rejected">

                                    <button type="submit" class="delete-btn">
                                        رفض
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.meals.destroy', $meal->id) }}"
                                    onsubmit="return confirm('هل أنت متأكد من حذف الوجبة؟')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="delete-btn" style="background:#7f1d1d;">
                                        حذف
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="empty">
                            لا توجد وجبات
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection