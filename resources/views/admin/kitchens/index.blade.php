@extends('admin.layouts.app')

@section('title', 'المطابخ')

@push('styles')
    <style>
        .kitchens-tools {
            display: flex;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .btn-rate {
            background: #7c3aed;
            color: #fff;
        }

        .btn-points {
            background: #0891b2;
            color: #fff;
        }

        .btn-points:hover {
            background: #0e7490;
        }

        .search-input {
            flex: 1;
            min-width: 260px;
            height: 46px;
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 14px;
            background: #fff;
        }

        .search-input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: center;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .btn {
            border: 0;
            border-radius: 9px;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
        }

        .btn-view {
            background: #2563eb;
            color: #fff;
        }

        .btn-code {
            background: #f59e0b;
            color: #fff;
        }

        .btn-active {
            background: #16a34a;
            color: #fff;
        }

        .btn-inactive {
            background: #dc2626;
            color: #fff;
        }

        .btn-copy {
            background: #0ea5e9;
            color: #fff;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            display: inline-block;
        }

        .badge-active {
            background: #dcfce7;
            color: #166534;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .code-box {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .code-value {
            background: #fef3c7;
            color: #92400e;
            padding: 7px 12px;
            border-radius: 10px;
            font-weight: 900;
        }

        #kitchensTable th,
        #kitchensTable td {
            text-align: center;
            vertical-align: middle;
        }

        #kitchensTable tbody tr:hover {
            background: #f9fafb;
        }

        /* Points Modal */

        .points-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .points-modal.show {
            display: flex;
        }

        .points-modal-overlay {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.65);
        }

        .points-modal-content {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 480px;
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
        }

        .points-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 22px;
        }

        .points-modal-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin: 0;
        }

        .points-modal-close {
            width: 36px;
            height: 36px;
            border: 0;
            border-radius: 50%;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .points-modal-close:hover {
            background: #e2e8f0;
        }

        .points-kitchen-name {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 12px;
            background: #ecfeff;
            color: #155e75;
            font-weight: 800;
        }

        .points-form-group {
            margin-bottom: 18px;
        }

        .points-form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 800;
            color: #334155;
        }

        .points-form-input,
        .points-form-textarea {
            width: 100%;
            border: 1px solid #dbe2ea;
            border-radius: 12px;
            padding: 12px 14px;
            font-size: 14px;
            background: #fff;
            box-sizing: border-box;
        }

        .points-form-input:focus,
        .points-form-textarea:focus {
            outline: none;
            border-color: #0891b2;
            box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
        }

        .points-form-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .points-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 22px;
        }

        .btn-save-points {
            background: #0891b2;
            color: #fff;
            padding: 11px 20px;
        }

        .btn-cancel-points {
            background: #e2e8f0;
            color: #334155;
            padding: 11px 20px;
        }

        .validation-errors {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 18px;
        }

        .validation-errors ul {
            margin: 0;
            padding-right: 18px;
        }
    </style>
@endpush

