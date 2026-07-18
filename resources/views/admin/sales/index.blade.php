@extends('admin.layouts.app')

@section('title', 'موظفو المبيعات')

@section('content')
    <div class="sales-page" dir="rtl">

        <div class="page-heading">
            <div>
                <h1>موظفو المبيعات</h1>
                <p>إدارة بيانات وحسابات موظفي المبيعات.</p>
            </div>

            <a href="{{ route('admin.sales.create') }}" class="primary-button">
                <span class="button-icon">+</span>
                إضافة موظف مبيعات
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <span>!</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="statistics-grid">
            <div class="statistic-card">
                <div class="statistic-icon statistic-icon-total">
                    👥
                </div>

                <div>
                    <span class="statistic-title">إجمالي الموظفين</span>
                    <strong>{{ $statistics['total'] }}</strong>
                </div>
            </div>

            <div class="statistic-card">
                <div class="statistic-icon statistic-icon-active">
                    ✓
                </div>

                <div>
                    <span class="statistic-title">الموظفون النشطون</span>
                    <strong>{{ $statistics['active'] }}</strong>
                </div>
            </div>

            <div class="statistic-card">
                <div class="statistic-icon statistic-icon-inactive">
                    ⏸
                </div>

                <div>
                    <span class="statistic-title">الموظفون غير النشطين</span>
                    <strong>{{ $statistics['inactive'] }}</strong>
                </div>
            </div>
        </div>

        <div class="content-card">
            <form
                action="{{ route('admin.sales.index') }}"
                method="GET"
                class="filter-form"
            >
                <div class="search-field">
                    <span class="search-icon">⌕</span>

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="ابحث بالاسم أو الكود أو الهاتف أو البريد..."
                    >
                </div>

                <select name="status">
                    <option value="">كل الحالات</option>

                    <option
                        value="active"
                        @selected($status === 'active')
                    >
                        نشط
                    </option>

                    <option
                        value="inactive"
                        @selected($status === 'inactive')
                    >
                        غير نشط
                    </option>
                </select>

                <button type="submit" class="filter-button">
                    بحث
                </button>

                @if ($search !== '' || $status)
                    <a
                        href="{{ route('admin.sales.index') }}"
                        class="reset-button"
                    >
                        إعادة تعيين
                    </a>
                @endif
            </form>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الكود</th>
                            <th>الموظف</th>
                            <th>رقم الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>الحالة</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($sales as $sale)
                            <tr>
                                <td>
                                    {{ $sales->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <span class="code-badge">
                                        {{ $sale->code }}
                                    </span>
                                </td>

                                <td>
                                    <div class="employee-cell">
                                        <div class="employee-avatar">
                                            {{ mb_substr($sale->name, 0, 1) }}
                                        </div>

                                        <div>
                                            <strong>{{ $sale->name }}</strong>
                                            <span>موظف مبيعات</span>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <a
                                        href="tel:{{ $sale->phone }}"
                                        class="table-link"
                                    >
                                        {{ $sale->phone }}
                                    </a>
                                </td>

                                <td>
                                    @if ($sale->email)
                                        <a
                                            href="mailto:{{ $sale->email }}"
                                            class="table-link"
                                        >
                                            {{ $sale->email }}
                                        </a>
                                    @else
                                        <span class="empty-value">
                                            غير مسجل
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span
                                        class="status-badge
                                        {{ $sale->status === 'active'
                                            ? 'status-active'
                                            : 'status-inactive' }}"
                                    >
                                        <span class="status-dot"></span>

                                        {{ $sale->status === 'active'
                                            ? 'نشط'
                                            : 'غير نشط' }}
                                    </span>
                                </td>

                                <td>
                                    {{ optional($sale->created_at)->format('Y/m/d') }}
                                </td>

                                <td>
                                    <div class="actions">
                                        <a
                                            href="{{ route('admin.sales.show', $sale) }}"
                                            class="action-button view-action"
                                            title="عرض"
                                        >
                                            عرض
                                        </a>

                                        <a
                                            href="{{ route('admin.sales.edit', $sale) }}"
                                            class="action-button edit-action"
                                            title="تعديل"
                                        >
                                            تعديل
                                        </a>

                                        <form
                                            action="{{ route('admin.sales.toggle-status', $sale) }}"
                                            method="POST"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="action-button status-action"
                                            >
                                                {{ $sale->status === 'active'
                                                    ? 'إيقاف'
                                                    : 'تفعيل' }}
                                            </button>
                                        </form>

                                        <form
                                            action="{{ route('admin.sales.destroy', $sale) }}"
                                            method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من حذف موظف المبيعات؟')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="action-button delete-action"
                                            >
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <div class="empty-state">
                                        <div class="empty-state-icon">👤</div>
                                        <h3>لا يوجد موظفو مبيعات</h3>
                                        <p>
                                            لم يتم العثور على بيانات مطابقة.
                                        </p>

                                        <a
                                            href="{{ route('admin.sales.create') }}"
                                            class="primary-button"
                                        >
                                            إضافة أول موظف
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($sales->hasPages())
                <div class="pagination-wrapper">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .sales-page {
            --primary: #4948ab;
            --primary-light: #efefff;
            --text-dark: #202b3d;
            --text-muted: #7b8494;
            --border: #e8ebf1;
            --background: #f6f7fb;
            font-family: "Cairo", sans-serif;
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 24px;
        }

        .page-heading h1 {
            margin: 0 0 7px;
            color: var(--text-dark);
            font-size: 27px;
            font-weight: 800;
        }

        .page-heading p {
            margin: 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .primary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            padding: 0 20px;
            border-radius: 12px;
            background: var(--primary);
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 700;
            border: 0;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(73, 72, 171, .18);
        }

        .primary-button:hover {
            color: #fff;
            background: #3e3d99;
        }

        .button-icon {
            font-size: 24px;
            line-height: 1;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 17px;
            margin-bottom: 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-success {
            color: #137a50;
            background: #e9faf2;
            border: 1px solid #bdebd5;
        }

        .alert-error {
            color: #b42318;
            background: #fff0ef;
            border: 1px solid #ffc9c5;
        }

        .statistics-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 22px;
        }

        .statistic-card {
            display: flex;
            align-items: center;
            gap: 16px;
            min-height: 108px;
            padding: 20px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(32, 43, 61, .04);
        }

        .statistic-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 15px;
            font-size: 21px;
            font-weight: 800;
        }

        .statistic-icon-total {
            color: #4948ab;
            background: #efefff;
        }

        .statistic-icon-active {
            color: #138a5b;
            background: #e7faf1;
        }

        .statistic-icon-inactive {
            color: #c47b10;
            background: #fff5df;
        }

        .statistic-card > div:last-child {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .statistic-title {
            color: var(--text-muted);
            font-size: 13px;
        }

        .statistic-card strong {
            color: var(--text-dark);
            font-size: 25px;
            font-weight: 800;
        }

        .content-card {
            overflow: hidden;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: 0 8px 28px rgba(32, 43, 61, .05);
        }

        .filter-form {
            display: grid;
            grid-template-columns: minmax(260px, 1fr) 190px auto auto;
            gap: 12px;
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .search-field {
            position: relative;
        }

        .search-icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #8d96a5;
            font-size: 21px;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            height: 45px;
            padding: 0 15px;
            border: 1px solid #dfe3ea;
            border-radius: 11px;
            background: #fff;
            color: var(--text-dark);
            font-family: inherit;
            font-size: 13px;
            outline: none;
        }

        .filter-form input {
            padding-right: 44px;
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(73, 72, 171, .08);
        }

        .filter-button,
        .reset-button {
            min-height: 45px;
            padding: 0 21px;
            border-radius: 11px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .filter-button {
            border: 0;
            background: var(--primary);
            color: #fff;
        }

        .reset-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #dfe3ea;
            background: #fff;
            color: var(--text-dark);
            text-decoration: none;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 1050px;
            border-collapse: collapse;
        }

        th {
            padding: 15px 17px;
            background: #fafbfc;
            color: #667085;
            text-align: right;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 17px;
            color: #3d4655;
            font-size: 13px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f2f6;
        }

        tbody tr:hover {
            background: #fcfcff;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .code-badge {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 8px;
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 800;
        }

        .employee-cell {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .employee-avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 50%;
            background: #dfe7ff;
            color: var(--primary);
            font-size: 16px;
            font-weight: 800;
        }

        .employee-cell > div:last-child {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .employee-cell strong {
            color: var(--text-dark);
            font-size: 13px;
        }

        .employee-cell span {
            color: var(--text-muted);
            font-size: 11px;
        }

        .table-link {
            color: #475467;
            text-decoration: none;
        }

        .table-link:hover {
            color: var(--primary);
        }

        .empty-value {
            color: #a2a9b4;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .status-active {
            color: #137a50;
            background: #e8f8f0;
        }

        .status-active .status-dot {
            background: #1dad72;
        }

        .status-inactive {
            color: #9a6700;
            background: #fff4da;
        }

        .status-inactive .status-dot {
            background: #e6a419;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 7px;
            white-space: nowrap;
        }

        .actions form {
            margin: 0;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 32px;
            padding: 0 10px;
            border-radius: 8px;
            border: 0;
            font-family: inherit;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .view-action {
            color: #175cd3;
            background: #eff6ff;
        }

        .edit-action {
            color: #6941c6;
            background: #f4f0ff;
        }

        .status-action {
            color: #9a6700;
            background: #fff5dd;
        }

        .delete-action {
            color: #b42318;
            background: #fff0ef;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state-icon {
            margin-bottom: 12px;
            font-size: 42px;
        }

        .empty-state h3 {
            margin: 0 0 7px;
            color: var(--text-dark);
            font-size: 18px;
        }

        .empty-state p {
            margin: 0 0 20px;
            color: var(--text-muted);
            font-size: 13px;
        }

        .pagination-wrapper {
            padding: 18px 20px;
            border-top: 1px solid var(--border);
        }

        @media (max-width: 900px) {
            .statistics-grid {
                grid-template-columns: 1fr;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .page-heading {
                align-items: stretch;
                flex-direction: column;
            }

            .primary-button {
                width: 100%;
            }
        }
    </style>
@endpush