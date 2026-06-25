@extends('admin.layouts.app')

@section('title', 'تعديل العرض')

@section('content')

<div class="page-title">
    تعديل العرض
</div>

@if ($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="table-card">

    <form method="POST" action="{{ route('admin.offers.update', $offer->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
    <label class="form-label">
        اسم العرض بالعربي
    </label>

    <input
        type="text"
        name="name_ar"
        class="form-input"
        value="{{ old('name_ar', $offer->name_ar) }}"
        required
    >
</div>

<div class="form-group">
    <label class="form-label">
        اسم العرض بالإنجليزي
    </label>

    <input
        type="text"
        name="name_en"
        class="form-input"
        value="{{ old('name_en', $offer->name_en) }}"
        required
    >
</div>

<div class="form-group">
    <label class="form-label">
        الوصف بالعربي
    </label>

    <textarea
        name="description_ar"
        class="form-input"
        style="height:120px;padding:12px;"
    >{{ old('description_ar', $offer->description_ar) }}</textarea>
</div>

<div class="form-group">
    <label class="form-label">
        الوصف بالإنجليزي
    </label>

    <textarea
        name="description_en"
        class="form-input"
        style="height:120px;padding:12px;"
    >{{ old('description_en', $offer->description_en) }}</textarea>
</div>

        <div class="form-group">
            <label class="form-label">عدد النقاط</label>
            <input type="number" name="points" class="form-input" value="{{ old('points', $offer->points) }}" min="0" required>
        </div>

        <div class="form-group">
            <label style="display:flex;align-items:center;gap:8px;font-weight:700;">
                <input type="checkbox" name="is_active" value="1" {{ $offer->is_active ? 'checked' : '' }}>
                مفعل
            </label>
        </div>

        <button type="submit" class="add-btn">
            حفظ التعديلات
        </button>

    </form>

</div>

@endsection