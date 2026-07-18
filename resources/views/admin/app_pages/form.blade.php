@php
    $page = $page ?? null;
@endphp

<div class="form-group">
    <label class="form-label">نوع التطبيق</label>

    <select name="app_type" class="form-input">
        <option value="">اختر نوع التطبيق</option>
        <option value="client" {{ old('app_type', $page->app_type ?? '') == 'client' ? 'selected' : '' }}>
            تطبيق العميل
        </option>
        <option value="kitchen" {{ old('app_type', $page->app_type ?? '') == 'kitchen' ? 'selected' : '' }}>
            تطبيق المطبخ
        </option>
        <option value="delivery" {{ old('app_type', $page->app_type ?? '') == 'delivery' ? 'selected' : '' }}>
            تطبيق الدليفري
        </option>
    </select>

    @error('app_type')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">نوع الصفحة</label>

    <select name="page_type" class="form-input">
        <option value="">اختر نوع الصفحة</option>
        <option value="privacy" {{ old('page_type', $page->page_type ?? '') == 'privacy' ? 'selected' : '' }}>
            سياسة الخصوصية
        </option>
        <option value="terms" {{ old('page_type', $page->page_type ?? '') == 'terms' ? 'selected' : '' }}>
            الشروط والأحكام
        </option>
        <option value="get_help" {{ old('page_type', $page->page_type ?? '') == 'get_help' ? 'selected' : '' }}>
            احصل على المساعدة
        </option>
    </select>

    @error('page_type')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">العنوان بالعربي</label>

    <input type="text" name="title_ar" class="form-input" value="{{ old('title_ar', $page->title_ar ?? '') }}">

    @error('title_ar')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">العنوان بالإنجليزي</label>

    <input type="text" name="title_en" class="form-input" value="{{ old('title_en', $page->title_en ?? '') }}">

    @error('title_en')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">المحتوى بالعربي</label>

    <textarea name="content_ar" class="form-input" rows="12">{{ old('content_ar', $page->content_ar ?? '') }}</textarea>

    @error('content_ar')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label class="form-label">المحتوى بالإنجليزي</label>

    <textarea name="content_en" class="form-input" rows="12">{{ old('content_en', $page->content_en ?? '') }}</textarea>

    @error('content_en')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label>
        <input type="checkbox" name="is_active" value="1"
            {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
        مفعل
    </label>
</div>
