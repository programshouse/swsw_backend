@php
    $address = $kitchen->defaultAddress ?? null;

    $selectedGovernmentId = old(
        'government_id',
        $kitchen->government_id
            ?? $address?->government_id
            ?? ''
    );

    $selectedAreaId = old(
        'area_id',
        $kitchen->area_id
            ?? $address?->area_id
            ?? ''
    );
@endphp

@if ($errors->any())
    <div class="validation-alert">
        <strong>يرجى مراجعة البيانات التالية:</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-section">
    <div class="section-heading">
        <h2>بيانات المطبخ</h2>

        <p>
            سيتم إنشاء الحساب بدور مطبخ وربطه تلقائيًا
            بحساب السيلز الحالي.
        </p>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label for="name">
                اسم المطبخ
                <span>*</span>
            </label>

            <input
                type="text"
                id="name"
                name="name"
                value="{{ old('name', $kitchen->name ?? '') }}"
                placeholder="اكتب اسم المطبخ"
                required
            >
        </div>

        <div class="form-group">
            <label for="phone">
                رقم الهاتف
                <span>*</span>
            </label>

            <input
                type="text"
                id="phone"
                name="phone"
                value="{{ old('phone', $kitchen->phone ?? '') }}"
                placeholder="01xxxxxxxxx"
                required
            >
        </div>

        <div class="form-group">
            <label for="email">
                البريد الإلكتروني
            </label>

            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $kitchen->email ?? '') }}"
                placeholder="example@email.com"
            >
        </div>

        <div class="form-group">
            <label for="is_company">
                نوع المطبخ
            </label>

            <select
                id="is_company"
                name="is_company"
            >
                <option
                    value="0"
                    @selected(
                        (string) old(
                            'is_company',
                            (int) ($kitchen->is_company ?? 0)
                        ) === '0'
                    )
                >
                    مطبخ فردي
                </option>

                <option
                    value="1"
                    @selected(
                        (string) old(
                            'is_company',
                            (int) ($kitchen->is_company ?? 0)
                        ) === '1'
                    )
                >
                    شركة
                </option>
            </select>
        </div>

        <div class="form-group">
            <label for="password">
                كلمة المرور

                @if (!isset($kitchen))
                    <span>*</span>
                @endif
            </label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="{{ isset($kitchen)
                        ? 'اتركها فارغة للاحتفاظ بكلمة المرور الحالية'
                        : '8 أحرف على الأقل' }}"
                    @required(!isset($kitchen))
                >

                <button
                    type="button"
                    onclick="togglePassword('password', this)"
                    class="password-toggle"
                >
                    إظهار
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                تأكيد كلمة المرور

                @if (!isset($kitchen))
                    <span>*</span>
                @endif
            </label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="أعد كتابة كلمة المرور"
                    @required(!isset($kitchen))
                >

                <button
                    type="button"
                    onclick="togglePassword(
                        'password_confirmation',
                        this
                    )"
                    class="password-toggle"
                >
                    إظهار
                </button>
            </div>
        </div>
    </div>
</div>

