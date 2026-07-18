@php
    $company = $company ?? null;
@endphp

<style>
    .company-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .company-form-group {
        margin-bottom: 0;
    }

    .company-form-group.full-width {
        grid-column: 1 / -1;
    }

    .company-form-label {
        display: block;
        margin-bottom: 9px;
        color: #354052;
        font-size: 14px;
        font-weight: 800;
    }

    .required-star {
        color: #e35050;
    }

    .company-form-control {
        width: 100%;
        min-height: 50px;
        padding: 12px 15px;
        border: 1px solid #dfe4ee;
        border-radius: 14px;
        color: #202b3d;
        background: #fbfcff;
        outline: none;
        transition: .2s ease;
    }

    textarea.company-form-control {
        min-height: 120px;
        resize: vertical;
    }

    .company-form-control:focus {
        border-color: #7da0fa;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(125, 160, 250, .14);
    }

    .company-form-control.is-invalid {
        border-color: #e35050;
    }

    .company-error {
        display: block;
        margin-top: 7px;
        color: #d74949;
        font-size: 12px;
        font-weight: 700;
    }

    .logo-upload-box {
        position: relative;
        min-height: 165px;
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 18px;
        border: 1px dashed #cdd5e4;
        border-radius: 18px;
        background: #fbfcff;
    }

    .logo-preview {
        width: 110px;
        height: 110px;
        flex: 0 0 110px;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e6eaf2;
        border-radius: 20px;
        color: #4948ab;
        background: #eff0ff;
        font-size: 38px;
    }

    .logo-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .upload-content h4 {
        margin: 0 0 7px;
        color: #202b3d;
        font-size: 15px;
        font-weight: 800;
    }

    .upload-content p {
        margin: 0 0 12px;
        color: #8b94a7;
        font-size: 12px;
    }

    .file-label {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 10px 15px;
        border-radius: 11px;
        color: #ffffff;
        background: #4948ab;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .file-input {
        display: none;
    }

    .active-switch-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px;
        border: 1px solid #e5e9f1;
        border-radius: 16px;
        background: #fbfcff;
    }

    .active-switch-info h4 {
        margin: 0 0 5px;
        color: #202b3d;
        font-size: 14px;
        font-weight: 800;
    }

    .active-switch-info p {
        margin: 0;
        color: #929aac;
        font-size: 12px;
    }

    .custom-switch {
        position: relative;
        width: 54px;
        height: 29px;
        flex: 0 0 54px;
    }

    .custom-switch input {
        width: 0;
        height: 0;
        opacity: 0;
    }

    .custom-switch-slider {
        position: absolute;
        inset: 0;
        border-radius: 30px;
        background: #cdd3dd;
        cursor: pointer;
        transition: .25s ease;
    }

    .custom-switch-slider::before {
        content: "";
        position: absolute;
        width: 21px;
        height: 21px;
        top: 4px;
        right: 4px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 3px 8px rgba(0, 0, 0, .18);
        transition: .25s ease;
    }

    .custom-switch input:checked + .custom-switch-slider {
        background: #4948ab;
    }

    .custom-switch input:checked + .custom-switch-slider::before {
        transform: translateX(-25px);
    }

    @media (max-width: 768px) {
        .company-form-grid {
            grid-template-columns: 1fr;
        }

        .company-form-group.full-width {
            grid-column: auto;
        }

        .logo-upload-box {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="company-form-grid">

    <div class="company-form-group">
        <label class="company-form-label">
            اسم الشركة
            <span class="required-star">*</span>
        </label>

        <input
            type="text"
            name="name"
            value="{{ old('name', $company?->name) }}"
            class="company-form-control @error('name') is-invalid @enderror"
            placeholder="أدخل اسم الشركة">

        @error('name')
            <span class="company-error">
                {{ $message }}
            </span>
        @enderror
    </div>

    <div class="company-form-group">
        <label class="company-form-label">
            رقم الهاتف
        </label>

        <input
            type="text"
            name="phone"
            value="{{ old('phone', $company?->phone) }}"
            class="company-form-control @error('phone') is-invalid @enderror"
            placeholder="مثال: 01000000000">

        @error('phone')
            <span class="company-error">
                {{ $message }}
            </span>
        @enderror
    </div>

    <div class="company-form-group">
        <label class="company-form-label">
            البريد الإلكتروني
        </label>

        <input
            type="email"
            name="email"
            value="{{ old('email', $company?->email) }}"
            class="company-form-control @error('email') is-invalid @enderror"
            placeholder="example@company.com">

        @error('email')
            <span class="company-error">
                {{ $message }}
            </span>
        @enderror
    </div>

    <div class="company-form-group">
        <label class="company-form-label">
            حالة الشركة
        </label>

        <div class="active-switch-card">
            <div class="active-switch-info">
                <h4>تفعيل الشركة</h4>
                <p>عند الإيقاف لن تظهر الشركة في الاختيارات المتاحة.</p>
            </div>

            <label class="custom-switch">
                <input
                    type="hidden"
                    name="is_active"
                    value="0">

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $company?->is_active ?? true) ? 'checked' : '' }}>

                <span class="custom-switch-slider"></span>
            </label>
        </div>
    </div>

    

   

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logoInput = document.getElementById('logoInput');
        const logoPreview = document.getElementById('logoPreview');

        if (!logoInput || !logoPreview) {
            return;
        }

        logoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (e) {
                logoPreview.innerHTML = `
                    <img
                        src="${e.target.result}"
                        alt="Logo Preview"
                        style="width:100%;height:100%;object-fit:cover;">
                `;
            };

            reader.readAsDataURL(file);
        });
    });
</script>