@extends('admin.layouts.app')

@section('title', 'تفاصيل الشركة')

@section('content')

    <style>
        .company-details-page {
            direction: rtl;
        }

        .details-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .details-title h1 {
            margin: 0 0 6px;
            color: #202b3d;
            font-size: 26px;
            font-weight: 900;
        }

        .details-title p {
            margin: 0;
            color: #8b94a7;
            font-size: 13px;
        }

        .header-actions {
            display: flex;
            gap: 9px;
        }

        .header-btn {
            min-height: 43px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 15px;
            border-radius: 12px;
            font-weight: 800;
            text-decoration: none;
        }

        .header-btn.back {
            color: #657084;
            background: #eef1f6;
        }

        .header-btn.edit {
            color: #ffffff;
            background: #4948ab;
        }

        .company-profile-card {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 25px;
            margin-bottom: 22px;
            border-radius: 22px;
            color: #ffffff;
            background:
                radial-gradient(circle at top left, rgba(255, 255, 255, .18), transparent 30%),
                linear-gradient(135deg, #4948ab, #7da0fa);
            box-shadow: 0 15px 40px rgba(73, 72, 171, .2);
        }

        .profile-logo {
            width: 105px;
            height: 105px;
            flex: 0 0 105px;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 4px solid rgba(255, 255, 255, .25);
            border-radius: 25px;
            color: #4948ab;
            background: #ffffff;
            font-size: 38px;
            font-weight: 900;
        }

        .profile-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info h2 {
            margin: 0 0 10px;
            font-size: 24px;
            font-weight: 900;
        }

        .profile-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 11px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .17);
            font-size: 12px;
            font-weight: 800;
        }

        .details-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(320px, .45fr);
            gap: 20px;
        }

        .details-card {
            overflow: hidden;
            border: 1px solid #edf0f7;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(32, 43, 61, .05);
        }

        .details-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 20px;
            border-bottom: 1px solid #edf0f7;
        }

        .details-card-header i {
            width: 39px;
            height: 39px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: #4948ab;
            background: #eff0ff;
            font-size: 19px;
        }

        .details-card-header h3 {
            margin: 0;
            color: #202b3d;
            font-size: 16px;
            font-weight: 900;
        }

        .info-list {
            padding: 8px 20px;
        }

        .info-row {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 15px;
            padding: 16px 0;
            border-bottom: 1px solid #f0f2f6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #8992a5;
            font-size: 13px;
            font-weight: 700;
        }

        .info-value {
            color: #303b4d;
            font-size: 14px;
            font-weight: 800;
        }

        .kitchens-list {
            padding: 12px;
        }

        .kitchen-item {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px;
            margin-bottom: 9px;
            border: 1px solid #edf0f7;
            border-radius: 14px;
            background: #fbfcff;
        }

        .kitchen-item:last-child {
            margin-bottom: 0;
        }

        .kitchen-avatar {
            width: 44px;
            height: 44px;
            flex: 0 0 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            color: #4948ab;
            background: #eff0ff;
            font-weight: 900;
        }

        .kitchen-name {
            margin-bottom: 4px;
            color: #202b3d;
            font-weight: 800;
        }

        .kitchen-phone {
            color: #9099aa;
            font-size: 12px;
        }

        .empty-kitchens {
            padding: 35px 15px;
            color: #929aac;
            text-align: center;
        }

        @media (max-width: 900px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .company-profile-card {
                align-items: flex-start;
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
            }

            .header-btn {
                flex: 1;
            }

            .info-row {
                grid-template-columns: 1fr;
                gap: 6px;
            }
        }
    </style>

    <div class="company-details-page">

        <div class="details-header">

            <div class="details-title">
                <h1>تفاصيل الشركة</h1>
                <p>عرض بيانات الشركة والمطابخ التابعة لها.</p>
            </div>

            <div class="header-actions">

                <a href="{{ route('admin.companies.index') }}"
                    class="header-btn back">
                    <i class="mdi mdi-arrow-right"></i>
                    رجوع
                </a>

                <a href="{{ route('admin.companies.edit', $company) }}"
                    class="header-btn edit">
                    <i class="mdi mdi-pencil-outline"></i>
                    تعديل
                </a>

            </div>

        </div>

        <div class="company-profile-card">

            <div class="profile-logo">
                @if ($company->logo)
                    <img
                        src="{{ asset('storage/' . $company->logo) }}"
                        alt="{{ $company->name }}">
                @else
                    {{ mb_substr($company->name, 0, 1) }}
                @endif
            </div>

            <div class="profile-info">
                <h2>{{ $company->name }}</h2>

                <div class="profile-meta">

                    <span class="profile-badge">
                        <i class="mdi mdi-silverware-fork-knife"></i>
                        {{ $company->kitchens->count() }} مطبخ
                    </span>

                    <span class="profile-badge">
                        <i class="mdi mdi-calendar-outline"></i>
                        {{ $company->created_at?->format('Y-m-d') }}
                    </span>

                    <span class="profile-badge">
                        <i class="mdi {{ $company->is_active ? 'mdi-check-circle-outline' : 'mdi-close-circle-outline' }}"></i>
                        {{ $company->is_active ? 'مفعلة' : 'غير مفعلة' }}
                    </span>

                </div>
            </div>

        </div>

        <div class="details-grid">

            <div class="details-card">

                <div class="details-card-header">
                    <i class="mdi mdi-information-outline"></i>
                    <h3>بيانات الشركة</h3>
                </div>

                <div class="info-list">

                    <div class="info-row">
                        <div class="info-label">اسم الشركة</div>
                        <div class="info-value">{{ $company->name }}</div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">رقم الهاتف</div>
                        <div class="info-value">
                            {{ $company->phone ?: 'غير محدد' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">البريد الإلكتروني</div>
                        <div class="info-value">
                            {{ $company->email ?: 'غير محدد' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">العنوان</div>
                        <div class="info-value">
                            {{ $company->address ?: 'غير محدد' }}
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-label">الحالة</div>
                        <div class="info-value">
                            {{ $company->is_active ? 'مفعلة' : 'غير مفعلة' }}
                        </div>
                    </div>

                </div>

            </div>

            <div class="details-card">

                <div class="details-card-header">
                    <i class="mdi mdi-silverware-fork-knife"></i>
                    <h3>المطابخ التابعة</h3>
                </div>

                @if ($company->kitchens->count())

                    <div class="kitchens-list">
                        @foreach ($company->kitchens as $kitchen)
                            <div class="kitchen-item">

                                <div class="kitchen-avatar">
                                    {{ mb_substr($kitchen->name, 0, 1) }}
                                </div>

                                <div>
                                    <div class="kitchen-name">
                                        {{ $kitchen->name }}
                                    </div>

                                    <div class="kitchen-phone">
                                        {{ $kitchen->phone }}
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                @else

                    <div class="empty-kitchens">
                        <i class="mdi mdi-food-off-outline"
                            style="font-size: 35px;"></i>

                        <div style="margin-top: 8px;">
                            لا توجد مطابخ تابعة لهذه الشركة.
                        </div>
                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection