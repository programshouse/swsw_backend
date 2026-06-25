<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <title>
        @yield('title', 'لوحة التحكم')
    </title>

    <link rel="stylesheet" href="{{ asset('admin/css/dashboard.css') }}">



    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @stack('styles')

    <style>
        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .video-preview{
    width: 350px;
    max-width: 100%;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 12px;
    background: #f9fafb;
}

.video-preview video{
    width: 100%;
    display: block;
}

        .back-btn {
            background: #2563eb;
            color: #fff;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
        }

        .user-profile-card {
            background: #fff;
            border-radius: 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: 800;
        }

        .user-info h2 {
            margin: 0 0 8px;
        }

        .user-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            color: #6b7280;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: #fff;
            padding: 22px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .stat-label {
            color: #6b7280;
            margin-bottom: 8px;
        }

        .stat-value {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
        }

        .section-card {
            background: #fff;
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .section-header {
            margin-bottom: 18px;
        }

        .section-header h3 {
            margin: 0;
            color: #111827;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .status-badge {
            background: #dcfce7;
            color: #166534;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        @media(max-width:768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .page-head {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }

            .user-profile-card {
                flex-direction: column;
                text-align: center;
            }
        }

        .add-btn {
            background: #2563eb;
            color: #fff;
            border: 0;
            padding: 10px 16px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .delete-btn {
            background: #dc2626;
            color: #fff;
            border: 0;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
        }

        .success-alert {
            background: #dcfce7;
            color: #166534;
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        .form-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-modal.active {
            display: flex;
        }

        .modal-card {
            width: 100%;
            max-width: 520px;
            background: #fff;
            border-radius: 18px;
            padding: 28px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
        }

        .modal-card h2 {
            margin-top: 0;
            margin-bottom: 22px;
            font-size: 24px;
            color: #111827;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 800;
            color: #374151;
        }

        .form-input {
            width: 100%;
            height: 46px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 0 12px;
            font-size: 15px;
        }

        .form-input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            margin-top: 20px;
        }

        .cancel-btn,
        .save-btn {
            border: 0;
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .cancel-btn {
            background: #e5e7eb;
            color: #111827;
        }

        .save-btn {
            background: #2563eb;
            color: #fff;
        }

        .page-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 22px;
            color: #111827;
        }

        .table-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .table-title {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
        }

        .refresh-btn,
        .view-btn {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            padding: 9px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .refresh-btn:hover,
        .view-btn:hover {
            background: #1d4ed8;
            color: #fff;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .table-wrapper table,
        .table-card table {
            width: 100%;
            border-collapse: collapse;
            direction: rtl;
        }

        .table-wrapper th,
        .table-wrapper td,
        .table-card th,
        .table-card td {
            padding: 14px 12px;
            border-bottom: 1px solid #e5e7eb;
            text-align: right;
            white-space: nowrap;
        }

        .table-wrapper th,
        .table-card th {
            background: #f9fafb;
            color: #374151;
            font-weight: 800;
        }

        .table-wrapper td,
        .table-card td {
            color: #111827;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #6b7280;
            font-weight: 700;
        }

        .nav-group .submenu {
            display: none;
            padding-right: 14px;
        }

        .nav-group.open>.submenu {
            display: block;
        }

        .nav-link.parent {
            cursor: pointer;
            justify-content: space-between;
        }

        .arrow {
            margin-right: auto;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            direction: rtl;
        }

        .admin-layout {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 280px;
            background: #111827;
            color: #fff;
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            padding: 20px 14px;
            overflow-y: auto;
        }

        .logo-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 28px;
            padding-top: 10px;
        }

        .logo-image {
            width: 95px;
            height: 95px;
            object-fit: contain;
            margin-bottom: 14px;
            border-radius: 20px;
            background: #fff;
            padding: 10px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #d1d5db;
            text-decoration: none;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 10px;
            font-weight: 700;
            transition: .2s;
        }

        .nav-link:hover {
            background: #1f2937;
            color: #fff;
            transform: translateX(-2px);
        }

        .nav-link.active {
            background: #2563eb;
            color: #fff;
        }

        .nav-icon {
            width: 24px;
            text-align: center;
            font-size: 18px;
        }

        .main-wrapper {
            margin-right: 280px;
            width: calc(100% - 280px);
            min-height: 100vh;
        }

        .topbar {
            height: 70px;
            background: #fff;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .admin-info {
            color: #374151;
            font-weight: 700;
            font-size: 15px;
        }

        .logout-btn {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            transition: .2s;
        }

        .logout-btn:hover {
            background: #b91c1c;
        }

        .content {
            padding: 30px;
        }

        @media(max-width:991px) {

            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }

            .main-wrapper {
                margin-right: 0;
                width: 100%;
            }

            .admin-layout {
                flex-direction: column;
            }
        }
    </style>

</head>

<body>

    <div class="admin-layout">

        <aside class="sidebar">

            <div class="logo-wrapper">

                @if (!empty($settings?->logo))
                    <img src="{{ url($settings->logo) }}" alt="اللوجو" class="logo-image">
                @else
                    <img src="{{ asset('logo.png') }}" alt="اللوجو" class="logo-image">
                @endif

                <div class="logo-text">
                    لوحة التحكم
                </div>

            </div>

            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">📊</span>
                <span>الإحصائيات</span>
            </a>

            <a href="{{ route('admin.clients.index') }}"
                class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">
                <span class="nav-icon">👥</span>
                <span>المستخدمين</span>
            </a>

            <a href="{{ route('admin.kitchens.index') }}"
                class="nav-link {{ request()->routeIs('admin.kitchens.*') ? 'active' : '' }}">
                <span class="nav-icon">🍽️</span>
                <span>المطابخ</span>
            </a>

            <a href="{{ route('admin.governments.index') }}"
                class="nav-link {{ request()->routeIs('admin.governments.*') ? 'active' : '' }}">
                <span class="nav-icon">🏛️</span>
                <span>المحافظات</span>
            </a>

            <a href="{{ route('admin.areas.index') }}"
                class="nav-link {{ request()->routeIs('admin.areas.*') ? 'active' : '' }}">
                <span class="nav-icon">📍</span>
                <span>المناطق</span>
            </a>

            <a href="{{ route('admin.tickets.index') }}"
                class="nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                <span class="nav-icon">🎫</span>
                <span>المشاكل</span>
            </a>

            <div
                class="nav-group {{ request()->routeIs('admin.delivery.*') || request()->routeIs('admin.delivery.points.*') ? 'open' : '' }}">

                <div class="nav-link parent" onclick="toggleMenu(this)">
                    <span class="nav-icon">🏍️</span>
                    <span>الدليفري</span>
                    <span class="arrow">⌄</span>
                </div>

                <div class="submenu">

                    <a href="{{ route('admin.delivery.pending') }}"
                        class="nav-link sub {{ request()->routeIs('admin.delivery.pending') ? 'active' : '' }}">
                        قيد الانتظار
                    </a>

                    <a href="{{ route('admin.delivery.profile.pending') }}"
                        class="nav-link sub {{ request()->routeIs('admin.delivery.profile.pending') ? 'active' : '' }}">
                        تعديلات ملفات الدليفري المعلقة
                    </a>

                    <a href="{{ route('admin.delivery.approved') }}"
                        class="nav-link sub {{ request()->routeIs('admin.delivery.approved') ? 'active' : '' }}">
                        الدليفري المعتمد
                    </a>

                    <a href="{{ route('admin.delivery.points.index') }}"
                        class="nav-link sub {{ request()->routeIs('admin.delivery.points.*') ? 'active' : '' }}">
                        نقاط الدليفري
                    </a>



                </div>
            </div>

            <a href="{{ route('admin.shifts.index') }}"
                class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                <span class="nav-icon">🕒</span>
                <span>الشيفتات</span>
            </a>

            <a href="{{ route('admin.points.index') }}"
                class="nav-link {{ request()->routeIs('admin.points.*') ? 'active' : '' }}">
                <span class="nav-icon">🏆</span>
                <span>النقاط</span>
            </a>

            <a href="{{ route('admin.rates.index') }}"
                class="nav-link {{ request()->routeIs('admin.rates.*') ? 'active' : '' }}">
                <span class="nav-icon">⭐</span>
                <span>التقييمات</span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
                class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <span class="nav-icon">🏷️</span>
                <span>فئات العناصر</span>
            </a>

            <a href="{{ route('admin.meals.index') }}"
                class="nav-link {{ request()->routeIs('admin.meals.*') ? 'active' : '' }}">
                <span class="nav-icon">🍔</span>
                <span>جميع الوجبات</span>
            </a>

            <a href="{{ route('admin.sliders.index') }}"
                class="nav-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}">
                <span class="nav-icon">🖼️</span>
                <span>السلايدر</span>
            </a>

            <a href="{{ route('admin.workdays.index') }}"
                class="nav-link {{ request()->routeIs('admin.workdays.*') ? 'active' : '' }}">
                <span class="nav-icon">🕒</span>
                <span>أيام وساعات العمل</span>
            </a>

            <a href="{{ route('admin.wallet.debit-requests') }}"
                class="nav-link {{ request()->routeIs('admin.wallet.*') ? 'active' : '' }}">
                <span class="nav-icon">💰</span>
                <span>محفظة المطابخ</span>
            </a>

            <a href="{{ route('admin.orders.index') }}"
                class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <span class="nav-icon">📦</span>
                <span>الطلبات</span>
            </a>

            <a href="{{ route('admin.levels.index') }}"
                class="nav-link {{ request()->routeIs('admin.levels.*') ? 'active' : '' }}">
                <span class="nav-icon">🏆</span>
                <span>المستويات</span>
            </a>

            <a href="{{ route('admin.offers.index') }}"
                class="nav-link {{ request()->routeIs('admin.offers.*') ? 'active' : '' }}">
                <span class="nav-icon">🎁</span>
                <span>العروض</span>
            </a>

            <a href="{{ route('admin.issue-types.index') }}"
                class="nav-link {{ request()->routeIs('admin.issue-types.*') ? 'active' : '' }}">
                <span class="nav-icon">⚠️</span>
                <span>أنواع المشاكل</span>
            </a>

            <a href="{{ route('admin.vehicles.index') }}"
                class="nav-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">

                <span class="nav-icon">🚗</span>

                <span>
                    وسائل التوصيل
                </span>

            </a>

            <a href="{{ route('admin.settings.index') }}"
                class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <span class="nav-icon">⚙️</span>
                <span>الإعدادات</span>
            </a>

        </aside>

        <main class="main-wrapper">

            <header class="topbar">

                <div class="admin-info">
                    {{ auth()->user()->name ?? 'Admin' }}
                    -
                    {{ auth()->user()->email ?? '' }}
                </div>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf

                    <button type="submit" class="logout-btn">
                        تسجيل الخروج
                    </button>
                </form>

            </header>

            <section class="content">
                @yield('content')
            </section>

        </main>

    </div>

    @stack('scripts')

    <script>
        function toggleMenu(el) {
            el.parentElement.classList.toggle('open');
        }
    </script>

</body>

</html>
