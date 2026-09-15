@extends('admin.layouts.app')

@section('title', 'تفاصيل الأدمن')

@section('content')
    <div class="page-title">
        تفاصيل الأدمن
    </div>

    <div class="details-card">

        <div class="details-header">
            <div>
                <h2>{{ $admin->name }}</h2>
                <p>بيانات حساب الأدمن والصلاحيات</p>
            </div>

            <a
                href="{{ route('admin.admins.edit', $admin->id) }}"
                class="edit-btn"
            >
                تعديل
            </a>
        </div>

        <div class="details-grid">

            <div class="item">
                <div class="label">الاسم</div>
                <div class="value">
                    {{ $admin->name ?? '-' }}
                </div>
            </div>

            <div class="item">
                <div class="label">البريد الإلكتروني</div>
                <div class="value">
                    {{ $admin->email ?? '-' }}
                </div>
            </div>

            <div class="item">
                <div class="label">رقم الهاتف</div>
                <div class="value">
                    {{ $admin->phone ?? '-' }}
                </div>
            </div>

            <div class="item">
                <div class="label">نوع الأدمن</div>

                <div class="value">
                    @switch($admin->admin_type)
                        @case('super_admin')
                            سوبر أدمن
                            @break

                        @case('area_admin')
                            أدمن منطقة
                            @break

                        @case('custom_admin')
                            أدمن مخصص
                            @break

                        @default
                            {{ $admin->admin_type ?? '-' }}
                    @endswitch
                </div>
            </div>

            <div class="item">
                <div class="label">الحالة</div>

                <div class="value">
                    @if($admin->is_admin_active)
                        <span class="status-badge active">
                            نشط
                        </span>
                    @else
                        <span class="status-badge inactive">
                            غير نشط
                        </span>
                    @endif
                </div>
            </div>

            <div class="item">
                <div class="label">المنطقة</div>

                <div class="value">
                    @if($admin->adminArea)
                        {{ $admin->adminArea->government?->name_ar
                            ?? $admin->adminArea->government?->name
                            ?? '-' }}

                        -

                        {{ $admin->adminArea->name_ar
                            ?? $admin->adminArea->name
                            ?? '-' }}
                    @else
                        -
                    @endif
                </div>
            </div>

            <div class="item">
                <div class="label">تاريخ الإنشاء</div>

                <div class="value">
                    {{ $admin->created_at?->format('Y-m-d h:i A') ?? '-' }}
                </div>
            </div>

            <div class="item">
                <div class="label">آخر تحديث</div>

                <div class="value">
                    {{ $admin->updated_at?->format('Y-m-d h:i A') ?? '-' }}
                </div>
            </div>

        </div>
    </div>

    <div class="permissions-card">

        <div class="section-title">
            الصلاحيات
        </div>

        @php
            $adminPermissions = is_array($admin->admin_permissions)
                ? $admin->admin_permissions
                : [];
        @endphp

        @if($admin->admin_type === 'super_admin')

            <div class="super-admin-message">
                هذا الحساب يمتلك جميع صلاحيات النظام.
            </div>

        @elseif(count($adminPermissions))

            <div class="permissions-grid">
                @foreach($adminPermissions as $permission)
                    <div class="permission-item">
                        <span class="permission-icon">✓</span>

                        <span>
                            {{ $permissionLabels[$permission] ?? $permission }}
                        </span>
                    </div>
                @endforeach
            </div>

        @else

            <div class="empty-state">
                لا توجد صلاحيات مخصصة لهذا الأدمن.
            </div>

        @endif
    </div>

    <div class="actions-row">
        <a
            href="{{ route('admin.admins.index') }}"
            class="back-btn"
        >
            العودة إلى القائمة
        </a>
    </div>
@endsection

@push('styles')
    <style>
        .page-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #1f2937;
        }

        .details-card,
        .permissions-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        }

        .details-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .details-header h2 {
            margin: 0 0 6px;
            font-size: 22px;
            color: #111827;
        }

        .details-header p {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
            background: #f9fafb;
        }

        .label {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }

        .value {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            word-break: break-word;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 74px;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .status-badge.active {
            background: #dcfce7;
            color: #166534;
        }

        .status-badge.inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 18px;
        }

        .permissions-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .permission-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #f3f4f6;
            color: #374151;
            font-size: 14px;
            font-weight: 600;
        }

        .permission-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: #dcfce7;
            color: #15803d;
            font-size: 13px;
            flex-shrink: 0;
        }

        .super-admin-message {
            padding: 16px;
            border-radius: 12px;
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 28px;
            border: 1px dashed #d1d5db;
            border-radius: 12px;
            color: #6b7280;
        }

        .actions-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .edit-btn,
        .back-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 700;
            font-size: 14px;
        }

        .edit-btn {
            background: #2563eb;
            color: #fff;
        }

        .back-btn {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        @media (max-width: 900px) {
            .permissions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .details-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .details-grid,
            .permissions-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush