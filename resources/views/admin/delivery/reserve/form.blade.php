<style>
    .reserve-form {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-section {
        grid-column: 1 / -1;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 18px;
        font-weight: 800;
        color: #111827;
        margin-top: 8px;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-label {
        font-size: 14px;
        margin-bottom: 8px;
    }

    .form-input {
        height: 48px;
        background: #fff;
    }

    .full-width {
        grid-column: 1 / -1;
    }

    .image-preview {
        margin-top: 12px;
    }

    .image-preview img {
        width: 90px;
        height: 90px;
        border-radius: 16px;
        object-fit: cover;
        border: 1px solid #e5e7eb;
        padding: 4px;
        background: #fff;
    }

    .form-actions {
        grid-column: 1 / -1;
        display: flex;
        gap: 12px;
        justify-content: flex-start;
        margin-top: 10px;
        border-top: 1px solid #e5e7eb;
        padding-top: 20px;
    }

    .cancel-link {
        background: #e5e7eb;
        color: #111827;
        padding: 10px 20px;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 800;
    }

    @media(max-width: 768px) {
        .reserve-form {
            grid-template-columns: 1fr;
        }
    }
</style>

<form action="{{ $action }}"
      method="POST"
      enctype="multipart/form-data"
      class="form-box reserve-form">

    @csrf

    @if($method === 'PUT')
        @method('PUT')
    @endif

  

    <div class="form-group">
        <label class="form-label">الاسم</label>
        <input type="text" name="name" class="form-input"
               value="{{ old('name', $delivery->name ?? '') }}" required>
        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" name="email" class="form-input"
               value="{{ old('email', $delivery->email ?? '') }}" required>
        @error('email') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">رقم الهاتف</label>
        <input type="text" name="phone" class="form-input"
               value="{{ old('phone', $delivery->phone ?? '') }}" required>
        @error('phone') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">تاريخ الميلاد</label>
        <input type="date" name="birthdate" class="form-input"
               value="{{ old('birthdate', $delivery->birthdate ?? '') }}" required>
    </div>

   

  <div class="form-group">
    <label class="form-label">المحافظة</label>

    <select
        name="government_id"
        id="government_id"
        class="form-input"
        required
    >
        <option value="">اختر المحافظة</option>

        @foreach($governments as $government)
            <option
                value="{{ $government->id }}"
                @selected(old('government_id', $delivery->government_id ?? '') == $government->id)
            >
                {{ $government->name_ar }}
            </option>
        @endforeach
    </select>

    @error('government_id')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">المنطقة</label>

    <select
        name="area_id"
        id="area_id"
        class="form-input"
        required
        disabled
    >
        <option value="">اختر المحافظة أولاً</option>
    </select>

    @error('area_id')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const governmentSelect = document.getElementById('government_id');
    const areaSelect = document.getElementById('area_id');

    const governments = @json(
        $governments->mapWithKeys(function ($government) {
            return [
                $government->id => $government->areas->map(function ($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->name_ar,
                    ];
                })->values()
            ];
        })
    );

    const selectedGovernment = "{{ old('government_id', $delivery->government_id ?? '') }}";
    const selectedArea = "{{ old('area_id', $delivery->area_id ?? '') }}";

    function loadAreas(governmentId, selected = '') {

        areaSelect.innerHTML = '';

        if (!governmentId || !governments[governmentId]) {
            areaSelect.disabled = true;
            areaSelect.innerHTML =
                '<option value="">اختر المحافظة أولاً</option>';
            return;
        }

        areaSelect.disabled = false;

        areaSelect.innerHTML =
            '<option value="">اختر المنطقة</option>';

        governments[governmentId].forEach(function (area) {

            const option = document.createElement('option');

            option.value = area.id;
            option.textContent = area.name;

            if (String(area.id) === String(selected)) {
                option.selected = true;
            }

            areaSelect.appendChild(option);
        });

        if (governments[governmentId].length === 0) {
            areaSelect.disabled = true;
            areaSelect.innerHTML =
                '<option value="">لا توجد مناطق متاحة</option>';
        }
    }

    governmentSelect.addEventListener('change', function () {
        loadAreas(this.value);
    });

    if (selectedGovernment) {
        loadAreas(selectedGovernment, selectedArea);
    }

});
</script>

    <div class="form-group">
        <label class="form-label">الشيفت</label>
        <select name="shift_id" class="form-input" required>
            @foreach($shifts as $shift)
                <option value="{{ $shift->id }}"
                    @selected(old('shift_id', $delivery->shift_id ?? '') == $shift->id)>
                    {{ $shift->name_ar }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">المستوى</label>
        <select name="level_id" class="form-input">
            <option value="">اختر المستوى</option>
            @foreach($levels as $level)
                <option value="{{ $level->id }}"
                    @selected(old('level_id', $delivery->level_id ?? '') == $level->id)>
                    {{ $level->name }}
                </option>
            @endforeach
        </select>
    </div>

  

    <div class="form-group">
        <label class="form-label">نوع التعاقد</label>
        <select name="type" class="form-input" required>
            <option value="company" @selected(old('type', $delivery->type ?? '') == 'company')>شركة</option>
            <option value="freelance" @selected(old('type', $delivery->type ?? '') == 'freelance')>فريلانسر</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">هل يمتلك مركبة؟</label>
        <select name="has_vehicle" class="form-input" required>
            <option value="1" @selected(old('has_vehicle', $delivery->has_vehicle ?? 1) == 1)>نعم</option>
            <option value="0" @selected(old('has_vehicle', $delivery->has_vehicle ?? 1) == 0)>لا</option>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label">وسيلة التوصيل</label>
        <select name="vehicle_id" class="form-input">
            <option value="">اختر وسيلة التوصيل</option>
            @foreach($vehicles as $vehicle)
                <option value="{{ $vehicle->id }}"
                    @selected(old('vehicle_id', $delivery->vehicle_id ?? '') == $vehicle->id)>
                    {{ $vehicle->name_ar }}
                </option>
            @endforeach
        </select>
    </div>

   

  

    <div class="form-group">
        <label class="form-label">
            كلمة المرور
            @if($delivery)
                <small style="color:#6b7280;">اتركها فارغة إذا لا تريد تغييرها</small>
            @endif
        </label>

        <input type="password" name="password" class="form-input" {{ $delivery ? '' : 'required' }}>
        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">تأكيد كلمة المرور</label>
        <input type="password" name="password_confirmation" class="form-input" {{ $delivery ? '' : 'required' }}>
    </div>

    <div class="form-group full-width">
        <label class="form-label">الصورة</label>
        <input type="file" name="image" class="form-input">

        @if(!empty($delivery?->image))
            <div class="image-preview">
                <img src="{{ asset($delivery->image) }}">
            </div>
        @endif
    </div>

    <div class="form-actions">
        <button class="save-btn">
            حفظ
        </button>

        <a href="{{ route('admin.reserve-deliveries.index') }}" class="cancel-link">
            رجوع
        </a>
    </div>

</form>