@extends('admin.layouts.app')

@section('title', 'باقات المطابخ')

@section('content')

<div class="page-title">باقات المطابخ</div>
<style>
.alert 
{ padding: 12px 16px; margin-bottom: 20px; border-radius: 8px; font-size: 14px; font-weight: 500; } 
.alert-success
 { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; } 
.alert-error 
{ background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
</style>

@if(session('success')) 
<div class="alert alert-success"> {{ session('success') }} </div> 
@endif @if(session('error')) 
<div class="alert alert-error"> {{ session('error') }} </div>
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
                 <th>عدد الوجبات</th>
                  <th>عدد الاوردرات</th>
                <th>المدة بالشهر </th>
                <th>الحالة</th>
                <th>الإجراءات</th>
            </tr>
        </thead>

        <tbody>
            @forelse($packages as $package)
                <tr>
                    <td>{{ $package->name }}</td>
                    <td>{{ number_format($package->price, 2) }}</td>
                     <td>{{ $package->meals_limit }}</td>
                      <td>{{ $package->orders_limit }}</td>
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