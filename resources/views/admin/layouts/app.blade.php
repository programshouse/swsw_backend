<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <title>
        @yield('title', 'لوحة التحكم')
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @stack('styles')

    <style>
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

                <img src="{{ asset('logo.png') }}" alt="logo" class="logo-image">

                <div class="logo-text">
                    Dashboard
                </div>

            </div>

            <a href="{{ route('admin.dashboard') }}"
                class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">

                <span class="nav-icon">📊</span>

                <span>
                    الإحصائيات
                </span>

            </a>

            <a href="{{ route('admin.clients.index') }}"
                class="nav-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}">

                <span class="nav-icon">👥</span>

                <span>
                    المستخدمين
                </span>

            </a>

            <a href="{{ route('admin.kitchens.index') }}"
                class="nav-link {{ request()->routeIs('admin.kitchens.*') ? 'active' : '' }}">

                <span class="nav-icon">🍽️</span>

                <span>
                    المطابخ
                </span>

            </a>

            <a href="#" class="nav-link">

                <span class="nav-icon">📍</span>

                <span>
                    المناطق
                </span>

            </a>

            <a href="{{ route('admin.categories.index') }}"
                class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">

                <span class="nav-icon">🏷️</span>

                <span>
                    فئات العناصر
                </span>

            </a>

            <a href="{{ route('admin.meals.index') }}"
                class="nav-link {{ request()->routeIs('admin.meals.*') ? 'active' : '' }}">

                <span class="nav-icon">🍔</span>

                <span>
                    جميع الوجبات
                </span>

            </a>

            <a href="{{ route('admin.sliders.index') }}"
   class="nav-link {{ request()->routeIs('admin.sliders.*') ? 'active' : '' }}">

    <span class="nav-icon">🖼️</span>

    <span>
        السلايدر
    </span>

</a>

            <a href="{{ route('admin.workdays.index') }}"
                class="nav-link {{ request()->routeIs('admin.workdays.*') ? 'active' : '' }}">

                <span class="nav-icon">🕒</span>

                <span>
                    أيام وساعات العمل
                </span>

            </a>

            <a href="{{ route('admin.wallet.debit-requests') }}"
                class="nav-link {{ request()->routeIs('admin.wallet.*') ? 'active' : '' }}">

                <span class="nav-icon">💰</span>
                <span>محفظة المطابخ</span>

            </a>



            <a href="#" class="nav-link">

                <span class="nav-icon">⚙️</span>

                <span>
                    الإعدادات
                </span>

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

</body>

</html>
