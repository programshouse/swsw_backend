<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileAccepted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // check if user have profile
        if (!$request->user()->profile) {
            return response()->json([
                'message' => "you do not have profile",
            ], 404);
        }

        // if user profile not accepted

        $profile_status = ['pending', 'rejected'];
        $account_status = ['not_active', 'pending'];
        $user_profile = $request->user()->profile->statue;
        $user_status = $request->user()->status;

        // check user account status
        if (in_array($user_status, $account_status)) {
            return response()->json([
                'message' => $user_status === 'pending' ? 'your account is pending not active yet' : 'your account rejected please contact support',
            ], 403);
        }

        if (in_array($user_profile, $profile_status)) {
            return response()->json([
                'message' => $user_profile === 'pending' ? 'your profile is pending not active yet' : 'your profile rejected please contact support',
            ], 403);
        }

        return $next($request);
    }
}
