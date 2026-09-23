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
                    تحكم في الحد الأدنى للتوصيل وشرائح المسافة والقيمة المضافة ورسوم العميل والمطبخ.
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

        {{-- ================= DELIVERY GROUP ================= --}}
        <section class="pricing-group pricing-group--delivery">

            <div class="pricing-group-header">
                <span class="pricing-group-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 7h11v8H3z" />
                        <path d="M14 10h4l3 3v2h-7z" />
                        <circle cx="7" cy="18" r="1.7" />
                        <circle cx="17.5" cy="18" r="1.7" />
                    </svg>
                </span>

                <div>
                    <h2 class="pricing-group-title">التوصيل</h2>
                    <p class="pricing-group-desc">
                        الحد الأدنى لسعر التوصيل، وسعر الكيلومتر حسب شرائح المسافة.
                    </p>
                </div>
            </div>

            <div class="pricing-group-body">

                {{-- Delivery settings --}}
                <div class="section-card">
                    <div class="section-header">
                        <h3>الحد الأدنى للتوصيل</h3>

                        <p style="margin: 8px 0 0; color: #6b7280;">
                            يُطبّق تلقائيًا لو مسافة التوصيل أقل من كيلومتر واحد.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.order-pricing.settings.update') }}"
                    >
                        @csrf
                        @method('PUT')

                        <div class="form-group" style="max-width: 320px;">
                            <label for="min_delivery_fee" class="form-label">
                                الحد الأدنى لسعر التوصيل
                            </label>

                            <input
                                type="number"
                                id="min_delivery_fee"
                                name="min_delivery_fee"
                                class="form-input"
                                min="0"
                                step="0.01"
                                value="{{ old(
                                    'min_delivery_fee',
                                    $settings?->min_delivery_fee ?? 0
                                ) }}"
                                required
                            >
                        </div>

                        <div style="display: flex; justify-content: flex-start; margin-top: 20px;">
                            <button type="submit" class="save-btn">
                                حفظ الإعدادات
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Add distance pricing rule --}}
                <div class="section-card">
                    <div class="section-header">
                        <h3>إضافة شريحة مسافة</h3>

                        <p style="margin: 8px 0 0; color: #6b7280;">
                            حددي سعر الكيلومتر حسب نطاق المسافة.
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('admin.order-pricing.distance-rules.store') }}"
                    >
                        @csrf

                        <div
                            style="
                                display: grid;
                                grid-template-columns: repeat(3, minmax(0, 1fr));
                                gap: 18px;
                            "
                            class="pricing-rules-grid"
                        >
                            <div class="form-group">
                                <label for="min_distance" class="form-label">
                                    المسافة من (كم)
                                </label>

                                <input
                                    type="number"
                                    id="min_distance"
                                    name="min_distance"
                                    class="form-input"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('min_distance') }}"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label for="max_distance" class="form-label">
                                    المسافة حتى (كم)
                                </label>

                                <input
                                    type="number"
                                    id="max_distance"
                                    name="max_distance"
                                    class="form-input"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('max_distance') }}"
                                >

                                <small style="color: #6b7280;">
                                    اتركيه فارغًا لو بدون حد أقصى.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="price_per_km" class="form-label">
                                    سعر الكيلومتر
                                </label>

                                <input
                                    type="number"
                                    id="price_per_km"
                                    name="price_per_km"
                                    class="form-input"
                                    min="0"
                                    step="0.01"
                                    value="{{ old('price_per_km') }}"
                                    required
                                >
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-start; margin-top: 20px;">
                            <button type="submit" class="add-btn">
                                إضافة شريحة المسافة
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Distance rules table --}}
                <div class="table-card">
                    <div class="table-header">
                        <div class="table-title">
                            شرائح مسافة التوصيل
                        </div>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>من (كم)</th>
                                    <th>حتى (كم)</th>
                                    <th>سعر الكيلومتر</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($distanceRules as $distanceRule)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ number_format((float) $distanceRule->min_distance, 2) }} كم
                                        </td>

                                        <td>
                                            @if($distanceRule->max_distance !== null)
                                                {{ number_format((float) $distanceRule->max_distance, 2) }} كم
                                            @else
                                                <span style="background:#f3f4f6;color:#374151;padding:6px 10px;border-radius:999px;font-size:13px;font-weight:700;">
                                                    بدون حد أقصى
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <span style="display:inline-block;background:#ccfbf1;color:#0f766e;padding:6px 12px;border-radius:999px;font-weight:700;">
                                                {{ number_format((float) $distanceRule->price_per_km, 2) }} جنيه
                                            </span>
                                        </td>

                                        <td>
                                            <div style="display:flex;gap:8px;align-items:center;">
                                                <button
                                                    type="button"
                                                    class="view-btn"
                                                    style="border:0;cursor:pointer;"
                                                    onclick="openEditDistanceModal(
                                                        {{ $distanceRule->id }},
                                                        @js($distanceRule->min_distance),
                                                        @js($distanceRule->max_distance),
                                                        @js($distanceRule->price_per_km)
                                                    )"
                                                >
                                                    تعديل
                                                </button>

                                                <form
                                                    method="POST"
                                                    action="{{ route('admin.order-pricing.distance-rules.destroy', $distanceRule) }}"
                                                    onsubmit="return confirm('هل أنت متأكدة من حذف هذه الشريحة؟');"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="delete-btn">
                                                        حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="empty">
                                            لا توجد شرائح مسافة حتى الآن.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>


        {{-- ================= TAXES GROUP ================= --}}
        <section class="pricing-group pricing-group--tax">

            <div class="pricing-group-header">
                <span class="pricing-group-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M9 15l6-6M9.5 10a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1zM14.5 15a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
                    </svg>
                </span>

                <div>
                    <h2 class="pricing-group-title">الضرائب</h2>
                    <p class="pricing-group-desc">
                        ضريبة القيمة المضافة على العميل، ونسبة الخصم الضريبي من المطابخ المسجلة ضريبيًا.
                    </p>
                </div>
            </div>

            <div class="pricing-group-body">

                <div class="section-card">
                    <div class="section-header">
                        <h3>إعدادات الضريبة</h3>

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

                        <input type="hidden" name="min_delivery_fee" value="{{ $settings?->min_delivery_fee ?? 0 }}">

                        <div
                            style="
                                display: grid;
                                grid-template-columns: repeat(2, minmax(0, 1fr));
                                gap: 18px;
                            "
                            class="pricing-settings-grid"
                        >
                            <div class="form-group">
                                <label for="client_vat_percentage" class="form-label">
                                    نسبة ضريبة القيمة المضافة على العميل
                                </label>

                                <input
                                    type="number"
                                    id="client_vat_percentage"
                                    name="client_vat_percentage"
                                    class="form-input"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    value="{{ old(
                                        'client_vat_percentage',
                                        $settings?->client_vat_percentage ?? 0
                                    ) }}"
                                    required
                                >

                                <small style="color: #6b7280;">
                                    تُحسب على قيمة الوجبات في حالة تفعيل الضريبة.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="kitchen_tax_deduction_percentage" class="form-label">
                                    نسبة الخصم الضريبي من المطبخ
                                </label>

                                <input
                                    type="number"
                                    id="kitchen_tax_deduction_percentage"
                                    name="kitchen_tax_deduction_percentage"
                                    class="form-input"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    value="{{ old(
                                        'kitchen_tax_deduction_percentage',
                                        $settings?->kitchen_tax_deduction_percentage ?? 0
                                    ) }}"
                                    required
                                >

                                <small style="color: #6b7280;">
                                    تُخصم فقط من المطابخ اللي ليها سجل ضريبي.
                                </small>
                            </div>
                        </div>

                        <div
                            style="
                                display: flex;
                                align-items: center;
                                gap: 10px;
                                margin-top: 18px;
                            "
                        >
                            <input
                                type="checkbox"
                                id="client_vat_enabled"
                                name="client_vat_enabled"
                                value="1"
                                {{ old('client_vat_enabled', $settings?->client_vat_enabled ?? true) ? 'checked' : '' }}
                                style="width: 18px; height: 18px;"
                            >

                            <label
                                for="client_vat_enabled"
                                style="
                                    font-weight: 700;
                                    color: #374151;
                                    cursor: pointer;
                                "
                            >
                                تفعيل ضريبة القيمة المضافة على العميل
                            </label>
                        </div>

                        <div style="display: flex; justify-content: flex-start; margin-top: 20px;">
                            <button type="submit" class="save-btn">
                                حفظ إعدادات الضريبة
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </section>

        {{-- ================= ORDER-VALUE FEES GROUP ================= --}}
        <section class="pricing-group pricing-group--fees">

            <div class="pricing-group-header">
                <span class="pricing-group-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 3h9l3 3v15H6z" />
                        <path d="M9 8h6M9 12h6M9 16h4" />
                    </svg>
                </span>

                <div>
                    <h2 class="pricing-group-title">رسوم الخدمة حسب قيمة الطلب</h2>
                    <p class="pricing-group-desc">
                        رسوم العميل والمطبخ تختلف باختلاف إجمالي قيمة الوجبات في الطلب.
                    </p>
                </div>
            </div>

            <div class="pricing-group-body">

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
        </section>

    </div>

    {{-- Edit distance rule modal --}}
    <div id="editDistanceRuleModal" class="form-modal">
        <div class="modal-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:15px;">
                <div>
                    <h2 style="margin-bottom:5px;">
                        تعديل شريحة المسافة
                    </h2>

                    <p style="margin: 0 0 20px; color: #6b7280;">
                        عدلي حدود المسافة وسعر الكيلومتر.
                    </p>
                </div>

                <button
                    type="button"
                    onclick="closeEditDistanceModal()"
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

            <form id="editDistanceRuleForm" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="edit_min_distance" class="form-label">
                        المسافة من (كم)
                    </label>

                    <input
                        type="number"
                        id="edit_min_distance"
                        name="min_distance"
                        class="form-input"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="edit_max_distance" class="form-label">
                        المسافة حتى (كم)
                    </label>

                    <input
                        type="number"
                        id="edit_max_distance"
                        name="max_distance"
                        class="form-input"
                        min="0"
                        step="0.01"
                    >
                </div>

                <div class="form-group">
                    <label for="edit_price_per_km" class="form-label">
                        سعر الكيلومتر
                    </label>

                    <input
                        type="number"
                        id="edit_price_per_km"
                        name="price_per_km"
                        class="form-input"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="modal-actions">
                    <button type="submit" class="save-btn">
                        حفظ التعديلات
                    </button>

                    <button type="button" class="cancel-btn" onclick="closeEditDistanceModal()">
                        إلغاء
                    </button>
                </div>
            </form>
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

        function openEditDistanceModal(id, minDistance, maxDistance, pricePerKm) {
            const modal = document.getElementById('editDistanceRuleModal');
            const form = document.getElementById('editDistanceRuleForm');

            form.action = "{{ url('admin/order-pricing/distance-rules') }}/" + id;

            document.getElementById('edit_min_distance').value = minDistance ?? '';
            document.getElementById('edit_max_distance').value = maxDistance ?? '';
            document.getElementById('edit_price_per_km').value = pricePerKm ?? 0;

            modal.classList.add('active');
        }

        function closeEditDistanceModal() {
            document.getElementById('editDistanceRuleModal').classList.remove('active');
        }

        document
            .getElementById('editDistanceRuleModal')
            .addEventListener('click', function (event) {
                if (event.target === this) {
                    closeEditDistanceModal();
                }
            });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeEditModal();
                closeEditDistanceModal();
            }
        });
    </script>
