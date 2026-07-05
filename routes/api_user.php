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

Route::get('/app-pages/{appType}/{pageType}', [AppPageApiController::class, 'show']);
// Public authentication routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/client-login', [UserController::class, 'client_login']);


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



 



Route::middleware('auth:api_user')->group(function () {

  Route::patch('/update-location', [UserController::class, 'update_location']);
    Route::get('/rates', [RateController::class, 'getRates']);
    Route::post('/kitchen-rate/{order}', [RateController::class, 'kitchenRate']);
    Route::post('/user-rate/{order}', [RateController::class, 'userRate']);
    Route::get('/{kitchen}/rates', [RateController::class, 'kitchenRates']);
  Route::get('/offers', [OfferController::class, 'kitchenOffers']);

  Route::patch('/meals/{meal}/quantity', [MealController::class, 'updateQuantity']);


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
    Route::post('/orders', [OrdersController::class, 'store']);

    Route::get('/orders/{order}', [OrdersController::class, 'show']);
    Route::patch('/orders/{order}', [OrdersController::class, 'update']);
    Route::delete('/orders/{order}', [OrdersController::class, 'destroy']);
    Route::patch('/orders/{order}/change-status', [OrdersController::class, 'accept_order']);
    Route::patch('/orders/{order}/deliver', [OrdersController::class, 'deliver_order']);
    Route::post('/order/{order}/cancel', [OrdersController::class, 'cancel_order']);

    // client address
    Route::get('/client-address', [UserAddressController::class, 'index']);
    Route::post('/create-client-address', [UserAddressController::class, 'store']);
    Route::delete('/delete-client-address/{address}', [UserAddressController::class, 'destroy']);
    Route::patch('/set-default-client-address/{address}', [UserAddressController::class, 'set_default_address']);


    // client app home
    Route::get('/client-home', [ClientController::class, 'ClientHome']);
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
    Route::get('/carusel', [CaruselController::class, 'index']);
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
