@extends('admin.layouts.app')

@section('title', 'المناطق')

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css">

    <style>
        .small-area-map {
            width: 260px;
            height: 150px;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            position: relative;
            z-index: 1;
        }

        .location-cell {
            min-width: 290px;
        }

        .location-map-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .location-placeholder {
            width: 260px;
            height: 150px;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            color: #94a3b8;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 15px;
            font-size: 13px;
        }

        .map-action-btn {
            border: none;
            background: #2563eb;
            color: #ffffff;
            padding: 9px 15px;
            border-radius: 9px;
            cursor: pointer;
            font-weight: 700;
            font-family: inherit;
        }

        .map-action-btn:hover {
            background: #1d4ed8;
        }

        .location-modal-card {
            width: min(900px, 95vw);
            max-height: 92vh;
            overflow-y: auto;
        }

        #locationMap {
            width: 100%;
            height: 500px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .location-help {
            margin: 12px 0 16px;
            color: #64748b;
            font-size: 13px;
            line-height: 1.9;
        }

        .selected-area-name {
            margin-bottom: 14px;
            padding: 12px 15px;
            background: #eff6ff;
            color: #1e40af;
            border-radius: 10px;
            font-weight: 700;
        }

        .map-toolbar {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .current-location-btn {
            border: none;
            padding: 9px 14px;
            border-radius: 9px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .clear-polygon-btn {
            border: none;
            padding: 9px 14px;
            border-radius: 9px;
            background: #fee2e2;
            color: #b91c1c;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .coordinates-message {
            color: #64748b;
            font-size: 13px;
        }

        .polygon-info {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 16px;
        }

        .polygon-info-card {
            padding: 12px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }

        .polygon-info-card span {
            display: block;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .polygon-info-card strong {
            direction: ltr;
            display: block;
            color: #0f172a;
            font-size: 14px;
        }

        .location-error {
            display: none;
            margin-top: 12px;
            padding: 11px 14px;
            border-radius: 9px;
            background: #fee2e2;
            color: #991b1b;
            font-size: 13px;
        }

        @media (max-width: 700px) {
            #locationMap {
                height: 380px;
            }

            .polygon-info {
                grid-template-columns: 1fr;
            }

            .small-area-map,
            .location-placeholder {
                width: 220px;
            }
        }
    </style>
@endpush

@section('content')

    <div class="page-title">
        المناطق
    </div>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            style="
                margin-bottom:20px;
                padding:15px;
                border-radius:10px;
                background:#fee2e2;
                color:#991b1b;
            ">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="table-card">

        <div class="table-header">

            <div class="table-title">
                قائمة المناطق
            </div>

            <button type="button" class="add-btn" onclick="openAreaModal()">
                إضافة منطقة
            </button>

        </div>

        @if ($areas->count())

            <div style="overflow-x:auto;">

                <table>

                    <thead>
                        <tr>
                            <th>المحافظة</th>
                            <th>المنطقة</th>
                            <th>حدود المنطقة</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($areas as $area)
                            @php
                                $polygonData = $area->polygon;

                                if (is_string($polygonData)) {
                                    $polygonData = json_decode($polygonData, true);
                                }

                                $hasPolygon = is_array($polygonData) && count($polygonData) >= 3;
                            @endphp

                            <tr>

                                <td>
                                    {{ $area->government->name_ar ?? '-' }}
                                </td>

                                <td>
                                    {{ $area->name_ar }}
                                </td>

                                <td class="location-cell">

                                    <div class="location-map-wrapper">

                                        @if ($hasPolygon)
                                            <div class="small-area-map" id="small-map-{{ $area->id }}"
                                                data-polygon='@json($polygonData)'></div>

                                            <button type="button" class="map-action-btn"
                                                onclick='openLocationModal(
                                                    @json($area->id),
                                                    @json($area->name_ar),
                                                    @json($polygonData)
                                                )'>
                                                تعديل حدود المنطقة
                                            </button>
                                        @else
                                            <div class="location-placeholder">
                                                لم يتم تحديد حدود المنطقة
                                            </div>

                                            <button type="button" class="map-action-btn"
                                                onclick='openLocationModal(
                                                    @json($area->id),
                                                    @json($area->name_ar),
                                                    null
                                                )'>
                                                تحديد حدود المنطقة
                                            </button>
                                        @endif

                                    </div>

                                </td>

                                <td>

                                    @if ($area->is_active)
                                        <span
                                            style="
                                                background:#dcfce7;
                                                color:#166534;
                                                padding:6px 12px;
                                                border-radius:999px;
                                                font-weight:700;
                                            ">
                                            نشطة
                                        </span>
                                    @else
                                        <span
                                            style="
                                                background:#fee2e2;
                                                color:#991b1b;
                                                padding:6px 12px;
                                                border-radius:999px;
                                                font-weight:700;
                                            ">
                                            معطلة
                                        </span>
                                    @endif

                                </td>

                                <td>

                                    <div
                                        style="
                                            display:flex;
                                            gap:8px;
                                            align-items:center;
                                            justify-content:center;
                                            flex-wrap:wrap;
                                        ">

                                        <form action="{{ route('admin.areas.toggle-status', $area->id) }}" method="POST">
                                            @csrf

                                            <button type="submit" class="save-btn"
                                                style="
                                                    background:
                                                    {{ $area->is_active ? '#f59e0b' : '#16a34a' }};
                                                ">
                                                {{ $area->is_active ? 'تعطيل' : 'تفعيل' }}
                                            </button>

                                        </form>

                                        <form action="{{ route('admin.areas.destroy', $area->id) }}" method="POST"
                                            onsubmit="return confirm('هل أنت متأكد من حذف المنطقة؟')">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="delete-btn">
                                                حذف
                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>
                        @endforeach

                    </tbody>

                </table>

            </div>
        @else
            <div class="empty">
                لا توجد مناطق
            </div>

        @endif

    </div>

    {{-- مودال إضافة منطقة --}}
    <div class="form-modal" id="areaModal">

        <div class="modal-card">

            <h2>
                إضافة منطقة جديدة
            </h2>

            <form action="{{ route('admin.areas.store') }}" method="POST">

                @csrf

                <div class="form-group">

                    <label class="form-label">
                        المحافظة
                    </label>

                    <select name="government_id" class="form-input" required>

                        <option value="">
                            اختر المحافظة
                        </option>

                        @foreach ($governments as $government)
                            <option value="{{ $government->id }}" @selected(old('government_id') == $government->id)>
                                {{ $government->name_ar }}
                            </option>
                        @endforeach

                    </select>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        اسم المنطقة بالعربية
                    </label>

                    <input type="text" name="name_ar" class="form-input" value="{{ old('name_ar') }}" required>

                </div>

                <div class="form-group">

                    <label class="form-label">
                        اسم المنطقة بالإنجليزية
                    </label>

                    <input type="text" name="name_en" class="form-input" value="{{ old('name_en') }}">

                </div>

                <div class="modal-actions">

                    <button type="button" class="cancel-btn" onclick="closeAreaModal()">
                        إلغاء
                    </button>

                    <button type="submit" class="save-btn">
                        حفظ
                    </button>

                </div>

            </form>

        </div>

    </div>

    {{-- مودال تحديد حدود المنطقة --}}
    <div class="form-modal" id="locationModal">

        <div class="modal-card location-modal-card">

            <h2>
                تحديد حدود المنطقة
            </h2>

            <div class="selected-area-name" id="selectedAreaName"></div>

            <form id="locationForm" method="POST">

                @csrf

                <div class="map-toolbar">

                    <button type="button" class="current-location-btn" onclick="useCurrentLocation()">
                        الانتقال إلى موقعي الحالي
                    </button>

                    <button type="button" class="clear-polygon-btn" onclick="clearAreaPolygon()">
                        مسح حدود المنطقة
                    </button>

                    <span class="coordinates-message" id="coordinatesMessage">
                        استخدمي أداة رسم المضلع وحددي حدود المنطقة
                    </span>

                </div>

                <div id="locationMap"></div>

                <p class="location-help">
                    اضغطي على أيقونة رسم المضلع الموجودة أعلى الخريطة،
                    ثم اضغطي على أكثر من نقطة لتحديد محيط المنطقة.
                    لإغلاق الشكل اضغطي على أول نقطة مرة أخرى.
                    ويمكنك بعد ذلك استخدام أداة التعديل لتحريك النقاط.
                </p>

                <input type="hidden" name="polygon" id="areaPolygon" required>

                <input type="hidden" name="lat" id="locationLat">

                <input type="hidden" name="lng" id="locationLng">

                <div class="polygon-info">

                    <div class="polygon-info-card">
                        <span>عدد نقاط الحدود</span>
                        <strong id="polygonPointsCount">0</strong>
                    </div>

                    <div class="polygon-info-card">
                        <span>مركز المنطقة Latitude</span>
                        <strong id="polygonCenterLat">-</strong>
                    </div>

                    <div class="polygon-info-card">
                        <span>مركز المنطقة Longitude</span>
                        <strong id="polygonCenterLng">-</strong>
                    </div>

                </div>

                <div class="location-error" id="locationError"></div>

                <div class="modal-actions">

                    <button type="button" class="cancel-btn" onclick="closeLocationModal()">
                        إلغاء
                    </button>

                    <button type="submit" class="save-btn">
                        حفظ حدود المنطقة
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>

    <script>
        const locationRouteTemplate = @json(route('admin.areas.update-location', [
                'area' => '__AREA_ID__',
            ]));

        const egyptLat = 26.8206;
        const egyptLng = 30.8025;

        let locationMap = null;
        let drawnItems = null;
        let drawControl = null;

        function openAreaModal() {
            document
                .getElementById('areaModal')
                .classList
                .add('active');
        }

        function closeAreaModal() {
            document
                .getElementById('areaModal')
                .classList
                .remove('active');
        }

        function initializeSmallMaps() {
            document
                .querySelectorAll('.small-area-map')
                .forEach(function(element) {

                    const polygon = parsePolygon(
                        element.dataset.polygon
                    );

                    if (polygon.length < 3) {
                        return;
                    }

                    const latLngs = polygon.map(function(point) {
                        return [
                            Number(point.lat),
                            Number(point.lng)
                        ];
                    });

                    const map = L.map(element, {
                        zoomControl: false,
                        dragging: false,
                        scrollWheelZoom: false,
                        doubleClickZoom: false,
                        boxZoom: false,
                        keyboard: false,
                        tap: false,
                        attributionControl: false
                    });

                    L.tileLayer(
                        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19
                        }
                    ).addTo(map);

                    const polygonLayer = L.polygon(latLngs, {
                        weight: 3,
                        fillOpacity: 0.25
                    }).addTo(map);

                    map.fitBounds(
                        polygonLayer.getBounds(), {
                            padding: [15, 15]
                        }
                    );

                });
        }

        function initializeLocationMap() {
            if (locationMap) {
                return;
            }

            locationMap = L.map('locationMap').setView(
                [egyptLat, egyptLng],
                6
            );

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }
            ).addTo(locationMap);

            drawnItems = new L.FeatureGroup();

            locationMap.addLayer(drawnItems);

            drawControl = new L.Control.Draw({
                position: 'topright',

                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        repeatMode: false,

                        shapeOptions: {
                            weight: 3,
                            fillOpacity: 0.25
                        }
                    },

                    polyline: false,
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false
                },

                edit: {
                    featureGroup: drawnItems,
                    edit: true,
                    remove: true
                }
            });

            locationMap.addControl(drawControl);

            locationMap.on(
                L.Draw.Event.CREATED,
                function(event) {

                    drawnItems.clearLayers();

                    const layer = event.layer;

                    drawnItems.addLayer(layer);

                    savePolygonCoordinates(layer);

                }
            );

            locationMap.on(
                L.Draw.Event.EDITED,
                function(event) {

                    event.layers.eachLayer(function(layer) {
                        savePolygonCoordinates(layer);
                    });

                }
            );

            locationMap.on(
                L.Draw.Event.DELETED,
                function() {
                    clearPolygonInputs();
                }
            );
        }

        function openLocationModal(
            areaId,
            areaName,
            polygon
        ) {
            const modal =
                document.getElementById('locationModal');

            const form =
                document.getElementById('locationForm');

            form.action = locationRouteTemplate.replace(
                '__AREA_ID__',
                areaId
            );

            document
                .getElementById('selectedAreaName')
                .textContent = 'المنطقة: ' + areaName;

            hideLocationError();

            modal.classList.add('active');

            setTimeout(function() {
                initializeLocationMap();

                locationMap.invalidateSize();

                drawnItems.clearLayers();

                clearPolygonInputs();

                const parsedPolygon = parsePolygon(polygon);

                if (parsedPolygon.length >= 3) {
                    const latLngs = parsedPolygon.map(
                        function(point) {
                            return [
                                Number(point.lat),
                                Number(point.lng)
                            ];
                        }
                    );

                    const polygonLayer = L.polygon(
                        latLngs, {
                            weight: 3,
                            fillOpacity: 0.25
                        }
                    );

                    drawnItems.addLayer(polygonLayer);

                    locationMap.fitBounds(
                        polygonLayer.getBounds(), {
                            padding: [30, 30]
                        }
                    );

                    savePolygonCoordinates(polygonLayer);
                } else {
                    locationMap.setView(
                        [egyptLat, egyptLng],
                        6
                    );
                }

            }, 200);
        }

        function closeLocationModal() {
            document
                .getElementById('locationModal')
                .classList
                .remove('active');
        }

        function parsePolygon(polygon) {
            if (!polygon) {
                return [];
            }

            if (Array.isArray(polygon)) {
                return polygon;
            }

            try {
                const parsed = JSON.parse(polygon);

                return Array.isArray(parsed) ?
                    parsed : [];
            } catch (error) {
                return [];
            }
        }

        function savePolygonCoordinates(layer) {
            let latLngs = layer.getLatLngs();

            if (
                Array.isArray(latLngs) &&
                Array.isArray(latLngs[0])
            ) {
                latLngs = latLngs[0];
            }

            const coordinates = latLngs.map(
                function(point) {
                    return {
                        lat: Number(point.lat.toFixed(7)),
                        lng: Number(point.lng.toFixed(7))
                    };
                }
            );

            if (coordinates.length < 3) {
                showLocationError(
                    'يجب تحديد ثلاث نقاط على الأقل لرسم حدود المنطقة.'
                );

                return;
            }

            const bounds = layer.getBounds();
            const center = bounds.getCenter();

            const centerLat =
                Number(center.lat).toFixed(7);

            const centerLng =
                Number(center.lng).toFixed(7);

            document
                .getElementById('areaPolygon')
                .value = JSON.stringify(coordinates);

            document
                .getElementById('locationLat')
                .value = centerLat;

            document
                .getElementById('locationLng')
                .value = centerLng;

            document
                .getElementById('polygonPointsCount')
                .textContent = coordinates.length;

            document
                .getElementById('polygonCenterLat')
                .textContent = centerLat;

            document
                .getElementById('polygonCenterLng')
                .textContent = centerLng;

            document
                .getElementById('coordinatesMessage')
                .textContent =
                'تم تحديد ' +
                coordinates.length +
                ' نقاط لحدود المنطقة';

            hideLocationError();
        }

        function clearAreaPolygon() {
            if (drawnItems) {
                drawnItems.clearLayers();
            }

            clearPolygonInputs();

            if (locationMap) {
                locationMap.setView(
                    [egyptLat, egyptLng],
                    6
                );
            }
        }

        function clearPolygonInputs() {
            document
                .getElementById('areaPolygon')
                .value = '';

            document
                .getElementById('locationLat')
                .value = '';

            document
                .getElementById('locationLng')
                .value = '';

            document
                .getElementById('polygonPointsCount')
                .textContent = '0';

            document
                .getElementById('polygonCenterLat')
                .textContent = '-';

            document
                .getElementById('polygonCenterLng')
                .textContent = '-';

            document
                .getElementById('coordinatesMessage')
                .textContent =
                'استخدمي أداة رسم المضلع وحددي حدود المنطقة';

            hideLocationError();
        }

        function useCurrentLocation() {
            initializeLocationMap();

            if (!navigator.geolocation) {
                showLocationError(
                    'المتصفح لا يدعم تحديد الموقع الحالي.'
                );

                return;
            }

            document
                .getElementById('coordinatesMessage')
                .textContent = 'جاري تحديد موقعك...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    locationMap.setView(
                        [
                            position.coords.latitude,
                            position.coords.longitude
                        ],
                        15
                    );

                    document
                        .getElementById('coordinatesMessage')
                        .textContent =
                        'تم الانتقال إلى موقعك، ابدئي رسم حدود المنطقة';

                    hideLocationError();
                },

                function() {
                    showLocationError(
                        'تعذر تحديد موقعك الحالي. تأكدي من السماح بالوصول إلى الموقع.'
                    );
                },

                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function showLocationError(message) {
            const errorElement =
                document.getElementById('locationError');

            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }

        function hideLocationError() {
            const errorElement =
                document.getElementById('locationError');

            errorElement.textContent = '';
            errorElement.style.display = 'none';
        }

        document
            .getElementById('locationForm')
            .addEventListener('submit', function(event) {

                const polygonInput =
                    document.getElementById('areaPolygon');

                const polygon = parsePolygon(
                    polygonInput.value
                );

                if (polygon.length < 3) {
                    event.preventDefault();

                    showLocationError(
                        'يجب رسم حدود المنطقة وتحديد ثلاث نقاط على الأقل قبل الحفظ.'
                    );
                }

            });

        document.addEventListener(
            'DOMContentLoaded',
            function() {

                initializeSmallMaps();

                document
                    .getElementById('areaModal')
                    .addEventListener(
                        'click',
                        function(event) {
                            if (event.target === this) {
                                closeAreaModal();
                            }
                        }
                    );

                document
                    .getElementById('locationModal')
                    .addEventListener(
                        'click',
                        function(event) {
                            if (event.target === this) {
                                closeLocationModal();
                            }
                        }
                    );

            }
        );
    </script>
@endpush
