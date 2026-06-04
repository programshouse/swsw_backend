@extends('admin.layouts.app')

@section('title', 'الشيفتات')



@section('content')

    <div class="page-title">
        الشيفتات
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="errors">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid">

        <div class="card">

            <div class="card-title">
                إضافة شيفت
            </div>

            <form method="POST" action="{{ route('admin.shifts.store') }}">

                @csrf

                <div class="form-group">

                    <label class="label">
                        اسم الشيفت عربى 
                    </label>

                    <input type="text" name="name_en" class="input" placeholder="مثال: شيفت صباحي" required>

                </div>

                 <div class="form-group">

                    <label class="label">
                        اسم الشيفت انجليزى
                    </label>

                    <input type="text" name="name_ar" class="input" placeholder="مثال: شيفت صباحي" required>

                </div>

                <div class="form-group">

                    <label class="label">
                        من الساعة
                    </label>

                    <input type="time" name="from_time" class="input" required>

                </div>

                <div class="form-group">

                    <label class="label">
                        إلى الساعة
                    </label>

                    <input type="time" name="to_time" class="input" required>

                </div>

                <button type="submit" class="btn btn-primary">
                    إضافة الشيفت
                </button>

            </form>

        </div>

        <div class="card">

            <div class="card-title">
                قائمة الشيفتات
            </div>

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>اسم الشيفت بالعربى</th>
                             <th>اسم الشيفت بالانجليزية</th>
                            <th>من</th>
                            <th>إلى</th>
                            <th>تاريخ الإنشاء</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($shifts as $shift)
                            <tr>

                                <td>
                                    {{ $shift->id }}
                                </td>

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

                                <td>
                                    {{ \Carbon\Carbon::parse($shift->from_time)->format('h:i A') }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($shift->to_time)->format('h:i A') }}
                                </td>

                                <td>
                                    {{ optional($shift->created_at)->format('Y-m-d H:i') }}
                                </td>

                                <td>

                                    <form method="POST" action="{{ route('admin.shifts.delete', $shift->id) }}"
                                        onsubmit="return confirm('هل تريد حذف الشيفت؟')">

                                        @csrf

                                        <button type="submit" class="btn btn-danger">
                                            حذف
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="6" style="text-align:center;padding:30px">
                                    لا توجد شيفتات
                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection
