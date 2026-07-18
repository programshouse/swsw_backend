@extends('sales.layouts.app')

@section('title', 'بيانات المطبخ')

@section('content')
@php
    $profile = $kitchen->kitchenProfile;
@endphp

<div class="kitchen-show-page" dir="rtl">

    <div class="page-header">
        <div>
            <a href="{{ route('sales.kitchens.index') }}" class="back-link">
                <i class="fas fa-arrow-right"></i>
                العودة إلى المطابخ
            </a>

            <h1>بيانات المطبخ</h1>
        </div>

        <a href="{{ route('sales.kitchens.edit', $kitchen) }}" class="edit-btn">
            <i class="fas fa-edit"></i>
            تعديل البيانات
        </a>
    </div>

    <div class="profile-card">
        <div class="cover">
            @if($profile?->cover)
                <img src="{{ Storage::url($profile->cover) }}">
            @endif
        </div>

        <div class="profile-header">
            <div class="logo">
                @if($profile?->logo)
                    <img src="{{ Storage::url($profile->logo) }}">
                @else
                    <i class="fas fa-store"></i>
                @endif
            </div>

            <div>
                <h2>{{ $profile?->name ?? $kitchen->name }}</h2>
                <p>{{ $kitchen->name }}</p>
            </div>

            <span class="approval approval-{{ $profile?->statue }}">
                @switch($profile?->statue)
                    @case('approved')
                        مقبول
                        @break
                    @case('rejected')
                        مرفوض
                        @break
                    @default
                        قيد المراجعة
                @endswitch
            </span>
        </div>

        <div class="details-grid">
            <div class="detail-item">
                <span>اسم صاحب المطبخ</span>
                <strong>{{ $kitchen->name }}</strong>
            </div>

            <div class="detail-item">
                <span>اسم المطبخ</span>
                <strong>{{ $profile?->name }}</strong>
            </div>

            <div class="detail-item">
                <span>رقم الهاتف</span>
                <strong>{{ $kitchen->phone }}</strong>
            </div>

            <div class="detail-item">
                <span>هاتف المطبخ</span>
                <strong>{{ $profile?->phone ?? '-' }}</strong>
            </div>

            <div class="detail-item">
                <span>البريد الإلكتروني</span>
                <strong>{{ $kitchen->email ?? '-' }}</strong>
            </div>

            <div class="detail-item">
                <span>واتساب</span>
                <strong>{{ $profile?->whatsapp ?? '-' }}</strong>
            </div>

            <div class="detail-item">
                <span>المحافظة</span>
                <strong>{{ $profile?->government?->name ?? '-' }}</strong>
            </div>

            <div class="detail-item">
                <span>المنطقة</span>
                <strong>{{ $profile?->area?->name ?? '-' }}</strong>
            </div>

            <div class="detail-item detail-full">
                <span>العنوان</span>
                <strong>{{ $profile?->location ?? '-' }}</strong>
            </div>

            <div class="detail-item">
                <span>بداية العمل</span>
                <strong>{{ $profile?->working_time_start }}</strong>
            </div>

            <div class="detail-item">
                <span>نهاية العمل</span>
                <strong>{{ $profile?->working_time_end }}</strong>
            </div>

            <div class="detail-item">
                <span>توصيل خاص</span>
                <strong>{{ $profile?->have_delivery ? 'نعم' : 'لا' }}</strong>
            </div>

            <div class="detail-item">
                <span>كود السيلز</span>
                <strong>{{ $kitchen->salesEmployee?->code ?? '-' }}</strong>
            </div>
        </div>

        @if($profile?->statue === 'rejected' && $profile?->rejected_note)
            <div class="rejection-box">
                <strong>سبب رفض المطبخ</strong>
                <p>{{ $profile->rejected_note }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .kitchen-show-page {
        --primary: #4948ab;
        --dark: #202b3d;
        --muted: #7b8494;
        --border: #e5e8ee;
        font-family: "Cairo", sans-serif;
    }

    .page-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-bottom: 12px;
        color: #667085;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .page-header h1 {
        margin: 0;
        color: var(--dark);
        font-size: 27px;
        font-weight: 800;
    }

    .edit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 45px;
        padding: 0 20px;
        border-radius: 11px;
        background: var(--primary);
        color: #fff;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .profile-card {
        overflow: hidden;
        max-width: 1000px;
        border: 1px solid var(--border);
        border-radius: 18px;
        background: #fff;
    }

    .cover {
        height: 220px;
        background: linear-gradient(135deg, #e1e2ff, #f4f4ff);
    }

    .cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-header {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 0 25px 22px;
        border-bottom: 1px solid var(--border);
    }

    .logo {
        width: 90px;
        height: 90px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: -45px;
        border: 5px solid #fff;
        border-radius: 18px;
        background: #eef0ff;
        color: var(--primary);
        font-size: 30px;
        box-shadow: 0 6px 18px rgba(32, 43, 61, .12);
    }

    .logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-header h2 {
        margin: 0 0 5px;
        color: var(--dark);
        font-size: 20px;
    }

    .profile-header p {
        margin: 0;
        color: var(--muted);
        font-size: 12px;
    }

    .approval {
        margin-right: auto;
        padding: 7px 13px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
    }

    .approval-approved {
        color: #137a50;
        background: #e8f8f0;
    }

    .approval-pending {
        color: #9a6700;
        background: #fff4da;
    }

    .approval-rejected {
        color: #b42318;
        background: #fff0ef;
    }

    .details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0 25px;
        padding: 25px;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 7px;
        padding: 17px 0;
        border-bottom: 1px solid #f0f2f5;
    }

    .detail-full {
        grid-column: 1 / -1;
    }

    .detail-item span {
        color: var(--muted);
        font-size: 12px;
    }

    .detail-item strong {
        color: var(--dark);
        font-size: 14px;
    }

    .rejection-box {
        margin: 0 25px 25px;
        padding: 16px;
        border-radius: 12px;
        background: #fff1f0;
        color: #b42318;
    }

    .rejection-box p {
        margin: 7px 0 0;
        font-size: 13px;
    }

    @media(max-width: 650px) {
        .page-header {
            align-items: stretch;
            flex-direction: column;
        }

        .details-grid {
            grid-template-columns: 1fr;
        }

        .detail-full {
            grid-column: auto;
        }

        .profile-header {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .approval {
            margin-right: 0;
        }
    }
</style>
@endpush