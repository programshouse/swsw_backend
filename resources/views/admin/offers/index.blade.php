@extends('admin.layouts.app')

@section('title', 'العروض')

@section('content')

    <div class="page-title">
        العروض
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">
                قائمة العروض
            </div>

            <a href="{{ route('admin.offers.create') }}" class="add-btn">
                إضافة عرض جديد
            </a>
        </div>

        @if ($offers->count())

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>الاسم بالعربي</th>
                            <th>الاسم بالإنجليزي</th>
                            <th>عدد النقاط</th>
                            <th>الحالة</th>
                            <th>عدد النقاط</th>
                            <th>الحالة</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($offers as $offer)
                            <tr>
                                <td>{{ $offer->id }}</td>
                                <td>{{ $offer->name_ar }}</td>
                                <td>{{ $offer->name_en }}</td>
                                <td>{{ $offer->points }}</td>

                                <td>
                                    @if ($offer->is_active)
                                        <span class="badge">مفعل</span>
                                    @else
                                        <span class="badge">غير مفعل</span>
                                    @endif
                                </td>
                                <td>{{ $offer->points }}</td>
                                <td>
                                    @if ($offer->is_active)
                                        <span class="badge">مفعل</span>
                                    @else
                                        <span class="badge">غير مفعل</span>
                                    @endif
                                </td>
                                <td>{{ optional($offer->created_at)->format('Y-m-d') }}</td>
                                <td>
                                    <div style="display:flex;gap:10px;">
                                        <a href="{{ route('admin.offers.edit', $offer->id) }}" class="refresh-btn">
                                            تعديل
                                        </a>

                                        <form method="POST" action="{{ route('admin.offers.destroy', $offer->id) }}"
                                            onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
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

            <div style="margin-top:20px;">
                {{ $offers->links() }}
            </div>
        @else
            <div class="empty">
                لا توجد عروض
            </div>
        @endif

    </div>

@endsection
