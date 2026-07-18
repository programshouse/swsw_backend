@extends('admin.layouts.app')

@section('title', 'إنشاء كود خصم')

@push('styles')
    <style>
        .cash-code-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .cash-code-form-grid .full-width {
            grid-column: 1 / -1;
        }

        .form-help {
            display: block;
            margin-top: 7px;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.7;
        }

        .form-error {
            display: block;
            margin-top: 7px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 700;
        }

        .switch-card {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 16px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #f9fafb;
        }

        .switch-info strong {
            display: block;
            margin-bottom: 5px;
            color: #111827;
        }

        .switch-info span {
            color: #6b7280;
            font-size: 12px;
        }

        .switch-control {
            width: 52px;
            height: 28px;
            position: relative;
            flex: 0 0 52px;
        }

        .switch-control input {
            width: 0;
            height: 0;
            opacity: 0;
        }

        .switch-slider {
            position: absolute;
            inset: 0;
            border-radius: 30px;
            background: #cbd5e1;
            cursor: pointer;
            transition: .2s;
        }

        .switch-slider::before {
            content: "";
            position: absolute;
            width: 20px;
            height: 20px;
            top: 4px;
            right: 4px;
            border-radius: 50%;
            background: #ffffff;
            transition: .2s;
            box-shadow: 0 2px 7px rgba(0, 0, 0, .2);
        }

        .switch-control input:checked + .switch-slider {
            background: #2563eb;
        }

        .switch-control input:checked + .switch-slider::before {
            transform: translateX(-24px);
        }

        .form-actions-row {
            display: flex;
            justify-content: flex-start;
            gap: 10px;
            padding-top: 20px;
            margin-top: 22px;
            border-top: 1px solid #e5e7eb;
        }

        .form-actions-row .cancel-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .type-options {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .type-option {
            position: relative;
        }

        .type-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .type-option label {
            display: block;
            height: 100%;
            padding: 18px;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            background: #ffffff;
            cursor: pointer;
            transition: .2s;
        }

        .type-option label:hover {
            border-color: #93c5fd;
            background: #f8fbff;
        }

        .type-option input:checked + label {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
        }

        .type-option-title {
            display: block;
            margin-bottom: 7px;
            color: #111827;
            font-weight: 900;
            font-size: 16px;
        }

        .type-option-description {
            display: block;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.7;
        }

        .conditional-section {
            display: none;
        }

        .conditional-section.active {
            display: block;
        }

        .field-card {
            padding: 16px;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #f9fafb;
        }

        .percentage-preview {
            padding: 15px;
            border-radius: 12px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 700;
            line-height: 1.8;
        }

        @media(max-width: 768px) {
            .cash-code-form-grid,
            .type-options {
                grid-template-columns: 1fr;
            }

            .cash-code-form-grid .full-width {
                grid-column: auto;
            }

            .form-actions-row {
                flex-direction: column;
            }

            .form-actions-row .save-btn,
            .form-actions-row .cancel-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')

    <div class="page-head">
        <div>
            <div class="page-title" style="margin-bottom: 6px;">
                إنشاء كود جديد
            </div>

            <div style="color: #6b7280; font-size: 14px;">
                إنشاء كود رصيد مالي أو كود خصم بنسبة لمستخدم محدد
            </div>
        </div>

        <a
            href="{{ route('admin.cash-codes.index') }}"
            class="back-btn"
        >
            رجوع
        </a>
    </div>

    @if ($errors->any())
        <div
            class="success-alert"
            style="background: #fee2e2; color: #991b1b;"
        >
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="section-card">

        <div class="section-header">
            <h3>بيانات الكود</h3>
        </div>

        <form
            method="POST"
            action="{{ route('admin.cash-codes.store') }}"
        >
            @csrf

            <div class="cash-code-form-grid">

                {{-- المستخدم --}}
                <div class="form-group">
                    <label class="form-label">
                        المستخدم
                        <span style="color: #dc2626;">*</span>
                    </label>

                    <select
                        name="user_id"
                        class="form-input"
                        required
                    >
                        <option value="">
                            اختر المستخدم
                        </option>

                        @forelse ($clients as $client)
                            <option
                                value="{{ $client->id }}"
                                {{ old('user_id') == $client->id ? 'selected' : '' }}
                            >
                                {{ $client->name ?? 'بدون اسم' }}

                                @if ($client->phone)
                                    - {{ $client->phone }}
                                @endif
                            </option>
                        @empty
                            <option value="" disabled>
                                لا يوجد مستخدمون متاحون
                            </option>
                        @endforelse
                    </select>

                    @error('user_id')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- الكود --}}
                <div class="form-group">
                    <label class="form-label">
                        الكود
                    </label>

                    <input
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        class="form-input"
                        placeholder="اتركه فارغًا لتوليده تلقائيًا"
                        dir="ltr"
                    >

                    <span class="form-help">
                        مثال لكود الرصيد: CASH-ABC12345
                        <br>
                        مثال لكود النسبة: DISCOUNT-ABC12345
                    </span>

                    @error('code')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- نوع الكود --}}
                <div class="form-group full-width">
                    <label class="form-label">
                        نوع الكود
                        <span style="color: #dc2626;">*</span>
                    </label>

                    <div class="type-options">

                        <div class="type-option">
                            <input
                                type="radio"
                                id="type_balance"
                                name="discount_type"
                                value="balance"
                                {{ old('discount_type', 'balance') === 'balance' ? 'checked' : '' }}
                            >

                            <label for="type_balance">
                                <span class="type-option-title">
                                    كود رصيد مالي
                                </span>

                                <span class="type-option-description">
                                    يضاف للمستخدم رصيد مالي يمكن استخدامه في دفع قيمة الطلبات.
                                </span>
                            </label>
                        </div>

                        <div class="type-option">
                            <input
                                type="radio"
                                id="type_percentage"
                                name="discount_type"
                                value="percentage"
                                {{ old('discount_type') === 'percentage' ? 'checked' : '' }}
                            >

                            <label for="type_percentage">
                                <span class="type-option-title">
                                    كود خصم بنسبة
                                </span>

                                <span class="type-option-description">
                                    يتم خصم نسبة محددة من قيمة الطلب مثل 20%.
                                </span>
                            </label>
                        </div>

                    </div>

                    @error('discount_type')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- بيانات كود الرصيد --}}
                <div
                    id="balanceFields"
                    class="conditional-section full-width"
                >
                    <div class="field-card">
                        <div class="cash-code-form-grid">

                            <div class="form-group">
                                <label class="form-label">
                                    قيمة الرصيد
                                    <span style="color: #dc2626;">*</span>
                                </label>

                                <input
                                    type="number"
                                    id="balance"
                                    name="balance"
                                    value="{{ old('balance') }}"
                                    class="form-input"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="مثال: 1000"
                                >

                                <span class="form-help">
                                    القيمة الإجمالية التي يمكن استخدامها في دفع الطلبات.
                                </span>

                                @error('balance')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    أقل قيمة للطلب
                                </label>

                                <input
                                    type="number"
                                    name="minimum_order_amount"
                                    value="{{ old('minimum_order_amount') }}"
                                    class="form-input"
                                    step="0.01"
                                    min="0"
                                    placeholder="مثال: 100"
                                >

                                <span class="form-help">
                                    اتركه فارغًا للسماح باستخدام الرصيد مع أي طلب.
                                </span>

                                @error('minimum_order_amount')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- بيانات كود النسبة --}}
                <div
                    id="percentageFields"
                    class="conditional-section full-width"
                >
                    <div class="field-card">
                        <div class="cash-code-form-grid">

                            <div class="form-group">
                                <label class="form-label">
                                    نسبة الخصم
                                    <span style="color: #dc2626;">*</span>
                                </label>

                                <input
                                    type="number"
                                    id="discount_percentage"
                                    name="discount_percentage"
                                    value="{{ old('discount_percentage') }}"
                                    class="form-input"
                                    step="0.01"
                                    min="0.01"
                                    max="100"
                                    placeholder="مثال: 20"
                                >

                                <span class="form-help">
                                    أدخل النسبة بدون علامة %، مثال: 20.
                                </span>

                                @error('discount_percentage')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    الحد الأقصى لقيمة الخصم
                                </label>

                                <input
                                    type="number"
                                    name="max_discount_amount"
                                    value="{{ old('max_discount_amount') }}"
                                    class="form-input"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="مثال: 150"
                                >

                                <span class="form-help">
                                    اختياري. يمنع الخصم من تجاوز قيمة محددة.
                                </span>

                                @error('max_discount_amount')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    أقل قيمة للطلب
                                </label>

                                <input
                                    type="number"
                                    name="minimum_order_amount"
                                    value="{{ old('minimum_order_amount') }}"
                                    class="form-input"
                                    step="0.01"
                                    min="0"
                                    placeholder="مثال: 300"
                                >

                                <span class="form-help">
                                    لا يمكن استخدام الكود إذا كانت قيمة الطلب أقل منها.
                                </span>

                                @error('minimum_order_amount')
                                    <span class="form-error">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label">
                                    مثال على الخصم
                                </label>

                                <div class="percentage-preview">
                                    إذا كانت نسبة الخصم 20% وقيمة الطلب 500 جنيه،
                                    تكون قيمة الخصم 100 جنيه.
                                    <br>
                                    إذا كان الحد الأقصى 80 جنيه، يكون الخصم النهائي 80 جنيه.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- الحد الأقصى للاستخدام --}}
                <div class="form-group">
                    <label class="form-label">
                        الحد الأقصى للاستخدام
                    </label>

                    <input
                        type="number"
                        name="max_uses"
                        value="{{ old('max_uses') }}"
                        class="form-input"
                        min="1"
                        placeholder="اتركه فارغًا لعدم تحديد حد"
                    >

                    <span class="form-help">
                        عدد الطلبات التي يمكن استخدام الكود فيها.
                    </span>

                    @error('max_uses')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- تاريخ الانتهاء --}}
                <div class="form-group">
                    <label class="form-label">
                        تاريخ ووقت الانتهاء
                        <span style="color: #dc2626;">*</span>
                    </label>

                    <input
                        type="datetime-local"
                        name="expires_at"
                        value="{{ old('expires_at') }}"
                        class="form-input"
                        min="{{ now()->format('Y-m-d\TH:i') }}"
                        required
                    >

                    @error('expires_at')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                {{-- حالة الكود --}}
                <div class="form-group">
                    <label class="form-label">
                        حالة الكود
                    </label>

                    <div class="switch-card">
                        <div class="switch-info">
                            <strong>تفعيل الكود</strong>

                            <span>
                                الكود غير المفعل لا يمكن استخدامه في الطلبات.
                            </span>
                        </div>

                        <label class="switch-control">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ old('is_active', true) ? 'checked' : '' }}
                            >

                            <span class="switch-slider"></span>
                        </label>
                    </div>
                </div>

                {{-- الملاحظات --}}
                <div class="form-group full-width">
                    <label class="form-label">
                        ملاحظات
                    </label>

                    <textarea
                        name="notes"
                        class="form-input"
                        rows="4"
                        style="height: auto; padding-top: 12px;"
                        placeholder="أدخل أي ملاحظات خاصة بالكود"
                    >{{ old('notes') }}</textarea>

                    @error('notes')
                        <span class="form-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

            </div>

            <div class="form-actions-row">
                <button
                    type="submit"
                    class="save-btn"
                >
                    حفظ الكود
                </button>

                <a
                    href="{{ route('admin.cash-codes.index') }}"
                    class="cancel-btn"
                >
                    إلغاء
                </a>
            </div>

        </form>

    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const balanceRadio =
                document.getElementById('type_balance');

            const percentageRadio =
                document.getElementById('type_percentage');

            const balanceFields =
                document.getElementById('balanceFields');

            const percentageFields =
                document.getElementById('percentageFields');

            const balanceInput =
                document.getElementById('balance');

            const percentageInput =
                document.getElementById('discount_percentage');

            function updateCodeTypeFields() {
                const isBalance = balanceRadio.checked;

                balanceFields.classList.toggle(
                    'active',
                    isBalance
                );

                percentageFields.classList.toggle(
                    'active',
                    !isBalance
                );

                balanceInput.required = isBalance;
                percentageInput.required = !isBalance;

                if (isBalance) {
                    percentageInput.removeAttribute('required');
                } else {
                    balanceInput.removeAttribute('required');
                }
            }

            balanceRadio.addEventListener(
                'change',
                updateCodeTypeFields
            );

            percentageRadio.addEventListener(
                'change',
                updateCodeTypeFields
            );

            updateCodeTypeFields();
        });
    </script>
@endpush