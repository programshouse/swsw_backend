@extends('admin.layouts.app')

@section('title', 'الدليفري المعتمد')



@push('styles')
<style>
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
    box-shadow: 0 4px 14px rgba(15, 23, 42, .05);
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

.avatar {
    width: 46px;
    height: 46px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.empty-avatar {
    color: #94a3b8;
    font-weight: 800;
}

.type-badge,
.cash-badge {
    display: inline-flex;
    padding: 6px 12px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #334155;
    font-weight: 800;
    font-size: 13px;
}

.status-badge {
    display: inline-flex;
    padding: 7px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 800;
}

.status-success {
    background: #dcfce7;
    color: #166534;
}

.status-danger {
    background: #fee2e2;
    color: #991b1b;
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
    box-shadow: 0 20px 45px rgba(15, 23, 42, .18);
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

.menu-btn.primary { background: #2563eb; }
.menu-btn.warning { background: #f59e0b; }
.menu-btn.danger { background: #dc2626; }
.menu-btn.success { background: #16a34a; }
.menu-btn.copy { background: #0f172a; }

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
</style>
@endpush

@section('content')

<div class="page-title">الدليفري المعتمد</div>

@if (session('success'))
    <div class="success-alert">{{ session('success') }}</div>
@endif

<div class="table-card">
    <div class="table-header">
        <div class="table-title">قائمة الدليفري</div>
    </div>

    <div class="table-wrapper">
        <table class="delivery-table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>البريد الإلكتروني</th>
                    <th>الهاتف</th>
                                            <th>الكود</th>
                                              <th>الكود تاكيد الهوية </th>

                    <th>النوع</th>
                    <th>المركبة</th>
                    <th>المستوى</th>
                    
                    {{-- <th>الصورة</th> --}}
                    <th>الحالة</th>
                   <th>الكود تاكيد الهوية </th>

                    <th>وقت الراحة</th>
                    {{-- <th>كاش {{ $this_month }}</th> --}}
                    <th>الإجراءات</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($deliveries as $delivery)
                    <tr>
                        <td class="user-name">{{ $delivery->name }}</td>
                        <td>{{ $delivery->email }}</td>
                        <td>{{ $delivery->phone }}</td>
                         <td>{{ $delivery->code ?? '-' }}</td>
                          <td>{{ $delivery->shift_code ?? '-' }}</td>
                        <td>
                            <span class="type-badge">{{ $delivery->type }}</span>
                        </td>
                        <td>
                            {{ $delivery->has_vehicle ? $delivery->vehicle_type : 'لا توجد مركبة' }}
                        </td>
                        <td>{{ $delivery->level->name ?? '-' }}</td>
                        {{-- <td>
                            @if ($delivery->image)
                                <img class="avatar" src="{{ asset('storage/' . $delivery->image) }}">
                            @else
                                <span class="empty-avatar">-</span>
                            @endif
                        </td> --}}
                        <td>
                            <span class="status-badge {{ $delivery->is_break ? 'status-danger' : 'status-success' }}">
                                {{ $delivery->is_break ? 'في راحة' : 'يعمل الآن' }}
                            </span>
                        </td>
                        <td>
                            @if ($delivery->is_break && $delivery->break_started_at)
                                <span class="timer"
                                      data-end="{{ $delivery->break_started_at->copy()->addMinutes($delivery->break_time)->timestamp }}">
                                </span>
                            @else
                                يعمل الآن
                            @endif
                        </td>
                        <td>
                            <span class="cash-badge">{{ $delivery->monthly_points }}</span>
                        </td>

                        <td class="actions-td">
                            <div class="dropdown-action">
                                <button type="button" class="dots-btn" onclick="toggleActionMenu(this)">
                                    ⋮
                                </button>

                                <div class="action-menu">

                                    <form method="POST" action="{{ route('admin.delivery.promotion', $delivery->id) }}">
                                        @csrf
                                        <div class="action-title">ترقية المستوى</div>
                                        <div class="action-row">
                                            <select name="level_id">
                                                @foreach ($levels as $level)
                                                    <option value="{{ $level->id }}">{{ $level->name }}</option>
                                                @endforeach
                                            </select>
                                            <button class="menu-btn primary">ترقية</button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('admin.delivery.generate.password', $delivery->id) }}">
                                        @csrf
                                        <button class="menu-btn full warning">إنشاء كلمة مرور</button>
                                    </form>

                                    @if (session('generated_code_user_id') == $delivery->id)
                                        <div class="code-box" id="code-box-{{ $delivery->id }}">
                                            <span id="code-{{ $delivery->id }}">{{ session('generated_code') }}</span>
                                            <button type="button"
                                                    class="menu-btn copy"
                                                    onclick="copyAndHideCode('code-{{ $delivery->id }}', 'code-box-{{ $delivery->id }}')">
                                                نسخ
                                            </button>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('admin.delivery.break', $delivery->id) }}">
                                        @csrf
                                        <div class="action-title">الراحة</div>
                                        <div class="action-row">
                                            @if (!$delivery->is_break)
                                                <input type="text" name="break_time" placeholder="الدقائق">
                                            @endif
                                            <button class="menu-btn danger">
                                                {{ !$delivery->is_break ? 'بدء الراحة' : 'إنهاء الراحة' }}
                                            </button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('admin.delivery.add.points', $delivery->id) }}">
                                        @csrf
                                        <div class="action-title">إضافة نقاط</div>
                                        <div class="action-row">
                                            <select name="point_id">
                                                @foreach ($points as $point)
                                                    <option value="{{ $point->id }}">{{ $point->number }}</option>
                                                @endforeach
                                            </select>
                                            <button class="menu-btn success">إضافة</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.timer').forEach(timer => {
                const end = parseInt(timer.dataset.end);

                function updateTimer() {
                    const now = Math.floor(Date.now() / 1000);
                    const remaining = Math.max(0, end - now);

                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;

                    timer.innerText = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }

                updateTimer();
                setInterval(updateTimer, 1000);
            });
        });

        function copyAndHideCode(codeId, boxId) {
            const codeElement = document.getElementById(codeId);

            if (!codeElement) return;

            navigator.clipboard.writeText(codeElement.innerText).then(() => {
                alert('تم نسخ الكود بنجاح');
                document.getElementById(boxId).style.display = 'none';
            });
        }
    </script>



<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.timer').forEach(timer => {
        const end = parseInt(timer.dataset.end);

        function updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = Math.max(0, end - now);

            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;

            timer.innerText = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }

        updateTimer();
        setInterval(updateTimer, 1000);
    });
});

function toggleActionMenu(button) {
    const menu = button.nextElementSibling;

    document.querySelectorAll('.action-menu').forEach(item => {
        if (item !== menu) item.classList.remove('active');
    });

    menu.classList.toggle('active');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.dropdown-action')) {
        document.querySelectorAll('.action-menu').forEach(menu => {
            menu.classList.remove('active');
        });
    }
});

function copyAndHideCode(codeId, boxId) {
    const codeElement = document.getElementById(codeId);

    if (!codeElement) return;

    navigator.clipboard.writeText(codeElement.innerText.trim()).then(() => {
        alert('تم نسخ الكود بنجاح');
        document.getElementById(boxId).style.display = 'none';
    });
}
</script>

@endpush
