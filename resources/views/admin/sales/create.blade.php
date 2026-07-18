@extends('admin.layouts.app')

@section('title', 'إضافة موظف مبيعات')

@section('content')
    <div class="sale-form-page" dir="rtl">

        <div class="page-heading">
            <div>
                <a
                    href="{{ route('admin.sales.index') }}"
                    class="back-link"
                >
                    ← العودة إلى موظفي المبيعات
                </a>

                <h1>إضافة موظف مبيعات</h1>
                <p>أدخل بيانات موظف المبيعات لإنشاء حساب جديد.</p>
            </div>
        </div>

        <form
            action="{{ route('admin.sales.store') }}"
            method="POST"
            class="form-card"
        >
            @csrf

            <div class="form-card-header">
                <div>
                    <h2>البيانات الأساسية</h2>
                    <p>الحقول التي تحتوي على علامة * مطلوبة.</p>
                </div>
            </div>

            <div class="form-card-body">
                @include('admin.sales._form')
            </div>

            <div class="form-actions">
                <a
                    href="{{ route('admin.sales.index') }}"
                    class="cancel-button"
                >
                    إلغاء
                </a>

                <button type="submit" class="save-button">
                    حفظ موظف المبيعات
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.sales._form_styles')
@endpush