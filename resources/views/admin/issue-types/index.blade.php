@extends('admin.layouts.app')

@section('title', 'أنواع المشاكل')

@section('content')

<div class="page-title">
    أنواع المشاكل
</div>

@if (session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            إضافة نوع مشكلة
        </div>
    </div>

    <form method="POST" action="{{ route('admin.issue-types.store') }}">
        @csrf

        <div class="form-grid">

            <div class="form-group">
                <label class="form-label">الاسم بالعربي</label>
                <input type="text" name="name_ar" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label">الاسم بالإنجليزي</label>
                <input type="text" name="name_en" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">الحالة</label>
                <label style="display:flex;align-items:center;gap:8px;margin-top:12px;">
                    <input type="checkbox" name="is_active" value="1" checked>
                    مفعل
                </label>
            </div>

        </div>

        <button type="submit" class="save-btn">
            حفظ
        </button>
    </form>

</div>

<div class="table-card" style="margin-top:24px;">

    <div class="table-header">
        <div class="table-title">
            قائمة أنواع المشاكل
        </div>
    </div>

    @if($issueTypes->count())

        <div class="table-wrapper">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>الاسم بالعربي</th>
                        <th>الاسم بالإنجليزي</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($issueTypes as $type)
                        <tr>
                            <form method="POST" action="{{ route('admin.issue-types.update', $type->id) }}">
                                @csrf
                                @method('PUT')

                                <td>{{ $type->id }}</td>

                                <td>
                                    <input type="text" name="name_ar" class="form-input" value="{{ $type->name_ar }}" required>
                                </td>

                                <td>
                                    <input type="text" name="name_en" class="form-input" value="{{ $type->name_en }}">
                                </td>

                                <td>
                                    <label style="display:flex;align-items:center;justify-content:center;gap:8px;">
                                        <input type="checkbox" name="is_active" value="1" {{ $type->is_active ? 'checked' : '' }}>
                                        مفعل
                                    </label>
                                </td>

                                <td>
                                    {{ optional($type->created_at)->format('Y-m-d') }}
                                </td>

                                <td>
                                    <button type="submit" class="save-btn">
                                        تعديل
                                    </button>
                            </form>

                                    <form method="POST"
                                          action="{{ route('admin.issue-types.destroy', $type->id) }}"
                                          style="display:inline-block;"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="delete-btn">
                                            حذف
                                        </button>
                                    </form>
                                </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    @else
        <div class="empty">
            لا توجد أنواع مشاكل
        </div>
    @endif

</div>

@endsection