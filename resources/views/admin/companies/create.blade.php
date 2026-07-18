@extends('admin.layouts.app')

@section('title', 'إضافة شركة')

@section('content')

    <style>
        .company-page {
            direction: rtl;
        }

        .company-page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .company-page-title h1 {
            margin: 0 0 6px;
            color: #202b3d;
            font-size: 26px;
            font-weight: 900;
        }

        .company-page-title p {
            margin: 0;
            color: #8b94a7;
            font-size: 13px;
        }

        .back-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 16px;
            border-radius: 12px;
            color: #5e687b;
            background: #eef1f6;
            font-weight: 800;
            text-decoration: none;
        }

        .company-form-card {
            padding: 25px;
            border: 1px solid #edf0f7;
            border-radius: 22px;
            background: #ffffff;
            box-shadow: 0 10px 35px rgba(32, 43, 61, .06);
        }

        .company-form-card-header {
            display: flex;
            align-items: center;
            gap: 13px;
            padding-bottom: 20px;
            margin-bottom: 24px;
            border-bottom: 1px solid #edf0f7;
        }

        .company-form-header-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            color: #4948ab;
            background: #eff0ff;
            font-size: 23px;
        }

        .company-form-card-header h3 {
            margin: 0 0 4px;
            color: #202b3d;
            font-size: 17px;
            font-weight: 900;
        }

        .company-form-card-header p {
            margin: 0;
            color: #929aac;
            font-size: 12px;
        }

        .form-actions-custom {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding-top: 23px;
            margin-top: 24px;
            border-top: 1px solid #edf0f7;
        }

        .form-action-btn {
            min-height: 47px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0 20px;
            border: none;
            border-radius: 13px;
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
        }

        .form-action-btn.save {
            color: #ffffff;
            background: #4948ab;
            box-shadow: 0 8px 18px rgba(73, 72, 171, .2);
        }

        .form-action-btn.cancel {
            color: #657084;
            background: #eef1f6;
        }

        @media (max-width: 600px) {
            .company-form-card {
                padding: 18px;
            }

            .form-actions-custom {
                flex-direction: column;
            }

            .form-action-btn {
                width: 100%;
            }
        }
    </style>

    <div class="company-page">

        <div class="company-page-header">

            <div class="company-page-title">
                <h1>إضافة شركة جديدة</h1>
                <p>أدخل بيانات الشركة لإضافتها إلى النظام.</p>
            </div>

            <a href="{{ route('admin.companies.index') }}"
                class="back-btn">
                <i class="mdi mdi-arrow-right"></i>
                العودة للشركات
            </a>

        </div>

        <div class="company-form-card">

            <div class="company-form-card-header">
                <div class="company-form-header-icon">
                    <i class="mdi mdi-domain-plus"></i>
                </div>

                <div>
                    <h3>بيانات الشركة</h3>
                    <p>الحقول التي تحتوي على علامة النجمة مطلوبة.</p>
                </div>
            </div>

            <form
                action="{{ route('admin.companies.store') }}"
                method="POST"
                enctype="multipart/form-data">

                @csrf

                @include('admin.companies._form')

                <div class="form-actions-custom">

                    <a href="{{ route('admin.companies.index') }}"
                        class="form-action-btn cancel">
                        إلغاء
                    </a>

                    <button type="submit" class="form-action-btn save">
                        <i class="mdi mdi-content-save-outline"></i>
                        حفظ الشركة
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection