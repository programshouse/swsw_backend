@extends('admin.layouts.app')

@section('title', 'وجبات المطبخ')



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