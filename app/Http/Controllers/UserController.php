<?php

namespace App\Http\Controllers;

use App\Events\UserRegister;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
     private function generateDeliveryCode(): string
{
    $lastDelivery = User::whereNotNull('code')
        ->orderByDesc('id')
        ->first();

    $nextNumber = 1;

    if ($lastDelivery && $lastDelivery->code) {
        $nextNumber = ((int) preg_replace('/[^0-9]/', '', $lastDelivery->code)) + 1;
    }

    return 'U' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
}
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
             'code' => $this->generateDeliveryCode(),
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users')->where(function ($query) use ($request) {
                    return $query->where('role', $request->role);
                })
            ],
            'government_id' => 'nullable|exists:governments,id',
            'area_id' => 'nullable|exists:areas,id',
            'role' => 'required|string|max:255|in:admin,client,kitchen,delivery',
            'password' => 'required|string|min:8|confirmed',
              'referral_code' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'government_id' => $validated['government_id'] ?? null,
            'area_id' => $validated['area_id'] ?? null,
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
             'referral_code' => $validated['referral_code'] ?? null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // check if user have profile
        if ($user->profile) {
            $user->have_profile = true;
        } else {
            $user->have_profile = false;
        }

        // event(new UserRegister($user)) ;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }


    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->where('role', 'kitchen')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens (optional - for single device login)
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;


        // check if user have profile
        if ($user->profile) {
            $user->have_profile = true;
        } else {
            $user->have_profile = false;
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function client_login(Request $request) {
        $request->validate([
            'phone' => 'required',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->where('role', 'client')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens (optional - for single device login)
        // $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function showAdminLogin()
{
    if (Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    return view('admin.auth.login');
}

public function adminLogin(Request $request)
{
    $validated = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string', 'min:6'],
    ]);

    $user = User::where('email', $validated['email'])
        ->where('role', 'admin')
        ->first();

    if (!$user || !Hash::check($validated['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    Auth::guard('web')->login($user, $request->boolean('remember'));

    $request->session()->regenerate();
    $request->session()->forget('url.intended');

    return redirect()->route('admin.dashboard');
}

    public function kitchen_users(Request $request)
    {

        $search = $request->query('search');

        if ($search) {
            $users = User::where('role',  'kitchen')->whereLike('name', '%' . $search . '%')->get();
        } else {
            $users = User::where('role',  'kitchen')->get();
        }

        return UserResource::collection($users);
    }


    // public function active(Request $request, User $user)
    // {
    //     $user->update([
    //         'status' => $request['status']
    //     ]);

    //     return response()->json([
    //         'message' => 'account updated',
    //         'user' => $user
    //     ], 201);
    // }


    public function active(Request $request, User $user)
{
    $validated = $request->validate([
        'status' => 'required|in:active,not_active'
    ]);

    $user->update([
        'status' => $validated['status']
    ]);

    return redirect()
        ->back()
        ->with('success', 'تم تحديث حالة الحساب بنجاح');
}

    public function kitchen_user_forget_password(Request $request)
    {
        // $user = $request->user();

        // if($user->role !== 'kitchen') {
        //     return response()->json([
        //         'message' => 'You are not authorized to access this resource',
        //     ], 401);
        // }

        $validated = $request->validate([
            'password' => 'required|string|confirmed',
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $validated['phone'])->where('role', 'kitchen')->first();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ], 201);
    }

    public function verifiy_kitchen_user_forget_password(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $validated['phone'])->where('role', 'kitchen')->first();

        if (!$user || $user->code !== $validated['code'] || $user->code_expires_at < now()) {
            return response()->json([
                'message' => 'Invalid code',
            ], 404);
        }

        $user->update([
            'code' => null,
            'code_expires_at' => null,
            'verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Code verified successfully',
            'user' => $user,
        ], 201);
    }

    public function generate_kitchen_user_forget_password_code(Request $request, User $user)
    {
        $admin = $request->user();

        if ($admin->role !== 'admin') {
            return response()->json([
                'message' => 'You are not authorized to access this resource',
            ], 401);
        }

        $code = rand(100000, 999999);

        $user->where('id', $user->id)->update([
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10),
        ]);

        return response()->json([
            'message' => 'Code generated successfully',
            'code' => $code,
        ], 201);
    }

    public function update_location(Request $request) {
        
        // if kitchen
        if($request->user()->role === 'kitchen') {

            $validated = $request->validate([
                'government_id' => 'required|exists:governments,id',
                'area_id' => 'required|exists:areas,id',
            ]);

            $request->user()->update([
                'government_id' => $validated['government_id'],
                'area_id' => $validated['area_id'],
            ]);
            $request->user()->profile()->update([
                'government_id' => $validated['government_id'],
                'area_id' => $validated['area_id'],
            ]);
        } else if($request->user()->role === 'client') {
            // update just null area and government in user address (default address)
            $validated = $request->validate([
                'government_id' => 'required|exists:governments,id',
                'area_id' => 'required|exists:areas,id',
                'full_address' => 'required|string',
                'location_link' => 'nullable|string',
                'phone' => 'required|string|max:11'
            ]);

            $request->user()->address()->where('is_default', true)->update([
                'government_id' => $validated['government_id'],
                'area_id' => $validated['area_id'],
                'full_address' => $validated['full_address'],
                'location_link' => $validated['location_link'],
                'phone' => $validated['phone'],
            ]);
        }

        return response()->json([
            'message' => 'your location updated successfully',
        ], 201);
    }


    public function adminLogout(Request $request)
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login');
}
}