@endpush

@push('styles')
    <style>
        .pricing-group {
            border-radius: 18px;
            padding: 26px;
            margin-bottom: 28px;
            border: 1px solid;
        }

        .pricing-group--delivery {
            background: linear-gradient(180deg, #f0fdfa 0%, #ffffff 220px);
            border-color: #99f6e4;
        }

        .pricing-group--fees {
            background: linear-gradient(180deg, #fffbeb 0%, #ffffff 220px);
            border-color: #fde68a;
        }

        .pricing-group--tax {
            background: linear-gradient(180deg, #f5f3ff 0%, #ffffff 220px);
            border-color: #ddd6fe;
        }

        .pricing-group-header {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 22px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(17, 24, 39, 0.08);
        }

        .pricing-group-icon {
            width: 44px;
            height: 44px;
            min-width: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pricing-group--delivery .pricing-group-icon {
            background: #0f766e;
            color: #ffffff;
        }

        .pricing-group--fees .pricing-group-icon {
            background: #b45309;
            color: #ffffff;
        }

        .pricing-group--tax .pricing-group-icon {
            background: #6d28d9;
            color: #ffffff;
        }


        .pricing-group-title {
            margin: 0 0 4px;
            font-size: 19px;
            font-weight: 800;
            color: #111827;
        }

        .pricing-group-desc {
            margin: 0;
            color: #6b7280;
            font-size: 14px;
        }

        .pricing-group-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .pricing-group-body > .section-card,
        .pricing-group-body > .table-card {
            margin: 0;
        }

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

            .pricing-group {
                padding: 18px;
            }
        }
    </style>
@endpush
