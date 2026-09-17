<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\client\ClientController;
use App\Http\Controllers\GovernmentController;
use App\Http\Controllers\KitchenProfileController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\orders\OrdersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkingDayController;
use App\Http\Middleware\EnsureProfileAccepted;
use App\Http\Controllers\KitchenAppHomeController;
use App\Http\Controllers\client\UserAddressController;
use App\Http\Controllers\client\CaruselController;
use App\Http\Controllers\rate\RateController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DashboardInsightsController;
use App\Http\Controllers\EmailOtpPasswordController;
use App\Http\Controllers\SettingsApiController;
use App\Http\Controllers\OfferController;
use App\Http\Middleware\Admin;
use App\Http\Middleware\EnsureGovernrateArea;
use App\Http\Controllers\Delivery\AuthController;
use App\Http\Controllers\Delivery\RateStoreController;
use App\Http\Controllers\AppPageApiController;
use App\Http\Controllers\Admin\AppPageController;
use App\Http\Controllers\Admin\KitchenPackageController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\FirebaseTokenController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderPaymentController;
use App\Http\Controllers\KashierWebhookController;
use App\Http\Controllers\KitchenSubscriptionController;


Route::get('/app-pages', [AppPageApiController::class, 'show']);


Route::post('/kashier/test-signature', function (
    \Illuminate\Http\Request $request
) {
    $data = $request->input('data', []);

    $signatureKeys = $data['signatureKeys'] ?? [];

    sort($signatureKeys, SORT_STRING);

    $parts = [];

    foreach ($signatureKeys as $key) {
        if (!array_key_exists($key, $data)) {
            return response()->json([
                'status' => false,
                'message' => "Missing key: {$key}",
            ], 422);
        }

        $value = $data[$key];

        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        } elseif ($value === null) {
            $value = '';
        } elseif (is_array($value) || is_object($value)) {
            $value = json_encode(
                $value,
                JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
            );
        } else {
            $value = (string) $value;
        }

        $parts[] = $key . '=' . rawurlencode($value);
    }

    $signaturePayload = implode('&', $parts);

    $apiKey = trim(
        (string) config('services.kashier.api_key')
    );

    return response()->json([
        'signature_payload' => $signaturePayload,
        'signature' => hash_hmac(
            'sha256',
            $signaturePayload,
            $apiKey
        ),
    ]);
});

Route::get('/firebase-test', function () {
    try {
        new \App\services\FirebaseNotificationService();

        return 'Firebase OK';
    } catch (\Throwable $e) {
        return $e->getMessage();
    }
});

Route::post('/kashier/webhook', [KashierWebhookController::class, 'handle'])->name('kashier.webhook');


// Public authentication routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/client-login', [UserController::class, 'client_login']);
Route::get('/companies', [CompanyController::class, 'companies']);


Route::post('password/send-otp', [EmailOtpPasswordController::class, 'sendOtp']);
Route::post('password/verify-otp', [EmailOtpPasswordController::class, 'verifyOtp']);
Route::post('password/reset', [EmailOtpPasswordController::class, 'resetPassword']);

Route::get('/settings', [SettingsApiController::class, 'index']);



Route::post('/verifiy-kitchen-user-forget-password', [UserController::class, 'verifiy_kitchen_user_forget_password']);
Route::post('/kitchen-user-forget-password', [UserController::class, 'kitchen_user_forget_password']);
Route::get('/categories', [CategoryController::class, 'index']);


// government
Route::get('/governments', [GovernmentController::class, 'appIndex']);

Route::get('/areas', [AreaController::class, 'appIndex']);

Route::get('/workdays', [WorkingDayController::class, 'index']);



Route::get('/kitchen/packages', [KitchenSubscriptionController::class, 'packages']);


