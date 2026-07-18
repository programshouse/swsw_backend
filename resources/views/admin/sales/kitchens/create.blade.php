@extends('admin.layouts.app')

@section('title', 'إضافة مطبخ')

@section('content')
    <div class="kitchen-form-page" dir="rtl">

        <div class="page-header">
            <div>
                <a href="{{ route('admin.sales.kitchens.index') }}" class="back-link">
                    <i class="fas fa-arrow-right"></i>
                    العودة إلى المطابخ
                </a>

                <h1>إضافة مطبخ جديد</h1>
                <p>أدخل بيانات المطبخ وسيتم ربطه تلقائيًا بحسابك.</p>
            </div>
        </div>

        <form action="{{ route('admin.sales.kitchens.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
            @csrf

            <div class="form-body">
                @include('admin.sales.kitchens._form')
            </div>

            <div class="form-actions">
                <a href="{{ route('admin.sales.kitchens.index') }}" class="cancel-btn">
                    إلغاء
                </a>

                <button type="submit" class="save-btn">
                    <i class="fas fa-save"></i>
                    حفظ المطبخ
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.sales.kitchens._form_styles')
@endpush
