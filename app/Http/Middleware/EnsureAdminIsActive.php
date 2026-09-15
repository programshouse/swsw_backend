<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth()->user();

        if (!$admin || $admin->role !== 'admin') {
            abort(403, 'غير مصرح لك بالدخول.');
        }

        if (!$admin->is_admin_active) {
            auth()->logout();

            return redirect()
                ->route('admin.login')
                ->withErrors([
                    'email' => 'تم إيقاف حساب الأدمن.',
                ]);
        }

        return $next($request);
    }
}