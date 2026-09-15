<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>حذف سائق | SWSW</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Tahoma, Arial, sans-serif;
            background: #f4f7f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 550px;
        }

        .card {
            background: #fff;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.08);
            border: 1px solid #e8eeee;
        }

        .logo {
            display: inline-block;
            background: #e9f7f1;
            color: #10805b;
            font-weight: bold;
            padding: 7px 16px;
            border-radius: 30px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 25px;
            color: #203d35;
            margin-bottom: 10px;
        }

        .description {
            color: #6b7975;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #344c45;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            height: 50px;
            padding: 0 15px;
            border: 1px solid #d9e2df;
            border-radius: 11px;
            outline: none;
            font-size: 15px;
            direction: ltr;
            text-align: left;
            transition: 0.2s;
        }

        input:focus {
            border-color: #15936a;
            box-shadow: 0 0 0 3px rgba(21, 147, 106, 0.10);
        }

        button {
            width: 100%;
            height: 50px;
            border: 0;
            border-radius: 11px;
            background: #dc3545;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }

        button:hover {
            background: #bd2635;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.7;
        }

        .alert-success {
            background: #e9f8f0;
            border: 1px solid #bfe8d1;
            color: #17663e;
        }

        .alert-error {
            background: #fff0f0;
            border: 1px solid #f3c4c4;
            color: #a12828;
        }

        .warning {
            background: #fff8e7;
            border: 1px solid #f3dfa7;
            color: #755b11;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13px;
            line-height: 1.8;
        }

        .warning strong {
            color: #a06c00;
        }

        .error-text {
            color: #dc3545;
            font-size: 13px;
            margin-top: 7px;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="card">

        <div class="logo">
            SWSW Admin
        </div>

        <h1>
            حذف مستخدم بالبريد الإلكتروني
        </h1>

        <p class="description">
            أدخل البريد الإلكتروني الخاص بالمستخدم الذي تريد حذفه.
        </p>


        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif


        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif


        <div class="warning">
            <strong>تحذير:</strong>
            عند تنفيذ العملية سيتم حذف حساب المستخدم بالكامل  
             وليس البريد الإلكتروني فقط.
        </div>


        <form
            action="{{ route('admin.delete-driver-email') }}"
            method="POST"
            onsubmit="return confirmDelete();"
        >

            @csrf
            @method('DELETE')


            <div class="form-group">

                <label for="email">
                    البريد الإلكتروني
                </label>

                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="example@email.com"
                    value="{{ old('email') }}"
                    required
                >

                @error('email')
                    <div class="error-text">
                        {{ $message }}
                    </div>
                @enderror

            </div>


            <button type="submit">
                حذف المستخدم
            </button>

        </form>

    </div>

</div>


<script>
    function confirmDelete() {

        const email = document.getElementById('email').value;

        return confirm(
            'هل أنت متأكد من حذف المستخدم صاحب البريد:\n\n'
            + email +
            '\n\nلا يمكن التراجع عن هذه العملية.'
        );
    }
</script>

</body>
</html>