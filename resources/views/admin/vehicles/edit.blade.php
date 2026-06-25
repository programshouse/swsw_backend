@extends('admin.layouts.app')

@section('title', 'تعديل وسيلة التوصيل')

@section('content')

<div class="page-title">
    تعديل وسيلة التوصيل
</div>

<div class="table-card">

    <form method="POST"
        action="{{ route('admin.vehicles.update',$vehicle->id) }}">

        @csrf
        @method('PUT')

        <div class="form-group">

            <label class="form-label">
                الاسم بالعربي
            </label>

            <input
                type="text"
                name="name_ar"
                class="form-input"
                value="{{ old('name_ar',$vehicle->name_ar) }}"
                required>

        </div>

        <div class="form-group">

            <label class="form-label">
                الاسم بالإنجليزي
            </label>

            <input
                type="text"
                name="name_en"
                class="form-input"
                value="{{ old('name_en',$vehicle->name_en) }}"
                required>

        </div>

        <div class="form-group">

            <label class="form-label">
                أقصى مسافة (كم)
            </label>

            <input
                type="number"
                name="max_km"
                class="form-input"
                value="{{ old('max_km',$vehicle->max_km) }}"
                required>

        </div>

        <div class="form-group">

            <label class="form-label">
                المسافة بالمتر
            </label>

            <input
                type="number"
                name="price_distance_meters"
                class="form-input"
                value="{{ old('price_distance_meters',$vehicle->price_distance_meters) }}"
                required>

        </div>

        <div class="form-group">

            <label class="form-label">
                التكلفة
            </label>

            <input
                type="number"
                step="0.01"
                name="price"
                class="form-input"
                value="{{ old('price',$vehicle->price) }}"
                required>

        </div>

        <div class="form-group">

            <label class="form-label">
                الوقت بالدقائق
            </label>

            <input
                type="number"
                name="estimated_time_minutes"
                class="form-input"
                value="{{ old('estimated_time_minutes',$vehicle->estimated_time_minutes) }}"
                required>

        </div>

        <button class="add-btn">
            حفظ التعديلات
        </button>

    </form>

</div>

@endsection