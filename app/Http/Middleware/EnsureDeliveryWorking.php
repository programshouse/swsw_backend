<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeliveryWorking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $delivery = $request->user();

        // Check if delivery user is currently on break
        if ($delivery->is_break) {

            // Ensure break start time exists
            if ($delivery->break_started_at) {

                // Calculate elapsed time since break started
                $minutesPassed = $delivery->break_started_at->diffInMinutes(now());

                // Check if break duration has been exceeded
                if ($minutesPassed >= $delivery->break_time) {

                    $delivery->update([
                        'is_break' => 0,
                        'break_time' => null,
                        'break_started_at' => null,
                    ]);
                } else {

                    return response()->json([
                        'message' => 'you are on break'
                    ]);
                }
            } else {

                return response()->json([
                    'message' => 'you are on break'
                ]);
            }
        }

        return $next($request);
    }
}
