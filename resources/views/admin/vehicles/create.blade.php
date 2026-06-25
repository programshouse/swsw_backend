@extends('admin.layouts.app')

@section('title', 'إضافة وسيلة توصيل')

@section('content')

<div class="page-title">
    إضافة وسيلة توصيل
</div>

<div class="table-card">

    <form method="POST"
        action="{{ route('admin.vehicles.store') }}">

        @csrf

        <div class="form-group">
            <label class="form-label">
                الاسم بالعربي
            </label>

            <input
                type="text"
                name="name_ar"
                class="form-input"
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
                min="0"
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
                min="0"
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
                min="0"
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
                min="0"
                required>
        </div>

        <button class="add-btn">
            حفظ
        </button>

    </form>

</div>

@endsection