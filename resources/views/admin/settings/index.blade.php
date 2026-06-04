@extends('admin.layouts.app')

@section('settings', 'الاعدادات')

@section('content')

    <div class="container">

        <h2 class="mb-4">Settings</h2>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <div class="card p-4">

                {{-- WhatsApp --}}
                <div class="mb-3">
                    <label>WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control"
                        value="{{ $settings->whatsapp_number ?? '' }}">
                </div>

                {{-- Facebook --}}
                <div class="mb-3">
                    <label>Facebook Link</label>
                    <input type="url" name="facebook_link" class="form-control"
                        value="{{ $settings->facebook_link ?? '' }}">
                </div>

                {{-- Instagram --}}
                <div class="mb-3">
                    <label>Instagram Link</label>
                    <input type="url" name="instgram_link" class="form-control"
                        value="{{ $settings->instgram_link ?? '' }}">
                </div>

                {{-- TikTok --}}
                <div class="mb-3">
                    <label>TikTok Link</label>
                    <input type="url" name="tiktok_link" class="form-control"
                        value="{{ $settings->tiktok_link ?? '' }}">
                </div>

                <button class="btn btn-primary">
                    Save Settings
                </button>

            </div>
        </form>

    </div>

@endsection
