<?php

namespace App\Http\Controllers;
use App\Models\FirebaseToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class FirebaseTokenController extends Controller
{
     public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => [
                'required',
                'string',
            ],
            'app_type' => [
                'required',
                Rule::in([
                    'client',
                    'kitchen',
                    'delivery',
                ]),
            ],
            'language' => [
                'required',
                Rule::in([
                    'ar',
                    'en',
                ]),
            ],
            'device_id' => [
                'nullable',
                'string',
                'max:255',
            ],
            'device_type' => [
                'nullable',
                'string',
                'max:50',
            ],
            'device_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $user = $request->user();

        /*
         * الـ token قديمًا كان مرتبطًا بحساب آخر،
         * لذلك نعيد ربطه بالحساب الحالي.
         */
        $firebaseToken = FirebaseToken::updateOrCreate(
            [
                'token' => $validated['token'],
            ],
            [
                'tokenable_type' => $user->getMorphClass(),
                'tokenable_id' => $user->getKey(),

                'app_type' => $validated['app_type'],
                'language' => $validated['language'],

                'device_id' => $validated['device_id'] ?? null,
                'device_type' => $validated['device_type'] ?? null,
                'device_name' => $validated['device_name'] ?? null,

                'is_active' => true,
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Firebase token saved successfully',
            'data' => [
                'id' => $firebaseToken->id,
                'app_type' => $firebaseToken->app_type,
                'language' => $firebaseToken->language,
            ],
        ]);
    }

    public function updateLanguage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => [
                'required',
                Rule::in([
                    'ar',
                    'en',
                ]),
            ],
            'app_type' => [
                'required',
                Rule::in([
                    'client',
                    'kitchen',
                    'delivery',
                ]),
            ],
            'device_id' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $user = $request->user();

        $query = $user->firebaseTokens()
            ->where('app_type', $validated['app_type']);

        if (!empty($validated['device_id'])) {
            $query->where(
                'device_id',
                $validated['device_id']
            );
        }

        $query->update([
            'language' => $validated['language'],
            'last_used_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Notification language updated successfully',
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => [
                'required',
                'string',
            ],
        ]);

        $user = $request->user();

        $user->firebaseTokens()
            ->where('token', $validated['token'])
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Firebase token removed successfully',
        ]);
    }
}
