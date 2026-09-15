@extends('admin.layouts.app')

@section('title', 'تعديل الأدمن')

@section('content')
    <div class="page-title">
        تعديل حساب الأدمن
    </div>

    @if ($errors->any())
        <div class="error-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.admins.update', $admin->id) }}"
        method="POST"
        class="form-card"
    >
        @csrf
        @method('PUT')

        <div class="form-grid">

            <div class="form-group">
                <label class="form-label">
                    الاسم
                </label>

                <input
                    type="text"
                    name="name"
                    class="form-input"
                    value="{{ old('name', $admin->name) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    البريد الإلكتروني
                </label>

                <input
                    type="email"
                    name="email"
                    class="form-input"
                    value="{{ old('email', $admin->email) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    رقم الهاتف
                </label>

                <input
                    type="text"
                    name="phone"
                    class="form-input"
                    value="{{ old('phone', $admin->phone) }}"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label">
                    نوع الأدمن
                </label>

                <select
                    name="admin_type"
                    id="adminType"
                    class="form-input"
                    required
                >
                    <option value="">
                        اختر نوع الأدمن
                    </option>

                    <option
                        value="area_admin"
                        {{ old('admin_type', $admin->admin_type) === 'area_admin' ? 'selected' : '' }}
                    >
                        أدمن منطقة
                    </option>

                    <option
                        value="custom_admin"
                        {{ old('admin_type', $admin->admin_type) === 'custom_admin' ? 'selected' : '' }}
                    >
                        أدمن مخصص
                    </option>
                </select>
            </div>

            <div
                class="form-group"
                id="areaGroup"
            >
                <label class="form-label">
                    المنطقة
                </label>

                <select
                    name="admin_area_id"
                    class="form-input"
                >
                    <option value="">
                        اختر المنطقة
                    </option>

                    @foreach ($areas as $area)
                        <option
                            value="{{ $area->id }}"
                            {{ (string) old('admin_area_id', $admin->admin_area_id) === (string) $area->id ? 'selected' : '' }}
                        >
                            {{ $area->government?->name_ar
                                ?? $area->government?->name
                                ?? '' }}

                            -

                            {{ $area->name_ar
                                ?? $area->name
                                ?? '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">
                    كلمة المرور الجديدة
                </label>

                <input
                    type="password"
                    name="password"
                    class="form-input"
                    autocomplete="new-password"
                >

                <small class="form-hint">
                    اتركيها فارغة إذا كنتِ لا تريدين تغييرها.
                </small>
            </div>

            <div class="form-group">
                <label class="form-label">
                    تأكيد كلمة المرور
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-input"
                    autocomplete="new-password"
                >
            </div>

            <div class="form-group status-group">
                <label class="checkbox-label">
                    <input
                        type="checkbox"
                        name="is_admin_active"
                        value="1"
                        {{ old('is_admin_active', $admin->is_admin_active) ? 'checked' : '' }}
                    >

                    <span>
                        الحساب نشط
                    </span>
                </label>
            </div>

        </div>

        <div class="permissions-section">
            <div class="section-header">
                <div>
                    <h3>الصلاحيات</h3>
                    <p>
                        حددي الصلاحيات المتاحة لهذا الأدمن.
                    </p>
                </div>

                <button
                    type="button"
                    class="secondary-btn"
                    id="togglePermissions"
                >
                    تحديد الكل
                </button>
            </div>

            <div class="permissions-grid">
                @foreach ($permissions as $permission)
                    <label class="permission-card">
                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->name }}"
                            class="permission-checkbox"
                            {{ in_array(
                                $permission->name,
                                old('permissions', $selectedPermissions),
                                true
                            ) ? 'checked' : '' }}
                        >

                        <span class="permission-content">
                            <span class="permission-title">
                                {{ $permission->label }}
                            </span>

                            <span class="permission-name">
                                {{ $permission->name }}
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-actions">
            <a
                href="{{ route('admin.admins.index') }}"
                class="cancel-btn"
            >
                إلغاء
            </a>

            <button
                type="submit"
                class="save-btn"
            >
                حفظ التعديلات
            </button>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .page-title {
            margin-bottom: 24px;
            font-size: 24px;
            font-weight: 700;
            color: #111827;
        }

        .form-card {
            padding: 24px;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
        }

        .form-input {
            width: 100%;
            min-height: 46px;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #ffffff;
            color: #111827;
            outline: none;
            transition: 0.2s;
        }

        .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .form-hint {
            color: #6b7280;
            font-size: 12px;
        }

        .status-group {
            justify-content: flex-end;
            min-height: 78px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #f9fafb;
            font-weight: 700;
            cursor: pointer;
        }

        .permissions-section {
            margin-top: 30px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 18px;
        }

        .section-header h3 {
            margin: 0 0 5px;
            font-size: 19px;
            color: #111827;
        }

        .section-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding: 4px;
        }

        .permission-card {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 13px;
            border: 1px solid #e5e7eb;
            border-radius: 11px;
            background: #f9fafb;
            cursor: pointer;
        }

        .permission-card:has(input:checked) {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .permission-checkbox {
            margin-top: 4px;
        }

        .permission-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .permission-title {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        .permission-name {
            font-size: 11px;
            color: #6b7280;
            direction: ltr;
            text-align: left;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .save-btn,
        .cancel-btn,
        .secondary-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 9px 18px;
            border-radius: 9px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .save-btn {
            border: 0;
            background: #2563eb;
            color: #ffffff;
        }

        .cancel-btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #374151;
        }

        .secondary-btn {
            border: 1px solid #d1d5db;
            background: #f9fafb;
            color: #374151;
        }

        .error-alert {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #fecaca;
            border-radius: 10px;
            background: #fef2f2;
            color: #991b1b;
        }

        .error-alert ul {
            margin: 0;
            padding-right: 18px;
        }

        @media (max-width: 1000px) {
            .permissions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .form-grid,
            .permissions-grid {
                grid-template-columns: 1fr;
            }

            .section-header,
            .form-actions {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const adminType = document.getElementById('adminType');
            const areaGroup = document.getElementById('areaGroup');
            const toggleButton = document.getElementById('togglePermissions');
            const permissionCheckboxes = Array.from(
                document.querySelectorAll('.permission-checkbox')
            );

            function toggleAreaField() {
                areaGroup.style.display =
                    adminType.value === 'area_admin'
                        ? 'flex'
                        : 'none';
            }

            function updateToggleButtonText() {
                const allChecked =
                    permissionCheckboxes.length > 0 &&
                    permissionCheckboxes.every(
                        checkbox => checkbox.checked
                    );

                toggleButton.textContent = allChecked
                    ? 'إلغاء تحديد الكل'
                    : 'تحديد الكل';
            }

            adminType.addEventListener('change', toggleAreaField);

            toggleButton.addEventListener('click', function () {
                const allChecked = permissionCheckboxes.every(
                    checkbox => checkbox.checked
                );

                permissionCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = !allChecked;
                });

                updateToggleButtonText();
            });

            permissionCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener(
                    'change',
                    updateToggleButtonText
                );
            });

            toggleAreaField();
            updateToggleButtonText();
        });
    </script>
@endpush