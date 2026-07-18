<?php

namespace App\Http\Controllers\Admin\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class SalesAuthController extends Controller
{
     public function showLogin(): View
    {
        return view('admin.sales.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => [
                'required',
                'string',
            ],

            'password' => [
                'required',
                'string',
            ],

            'remember' => [
                'nullable',
                'boolean',
            ],
        ], [
            'login.required' => 'البريد الإلكتروني أو رقم الهاتف أو الكود مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        $login = trim($validated['login']);

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (is_numeric($login) ? 'phone' : 'code');

        $credentials = [
            $field => $login,
            'password' => $validated['password'],
            'status' => 'active',
        ];

        $remember = $request->boolean('remember');

        if (!Auth::guard('sales')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('login'))
                ->withErrors([
                    'login' => 'بيانات الدخول غير صحيحة أو الحساب غير نشط.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('admin.sales.kitchens.index'));
    }

   public function logout(Request $request): RedirectResponse
{
    Auth::guard('sales')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('login')
        ->with('success', 'تم تسجيل الخروج بنجاح.');
}
}