Route::middleware('auth:api_user')->group(function () {

    Route::post('/logout', [UserController::class, 'logout']);
 


    Route::get('/kitchen/points', [KitchenProfileController::class, 'myPo-ints']);
    Route::get('/kitchen/subscription', [KitchenSubscriptionController::class, 'current']);
    Route::post('/kitchen/packages/{package}/subscribe', [KitchenSubscriptionController::class, 'subscribe']);
    Route::post('/kitchen/subscription-payments/{payment}/verify', [KitchenSubscriptionController::class, 'verifySubscriptionPayment'])->middleware('throttle:10,1');
    Route::get('/kitchen/subscription-payments/{payment}/status', [KitchenSubscriptionController::class, 'subscriptionPaymentStatus']);

    Route::post(
        '/wallet/debit-requests/{debitRequest}/execute',
        [
            WalletController::class,
            'executeApprovedWithdrawal',
        ]
    );

    Route::post(
        '/wallet/debit-requests/{debitRequest}/sync',
        [
            WalletController::class,
            'syncApprovedWithdrawal',
        ]
    );

    Route::post(
        '/wallet/debit-requests/{debitRequest}/execute',
        [
            WalletController::class,
            'executeApprovedWithdrawal',
        ]
    )->name('wallet.debit-requests.execute');

    Route::get(
        '/wallet/summary',
        [
            WalletController::class,
            'myWalletWithDebitRequests',
        ]
    );


    Route::get(
        '/wallet/debit-requests/{debitRequest}/status',
        [
            WalletController::class,
            'withdrawalRequestStatus',
        ]
    );


    Route::patch('/update-location', [UserController::class, 'update_location']);
    Route::get('/rates', [RateController::class, 'getRates']);
    Route::post('/kitchen-rate/{order}', [RateController::class, 'kitchenRate']);
    Route::post('/user-rate/{order}', [RateController::class, 'userRate']);
    Route::get('/{kitchen}/rates', [RateController::class, 'kitchenRates']);
    Route::get('/offers', [OfferController::class, 'kitchenOffers']);

    Route::get('/{order}/approval-status', [OrderSController::class, 'orderApprovalStatus']);

    Route::patch('/meals/{meal}/quantity', [MealController::class, 'updateQuantity']);
    Route::patch('/meals/{meal}/preparation-time', [MealController::class, 'updatepreparingTime']);

    Route::get('/kitchen/packages', [KitchenPackageController::class, 'packages']);

    Route::get('/my-points', [OfferController::class, 'myPoints']); //////

    Route::post('/firebase-token', [FirebaseTokenController::class, 'store']);

    Route::post('/firebase-token/language', [FirebaseTokenController::class, 'updateLanguage']);

    Route::delete('/firebase-token', [FirebaseTokenController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::get('/payment-status/{order}', [OrdersController::class, 'paymentStatus']);

    Route::middleware(EnsureGovernrateArea::class)->group(function () {
        // kitchen
        Route::post('/kitchen-profile', [KitchenProfileController::class, 'store']);
        Route::get('/my-kitchen-profile', [KitchenProfileController::class, 'me'])->middleware(EnsureProfileAccepted::class);
        Route::patch('/open-status-kitchen-profile', [KitchenProfileController::class, 'open_status']);
        Route::get('/kitchen-app-home', [KitchenAppHomeController::class, 'index']);

        // meals
        Route::post('/create-meal', [MealController::class, 'store'])->middleware(EnsureProfileAccepted::class);
        Route::delete('/meal/{meal}/delete', [MealController::class, 'destroy']);
        Route::get('/my-meals', [MealController::class, 'my_meals'])->middleware(EnsureProfileAccepted::class);
        Route::patch('/meal-availability/{meal}', [MealController::class, 'switchAvailability']);

        // orders
        Route::get('/kitchen-orders', [OrdersController::class, 'kitchen_orders']);
        Route::get('/client-orders', [OrdersController::class, 'client_orders']);

        Route::get('/orders', [OrdersController::class, 'index']);
        Route::post('/orders', [OrdersController::class, 'store']);                          ///notify
        Route::post('/orders/{order}/payment-method', [ OrdersController::class,'selectPaymentMethod', ]);

        Route::post('/orders/{order}/apply-cash-code',[OrdersController::class,'applyCashCode',]);
        
        Route::get('/orders/{order}', [OrdersController::class, 'show']);
        Route::patch('/orders/{order}', [OrdersController::class, 'update']);
        Route::delete('/orders/{order}', [OrdersController::class, 'destroy']);
        Route::patch('/orders/{order}/change-status', [OrdersController::class, 'accept_order']);      ///notify
        Route::patch('/orders/{order}/deliver', [OrdersController::class, 'deliver_order']);
        Route::post('/order/{order}/cancel', [OrdersController::class, 'cancel_order']);                    //notify

        // client address
        Route::get('/client-address', [UserAddressController::class, 'index']);
        Route::post('/create-client-address', [UserAddressController::class, 'store']);
        Route::delete('/delete-client-address/{address}', [UserAddressController::class, 'destroy']);
        Route::patch('/set-default-client-address/{address}', [UserAddressController::class, 'set_default_address']);


        // client app home
        Route::get('/client-home', [ClientController::class, 'ClientHome']);
        Route::get('/company-kitchens', [ClientController::class, 'companyKitchens']);

        Route::get('/kitchens-by-area', [ClientController::class, 'kitchensByArea']);
        Route::get('/client-kitchen-details/{kitchen}', [ClientController::class, 'kitchen_details']);
        Route::get('/client-meal-by-category/{category}', [ClientController::class, 'meals']);
        Route::get('/client-my-profile', [ClientController::class, 'client_my_profile']);

        // client search
        Route::get('/meal-search', [ClientController::class, 'meals_list']);
        Route::get('/kitchen-search', [ClientController::class, 'kitchens_list']);


        // wallet
        Route::get('/my-wallet', [WalletController::class, 'my_wallet']);
        Route::post('/create-debit-request', [WalletController::class, 'create_debit_request']);
        Route::get('/my-debit-requests', [WalletController::class, 'my_debit_requests']);






        ///done


        Route::get('/kitchens/{kitchen}/rates', [RateController::class, 'kitchenRates']);
        Route::get('/carusel', [CaruselController::class, 'index']);




        Route::get('/orders/{order}/payment-options', [OrderPaymentController::class, 'options']);
        Route::post('/orders/{order}/payments/kashier', [OrderPaymentController::class, 'createKashierPayment']);
        Route::get('kashier/{order}/methods', [OrderPaymentController::class, 'kashierMethods']);

        Route::get('/payments/{payment}/status', [OrderPaymentController::class, 'status']);
        Route::post('/payments/{payment}/verify', [OrderPaymentController::class, 'verify'])->middleware('throttle:10,1');
        Route::post('/orders/{order}/payments/cash', [OrderPaymentController::class, 'selectCash']);


        // rates
    });

    Route::middleware(['auth:web', 'admin'])->group(function () {
        // dashboard
        // Route::get('/all-clients', [ClientController::class, 'all_clients']);
        //  Route::get('/dashboard-insights', [DashboardInsightsController::class, 'index']);
        // Route::get('/client/{user}', [ClientController::class, 'client_profile']);
        // Route::get('/dash-kitchen-wallet/{kitchen}', [WalletController::class, 'wallet_transactions_for_kitchen']);
        Route::get('/all-debit-requests', [WalletController::class, 'all_debit_requests']);
        // Route::patch('/approve-debit-request/{debit_request}', [WalletController::class, 'approve_debit_request']);


        // // carusel

        // Route::post('/create-carusel', [CaruselController::class, 'store']);
        // Route::delete('/carusel/{carusel}/delete', [CaruselController::class, 'destroy']);

        // // kitchen
        //  Route::post('/dashboard-generate-kitchen-user-forget-password-code/{user}', [UserController::class, 'generate_kitchen_user_forget_password_code']);
        //  Route::get('/kitchen-profile/{profile}', [KitchenProfileController::class, 'show']);
        //  Route::patch('/active-kitchen-profile/{profile}', [KitchenProfileController::class, 'active']);
        //    Route::patch('/add-star-to-kitchen-profile/{kitchen}', [KitchenProfileController::class, 'add_star']);
        //  Route::get('/kitchen-meals/{kitchen}', [KitchenProfileController::class, 'meals'])->middleware(EnsureProfileAccepted::class);
        // // account
        //  Route::patch('/active-kitchen-account/{user}', [UserController::class, 'active']);
        Route::get('/kitchen-accounts', [UserController::class, 'kitchen_users']);
        // // meals
        // Route::post('/approve-meal/{meal}', [MealController::class, 'approveMeal']);
        // Route::get('/all-meals', [MealController::class, 'allMeals']);
    });
});
