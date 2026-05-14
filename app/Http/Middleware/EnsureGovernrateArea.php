<?php

namespace App\Http\Middleware;

use App\Models\UserAddress;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGovernrateArea
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // check if user have profile
        if ($request->user()->role === 'kitchen') {

            // check if government deleted
            if (!$request->user()->government_id) {
                return response()->json([
                    'message' => "your current government need to update please update it",
                ], 409);
            }
            // check if area deleted
            if (!$request->user()->area_id) {
                return response()->json([
                    'message' => "your current area need to update please update it",
                ], 409);
            }
            // if both
            if (!$request->user()->area_id && !$request->user()->government_id) {
                return response()->json([
                    'message' => "your current location info need to update please update it",
                ], 409);
            }
        }

        if ($request->user()->role === 'client') {
            $default_address = UserAddress::where('user_id', $request->user()->id)->where('is_default', true)->first();

            if ($default_address) {
                // check client default address
                if (!$default_address->area_id || !$default_address->government_id) {
                    return response()->json([
                        'message' => "your current location info need to update please update it",
                    ], 409);
                }
            }
        }

        return $next($request);
    }
}