<div class="form-section">
    <div class="section-heading">
        <h2>العنوان والموقع</h2>

        <p>
            اختر المحافظة والمنطقة، ثم أدخل موقع المطبخ.
        </p>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label for="government_id">
                المحافظة
                <span>*</span>
            </label>

            <select
                name="government_id"
                id="government_id"
                data-areas-url="{{ route(
                    'admin.sales.governments.areas',
                    '__ID__'
                ) }}"
                required
            >
                <option value="">
                    اختر المحافظة
                </option>

                @foreach ($governments as $government)
                    <option
                        value="{{ $government->id }}"
                        @selected(
                            $selectedGovernmentId == $government->id
                        )
                    >
                        {{ $government->name_ar }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="area_id">
                المنطقة
                <span>*</span>
            </label>

            <select
                name="area_id"
                id="area_id"
                required
            >
                <option value="">
                    اختر المنطقة
                </option>

                @foreach ($areas ?? [] as $area)
                    <option
                        value="{{ $area->id }}"
                        @selected(
                            $selectedAreaId == $area->id
                        )
                    >
                        {{ $area->name_ar }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group form-group-full">
            <label for="full_address">
                العنوان التفصيلي
            </label>

            <input
                type="text"
                id="full_address"
                name="full_address"
                value="{{ old(
                    'full_address',
                    $address?->full_address ?? ''
                ) }}"
                placeholder="مثال: شارع التحرير، بجوار..."
            >

            <small>
                إذا تُرك فارغًا سيتم تسجيل اسم المحافظة والمنطقة.
            </small>
        </div>

        <div class="form-group">
            <label for="lat">
                خط العرض
                <span>*</span>
            </label>

            <input
                type="number"
                step="any"
                id="lat"
                name="lat"
                value="{{ old('lat', $address?->lat ?? '') }}"
                placeholder="مثال: 30.044420"
                required
            >
        </div>

        <div class="form-group">
            <label for="lng">
                خط الطول
                <span>*</span>
            </label>

            <input
                type="number"
                step="any"
                id="lng"
                name="lng"
                value="{{ old('lng', $address?->lng ?? '') }}"
                placeholder="مثال: 31.235712"
                required
            >
        </div>

        <div class="form-group form-group-full">
            <label for="location_link">
                رابط الموقع
            </label>

            <input
                type="text"
                id="location_link"
                name="location_link"
                value="{{ old(
                    'location_link',
                    $address?->location_link ?? ''
                ) }}"
                placeholder="رابط الموقع من Google Maps"
            >
        </div>

        <div class="form-group form-group-full">
            <button
                type="button"
                class="location-button"
                onclick="getCurrentLocation()"
            >
                <i class="fas fa-map-marker-alt"></i>
                استخدام موقعي الحالي
            </button>

            <span
                id="locationStatus"
                class="location-status"
            ></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = 'إخفاء';
        } else {
            input.type = 'password';
            button.textContent = 'إظهار';
        }
    }

    function getCurrentLocation() {
        const statusElement =
            document.getElementById('locationStatus');

        if (!navigator.geolocation) {
            statusElement.textContent =
                'المتصفح لا يدعم تحديد الموقع.';

            return;
        }

        statusElement.textContent =
            'جاري تحديد الموقع...';

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const latitude =
                    position.coords.latitude;

                const longitude =
                    position.coords.longitude;

                document.getElementById('lat').value =
                    latitude;

                document.getElementById('lng').value =
                    longitude;

                document.getElementById(
                    'location_link'
                ).value =
                    'https://www.google.com/maps?q='
                    + latitude
                    + ','
                    + longitude;

                statusElement.textContent =
                    'تم تحديد الموقع بنجاح.';
            },

            function () {
                statusElement.textContent =
                    'تعذر تحديد الموقع. تأكد من السماح بالوصول للموقع.';
            },

            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    document.addEventListener(
        'DOMContentLoaded',
        function () {
            const governmentSelect =
                document.getElementById('government_id');

            const areaSelect =
                document.getElementById('area_id');

            if (!governmentSelect || !areaSelect) {
                return;
            }

            governmentSelect.addEventListener(
                'change',
                async function () {
                    const governmentId = this.value;

                    areaSelect.disabled = true;

                    areaSelect.innerHTML =
                        '<option value="">جاري تحميل المناطق...</option>';

                    if (!governmentId) {
                        areaSelect.innerHTML =
                            '<option value="">اختر المنطقة</option>';

                        areaSelect.disabled = false;

                        return;
                    }

                    const routeTemplate =
                        this.dataset.areasUrl;

                    const url = routeTemplate.replace(
                        '__ID__',
                        governmentId
                    );

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });

                        if (!response.ok) {
                            throw new Error(
                                'Failed to load areas'
                            );
                        }

                        const result = await response.json();

                        areaSelect.innerHTML =
                            '<option value="">اختر المنطقة</option>';

                        result.data.forEach(function (area) {
                            const option =
                                document.createElement('option');

                            option.value = area.id;

                            option.textContent =
                                area.name_ar;

                            areaSelect.appendChild(option);
                        });
                    } catch (error) {
                        areaSelect.innerHTML =
                            '<option value="">تعذر تحميل المناطق</option>';
                    } finally {
                        areaSelect.disabled = false;
                    }
                }
            );
        }
    );
</script>
@endpush