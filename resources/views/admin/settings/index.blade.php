@extends('admin.layouts.app')

@section('title', 'الإعدادات')

@section('content')

@php
    $assetPrefix = 'public/';
@endphp

<div class="page-title">
    الإعدادات
</div>

@if (session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="errors">
        {{ $errors->first() }}
    </div>
@endif

<div class="table-card">

    <div class="table-header">
        <div class="table-title">
            إعدادات الموقع
        </div>
    </div>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label class="form-label">رقم الواتساب</label>
            <input type="text" name="whatsapp_number" class="form-input"
                value="{{ old('whatsapp_number', $settings->whatsapp_number ?? '') }}"
                placeholder="مثال: 01000000000">
        </div>

        <div class="form-group">
            <label class="form-label">رابط فيسبوك</label>
            <input type="url" name="facebook_link" class="form-input"
                value="{{ old('facebook_link', $settings->facebook_link ?? '') }}"
                placeholder="https://facebook.com/...">
        </div>

        <div class="form-group">
            <label class="form-label">رابط إنستجرام</label>
            <input type="url" name="instgram_link" class="form-input"
                value="{{ old('instgram_link', $settings->instgram_link ?? '') }}"
                placeholder="https://instagram.com/...">
        </div>

        <div class="form-group">
            <label class="form-label">رابط تيك توك</label>
            <input type="url" name="tiktok_link" class="form-input"
                value="{{ old('tiktok_link', $settings->tiktok_link ?? '') }}"
                placeholder="https://tiktok.com/...">
        </div>

        <div class="form-group">
            <label class="form-label">اللوجو</label>

            @if(!empty($settings->logo))
                <div class="logo-preview">
                    <img src="{{ url($assetPrefix . $settings->logo) }}" alt="Logo">
                </div>
            @endif

            <input type="file" name="logo" class="file-input" accept=".jpg,.jpeg,.png,.webp">
        </div>

     <div class="videos-grid">

    <div class="video-card">
        <label class="form-label">فيديو شرح المستخدم</label>

        @if(!empty($settings->user_video))
            <div class="video-preview">
                <video controls preload="metadata">
                    <source src="{{ url($assetPrefix . $settings->user_video) }}" type="video/mp4">
                </video>
            </div>

            <div class="video-name">
                {{ basename($settings->user_video) }}
            </div>
        @endif

        <input type="file" name="user_video" class="file-input"
            accept="video/mp4,video/webm,video/quicktime,video/x-msvideo">
    </div>

    <div class="video-card">
        <label class="form-label">فيديو شرح المطبخ</label>

        @if(!empty($settings->kitchen_video))
            <div class="video-preview">
                <video controls preload="metadata">
                    <source src="{{ url($assetPrefix . $settings->kitchen_video) }}" type="video/mp4">
                </video>
            </div>

            <div class="video-name">
                {{ basename($settings->kitchen_video) }}
            </div>
        @endif

        <input type="file" name="kitchen_video" class="file-input"
            accept="video/mp4,video/webm,video/quicktime,video/x-msvideo">
    </div>

    <div class="video-card">
        <label class="form-label">فيديو شرح الدليفري</label>

        @if(!empty($settings->delivery_video))
            <div class="video-preview">
                <video controls preload="metadata">
                    <source src="{{ url($assetPrefix . $settings->delivery_video) }}" type="video/mp4">
                </video>
            </div>

            <div class="video-name">
                {{ basename($settings->delivery_video) }}
            </div>
        @endif

        <input type="file" name="delivery_video" class="file-input"
            accept="video/mp4,video/webm,video/quicktime,video/x-msvideo">
    </div>

</div>

        <button type="submit" class="add-btn">
            حفظ الإعدادات
        </button>

    </form>

</div>

@endsection

@push('styles')
<style>
.logo-preview {
    width: 160px;
    height: 120px;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    background: #f9fafb;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    overflow: hidden;
}

.logo-preview img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.videos-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

.video-card {
    min-width: 0;
}


.video-preview {
    width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 10px;
    background: #111827;
}

.video-preview video {
    width: 100%;
    height: 180px;
    display: block;
    object-fit: contain;
    background: #111827;
}

.video-link {
    display: inline-block;
    margin-bottom: 6px;
    color: #2563eb;
    font-size: 13px;
    text-decoration: underline;
}

.video-name {
    width: 100%;
    font-size: 13px;
    color: #64748b;
    direction: ltr;
    text-align: left;
    margin-bottom: 10px;
    word-break: break-all;
}

.file-input {
    width: 100%;
    padding: 10px;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    cursor: pointer;
}
@media (max-width: 1100px) {
    .videos-grid {
        grid-template-columns: 1fr;
    }
}

.form-group {
    margin-bottom: 24px;
}
</style>
@endpush