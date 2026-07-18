@extends('sales.layouts.app')

@section('title', 'تعديل المطبخ')

@section('content')
<div class="kitchen-form-page" dir="rtl">

    <div class="page-header">
        <div>
            <a href="{{ route('sales.kitchens.index') }}" class="back-link">
                <i class="fas fa-arrow-right"></i>
                العودة إلى المطابخ
            </a>

            <h1>تعديل المطبخ</h1>
            <p>{{ $kitchen->kitchenProfile?->name ?? $kitchen->name }}</p>
        </div>
    </div>

    <form
        action="{{ route('sales.kitchens.update', $kitchen) }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf
        @method('PUT')

        <div class="form-body">
            @include('sales.kitchens._form', [
                'kitchen' => $kitchen,
            ])
        </div>

        <div class="form-actions">
            <a href="{{ route('sales.kitchens.index') }}" class="cancel-btn">
                إلغاء
            </a>

            <button type="submit" class="save-btn">
                <i class="fas fa-save"></i>
                حفظ التعديلات
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
    @include('sales.kitchens._form_styles')
@endpush