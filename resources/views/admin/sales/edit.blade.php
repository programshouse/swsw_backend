@extends('admin.layouts.app')

@section('title', 'تعديل موظف المبيعات')

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

                <h1>تعديل موظف المبيعات</h1>
                <p>
                    تعديل بيانات:
                    <strong>{{ $sale->name }}</strong>
                </p>
            </div>
        </div>

        <form
            action="{{ route('admin.sales.update', $sale) }}"
            method="POST"
            class="form-card"
        >
            @csrf
            @method('PUT')

            <div class="form-card-header">
                <div>
                    <h2>بيانات موظف المبيعات</h2>
                    <p>
                        اترك كلمة المرور فارغة إذا كنت لا تريد تغييرها.
                    </p>
                </div>

                <span
                    class="header-status
                    {{ $sale->status === 'active'
                        ? 'header-status-active'
                        : 'header-status-inactive' }}"
                >
                    {{ $sale->status === 'active'
                        ? 'نشط'
                        : 'غير نشط' }}
                </span>
            </div>

            <div class="form-card-body">
                @include('admin.sales._form', [
                    'sale' => $sale,
                ])
            </div>

            <div class="form-actions">
                <a
                    href="{{ route('admin.sales.index') }}"
                    class="cancel-button"
                >
                    إلغاء
                </a>

                <button type="submit" class="save-button">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
@endsection

@push('styles')
    @include('admin.sales._form_styles')
@endpush