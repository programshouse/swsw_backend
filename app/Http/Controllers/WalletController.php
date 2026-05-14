<?php

namespace App\Http\Controllers;

use App\Models\KitchenProfile;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletDebitRequest;
use App\services\WalletService;
use Illuminate\Support\Facades\Hash;

class WalletController extends Controller
{
    function my_wallet(Request $request)
    {
        $user = $request->user();
        $wallet = $user->wallet()->first();

        return response()->json([
            'wallet' => $wallet
        ], 200);
    }


    function create_debit_request(Request $request)
    {

        $user = $request->user();
        $wallet = $user->wallet()->first();

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'password' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'payment_method' => 'required|in:instapay,vodafone_cash,etisalat_cash,orange_cash,bank_transfer',
        ]);

        if (!Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid password',
            ], 401);
        }

        $debit_request = WalletDebitRequest::create([
            'wallet_id' => $wallet->id,
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
            'phone' => $validated['phone'],
            'payment_method' => $validated['payment_method'],
        ]);

        return response()->json([
            'message' => 'Debit request created successfully',
            'debit_request' => $debit_request
        ], 200);
    }

    function my_debit_requests(Request $request)
    {
        $user = $request->user();
        $debit_requests = WalletDebitRequest::where('user_id', $user->id)->get();
        return response()->json([
            'debit_requests' => $debit_requests
        ], 200);
    }

    // function approve_debit_request(Request $request, WalletDebitRequest $debit_request , WalletService $walletService) {
    //     $validated = $request->validate([
    //         'status' => 'required|in:approved,rejected'
    //     ]);

    //     $debit_request->update([
    //         'status' => $validated['status']
    //     ]);
    //     if ($validated['status'] == 'approved') {
    //         $walletService->debit($request, $debit_request);
    //     }

    //     return response()->json([
    //         'message' => 'Debit request ' . $validated['status'] . ' successfully',
    //         'debit_request' => $debit_request
    //     ], 200);
    // }

    // function all_debit_requests(Request $request) {
    //     $debit_requests = WalletDebitRequest::with('wallet', 'user' , 'user.profile')->get();

    //     return response()->json([
    //         'debit_requests' => $debit_requests
    //     ], 200);
    // }

    // function wallet_transactions_for_kitchen(Request $request , User $kitchen) {
    //     $wallet_owner = $kitchen ;

    //     $kitchen_wallet = $wallet_owner->wallet;
    //     $kitchen_wallet->load('transactions', 'transactions.order' );

    //     return response()->json([
    //         'wallet' => $kitchen_wallet ,
    //     ] , 200);
    // }



    public function wallet_transactions_for_kitchen(Request $request, User $kitchen)
    {
        if ($kitchen->role !== 'kitchen') {
            abort(404);
        }

        $wallet = $kitchen->wallet;

        if ($wallet) {
            $wallet->load(['transactions.order']);
        }

        return view('admin.wallet.kitchen-transactions', [
            'kitchen' => $kitchen,
            'wallet' => $wallet,
        ]);
    }

    public function all_debit_requests(Request $request)
    {
        $debit_requests = WalletDebitRequest::with(['wallet', 'user.profile'])
            ->latest()
            ->get();

        return view('admin.wallet.debit-requests', compact('debit_requests'));
    }

    public function approve_debit_request(Request $request, WalletDebitRequest $debit_request, WalletService $walletService)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        if ($debit_request->status !== 'pending') {
            return redirect()
                ->back()
                ->with('error', 'تم التعامل مع هذا الطلب من قبل');
        }

        $debit_request->update([
            'status' => $validated['status']
        ]);

        if ($validated['status'] === 'approved') {
            $walletService->debit($request, $debit_request);
        }

        return redirect()
            ->back()
            ->with('success', $validated['status'] === 'approved'
                ? 'تم قبول طلب السحب بنجاح'
                : 'تم رفض طلب السحب');
    }
}
