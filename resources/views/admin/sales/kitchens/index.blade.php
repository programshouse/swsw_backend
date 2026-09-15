@extends('admin.layouts.app')

@section('title', 'مطابخي')

@section('content')
    <div class="kitchens-page" dir="rtl">

        <div class="page-header">
            <div>
                <h1>مطابخي</h1>

                <p>
                    إدارة المطابخ التي تمت إضافتها من خلال حساب السيلز الخاص بك.
                </p>
            </div>

            <a href="{{ route('admin.sales.kitchens.create') }}" class="primary-btn">
                <i class="fas fa-plus"></i>
                إضافة مطبخ جديد
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="statistics-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-store"></i>
                </div>

                <div>
                    <span>إجمالي المطابخ</span>

                    <strong>
                        {{ $statistics['total'] ?? 0 }}
                    </strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>

                <div>
                    <span>المطابخ النشطة</span>

                    <strong>
                        {{ $statistics['active'] ?? 0 }}
                    </strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>

                <div>
                    <span>المطابخ المعلقة</span>

                    <strong>
                        {{ $statistics['pending'] ?? 0 }}
                    </strong>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fas fa-ban"></i>
                </div>

                <div>
                    <span>المطابخ غير النشطة</span>

                    <strong>
                        {{ $statistics['not_active'] ?? 0 }}
                    </strong>
                </div>
            </div>
        </div>

        <div class="content-card">

            <form method="GET" action="{{ route('admin.sales.kitchens.index') }}" class="filters">
                <div class="search-input">
                    <i class="fas fa-search"></i>

                    <input type="text" name="search" value="{{ $search ?? '' }}"
                        placeholder="ابحث بالاسم أو الهاتف أو البريد أو الكود...">
                </div>

                <select name="status">
                    <option value="">
                        كل حالات الحساب
                    </option>

                    <option value="active" @selected(($status ?? '') === 'active')>
                        نشط
                    </option>

                    <option value="pending" @selected(($status ?? '') === 'pending')>
                        معلق
                    </option>

                    <option value="not_active" @selected(($status ?? '') === 'not_active')>
                        غير نشط
                    </option>
                </select>

                <select name="is_company">
                    <option value="">
                        كل أنواع المطابخ
                    </option>

                    <option value="0" @selected((string) ($isCompany ?? '') === '0')>
                        مطبخ فردي
                    </option>

                    <option value="1" @selected((string) ($isCompany ?? '') === '1')>
                        شركة
                    </option>
                </select>

                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                    بحث
                </button>

                @if (!empty($search) || !empty($status) || (string) ($isCompany ?? '') !== '')
                    <a href="{{ route('admin.sales.kitchens.index') }}" class="reset-btn">
                        إعادة تعيين
                    </a>
                @endif
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>المطبخ</th>
                            <th>الكود</th>
                            <th>بيانات التواصل</th>
                            <th>الموقع</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($kitchens as $kitchen)
                            @php
                                $address = $kitchen->defaultAddress;

                                $governmentName =
                                    $kitchen->government?->name_ar ??
                                    ($kitchen->government?->name ??
                                        ($address?->government?->name_ar ?? ($address?->government?->name ?? '-')));

                                $areaName =
                                    $kitchen->area?->name_ar ??
                                    ($kitchen->area?->name ??
                                        ($address?->area?->name_ar ?? ($address?->area?->name ?? '-')));
                            @endphp

                            <tr>
                                <td>
                                    {{ $kitchens->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="kitchen-info">
                                        <div class="kitchen-avatar">
                                            {{ mb_substr($kitchen->name, 0, 1) }}
                                        </div>

                                        <div>
                                            <strong>
                                                {{ $kitchen->name }}
                                            </strong>

                                            <span>
                                                {{ $kitchen->email ?: 'بدون بريد إلكتروني' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="code-badge">
                                        {{ $kitchen->code ?: '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="contact-info">
                                        <span>
                                            <i class="fas fa-phone"></i>
                                            {{ $kitchen->phone }}
                                        </span>

                                        @if ($kitchen->email)
                                            <span>
                                                <i class="fas fa-envelope"></i>
                                                {{ $kitchen->email }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="location-info">
                                        <strong>
                                            {{ $governmentName }}
                                            -
                                            {{ $areaName }}
                                        </strong>

                                        <span>
                                            {{ $address?->full_address ?: 'لا يوجد عنوان تفصيلي' }}
                                        </span>

                                        @if ($address?->location_link)
                                            <a href="{{ $address->location_link }}" target="_blank"
                                                rel="noopener noreferrer">
                                                عرض الموقع
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="type-badge">
                                        {{ $kitchen->is_company ? 'شركة' : 'مطبخ فردي' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge status-{{ $kitchen->status }}">
                                        <span class="status-dot"></span>

                                        @switch($kitchen->status)
                                            @case('active')
                                                نشط
                                            @break

                                            @case('not_active')
                                                غير نشط
                                            @break

                                            @default
                                                معلق
                                        @endswitch
                                    </span>
                                </td>

                                <td>
                                    {{ $kitchen->created_at?->format('Y/m/d') ?? '-' }}
                                </td>

                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.sales.kitchens.show', $kitchen) }}"
                                            class="action-btn view" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('admin.sales.kitchens.edit', $kitchen) }}"
                                            class="action-btn edit" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form
                                            action="{{ route('admin.sales.kitchens.toggle-status', $kitchen) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="action-btn toggle"
                                                title="{{ $kitchen->status === 'active' ? 'إيقاف' : 'تفعيل' }}">
                                                <i
                                                    class="fas {{ $kitchen->status === 'active' ? 'fa-ban' : 'fa-check' }}"></i>
                                            </button>
                                        </form>

                                        <form
                                            action="{{ route('admin.sales.kitchens.destroy', $kitchen) }}"
                                            method="POST"
                                            onsubmit="return confirm(
                                            'هل أنت متأكد من حذف هذا المطبخ؟'
                                        )">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="action-btn delete" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="empty-state">
                                            <i class="fas fa-store-slash"></i>

                                            <h3>
                                                لا توجد مطابخ
                                            </h3>

                                            <p>
                                                لم تقم بإضافة أي مطابخ حتى الآن.
                                            </p>

                                            <a href="{{ route('admin.sales.kitchens.create') }}"
                                                class="primary-btn">
                                                <i class="fas fa-plus"></i>
                                                إضافة أول مطبخ
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($kitchens->hasPages())
                    <div class="pagination-wrapper">
                        {{ $kitchens->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endsection

    @push('styles')

        @push('styles')
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


            <style>
                .kitchens-page {
                    --primary: #4948ab;
                    --dark: #202b3d;
                    --muted: #7b8494;
                    --border: #e7eaf0;
                    font-family: "Cairo", sans-serif;
                }

                .page-header {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 20px;
                    margin-bottom: 24px;
                }

                .page-header h1 {
                    margin: 0 0 6px;
                    color: var(--dark);
                    font-size: 28px;
                    font-weight: 800;
                }

                .page-header p {
                    margin: 0;
                    color: var(--muted);
                    font-size: 14px;
                }

                .primary-btn {
                    min-height: 46px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 9px;
                    padding: 0 20px;
                    border: 0;
                    border-radius: 12px;
                    background: var(--primary);
                    color: #fff;
                    font-family: inherit;
                    font-size: 13px;
                    font-weight: 700;
                    text-decoration: none;
                    cursor: pointer;
                }

                .alert {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 14px 18px;
                    margin-bottom: 20px;
                    border-radius: 12px;
                    font-size: 13px;
                    font-weight: 700;
                }

                .alert-success {
                    border: 1px solid #b7e9d2;
                    background: #eafaf3;
                    color: #137a50;
                }

                .alert-error {
                    border: 1px solid #ffc9c5;
                    background: #fff0ef;
                    color: #b42318;
                }

                .statistics-grid {
                    display: grid;
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                    gap: 17px;
                    margin-bottom: 22px;
                }

                .stat-card {
                    min-height: 105px;
                    display: flex;
                    align-items: center;
                    gap: 14px;
                    padding: 19px;
                    border: 1px solid var(--border);
                    border-radius: 16px;
                    background: #fff;
                    box-shadow: 0 8px 25px rgba(32, 43, 61, .04);
                }

                .stat-icon {
                    width: 50px;
                    height: 50px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    border-radius: 14px;
                }

                .stat-icon.purple {
                    color: #4948ab;
                    background: #eeeeff;
                }

                .stat-icon.green {
                    color: #16875b;
                    background: #e9faf2;
                }

                .stat-icon.orange {
                    color: #c47b10;
                    background: #fff5df;
                }

                .stat-icon.red {
                    color: #c4382b;
                    background: #fff0ef;
                }

                .stat-card>div:last-child {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }

                .stat-card span {
                    color: var(--muted);
                    font-size: 12px;
                }

                .stat-card strong {
                    color: var(--dark);
                    font-size: 24px;
                }

                .content-card {
                    overflow: hidden;
                    border: 1px solid var(--border);
                    border-radius: 17px;
                    background: #fff;
                }

                .filters {
                    display: grid;
                    grid-template-columns: minmax(250px, 1fr) 180px 180px auto auto;
                    gap: 11px;
                    padding: 20px;
                    border-bottom: 1px solid var(--border);
                }

                .search-input {
                    position: relative;
                }

                .search-input i {
                    position: absolute;
                    top: 50%;
                    right: 15px;
                    transform: translateY(-50%);
                    color: #98a1af;
                }

                .filters input,
                .filters select {
                    width: 100%;
                    height: 45px;
                    padding: 0 14px;
                    border: 1px solid #dfe3ea;
                    border-radius: 11px;
                    background: #fff;
                    font-family: inherit;
                    outline: none;
                }

                .search-input input {
                    padding-right: 42px;
                }

                .filters input:focus,
                .filters select:focus {
                    border-color: var(--primary);
                    box-shadow: 0 0 0 3px rgba(73, 72, 171, .08);
                }

                .search-btn,
                .reset-btn {
                    min-height: 45px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 7px;
                    padding: 0 18px;
                    border-radius: 11px;
                    font-family: inherit;
                    font-size: 12px;
                    font-weight: 700;
                }

                .search-btn {
                    border: 0;
                    background: var(--primary);
                    color: #fff;
                    cursor: pointer;
                }

                .reset-btn {
                    border: 1px solid #dfe3ea;
                    background: #fff;
                    color: var(--dark);
                    text-decoration: none;
                }

                .table-responsive {
                    width: 100%;
                    overflow-x: auto;
                }

                table {
                    width: 100%;
                    min-width: 1200px;
                    border-collapse: collapse;
                }

                th {
                    padding: 15px 16px;
                    border-bottom: 1px solid var(--border);
                    background: #fafbfc;
                    color: #667085;
                    text-align: right;
                    font-size: 12px;
                    font-weight: 700;
                    white-space: nowrap;
                }

                td {
                    padding: 16px;
                    border-bottom: 1px solid #f0f2f5;
                    color: #475467;
                    font-size: 12px;
                    vertical-align: middle;
                }

                tbody tr:hover {
                    background: #fcfcff;
                }

                .kitchen-info {
                    display: flex;
                    align-items: center;
                    gap: 11px;
                }

                .kitchen-avatar {
                    width: 42px;
                    height: 42px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    border-radius: 12px;
                    background: #eeeeff;
                    color: var(--primary);
                    font-size: 17px;
                    font-weight: 800;
                }

                .kitchen-info>div:last-child {
                    display: flex;
                    flex-direction: column;
                    gap: 4px;
                }

                .kitchen-info strong {
                    color: var(--dark);
                    font-size: 13px;
                }

                .kitchen-info span {
                    color: var(--muted);
                    font-size: 11px;
                }

                .code-badge,
                .type-badge {
                    display: inline-flex;
                    padding: 6px 10px;
                    border-radius: 8px;
                    background: #f2f4f7;
                    color: #344054;
                    font-weight: 700;
                    white-space: nowrap;
                }

                .code-badge {
                    background: #eeeeff;
                    color: var(--primary);
                }

                .contact-info,
                .location-info {
                    display: flex;
                    flex-direction: column;
                    gap: 6px;
                }

                .contact-info span {
                    display: flex;
                    align-items: center;
                    gap: 7px;
                }

                .contact-info i {
                    width: 14px;
                    color: var(--primary);
                }

                .location-info strong {
                    color: var(--dark);
                    font-size: 12px;
                }

                .location-info span {
                    max-width: 230px;
                    color: var(--muted);
                    font-size: 11px;
                    white-space: normal;
                }

                .location-info a {
                    width: fit-content;
                    color: var(--primary);
                    font-size: 11px;
                    font-weight: 700;
                    text-decoration: none;
                }

                .status-badge {
                    display: inline-flex;
                    align-items: center;
                    gap: 7px;
                    padding: 6px 10px;
                    border-radius: 20px;
                    font-size: 10px;
                    font-weight: 700;
                    white-space: nowrap;
                }

                .status-dot {
                    width: 7px;
                    height: 7px;
                    border-radius: 50%;
                }

                .status-active {
                    background: #e8f8f0;
                    color: #137a50;
                }

                .status-active .status-dot {
                    background: #1dad72;
                }

                .status-pending {
                    background: #fff4da;
                    color: #9a6700;
                }

                .status-pending .status-dot {
                    background: #e6a419;
                }

                .status-not_active {
                    background: #fff0ef;
                    color: #b42318;
                }

                .status-not_active .status-dot {
                    background: #e04f44;
                }

                .actions {
                    display: flex;
                    align-items: center;
                    gap: 7px;
                }

                .actions form {
                    margin: 0;
                }

                .action-btn {
                    width: 33px;
                    height: 33px;
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    border: 0;
                    border-radius: 8px;
                    text-decoration: none;
                    cursor: pointer;
                }

                .action-btn.view {
                    background: #eff6ff;
                    color: #175cd3;
                }

                .action-btn.edit {
                    background: #f4f0ff;
                    color: #6941c6;
                }

                .action-btn.toggle {
                    background: #fff5df;
                    color: #a5650b;
                }

                .action-btn.delete {
                    background: #fff0ef;
                    color: #b42318;
                }

                .empty-state {
                    padding: 65px 20px;
                    text-align: center;
                }

                .empty-state>i {
                    margin-bottom: 15px;
                    color: #c5c9d0;
                    font-size: 50px;
                }

                .empty-state h3 {
                    margin: 0 0 7px;
                    color: var(--dark);
                }

                .empty-state p {
                    margin: 0 0 20px;
                    color: var(--muted);
                }

                .pagination-wrapper {
                    padding: 18px 20px;
                    border-top: 1px solid var(--border);
                }



                @media (max-width: 1100px) {
                    .statistics-grid {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }

                    .filters {
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                    }
                }

                @media (max-width: 650px) {
                    .page-header {
                        align-items: stretch;
                        flex-direction: column;
                    }

                    .statistics-grid,
                    .filters {
                        grid-template-columns: 1fr;
                    }

                    .primary-btn {
                        width: 100%;
                    }
                }
            </style>


        @endpush
