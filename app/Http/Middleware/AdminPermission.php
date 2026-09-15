<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminPermission
{
    public function handle(
        Request $request,
        Closure $next,
        string $permission
    ): Response {
        $admin = auth('web')->user();

        abort_unless(
            $admin
            && $admin->role === 'admin'
            && $admin->hasAdminPermission($permission),
            403,
            'ليس لديك صلاحية للوصول إلى هذا القسم.'
        );

        return $next($request);
    }
}