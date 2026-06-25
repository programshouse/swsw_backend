@extends('admin.layouts.app')

@section('title', 'الشيفتات')

@section('content')

<div class="page-title">
    الشيفتات
</div>

@if (session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            إضافة شيفت جديد
        </div>
    </div>

    <form method="POST" action="{{ route('admin.shifts.store') }}">
        @csrf

        <div class="form-grid">

            <div class="form-group">
                <label class="form-label">اسم الشيفت بالعربي</label>
                <input
                    type="text"
                    name="name_ar"
                    class="form-input"
                    placeholder="مثال: شيفت صباحي"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">اسم الشيفت بالإنجليزي</label>
                <input
                    type="text"
                    name="name_en"
                    class="form-input"
                    placeholder="Example: Morning shift"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">من الساعة</label>
                <input
                    type="time"
                    name="from_time"
                    class="form-input"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">إلى الساعة</label>
                <input
                    type="time"
                    name="to_time"
                    class="form-input"
                    required
                >
            </div>

        </div>

        <button type="submit" class="save-btn">
            إضافة الشيفت
        </button>
    </form>

</div>

<div class="table-card" style="margin-top:24px;">

    <div class="table-header">
        <div class="table-title">
            قائمة الشيفتات
        </div>
    </div>

    <div class="table-wrapper">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>اسم الشيفت بالعربي</th>
                    <th>اسم الشيفت بالإنجليزي</th>
                    <th>من</th>
                    <th>إلى</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $shift->id }}</td>

                        <td>
                            <span class="badge">
                                {{ $shift->name_ar }}
                            </span>
                        </td>

                        <td>
                            <span class="badge">
                                {{ $shift->name_en }}
                            </span>
                        </td>

                        <td>{{ \Carbon\Carbon::parse($shift->from_time)->format('h:i A') }}</td>

                        <td>{{ \Carbon\Carbon::parse($shift->to_time)->format('h:i A') }}</td>

                        <td>{{ optional($shift->created_at)->format('Y-m-d H:i') }}</td>

                        <td>
                            <form
                                method="POST"
                                action="{{ route('admin.shifts.delete', $shift->id) }}"
                                onsubmit="return confirm('هل تريد حذف الشيفت؟')"
                            >
                                @csrf

                                <button type="submit" class="delete-btn">
                                    حذف
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty">
                            لا توجد شيفتات
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

@endsection