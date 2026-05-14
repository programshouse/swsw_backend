<?php

namespace App\services;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\WalletDebitRequest;
use App\Models\KitchenProfile;

class WalletService
{

    public function credit(Order $order, Request $request)
    {

        // get user from kitchen profile
        $user = KitchenProfile::find($order->kitchen_id)->user()->first();

        // get wallet from user
        $user_wallet = Wallet::where('owner_id', $user->id)->first();

        $app_fees = 10;

        $amount = $order->total - $app_fees;

        WalletTransaction::create([
            'user_id' => $request->user()->id,
            'wallet_id' => $user_wallet->id,
            'order_id' => $order->id,
            'type' => 'credit',
            'amount' => $amount
        ]);

        Wallet::find($user_wallet->id)->update([
            'available_amount' => $user_wallet->available_amount + $amount
        ]);

    }


    public function debit(Request $request , WalletDebitRequest $debit_request)
    {
        $user_wallet = $debit_request->wallet()->first();
        $user = $debit_request->user()->first();

        WalletTransaction::create([
            'wallet_id' => $user_wallet->id,
            'order_id' => null,
            'wallet_debit_request_id' => $debit_request->id,
            'type' => 'debit',
            'amount' => $debit_request->amount,
            'user_id' => $request->user()->id
        ]);

        Wallet::find($user_wallet->id)->update([
            'available_amount' => $user_wallet->available_amount - $debit_request->amount,
        ]);


    }
}
