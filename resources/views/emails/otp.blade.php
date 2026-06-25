<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق</title>
</head>
<body>
    <h2>مرحباً {{ $name }}</h2>

    <p>رمز التحقق الخاص بك هو:</p>

    <h1 style="letter-spacing: 4px;">{{ $otp }}</h1>

    <p>ينتهي هذا الرمز خلال 10 دقائق.</p>
</body>
</html>