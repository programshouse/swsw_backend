@extends('admin.layouts.app')

@section('title', 'إعدادات تسعير الطلبات')

@section('content')
    <div dir="rtl">

        {{-- Page heading --}}
        <div class="page-head">
            <div>
                <h1 class="page-title" style="margin-bottom: 6px;">
                    إعدادات تسعير الطلبات
                </h1>

                <p style="margin: 0; color: #6b7280;">
                    تحكم في سعر التوصيل والقيمة المضافة ورسوم العميل والمطبخ.
                </p>
            </div>
        </div>

        {{-- Success --}}
        @if(session('success'))
            <div class="success-alert">
                {{ session('success') }}
            </div>
        @endif

        {{-- Errors --}}
        @if($errors->any())
            <div
                style="
                    background: #fee2e2;
                    color: #991b1b;
                    padding: 14px 18px;
                    border-radius: 12px;
                    margin-bottom: 18px;
                    font-weight: 700;
                "
            >
                <div style="margin-bottom: 8px;">
                    يوجد خطأ في البيانات المدخلة:
                </div>

                <ul style="margin: 0; padding-right: 20px;">
                    @foreach($errors->all() as $error)
                        <li style="margin-bottom: 4px;">
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- General settings --}}
        <div class="section-card">
            <div class="section-header">
                <h3>إعدادات التوصيل والضريبة</h3>

                <p style="margin: 8px 0 0; color: #6b7280;">
                    يتم حفظ هذه القيم داخل كل طلب جديد وقت إنشائه.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.order-pricing.settings.update') }}"
            >
                @csrf
                @method('PUT')

                <div
                    style="
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 18px;
                    "
                    class="pricing-settings-grid"
                >
                    <div class="form-group">
                        <label
                            for="delivery_meter_price"
                            class="form-label"
                        >
                            سعر التوصيل لكل كيلومتر
                        </label>

                        <input
                            type="number"
                            id="delivery_meter_price"
                            name="delivery_meter_price"
                            class="form-input"
                            min="0"
                            step="0.01"
                            value="{{ old(
                                'delivery_meter_price',
                                $settings?->delivery_meter_price ?? 0
                            ) }}"
                            required
                        >

                        <small style="color: #6b7280;">
                            مثال: المسافة 5 كم وسعر الكيلومتر 10 جنيه،
                            يكون سعر التوصيل 50 جنيه.
                        </small>
                    </div>

                    <div class="form-group">
                        <label
                            for="vat_percentage"
                            class="form-label"
                        >
                            نسبة القيمة المضافة
                        </label>

                        <input
                            type="number"
                            id="vat_percentage"
                            name="vat_percentage"
                            class="form-input"
                            min="0"
                            max="100"
                            step="0.01"
                            value="{{ old(
                                'vat_percentage',
                                $settings?->vat_percentage ?? 0
                            ) }}"
                            required
                        >

                        <small style="color: #6b7280;">
                            يتم حساب القيمة المضافة على قيمة الوجبات.
                        </small>
                    </div>
                </div>

                <div
                    style="
                        display: flex;
                        justify-content: flex-start;
                        margin-top: 20px;
                    "
                >
                    <button type="submit" class="save-btn">
                        حفظ الإعدادات
                    </button>
                </div>
            </form>
        </div>

      

        {{-- Add pricing rule --}}
        <div class="section-card">
            <div class="section-header">
                <h3>إضافة شريحة تسعير</h3>

                <p style="margin: 8px 0 0; color: #6b7280;">
                    يتم تحديد رسوم العميل والمطبخ حسب قيمة الوجبات في الطلب.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.order-pricing.rules.store') }}"
            >
                @csrf

                <div
                    style="
                        display: grid;
                        grid-template-columns: repeat(4, minmax(0, 1fr));
                        gap: 18px;
                    "
                    class="pricing-rules-grid"
                >
                    <div class="form-group">
                        <label
                            for="min_order_amount"
                            class="form-label"
                        >
                            يبدأ سعر الطلب من
                        </label>

                        <input
                            type="number"
                            id="min_order_amount"
                            name="min_order_amount"
                            class="form-input"
                            min="0"
                            step="0.01"
                            value="{{ old('min_order_amount') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label
                            for="max_order_amount"
                            class="form-label"
                        >
                            حتى سعر
                        </label>

                        <input
                            type="number"
                            id="max_order_amount"
                            name="max_order_amount"
                            class="form-input"
                            min="0"
                            step="0.01"
                            value="{{ old('max_order_amount') }}"
                        >

                        <small style="color: #6b7280;">
                            اتركيه فارغًا إذا كانت الشريحة بدون حد أقصى.
                        </small>
                    </div>

                    <div class="form-group">
                        <label
                            for="client_service_fee"
                            class="form-label"
                        >
                            رسوم خدمة العميل
                        </label>

                        <input
                            type="number"
                            id="client_service_fee"
                            name="client_service_fee"
                            class="form-input"
                            min="0"
                            step="0.01"
                            value="{{ old('client_service_fee', 0) }}"
                            required
                        >

                        <small style="color: #6b7280;">
                            مبلغ إضافي يدفعه العميل.
                        </small>
                    </div>

                    <div class="form-group">
                        <label
                            for="kitchen_service_fee"
                            class="form-label"
                        >
                            رسوم خدمة المطبخ
                        </label>

                        <input
                            type="number"
                            id="kitchen_service_fee"
                            name="kitchen_service_fee"
                            class="form-input"
                            min="0"
                            step="0.01"
                            value="{{ old('kitchen_service_fee', 0) }}"
                            required
                        >

                        <small style="color: #6b7280;">
                            يتم خصمها من قيمة الوجبات المستحقة للمطبخ.
                        </small>
                    </div>
                </div>

                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        margin-top: 5px;
                    "
                >
                    <input
                        type="checkbox"
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        style="width: 18px; height: 18px;"
                    >

                    <label
                        for="is_active"
                        style="
                            font-weight: 700;
                            color: #374151;
                            cursor: pointer;
                        "
                    >
                        الشريحة مفعلة
                    </label>
                </div>

                <div
                    style="
                        display: flex;
                        justify-content: flex-start;
                        margin-top: 20px;
                    "
                >
                    <button type="submit" class="add-btn">
                        إضافة الشريحة
                    </button>
                </div>
            </form>
        </div>

        {{-- Pricing rules table --}}
        <div class="table-card">
            <div class="table-header">
                <div>
                    <div class="table-title">
                        شرائح التسعير
                    </div>

                    <div
                        style="
                            color: #6b7280;
                            margin-top: 6px;
                            font-size: 14px;
                        "
                    >
                        عدد الشرائح: {{ $rules->count() }}
                    </div>
                </div>

                <button
                    type="button"
                    class="refresh-btn"
                    onclick="window.location.reload()"
                >
                    تحديث
                </button>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>سعر الطلب من</th>
                            <th>سعر الطلب حتى</th>
                            <th>رسوم العميل</th>
                            <th>رسوم المطبخ</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($rules as $rule)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ number_format(
                                        (float) $rule->min_order_amount,
                                        2
                                    ) }}
                                    جنيه
                                </td>

                                <td>
                                    @if($rule->max_order_amount !== null)
                                        {{ number_format(
                                            (float) $rule->max_order_amount,
                                            2
                                        ) }}
                                        جنيه
                                    @else
                                        <span
                                            style="
                                                background: #f3f4f6;
                                                color: #374151;
                                                padding: 6px 10px;
                                                border-radius: 999px;
                                                font-size: 13px;
                                                font-weight: 700;
                                            "
                                        >
                                            بدون حد أقصى
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span
                                        style="
                                            display: inline-block;
                                            background: #dbeafe;
                                            color: #1d4ed8;
                                            padding: 6px 12px;
                                            border-radius: 999px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ number_format(
                                            (float) $rule->client_service_fee,
                                            2
                                        ) }}
                                        جنيه
                                    </span>
                                </td>

                                <td>
                                    <span
                                        style="
                                            display: inline-block;
                                            background: #f3e8ff;
                                            color: #7e22ce;
                                            padding: 6px 12px;
                                            border-radius: 999px;
                                            font-weight: 700;
                                        "
                                    >
                                        {{ number_format(
                                            (float) $rule->kitchen_service_fee,
                                            2
                                        ) }}
                                        جنيه
                                    </span>
                                </td>

                                <td>
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.order-pricing.rules.toggle',
                                            $rule
                                        ) }}"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <button
                                            type="submit"
                                            style="
                                                border: 0;
                                                cursor: pointer;
                                                padding: 6px 12px;
                                                border-radius: 999px;
                                                font-weight: 700;
                                                background:
                                                    {{ $rule->is_active
                                                        ? '#dcfce7'
                                                        : '#fee2e2' }};
                                                color:
                                                    {{ $rule->is_active
                                                        ? '#166534'
                                                        : '#991b1b' }};
                                            "
                                        >
                                            {{ $rule->is_active
                                                ? 'مفعلة'
                                                : 'متوقفة' }}
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <div
                                        style="
                                            display: flex;
                                            gap: 8px;
                                            align-items: center;
                                        "
                                    >
                                        <button
                                            type="button"
                                            class="view-btn"
                                            style="border: 0; cursor: pointer;"
                                            onclick="openEditModal(
                                                {{ $rule->id }},
                                                @js($rule->min_order_amount),
                                                @js($rule->max_order_amount),
                                                @js($rule->client_service_fee),
                                                @js($rule->kitchen_service_fee),
                                                {{ $rule->is_active ? 1 : 0 }}
                                            )"
                                        >
                                            تعديل
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.order-pricing.rules.destroy',
                                                $rule
                                            ) }}"
                                            onsubmit="
                                                return confirm(
                                                    'هل أنت متأكد من حذف هذه الشريحة؟'
                                                );
                                            "
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="delete-btn"
                                            >
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty">
                                    لا توجد شرائح تسعير حتى الآن.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Edit modal --}}
    <div id="editRuleModal" class="form-modal">
        <div class="modal-card">
            <div
                style="
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    gap: 15px;
                "
            >
                <div>
                    <h2 style="margin-bottom: 5px;">
                        تعديل شريحة التسعير
                    </h2>

                    <p style="margin: 0 0 20px; color: #6b7280;">
                        عدلي حدود الشريحة ورسوم العميل والمطبخ.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="closeEditModal()"
                    style="
                        width: 38px;
                        height: 38px;
                        border-radius: 50%;
                        border: 0;
                        cursor: pointer;
                        background: #e5e7eb;
                        color: #111827;
                        font-size: 22px;
                    "
                >
                    ×
                </button>
            </div>

            <form id="editRuleForm" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label
                        for="edit_min_order_amount"
                        class="form-label"
                    >
                        يبدأ سعر الطلب من
                    </label>

                    <input
                        type="number"
                        id="edit_min_order_amount"
                        name="min_order_amount"
                        class="form-input"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label
                        for="edit_max_order_amount"
                        class="form-label"
                    >
                        حتى سعر
                    </label>

                    <input
                        type="number"
                        id="edit_max_order_amount"
                        name="max_order_amount"
                        class="form-input"
                        min="0"
                        step="0.01"
                    >
                </div>

                <div class="form-group">
                    <label
                        for="edit_client_service_fee"
                        class="form-label"
                    >
                        رسوم خدمة العميل
                    </label>

                    <input
                        type="number"
                        id="edit_client_service_fee"
                        name="client_service_fee"
                        class="form-input"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label
                        for="edit_kitchen_service_fee"
                        class="form-label"
                    >
                        رسوم خدمة المطبخ
                    </label>

                    <input
                        type="number"
                        id="edit_kitchen_service_fee"
                        name="kitchen_service_fee"
                        class="form-input"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    "
                >
                    <input
                        type="checkbox"
                        id="edit_is_active"
                        name="is_active"
                        value="1"
                        style="width: 18px; height: 18px;"
                    >

                    <label
                        for="edit_is_active"
                        style="
                            font-weight: 700;
                            color: #374151;
                            cursor: pointer;
                        "
                    >
                        الشريحة مفعلة
                    </label>
                </div>

                <div class="modal-actions">
                    <button
                        type="submit"
                        class="save-btn"
                    >
                        حفظ التعديلات
                    </button>

                    <button
                        type="button"
                        class="cancel-btn"
                        onclick="closeEditModal()"
                    >
                        إلغاء
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openEditModal(
            id,
            minAmount,
            maxAmount,
            clientFee,
            kitchenFee,
            isActive
        ) {
            const modal = document.getElementById('editRuleModal');
            const form = document.getElementById('editRuleForm');

            form.action =
                "{{ url('admin/order-pricing/rules') }}/" + id;

            document.getElementById(
                'edit_min_order_amount'
            ).value = minAmount ?? '';

            document.getElementById(
                'edit_max_order_amount'
            ).value = maxAmount ?? '';

            document.getElementById(
                'edit_client_service_fee'
            ).value = clientFee ?? 0;

            document.getElementById(
                'edit_kitchen_service_fee'
            ).value = kitchenFee ?? 0;

            document.getElementById(
                'edit_is_active'
            ).checked = Number(isActive) === 1;

            modal.classList.add('active');
        }

        function closeEditModal() {
            document
                .getElementById('editRuleModal')
                .classList
                .remove('active');
        }

        document
            .getElementById('editRuleModal')
            .addEventListener('click', function (event) {
                if (event.target === this) {
                    closeEditModal();
                }
            });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        @media(max-width: 991px) {
            .pricing-settings-grid,
            .pricing-rules-grid,
            .pricing-example-grid {
                grid-template-columns:
                    repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media(max-width: 600px) {
            .pricing-settings-grid,
            .pricing-rules-grid,
            .pricing-example-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
@endpush