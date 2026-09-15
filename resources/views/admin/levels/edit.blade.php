@extends('admin.layouts.app')

@section('title', 'تعديل المستوى')

@push('styles')
    <style>
        .level-edit-page {
            direction: rtl;
        }

        .level-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .level-page-title {
            margin: 0;
            font-size: 26px;
            font-weight: 900;
            color: #111827;
        }

        .level-page-description {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .level-back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            background: #e5e7eb;
            color: #111827;
            font-weight: 800;
            text-decoration: none;
            transition: 0.2s;
        }

        .level-back-btn:hover {
            background: #d1d5db;
            color: #111827;
        }

        .level-form-card {
            background: #fff;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .level-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .level-form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .level-form-group.full-width {
            grid-column: 1 / -1;
        }

        .level-form-label {
            font-size: 14px;
            font-weight: 800;
            color: #374151;
        }

        .level-form-input {
            width: 100%;
            height: 48px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 0 14px;
            font-size: 15px;
            color: #111827;
            background: #fff;
            transition: 0.2s;
        }

        .level-form-input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .level-form-input.is-invalid {
            border-color: #dc2626;
        }

        .level-input-hint {
            color: #6b7280;
            font-size: 13px;
        }

        .level-error-text {
            color: #dc2626;
            font-size: 13px;
            font-weight: 700;
        }

        .level-form-actions {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 12px;
            margin-top: 26px;
            padding-top: 22px;
            border-top: 1px solid #e5e7eb;
        }

        .level-save-btn,
        .level-cancel-btn {
            border: 0;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 800;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .level-save-btn {
            background: #2563eb;
            color: #fff;
        }

        .level-save-btn:hover {
            background: #1d4ed8;
        }

        .level-cancel-btn {
            background: #e5e7eb;
            color: #111827;
        }

        .level-cancel-btn:hover {
            background: #d1d5db;
            color: #111827;
        }

        .level-validation-alert {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 12px;
            padding: 14px 18px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .level-validation-alert ul {
            margin: 8px 0 0;
            padding-right: 20px;
        }

        @media (max-width: 768px) {
            .level-form-grid {
                grid-template-columns: 1fr;
            }

            .level-form-group.full-width {
                grid-column: auto;
            }

            .level-form-card {
                padding: 20px;
            }

            .level-form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .level-save-btn,
            .level-cancel-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')

    <div class="level-edit-page">

        <div class="level-page-header">
            <div>
                <h1 class="level-page-title">
                    تعديل المستوى
                </h1>

                <p class="level-page-description">
                    تعديل بيانات المستوى وحد الكاش والمسافة ونوع وسيلة التوصيل.
                </p>
            </div>

            <a href="{{ route('admin.levels.index') }}"
                class="level-back-btn">
                العودة إلى المستويات
            </a>
        </div>

        @if ($errors->any())
            <div class="level-validation-alert">
                <div>
                    يوجد خطأ في البيانات المدخلة:
                </div>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="level-form-card">

            <form
                action="{{ route('admin.levels.update', $level->id) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="level-form-grid">

                    <div class="level-form-group">
                        <label
                            for="name"
                            class="level-form-label"
                        >
                            اسم المستوى
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $level->name) }}"
                            class="level-form-input @error('name') is-invalid @enderror"
                            placeholder="أدخل اسم المستوى"
                            required
                        >

                        @error('name')
                            <div class="level-error-text">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="level-form-group">
                        <label
                            for="cash_money"
                            class="level-form-label"
                        >
                            الحد الأقصى للكاش
                        </label>

                        <input
                            type="number"
                            id="cash_money"
                            name="cash_money"
                            value="{{ old('cash_money', $level->cash_money) }}"
                            class="level-form-input @error('cash_money') is-invalid @enderror"
                            min="0"
                            step="0.01"
                            placeholder="مثال: 1000"
                            required
                        >

                        <div class="level-input-hint">
                            الحد الذي يجب بعده على الدليفري توريد المبلغ.
                        </div>

                        @error('cash_money')
                            <div class="level-error-text">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="level-form-group">
                        <label
                            for="km"
                            class="level-form-label"
                        >
                            نطاق المسافة بالكيلومتر
                        </label>

                        <input
                            type="number"
                            id="km"
                            name="km"
                            value="{{ old('km', $level->km) }}"
                            class="level-form-input @error('km') is-invalid @enderror"
                            min="0"
                            step="0.01"
                            placeholder="مثال: 10"
                            required
                        >

                        <div class="level-input-hint">
                            أقصى مسافة متاحة لهذا المستوى.
                        </div>

                        @error('km')
                            <div class="level-error-text">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- <div class="level-form-group">
                        <label
                            for="vehicle_type"
                            class="level-form-label"
                        >
                            نوع وسيلة التوصيل
                        </label>

                        <input
                            type="text"
                            id="vehicle_type"
                            name="vehicle_type"
                            value="{{ old('vehicle_type', $level->vehicle_type) }}"
                            class="level-form-input @error('vehicle_type') is-invalid @enderror"
                            placeholder="مثال: motorcycle"
                            required
                        >

                        @error('vehicle_type')
                            <div class="level-error-text">
                                {{ $message }}
                            </div>
                        @enderror
                    </div> --}}

                </div>

                <div class="level-form-actions">

                    <button
                        type="submit"
                        class="level-save-btn"
                    >
                        حفظ التعديلات
                    </button>

                    <a
                        href="{{ route('admin.levels.index') }}"
                        class="level-cancel-btn"
                    >
                        إلغاء
                    </a>

                </div>
            </form>

        </div>
    </div>

@endsection