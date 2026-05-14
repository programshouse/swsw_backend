<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitchen Orders Realtime</title>

    {{-- توكن المطبخ (Bearer كامل كما أعطيته) --}}
    <meta name="kitchen-token" content="Bearer 1|lqxGTAuuysnV7rtjdL7bnCYQWoodToDTxV9bduUre562d443">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">
    <div class="max-w-2xl w-full bg-white shadow-md rounded-lg p-6 space-y-4">
        <h1 class="text-2xl font-semibold text-gray-800">
            المطبخ - متابعة الأوردرات الجديدة (Realtime)
        </h1>

        <p class="text-sm text-gray-600">
            افتح هذه الصفحة، ثم من تطبيق العميل اعمل أوردر جديد للمطبخ صاحب هذا التوكن.
            أي أوردر جديد هيظهر هنا مباشرة بدون ما تعمل ريفرش للصفحة.
        </p>

        <div id="status" class="text-sm font-medium text-blue-700">
            جاري الاتصال بالسيرفر وجلب بيانات المطبخ...
        </div>

        <div class="border-t pt-4">
            <h2 class="text-lg font-semibold mb-2">الأوردرات الجديدة:</h2>
            <ul id="orders" class="space-y-2 text-sm text-gray-800 max-h-80 overflow-y-auto">
                {{-- سيتم إضافة الأوردرات هنا من خلال JavaScript --}}
            </ul>
        </div>
    </div>
</body>
</html>

