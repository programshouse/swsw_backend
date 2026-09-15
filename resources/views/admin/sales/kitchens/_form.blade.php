@php
    $kitchen = $kitchen ?? null;

    $address = $kitchen?->defaultAddress ?? null;

    $selectedGovernmentId = old(
        'government_id',
        $kitchen?->government_id ??
            ($address?->government_id ?? '')
    );

    $selectedAreaId = old(
        'area_id',
        $kitchen?->area_id ??
            ($address?->area_id ?? '')
    );

    $selectedLat = old(
        'lat',
        $address?->lat ?? ''
    );

    $selectedLng = old(
        'lng',
        $address?->lng ?? ''
    );
@endphp

@if ($errors->any())
    <div class="validation-alert">
        <strong>
            يرجى مراجعة البيانات التالية:
        </strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>
                    {{ $error }}
                </li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-section">
    <div class="section-heading">
        <h2>
            بيانات المطبخ
        </h2>

        <p>
            أدخل بيانات المطبخ لإنشاء طلب التسجيل.
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
                value="{{ old('name', $kitchen?->name ?? '') }}"
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
                value="{{ old('phone', $kitchen?->phone ?? '') }}"
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
                value="{{ old('email', $kitchen?->email ?? '') }}"
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
                            (int) ($kitchen?->is_company ?? 0)
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
                            (int) ($kitchen?->is_company ?? 0)
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

                @if (!$kitchen)
                    <span>*</span>
                @endif
            </label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="{{ $kitchen
                        ? 'اتركها فارغة للاحتفاظ بكلمة المرور الحالية'
                        : '8 أحرف على الأقل' }}"
                    @required(!$kitchen)
                >

                <button
                    type="button"
                    onclick="togglePassword(
                        'password',
                        this
                    )"
                    class="password-toggle"
                >
                    إظهار
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">
                تأكيد كلمة المرور

                @if (!$kitchen)
                    <span>*</span>
                @endif
            </label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    placeholder="أعد كتابة كلمة المرور"
                    @required(!$kitchen)
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
        <h2>
            العنوان والموقع
        </h2>

        <p>
            اختر المحافظة والمنطقة، ثم اضغط على استخدام موقعي الحالي.
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
                    'kitchens.public.areas',
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
                            (string) $selectedGovernmentId ===
                                (string) $government->id
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
                data-selected-area="{{ $selectedAreaId }}"
                required
                disabled
            >
                <option value="">
                    اختر المحافظة أولًا
                </option>
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

        <input
            type="hidden"
            id="lat"
            name="lat"
            value="{{ $selectedLat }}"
        >

        <input
            type="hidden"
            id="lng"
            name="lng"
            value="{{ $selectedLng }}"
        >

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
                placeholder="سيتم إنشاؤه تلقائيًا عند تحديد موقعك"
                readonly
            >
        </div>

        <div class="form-group form-group-full">
            <button
                type="button"
                class="location-button"
                id="currentLocationButton"
            >
                <i class="fas fa-map-marker-alt"></i>

                استخدام موقعي الحالي
            </button>

            <span
                id="locationStatus"
                class="location-status"
                style="
                    display: block;
                    margin-top: 10px;
                "
            ></span>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        function togglePassword(inputId, button) {
            const input =
                document.getElementById(inputId);

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

        document.addEventListener(
            'DOMContentLoaded',
            function() {
                /*
                |--------------------------------------------------------------------------
                | Current Location
                |--------------------------------------------------------------------------
                */

                const latitudeInput =
                    document.getElementById('lat');

                const longitudeInput =
                    document.getElementById('lng');

                const locationLinkInput =
                    document.getElementById(
                        'location_link'
                    );

                const locationStatus =
                    document.getElementById(
                        'locationStatus'
                    );

                const currentLocationButton =
                    document.getElementById(
                        'currentLocationButton'
                    );

                currentLocationButton?.addEventListener(
                    'click',
                    function() {
                        if (!navigator.geolocation) {
                            if (locationStatus) {
                                locationStatus.textContent =
                                    'المتصفح لا يدعم تحديد الموقع.';
                            }

                            return;
                        }

                        currentLocationButton.disabled = true;

                        if (locationStatus) {
                            locationStatus.textContent =
                                'جاري تحديد موقعك الحالي...';
                        }

                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                const latitude =
                                    Number(
                                        position.coords.latitude
                                    ).toFixed(7);

                                const longitude =
                                    Number(
                                        position.coords.longitude
                                    ).toFixed(7);

                                if (latitudeInput) {
                                    latitudeInput.value =
                                        latitude;
                                }

                                if (longitudeInput) {
                                    longitudeInput.value =
                                        longitude;
                                }

                                if (locationLinkInput) {
                                    locationLinkInput.value =
                                        'https://www.google.com/maps?q=' +
                                        latitude +
                                        ',' +
                                        longitude;
                                }

                                if (locationStatus) {
                                    locationStatus.textContent =
                                        'تم تحديد موقع المطبخ بنجاح.';
                                }

                                currentLocationButton.disabled = false;
                            },

                            function(error) {
                                console.error(
                                    'Geolocation error:',
                                    error
                                );

                                let message =
                                    'تعذر تحديد الموقع. تأكد من السماح للمتصفح بالوصول إلى موقعك.';

                                if (error.code === 1) {
                                    message =
                                        'تم رفض إذن الوصول إلى الموقع. يرجى السماح بالوصول من إعدادات المتصفح.';
                                }

                                if (error.code === 2) {
                                    message =
                                        'تعذر الوصول إلى موقعك الحالي.';
                                }

                                if (error.code === 3) {
                                    message =
                                        'استغرق تحديد الموقع وقتًا طويلًا. حاول مرة أخرى.';
                                }

                                if (locationStatus) {
                                    locationStatus.textContent =
                                        message;
                                }

                                currentLocationButton.disabled = false;
                            },

                            {
                                enableHighAccuracy: true,
                                timeout: 15000,
                                maximumAge: 0
                            }
                        );
                    }
                );

                /*
                |--------------------------------------------------------------------------
                | Governments And Areas
                |--------------------------------------------------------------------------
                */

                const governmentSelect =
                    document.getElementById(
                        'government_id'
                    );

                const areaSelect =
                    document.getElementById(
                        'area_id'
                    );

                if (
                    !governmentSelect ||
                    !areaSelect
                ) {
                    return;
                }

                const selectedAreaId =
                    areaSelect.dataset.selectedArea || '';

                async function loadAreas(
                    governmentId,
                    areaId = ''
                ) {
                    areaSelect.disabled = true;

                    areaSelect.innerHTML =
                        '<option value="">جاري تحميل المناطق...</option>';

                    if (!governmentId) {
                        areaSelect.innerHTML =
                            '<option value="">اختر المحافظة أولًا</option>';

                        return;
                    }

                    const routeTemplate =
                        governmentSelect.dataset.areasUrl;

                    const url =
                        routeTemplate.replace(
                            '__ID__',
                            governmentId
                        );

                    try {
                        const response =
                            await fetch(
                                url,
                                {
                                    method: 'GET',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest'
                                    }
                                }
                            );

                        if (!response.ok) {
                            const errorResponse =
                                await response.text();

                            console.error(
                                'Areas request error:',
                                response.status,
                                errorResponse
                            );

                            throw new Error(
                                'Failed to load areas'
                            );
                        }

                        const result =
                            await response.json();

                        areaSelect.innerHTML =
                            '<option value="">اختر المنطقة</option>';

                        const areas =
                            Array.isArray(result.areas)
                                ? result.areas
                                : (
                                    Array.isArray(result.data)
                                        ? result.data
                                        : []
                                );

                        if (areas.length === 0) {
                            areaSelect.innerHTML =
                                '<option value="">لا توجد مناطق لهذه المحافظة</option>';

                            areaSelect.disabled = true;

                            return;
                        }

                        areas.forEach(
                            function(area) {
                                const option =
                                    document.createElement(
                                        'option'
                                    );

                                option.value =
                                    area.id;

                                option.textContent =
                                    area.name_ar;

                                if (
                                    areaId &&
                                    String(areaId) ===
                                    String(area.id)
                                ) {
                                    option.selected = true;
                                }

                                areaSelect.appendChild(
                                    option
                                );
                            }
                        );

                        areaSelect.disabled = false;
                    } catch (error) {
                        console.error(
                            'Unable to load areas:',
                            error
                        );

                        areaSelect.innerHTML =
                            '<option value="">تعذر تحميل المناطق</option>';

                        areaSelect.disabled = true;
                    }
                }

                governmentSelect.addEventListener(
                    'change',
                    function() {
                        loadAreas(this.value);
                    }
                );

                if (governmentSelect.value) {
                    loadAreas(
                        governmentSelect.value,
                        selectedAreaId
                    );
                }
            }
        );
    </script>
@endpush