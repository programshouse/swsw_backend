@extends('admin.layouts.app')

@section('title', 'الشركات')

@section('content')

    {{-- عنوان الصفحة وزر الإضافة --}}
    <div class="page-head">

        <div>
            <div class="page-title" style="margin-bottom: 6px;">
                إدارة الشركات
            </div>

            <div style="color: #6b7280; font-size: 14px;">
                إضافة وتعديل الشركات ومتابعة المطابخ التابعة لكل شركة
            </div>
        </div>

        <a href="{{ route('admin.companies.create') }}"
           class="add-btn"
           style="text-decoration: none; display: inline-flex; align-items: center; gap: 7px;">

            <span style="font-size: 18px;">+</span>
            إضافة شركة جديدة
        </a>

    </div>


    {{-- رسائل النجاح --}}
    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif


    {{-- رسائل الخطأ --}}
    @if (session('error'))
        <div class="success-alert"
             style="background: #fee2e2; color: #991b1b;">

            {{ session('error') }}
        </div>
    @endif


    {{-- الإحصائيات --}}
    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-label">
                إجمالي الشركات
            </div>

            <div class="stat-value">
                {{ number_format($statistics['total'] ?? 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                الشركات المفعلة
            </div>

            <div class="stat-value" style="color: #166534;">
                {{ number_format($statistics['active'] ?? 0) }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                الشركات غير المفعلة
            </div>

            <div class="stat-value" style="color: #dc2626;">
                {{ number_format($statistics['inactive'] ?? 0) }}
            </div>
        </div>

    </div>


    {{-- البحث والفلاتر --}}
    <div class="section-card">

        <div class="section-header">
            <h3>البحث والتصفية</h3>
        </div>

        <form method="GET"
              action="{{ route('admin.companies.index') }}">

            <div style="
                display: grid;
                grid-template-columns: minmax(250px, 1fr) minmax(180px, 250px);
                gap: 18px;
                align-items: end;
            ">

                {{-- البحث --}}
                <div class="form-group" style="margin-bottom: 0;">

                    <label class="form-label">
                        البحث
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-input"
                           placeholder="ابحث بالاسم أو الهاتف أو البريد الإلكتروني">

                </div>


                {{-- الحالة --}}
                <div class="form-group" style="margin-bottom: 0;">

                    <label class="form-label">
                        حالة الشركة
                    </label>

                    <select name="status"
                            class="form-input">

                        <option value="">
                            كل الحالات
                        </option>

                        <option value="1"
                            {{ request('status') === '1' ? 'selected' : '' }}>

                            مفعلة
                        </option>

                        <option value="0"
                            {{ request('status') === '0' ? 'selected' : '' }}>

                            غير مفعلة
                        </option>

                    </select>

                </div>

            </div>


            <div class="modal-actions"
                 style="margin-top: 18px;">

                <button type="submit"
                        class="save-btn">

                    بحث
                </button>

                <a href="{{ route('admin.companies.index') }}"
                   class="cancel-btn"
                   style="
                        text-decoration: none;
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                   ">

                    إعادة تعيين
                </a>

            </div>

        </form>

    </div>


    {{-- جدول الشركات --}}
    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                قائمة الشركات
            </div>

            <div style="color: #6b7280; font-size: 14px;">
                عدد النتائج:
                <strong>
                    {{ $companies->total() }}
                </strong>
            </div>

        </div>


        @if ($companies->count())

            <div class="table-wrapper">

                <table>

                    <thead>
                        <tr>
                            <th>الشركة</th>
                            <th>بيانات التواصل</th>
                            <th>عدد المطابخ</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>


                    <tbody>

                        @foreach ($companies as $company)

                            <tr>

                                {{-- الشركة --}}
                                <td>

                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        gap: 12px;
                                    ">

                                        {{-- اللوجو --}}
                                        <div style="
                                            width: 48px;
                                            height: 48px;
                                            min-width: 48px;
                                            border-radius: 12px;
                                            overflow: hidden;
                                            display: flex;
                                            align-items: center;
                                            justify-content: center;
                                            background: #eff6ff;
                                            color: #2563eb;
                                            font-weight: 800;
                                            font-size: 19px;
                                        ">

                                            @if ($company->logo)

                                                <img src="{{ asset('storage/' . $company->logo) }}"
                                                     alt="{{ $company->name }}"
                                                     style="
                                                        width: 100%;
                                                        height: 100%;
                                                        object-fit: cover;
                                                     ">

                                            @else

                                                {{ mb_substr($company->name, 0, 1) }}

                                            @endif

                                        </div>


                                        {{-- اسم الشركة --}}
                                        <div>

                                            <div style="
                                                font-weight: 800;
                                                color: #111827;
                                                margin-bottom: 4px;
                                            ">
                                                {{ $company->name }}
                                            </div>

                                            @if ($company->address)
                                                <div style="
                                                    color: #6b7280;
                                                    font-size: 12px;
                                                    max-width: 240px;
                                                    overflow: hidden;
                                                    text-overflow: ellipsis;
                                                ">
                                                    {{ $company->address }}
                                                </div>
                                            @endif

                                        </div>

                                    </div>

                                </td>


                                {{-- التواصل --}}
                                <td>

                                    @if ($company->phone)

                                        <div style="margin-bottom: 5px;">
                                            <strong>الهاتف:</strong>
                                            {{ $company->phone }}
                                        </div>

                                    @endif


                                    @if ($company->email)

                                        <div style="color: #6b7280;">
                                            <strong>البريد:</strong>
                                            {{ $company->email }}
                                        </div>

                                    @endif


                                    @if (!$company->phone && !$company->email)

                                        <span style="color: #6b7280;">
                                            لا توجد بيانات
                                        </span>

                                    @endif

                                </td>


                                {{-- عدد المطابخ --}}
                                <td>

                                    <span style="
                                        display: inline-flex;
                                        align-items: center;
                                        justify-content: center;
                                        min-width: 45px;
                                        padding: 7px 12px;
                                        border-radius: 10px;
                                        background: #eff6ff;
                                        color: #2563eb;
                                        font-weight: 800;
                                    ">

                                        {{ $company->kitchens_count ?? 0 }}

                                    </span>

                                </td>


                                {{-- الحالة --}}
                                <td>

                                    @if ($company->is_active)

                                        <span class="status-badge">
                                            مفعلة
                                        </span>

                                    @else

                                        <span class="status-badge"
                                              style="
                                                background: #fee2e2;
                                                color: #991b1b;
                                              ">

                                            غير مفعلة
                                        </span>

                                    @endif

                                </td>


                                {{-- تاريخ الإضافة --}}
                                <td>
                                    {{ $company->created_at?->format('Y-m-d') }}
                                </td>


                                {{-- الإجراءات --}}
                                <td>

                                    <div style="
                                        display: flex;
                                        align-items: center;
                                        gap: 7px;
                                        flex-wrap: wrap;
                                    ">

                                        {{-- عرض --}}
                                        <a href="{{ route('admin.companies.show', $company) }}"
                                           class="view-btn">

                                            عرض
                                        </a>


                                        {{-- تعديل --}}
                                        <a href="{{ route('admin.companies.edit', $company) }}"
                                           class="refresh-btn"
                                           style="
                                                background: #f59e0b;
                                                color: #ffffff;
                                           ">

                                            تعديل
                                        </a>


                                        {{-- تغيير الحالة --}}
                                        <form action="{{ route('admin.companies.toggle-status', $company) }}"
                                              method="POST"
                                              style="margin: 0;">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="refresh-btn"
                                                    style="
                                                        border: 0;
                                                        cursor: pointer;
                                                        background:
                                                            {{ $company->is_active ? '#6b7280' : '#16a34a' }};
                                                    "
                                                    onclick="return confirm(
                                                        '{{ $company->is_active
                                                            ? 'هل تريد إيقاف هذه الشركة؟'
                                                            : 'هل تريد تفعيل هذه الشركة؟' }}'
                                                    )">

                                                {{ $company->is_active
                                                    ? 'إيقاف'
                                                    : 'تفعيل' }}

                                            </button>

                                        </form>


                                        {{-- حذف --}}
                                        <form action="{{ route('admin.companies.destroy', $company) }}"
                                              method="POST"
                                              style="margin: 0;"
                                              onsubmit="return confirm(
                                                  'هل أنت متأكد من حذف شركة {{ $company->name }}؟'
                                              )">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="delete-btn">

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


            {{-- Pagination --}}
            @if ($companies->hasPages())

                <div style="margin-top: 22px;">
                    {{ $companies->links() }}
                </div>

            @endif

        @else

            <div class="empty">

                لا توجد شركات مطابقة لنتائج البحث الحالية.

                <div style="margin-top: 16px;">

                    <a href="{{ route('admin.companies.create') }}"
                       class="add-btn"
                       style="text-decoration: none; display: inline-block;">

                        إضافة أول شركة
                    </a>

                </div>

            </div>

        @endif

    </div>


    {{-- Responsive للفلاتر --}}
    <style>
        @media (max-width: 768px) {
            .section-card form > div:first-of-type {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

@endsection