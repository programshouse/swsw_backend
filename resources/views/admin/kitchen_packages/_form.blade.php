@php
    $package = $package ?? null;
@endphp

<div class="form-group">
    <label class="form-label">اسم الباقة</label>
    <input type="text" name="name" class="form-input"
           value="{{ old('name', $package->name ?? '') }}">
</div>

<div class="form-group">
    <label class="form-label">الوصف</label>
    <textarea name="desc" class="form-input" rows="4">{{ old('desc', $package->desc ?? '') }}</textarea>
</div>

<div class="form-group">
    <label class="form-label">المميزات</label>
    <textarea name="features" class="form-input" rows="6"
              placeholder="اكتب كل ميزة في سطر">{{ old('features', $package ? implode("\n", $package->features ?? []) : '') }}</textarea>
</div>

<div class="form-group">
    <label class="form-label">السعر</label>
    <input type="number" step="0.01" name="price" class="form-input"
           value="{{ old('price', $package->price ?? '') }}">
</div>

<div class="form-group">
    <label class="form-label">المدة</label>
    <input type="number" name="duration" class="form-input"
           value="{{ old('duration', $package->duration ?? '') }}"
           placeholder="مثال: 30">
</div>

<div class="form-group">
    <label>
        <input type="checkbox" name="active" value="1"
               {{ old('active', $package->active ?? true) ? 'checked' : '' }}>
        مفعلة
    </label>
</div>

<button type="submit" class="add-btn">
    حفظ
</button>