@section('content')

    <div class="page-head">
        <div class="page-title" style="margin-bottom:0;">
            قائمة المطابخ
        </div>

        <a href="{{ route('admin.kitchens.index') }}" class="back-btn">
            تحديث البيانات
        </a>
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="validation-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">
            <div class="table-title">
                المطابخ
            </div>
        </div>

        <div class="kitchens-tools">
            <input
                type="text"
                id="kitchenSearch"
                class="search-input"
                placeholder="بحث بالاسم أو البريد أو الهاتف"
            >
        </div>

        <div class="table-wrapper">
            <table id="kitchensTable">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الكود</th>
                        <th>عدد التسجيلات</th>
                        <th>المحافظة</th>
                        <th>المنطقة</th>
                        <th>حالة الحساب</th>
                        <th>حالة المطبخ</th>
                        <th>الملف</th>
                        <th>تاريخ الإنشاء</th>
                        <th>كود تغيير كلمة المرور</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($kitchens as $kitchenUser)
                        <tr>
                            <td>
                                <strong>{{ $kitchenUser->name ?? '-' }}</strong>
                            </td>

                            <td>{{ $kitchenUser->email ?? '-' }}</td>

                            <td>{{ $kitchenUser->phone ?? '-' }}</td>

                            <td>{{ $kitchenUser->code ?? '-' }}</td>

                            <td>
                                <span class="referral-badge">
                                    {{ $kitchenUser->referrals_count }}
                                </span>
                            </td>

                            <td>
                                {{ $kitchenUser->profile->government->name ?? '-' }}
                            </td>

                            <td>
                                {{ $kitchenUser->profile->area->name ?? '-' }}
                            </td>

                            <td>
                                @if ($kitchenUser->status === 'active')
                                    <span class="badge badge-active">
                                        نشط
                                    </span>
                                @else
                                    <span class="badge badge-inactive">
                                        غير نشط
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if (($kitchenUser->profile->statue ?? null) === 'approved')
                                    <span class="badge badge-active">
                                        مقبول
                                    </span>
                                @elseif (($kitchenUser->profile->statue ?? null) === 'rejected')
                                    <span class="badge badge-inactive">
                                        مرفوض
                                    </span>
                                @else
                                    <span class="badge badge-warning">
                                        {{ $kitchenUser->profile->statue ?? '-' }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                {{ $kitchenUser->profile ? 'موجود' : 'غير موجود' }}
                            </td>

                            <td>
                                {{ optional($kitchenUser->created_at)->format('Y-m-d H:i') }}
                            </td>

                            <td>
                                @if (session('generated_code_user_id') == $kitchenUser->id)
                                    <div
                                        class="code-box"
                                        id="code-box-{{ $kitchenUser->id }}"
                                    >
                                        <span
                                            class="code-value"
                                            id="code-{{ $kitchenUser->id }}"
                                        >
                                            {{ session('generated_code') }}
                                        </span>

                                        <button
                                            type="button"
                                            class="btn btn-copy"
                                            onclick="copyAndHideCode(
                                                'code-{{ $kitchenUser->id }}',
                                                'code-box-{{ $kitchenUser->id }}'
                                            )"
                                        >
                                            نسخ
                                        </button>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>

                            <td>
                                <div class="actions">

                                    <a
                                        href="{{ route('admin.kitchens.show', $kitchenUser->id) }}"
                                        class="btn btn-view"
                                    >
                                        عرض التفاصيل
                                    </a>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.kitchens.generate-code', $kitchenUser->id) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="btn btn-code"
                                        >
                                            تغيير الباسورد
                                        </button>
                                    </form>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.kitchens.active', $kitchenUser->id) }}"
                                    >
                                        @csrf

                                        <input
                                            type="hidden"
                                            name="status"
                                            value="{{ $kitchenUser->status === 'active' ? 'not_active' : 'active' }}"
                                        >

                                        <button
                                            type="submit"
                                            class="btn {{ $kitchenUser->status === 'active' ? 'btn-inactive' : 'btn-active' }}"
                                        >
                                            {{ $kitchenUser->status === 'active' ? 'تعطيل الحساب' : 'تفعيل الحساب' }}
                                        </button>
                                    </form>

                                    <a
                                        href="{{ route('admin.kitchens.rate', $kitchenUser->id) }}"
                                        class="btn btn-rate"
                                    >
                                        إضافة تقييم
                                    </a>

                                    <a
                                        href="{{ route('admin.kitchens.rates', $kitchenUser->id) }}"
                                        class="btn btn-rate"
                                    >
                                        التقييمات
                                    </a>

                                    <button
                                        type="button"
                                        class="btn btn-points"
                                        onclick="openPointsModal(
                                            @js($kitchenUser->name ?? 'المطبخ'),
                                            @js(route('admin.kitchens.points.add', $kitchenUser->id))
                                        )"
                                    >
                                        إضافة نقاط
                                    </button>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="13" class="empty">
                                لا توجد مطابخ
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <div class="points-modal" id="pointsModal">

        <div
            class="points-modal-overlay"
            onclick="closePointsModal()"
        ></div>

        <div class="points-modal-content" dir="rtl">

            <div class="points-modal-header">
                <h3 class="points-modal-title">
                    إضافة نقاط للمطبخ
                </h3>

                <button
                    type="button"
                    class="points-modal-close"
                    onclick="closePointsModal()"
                >
                    ×
                </button>
            </div>

            <div class="points-kitchen-name">
                المطبخ:
                <span id="pointsKitchenName">-</span>
            </div>

            <form method="POST" id="pointsForm">
                @csrf

                <div class="points-form-group">
                    <label
                        for="pointsInput"
                        class="points-form-label"
                    >
                        عدد النقاط
                    </label>

                    <input
                        type="number"
                        name="points"
                        id="pointsInput"
                        class="points-form-input"
                        min="1"
                        step="1"
                        required
                        placeholder="مثال: 100"
                    >
                </div>

                <div class="points-form-group">
                    <label
                        for="pointsNotes"
                        class="points-form-label"
                    >
                        الملاحظات
                    </label>

                    <textarea
                        name="notes"
                        id="pointsNotes"
                        class="points-form-textarea"
                        placeholder="سبب إضافة النقاط"
                    ></textarea>
                </div>

                <div class="points-modal-actions">

                    <button
                        type="button"
                        class="btn btn-cancel-points"
                        onclick="closePointsModal()"
                    >
                        إلغاء
                    </button>

                    <button
                        type="submit"
                        class="btn btn-save-points"
                    >
                        حفظ النقاط
                    </button>

                </div>
            </form>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const kitchenSearch =
            document.getElementById('kitchenSearch');

        const rows =
            document.querySelectorAll('#kitchensTable tbody tr');

        const pointsModal =
            document.getElementById('pointsModal');

        const pointsForm =
            document.getElementById('pointsForm');

        const pointsKitchenName =
            document.getElementById('pointsKitchenName');

        const pointsInput =
            document.getElementById('pointsInput');

        const pointsNotes =
            document.getElementById('pointsNotes');

        if (kitchenSearch) {
            kitchenSearch.addEventListener('keyup', function() {
                const value = this.value.toLowerCase();

                rows.forEach(row => {
                    row.style.display =
                        row.innerText.toLowerCase().includes(value)
                            ? ''
                            : 'none';
                });
            });
        }

        function copyAndHideCode(codeId, boxId) {
            const code =
                document.getElementById(codeId);

            const box =
                document.getElementById(boxId);

            if (!code) {
                return;
            }

            navigator.clipboard
                .writeText(code.innerText.trim())
                .then(() => {
                    if (box) {
                        setTimeout(() => {
                            box.style.display = 'none';
                        }, 500);
                    }
                })
                .catch(error => {
                    console.error(
                        'Failed to copy code:',
                        error
                    );
                });
        }

        function openPointsModal(
            kitchenName,
            actionUrl
        ) {
            if (
                !pointsModal ||
                !pointsForm ||
                !pointsKitchenName
            ) {
                return;
            }

            pointsForm.action = actionUrl;

            pointsKitchenName.textContent =
                kitchenName || 'المطبخ';

            if (pointsInput) {
                pointsInput.value = '';
            }

            if (pointsNotes) {
                pointsNotes.value = '';
            }

            pointsModal.classList.add('show');

            document.body.style.overflow = 'hidden';

            setTimeout(() => {
                pointsInput?.focus();
            }, 100);
        }

        function closePointsModal() {
            if (!pointsModal) {
                return;
            }

            pointsModal.classList.remove('show');

            document.body.style.overflow = '';
        }

        document.addEventListener(
            'keydown',
            function(event) {
                if (event.key === 'Escape') {
                    closePointsModal();
                }
            }
        );

        pointsForm?.addEventListener(
            'submit',
            function() {
                const submitButton =
                    this.querySelector(
                        'button[type="submit"]'
                    );

                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.innerText =
                        'جاري الحفظ...';
                }
            }
        );
    </script>
@endpush