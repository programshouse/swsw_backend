@extends('admin.layouts.app')

@section('content')

<div class="page-title">الحسابات الاحتياطية</div>

@if(session('success'))
    <div class="success-alert">{{ session('success') }}</div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">قائمة الحسابات الاحتياطية</div>

        <a href="{{ route('admin.reserve-deliveries.create') }}" class="add-btn">
            إضافة حساب
        </a>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الهاتف</th>
                    <th>المنطقة</th>
                    <th>الشيفت</th>
                    <th>المستوى</th>
                    <th>المركبة</th>
                    <th>الحالة</th>
                    <th style="text-align:center;">الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($deliveries as $delivery)
                    <tr>
                        <td>{{ $delivery->name }}</td>
                        <td>{{ $delivery->phone }}</td>
                        <td>{{ $delivery->area->name_ar ?? '-' }}</td>
                        <td>{{ $delivery->shift->name_ar ?? '-' }}</td>
                        <td>{{ $delivery->level->name ?? '-' }}</td>
                        <td>{{ $delivery->vehicle->name_ar ?? '-' }}</td>
                        <td>
                            <span class="status-badge">معتمد</span>
                        </td>
                        <td style="text-align:center;">
                            <div class="action-buttons" style="justify-content:center;">
                                <a href="{{ route('admin.reserve-deliveries.edit', $delivery->id) }}" class="edit-btn">
                                    تعديل
                                </a>

                                <form action="{{ route('admin.reserve-deliveries.destroy', $delivery->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="delete-btn">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">لا توجد حسابات احتياطية</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection