@extends('admin.layouts.app')

@section('title', 'الدليفري المعتمد')

@push('styles')
    <style>
        .avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
            border: 2px solid #e5e7eb;
            display: inline-block;
            background: #f1f5f9;
        }

        .avatar-fallback {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: 20px;
        }

        .delivery-table {
            border-collapse: separate !important;
            border-spacing: 0 10px;
        }

        .delivery-table thead th {
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            text-align: center;
        }

        .delivery-table tbody tr {
            background: #fff;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
        }

        .delivery-table td {
            text-align: center !important;
            vertical-align: middle;
            padding: 18px 12px !important;
        }

        .user-name {
            font-weight: 800;
            color: #111827;
        }

        .type-badge,
        .cash-badge,
        .timer-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-weight: 800;
            font-size: 13px;
        }

        .timer-badge {
            min-width: 75px;
            direction: ltr;
        }

        .timer-finished {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-badge {
            display: inline-flex;
            padding: 7px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-success {
            background: #dcfce7;
            color: #166534;
        }

        .filter-box {
            background: #fff;
            padding: 18px 20px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(15, 23, 42, .06);
        }


        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            direction: rtl;
        }


        .filter-input,
        .filter-select {
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0 14px;
            font-size: 14px;
            outline: none;
            background: #fff;
            transition: .2s;
        }


        .filter-input {
            width: 260px;
        }


        .filter-select {
            width: 180px;
        }


        .filter-input:focus,
        .filter-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
        }


        .filter-btn {
            height: 42px;
            padding: 0 25px;
            border: none;
            border-radius: 10px;
            background: #2563eb;
            color: white;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
        }


        .filter-btn:hover {
            background: #1d4ed8;
        }


        .reset-btn {
            height: 42px;
            padding: 0 18px;
            display: flex;
            align-items: center;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            font-weight: 700;
        }


        .reset-btn:hover {
            background: #e2e8f0;
        }

        .status-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-warning {
            background: #fef3c7;
            color: #92400e;
        }

        .actions-td {
            position: relative;
        }

        .dropdown-action {
            position: relative;
            display: inline-block;
        }

        .dots-btn {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 12px;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 24px;
            font-weight: 900;
            cursor: pointer;
            line-height: 1;
        }

        .dots-btn:hover {
            background: #2563eb;
            color: #fff;
        }

        .action-menu {
            display: none;
            position: absolute;
            left: 0;
            top: 46px;
            width: 300px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
            padding: 14px;
            z-index: 999;
            text-align: right;
        }

        .action-menu.active {
            display: block;
        }

        .action-menu form {
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }

        .action-menu form:last-child {
            border-bottom: 0;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .action-title {
            font-size: 13px;
            font-weight: 900;
            color: #475569;
            margin-bottom: 8px;
        }

        .action-row {
            display: flex;
            gap: 8px;
        }

        .action-row select,
        .action-row input {
            flex: 1;
            min-width: 0;
            height: 38px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0 10px;
        }

        .menu-btn {
            border: 0;
            border-radius: 10px;
            padding: 9px 12px;
            color: #fff;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }

        .menu-btn.full {
            width: 100%;
        }

        .menu-btn.primary {
            background: #2563eb;
        }

        .menu-btn.warning {
            background: #f59e0b;
        }

        .menu-btn.danger {
            background: #dc2626;
        }

        .menu-btn.success {
            background: #16a34a;
        }

        .menu-btn.copy {
            background: #0f172a;
        }

        .code-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .code-box span {
            font-weight: 900;
            direction: ltr;
        }

        .validation-alert {
            padding: 12px 16px;
            margin-bottom: 15px;
            border-radius: 10px;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 700;
        }

        .validation-alert ul {
            margin: 0;
            padding-right: 20px;
        }
    </style>
@endpush

@section('content')

    <div class="filter-box">

    <form method="GET" class="filter-form">

        <input 
            type="text"
            name="name"
            class="filter-input"
            placeholder="اسم الدليفري"
            value="{{ request('name') }}"
        >

        <select name="area_id" class="filter-select">

            <option value="">
                كل المناطق
            </option>

            @foreach($areas as $area)

                <option 
                    value="{{ $area->id }}"
                    @selected(request('area_id') == $area->id)
                >
                    {{ $area->name }}
                </option>

            @endforeach

        </select>


        <button class="filter-btn">
            بحث
        </button>


        <a href="{{ route('admin.delivery.approved') }}" class="reset-btn">
            إعادة تعيين
        </a>

    </form>

</div>

    </form>

    @if (session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="validation-alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif




    <div class="table-card">

        <div class="table-header">
            <div class="table-title">
                قائمة الدليفري
            </div>
        </div>

        <div class="table-wrapper">

            <table class="delivery-table">

                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الهاتف</th>
                        <th>الكود</th>
                        <th>كود بداية الشيفت</th>
                        <th>النوع</th>
                        <th>المركبة</th>
                        <th>المستوى</th>
                        <th>المحافظة</th>
                        <th>المنطقة</th>
                        <th>الصورة</th>
                        <th>الحالة</th>
                        <th>وقت الراحة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($deliveries as $delivery)

                        <tr>

                            <td class="user-name">
                                {{ $delivery->name }}
                            </td>

                            <td>
                                {{ $delivery->email ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->phone ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->code ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->shift_code ?? '-' }}
                            </td>

                            <td>
                                <span class="type-badge">
                                    {{ $delivery->type ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if ($delivery->has_vehicle)
                                    {{ $delivery->vehicle_type ?? 'توجد مركبة' }}
                                @else
                                    لا توجد مركبة
                                @endif
                            </td>

                            <td>
                                {{ $delivery->level?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->government?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->area?->name ?? '-' }}
                            </td>

                            <td>
                                @if ($delivery->image)
                                    @php
                                        if (str_starts_with($delivery->image, 'http')) {
                                            $image = $delivery->image;
                                        } elseif (str_starts_with($delivery->image, 'public/')) {
                                            $image = url($delivery->image);
                                        } else {
                                            $image = asset('storage/' . $delivery->image);
                                        }
                                    @endphp

                                    <img class="avatar" src="{{ $image }}" alt="{{ $delivery->name }}">
                                @else
                                    <span class="avatar-fallback">
                                        {{ mb_substr($delivery->name, 0, 1) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ($delivery->status === 'inactive')
                                    <span class="status-badge status-warning">
                                        معطل
                                    </span>
                                @elseif ((bool) $delivery->is_break)
                                    <span class="status-badge status-danger">
                                        في راحة
                                    </span>
                                @else
                                    <span class="status-badge status-success">
                                        يعمل الآن
                                    </span>
                                @endif
                            </td>

                            <td>
                                @if ((bool) $delivery->is_break && !empty($delivery->break_started_at) && !empty($delivery->break_time))
                                    @php
                                        $breakStartedAt =
                                            $delivery->break_started_at instanceof \Carbon\Carbon
                                                ? $delivery->break_started_at
                                                : \Carbon\Carbon::parse($delivery->break_started_at);

                                        $breakEndsAt = $breakStartedAt->copy()->addMinutes((int) $delivery->break_time);

                                        $remainingSeconds = (int) floor(
                                            max(0, now()->diffInSeconds($breakEndsAt, false)),
                                        );
                                    @endphp

                                    <span class="timer timer-badge" data-remaining="{{ $remainingSeconds }}">
                                        00:00
                                    </span>
                                @else
                                    <span class="cash-badge">
                                        -
                                    </span>
                                @endif
                            </td>

                            <td class="actions-td">

                                <div class="dropdown-action">

                                    <button type="button" class="dots-btn" onclick="toggleActionMenu(this)">
                                        ⋮
                                    </button>

                                    <div class="action-menu">

                                        {{-- ترقية المستوى --}}
                                        <form method="POST"
                                            action="{{ route('admin.delivery.promotion', $delivery->id) }}">
                                            @csrf

                                            <div class="action-title">
                                                ترقية المستوى
                                            </div>

                                            <div class="action-row">

                                                <select name="level_id" required>

                                                    <option value="">
                                                        اختر المستوى
                                                    </option>

                                                    @foreach ($levels as $level)
                                                        <option value="{{ $level->id }}" @selected($delivery->level_id == $level->id)>
                                                            {{ $level->name }}
                                                        </option>
                                                    @endforeach

                                                </select>

                                                <button type="submit" class="menu-btn primary">
                                                    ترقية
                                                </button>

                                            </div>
                                        </form>

                                        {{-- إنشاء كلمة مرور --}}
                                        <form method="POST"
                                            action="{{ route('admin.delivery.generate.password', $delivery->id) }}">
                                            @csrf

                                            <button type="submit" class="menu-btn full warning">
                                                إنشاء كلمة مرور
                                            </button>
                                        </form>

                                        {{-- عرض الكود بعد إنشائه --}}
                                        @if (session('generated_code_user_id') == $delivery->id)
                                            <div class="code-box" id="code-box-{{ $delivery->id }}">
                                                <span id="code-{{ $delivery->id }}">
                                                    {{ session('generated_code') }}
                                                </span>

                                                <button type="button" class="menu-btn copy"
                                                    onclick="copyAndHideCode(
                                                        'code-{{ $delivery->id }}',
                                                        'code-box-{{ $delivery->id }}'
                                                    )">
                                                    نسخ
                                                </button>
                                            </div>
                                        @endif

                                        {{-- بدء أو إنهاء الراحة --}}
                                        <form method="POST" action="{{ route('admin.delivery.break', $delivery->id) }}">
                                            @csrf

                                            <div class="action-title">
                                                الراحة
                                            </div>

                                            <div class="action-row">

                                                @if (!(bool) $delivery->is_break)
                                                    <input type="number" name="break_time" min="1" max="1440"
                                                        required placeholder="المدة بالدقائق">
                                                @endif

                                                <button type="submit"
                                                    class="menu-btn {{ $delivery->is_break ? 'success' : 'danger' }}">
                                                    {{ $delivery->is_break ? 'إنهاء الراحة' : 'بدء الراحة' }}
                                                </button>

                                            </div>
                                        </form>

                                        {{-- تعطيل أو تفعيل الدليفري --}}
                                        <form method="POST"
                                            action="{{ route('admin.delivery.toggle.status', $delivery->id) }}">
                                            @csrf

                                            <button type="submit"
                                                class="menu-btn full {{ $delivery->status === 'inactive' ? 'success' : 'danger' }}"
                                                onclick="return confirm(
                                                    '{{ $delivery->status === 'inactive' ? 'هل تريد تفعيل هذا الدليفري؟' : 'هل تريد تعطيل هذا الدليفري؟' }}'
                                                )">
                                                {{ $delivery->status === 'inactive' ? 'تفعيل الدليفري' : 'تعطيل الدليفري' }}
                                            </button>
                                        </form>

                                        {{-- إضافة نقاط --}}
                                        <form method="POST"
                                            action="{{ route('admin.delivery.add.points', $delivery->id) }}">
                                            @csrf

                                            <div class="action-title">
                                                إضافة نقاط
                                            </div>

                                            <div class="action-row">

                                                <select name="point_id" required>

                                                    <option value="">
                                                        اختر النقاط
                                                    </option>

                                                    @foreach ($points as $point)
                                                        <option value="{{ $point->id }}">
                                                            {{ $point->number }} نقطة
                                                        </option>
                                                    @endforeach

                                                </select>

                                                <button type="submit" class="menu-btn success">
                                                    إضافة
                                                </button>

                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="12">
                                لا يوجد دليفري معتمد حاليًا.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.timer').forEach(function(timer) {

                let remaining = parseInt(timer.dataset.remaining, 10) || 0;

                function updateTimer() {

                    remaining = Math.max(
                        0,
                        Math.floor(remaining)
                    );

                    if (remaining <= 0) {
                        timer.innerText = '00:00';
                        timer.classList.add('timer-finished');

                        return false;
                    }

                    const totalMinutes = Math.floor(remaining / 60);
                    const seconds = Math.floor(remaining % 60);

                    timer.innerText =
                        totalMinutes.toString().padStart(2, '0') +
                        ':' +
                        seconds.toString().padStart(2, '0');

                    return true;
                }

                const isRunning = updateTimer();

                if (!isRunning) {
                    return;
                }

                const interval = setInterval(function() {

                    remaining--;

                    const stillRunning = updateTimer();

                    if (!stillRunning) {
                        clearInterval(interval);
                    }

                }, 1000);

            });

        });

        function toggleActionMenu(button) {

            const menu = button.nextElementSibling;

            document.querySelectorAll('.action-menu').forEach(function(item) {

                if (item !== menu) {
                    item.classList.remove('active');
                }

            });

            menu.classList.toggle('active');
        }

        document.addEventListener('click', function(event) {

            if (!event.target.closest('.dropdown-action')) {

                document.querySelectorAll('.action-menu').forEach(function(menu) {
                    menu.classList.remove('active');
                });

            }

        });

        function copyAndHideCode(codeId, boxId) {

            const codeElement = document.getElementById(codeId);
            const boxElement = document.getElementById(boxId);

            if (!codeElement || !boxElement) {
                return;
            }

            navigator.clipboard
                .writeText(codeElement.innerText.trim())
                .then(function() {

                    alert('تم نسخ الكود بنجاح');

                    boxElement.style.display = 'none';

                })
                .catch(function() {
                    alert('تعذر نسخ الكود');
                });
        }
    </script>
@endpush
