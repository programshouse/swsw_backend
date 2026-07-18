@extends('admin.layouts.app')

@section('title', 'باقات المطابخ')

@section('content')

<div class="page-title">باقات المطابخ</div>

@if(session('success'))
    <div class="success-alert">{{ session('success') }}</div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">قائمة الباقات</div>

        <a href="{{ route('admin.kitchen-packages.create') }}" class="add-btn">
            إضافة باقة
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>الاسم</th>
                <th>السعر</th>
                <th>المدة</th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>

        <tbody>
            @forelse($packages as $package)
                <tr>
                    <td>{{ $package->name }}</td>
                    <td>{{ number_format($package->price, 2) }}</td>
                    <td>{{ $package->duration }}</td>
                    <td>{{ $package->active ? 'مفعلة' : 'غير مفعلة' }}</td>
                    <td>
                        <a href="{{ route('admin.kitchen-packages.edit', $package->id) }}" class="edit-btn">
                            تعديل
                        </a>

                        <form action="{{ route('admin.kitchen-packages.destroy', $package->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')

                            <button class="delete-btn" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                حذف
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">لا توجد باقات</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection