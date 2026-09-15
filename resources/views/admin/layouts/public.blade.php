@php
    $settings = \App\Models\Setting::first();
@endphp

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'SWSW')
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f6f8fc;
            font-family: 'Cairo', sans-serif;
            color: #172033;
        }

        a {
            text-decoration: none;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        .public-header {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 35px 20px 10px;
        }

        .public-header img {
            width: auto;
            max-width: 220px;
            max-height: 95px;
            object-fit: contain;
        }

        .logo-wrapper {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }

        .logo-image {
            width: auto;
            height: 200px;
            /* أو 90px حسب اللي يعجبك */
            max-width: 220px;
            object-fit: contain;
            display: block;
        }
    </style>

    @stack('styles')
</head>

<body>
             {{-- sales page --}}
    @if (!empty($settings?->logo))
        <div class="logo-wrapper">
            {{-- <img src="https://programshouse.com/swsw/public/uploads/settings/1782547392_logo_swsw-logo.jpg"
                alt="اللوجو" class="logo-image"> --}}

        </div>
    @endif



    @yield('content')

    @stack('scripts')

</body>

</html>
