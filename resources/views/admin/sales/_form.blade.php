<div class="form-intro">
    <div>
        <h3>معلومات موظف المبيعات</h3>

        <p>
            أدخل بيانات الحساب الأساسية وبيانات تسجيل الدخول.
        </p>
    </div>

    <span class="form-code-preview">
        {{ old('code', $sale->code ?? 'Sales Code') }}
    </span>
</div>

<div class="form-grid">

    <div class="form-group">
        <label for="code">
            كود موظف المبيعات
            <span class="required">*</span>
        </label>

        <input
            type="text"
            id="code"
            name="code"
            value="{{ old('code', $sale->code ?? '') }}"
            placeholder="مثال: SALES-001"
            class="@error('code') input-error @enderror"
            required
        >

        @error('code')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="name">
            الاسم
            <span class="required">*</span>
        </label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $sale->name ?? '') }}"
            placeholder="اكتب اسم موظف المبيعات"
            class="@error('name') input-error @enderror"
            required
        >

        @error('name')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="phone">
            رقم الهاتف
            <span class="required">*</span>
        </label>

        <input
            type="text"
            id="phone"
            name="phone"
            value="{{ old('phone', $sale->phone ?? '') }}"
            placeholder="01xxxxxxxxx"
            class="@error('phone') input-error @enderror"
            required
        >

        @error('phone')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="email">
            البريد الإلكتروني
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $sale->email ?? '') }}"
            placeholder="example@email.com"
            class="@error('email') input-error @enderror"
        >

        @error('email')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password">
            كلمة المرور

            @if (!isset($sale))
                <span class="required">*</span>
            @endif
        </label>

        <div class="password-field">
            <input
                type="password"
                id="password"
                name="password"
                placeholder="{{ isset($sale)
                    ? 'اتركها فارغة للاحتفاظ بكلمة المرور'
                    : 'لا تقل عن 8 أحرف' }}"
                class="@error('password') input-error @enderror"
                @required(!isset($sale))
            >

            <button
                type="button"
                class="toggle-password"
                onclick="togglePassword('password', this)"
            >
                إظهار
            </button>
        </div>

        @error('password')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">
            تأكيد كلمة المرور

            @if (!isset($sale))
                <span class="required">*</span>
            @endif
        </label>

        <div class="password-field">
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                placeholder="أعد كتابة كلمة المرور"
                @required(!isset($sale))
            >

            <button
                type="button"
                class="toggle-password"
                onclick="togglePassword('password_confirmation', this)"
            >
                إظهار
            </button>
        </div>
    </div>

    <div class="form-group form-group-full">
        <label>
            حالة الحساب
            <span class="required">*</span>
        </label>

        <div class="status-options">
            <label class="status-option">
                <input
                    type="radio"
                    name="status"
                    value="active"
                    @checked(old('status', $sale->status ?? 'active') === 'active')
                >

                <span class="status-option-content active-option">
                    <strong>نشط</strong>
                    <small>يمكن للموظف استخدام حسابه.</small>
                </span>
            </label>

            <label class="status-option">
                <input
                    type="radio"
                    name="status"
                    value="inactive"
                    @checked(old('status', $sale->status ?? '') === 'inactive')
                >

                <span class="status-option-content inactive-option">
                    <strong>غير نشط</strong>
                    <small>يتم إيقاف استخدام الحساب.</small>
                </span>
            </label>
        </div>

        @error('status')
            <span class="error-message">{{ $message }}</span>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            const isPassword = input.type === 'password';

            input.type = isPassword ? 'text' : 'password';
            button.textContent = isPassword ? 'إخفاء' : 'إظهار';
        }
    </script>





<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);

        if (!input) {
            return;
        }

        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';
        button.textContent = isPassword ? 'إخفاء' : 'إظهار';
    }

    function updateCodePreview(value) {
        const preview = document.querySelector(
            '.form-code-preview'
        );

        if (!preview) {
            return;
        }

        preview.textContent = value.trim()
            ? value
            : 'Sales Code';
    }
</script>
@endpush
