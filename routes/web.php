<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\client\ClientController;
use App\Http\Controllers\GovernmentController;
use App\Http\Controllers\KitchenProfileController;
use App\Http\Controllers\MealController;
use App\Http\Controllers\orders\OrdersController;
use Illuminate\Http\Request;
use App\Http\Controllers\WorkingDayController;
use App\Http\Middleware\EnsureProfileAccepted;
use App\Http\Controllers\KitchenAppHomeController;
use App\Http\Controllers\client\UserAddressController;
use App\Http\Controllers\client\CaruselController;
use App\Http\Controllers\rate\RateController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DashboardInsightsController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Middleware\Admin;
use App\Http\Middleware\EnsureGovernrateArea;
use App\Http\Controllers\Admin\LevelController;

Route::get('/test', function () {
    return view('welcome');
});

Route::get('/kitchen-realtime', function () {
    return view('kitchen');
});


Route::get('/', [UserController::class, 'showAdminLogin'])
    ->name('admin.login');                                                  ////done

Route::post('/', [UserController::class, 'adminLogin'])
    ->name('admin.login.submit');                                         ///done



Route::middleware(['auth:web', 'admin'])->group(function () {

    Route::post('/admin/logout', [UserController::class, 'adminLogout'])
        ->name('admin.logout');                                                        ////done

    Route::get('/admin/clients', [ClientController::class, 'all_clients'])            ////done
        ->name('admin.clients.index');

    Route::get('/admin/clients/{user}', [ClientController::class, 'client_profile'])           ////done
        ->name('admin.clients.show');


    Route::get('/admin/dashboard', [DashboardInsightsController::class, 'index'])
        ->name('admin.dashboard');                                                                  ///done


    Route::get('/admin/meals', [MealController::class, 'allMeals'])
        ->name('admin.meals.index');                                                  //done

    Route::post('/admin/meals/{meal}/approve', [MealController::class, 'approveMeal'])
        ->name('admin.meals.approve');                                                        //////done


    Route::get('/admin/categories', [CategoryController::class, 'GetAll'])
        ->name('admin.categories.index');                                             ///done

    Route::post('/admin/categories', [CategoryController::class, 'store'])
        ->name('admin.categories.store');                                            ///done

    Route::post('/admin/categories/{category}/delete', [CategoryController::class, 'destroy'])
        ->name('admin.categories.destroy');                                                          //done



    Route::get('/admin/kitchens', [KitchenProfileController::class, 'kitchens'])
        ->name('admin.kitchens.index');

    Route::get('/admin/kitchens/{profile}', [KitchenProfileController::class, 'kitchen_show'])
        ->name('admin.kitchens.show');

    Route::post('/admin/kitchens/{user}/generate-code', [KitchenProfileController::class, 'generate_kitchen_user_forget_password_code'])
        ->name('admin.kitchens.generate-code');

    Route::get('/admin/kitchens/profile/{kitchen}/meals', [KitchenProfileController::class, 'meals'])
        ->name('admin.kitchens.meals');

    Route::post('/admin/kitchens/{user}/active', [UserController::class, 'active'])
        ->name('admin.kitchens.active');

    Route::post('/admin/kitchens/{profile}/status', [KitchenProfileController::class, 'active'])
        ->name('admin.kitchens.status');

    Route::post('/admin/kitchens/{kitchen}/star', [KitchenProfileController::class, 'add_star'])
        ->name('admin.kitchens.star');



    Route::get('/admin/wallet/debit-requests', [WalletController::class, 'all_debit_requests'])
        ->name('admin.wallet.debit-requests');

    Route::post('/admin/wallet/debit-requests/{debit_request}/status', [WalletController::class, 'approve_debit_request'])
        ->name('admin.wallet.debit-requests.status');

    Route::get('/admin/kitchens/{kitchen}/wallet-transactions', [WalletController::class, 'wallet_transactions_for_kitchen'])
        ->name('admin.kitchens.wallet-transactions');



    Route::get('/admin/workdays', [WorkingDayController::class, 'GetAll'])
        ->name('admin.workdays.index');

    Route::post('/admin/workdays', [WorkingDayController::class, 'store'])
        ->name('admin.workdays.store');

    Route::post('/admin/workdays/{workday}/delete', [WorkingDayController::class, 'destroy'])
        ->name('admin.workdays.delete');

    Route::get('/admin/sliders', [CaruselController::class, 'GetAll'])
        ->name('admin.sliders.index');

    Route::post('/admin/sliders', [CaruselController::class, 'store'])
        ->name('admin.sliders.store');

    Route::post('/admin/sliders/{carusel}/delete', [CaruselController::class, 'destroy'])
        ->name('admin.sliders.delete');

    Route::get('/admin/kitchens/{kitchen}/meals', [KitchenProfileController::class, 'meals'])
        ->name('admin.kitchens.meals');

    // Route::get('/client/{user}', [ClientController::class, 'client_profile']);
    Route::get('/dash-kitchen-wallet/{kitchen}', [WalletController::class, 'wallet_transactions_for_kitchen']);
    Route::get('/all-debit-requests', [WalletController::class, 'all_debit_requests']);
    Route::patch('/approve-debit-request/{debit_request}', [WalletController::class, 'approve_debit_request']);


    // carusel
    Route::get('/carusel', [CaruselController::class, 'index']);
    Route::post('/create-carusel', [CaruselController::class, 'store']);
    Route::delete('/carusel/{carusel}/delete', [CaruselController::class, 'destroy']);

    // kitchen
    Route::post('/dashboard-generate-kitchen-user-forget-password-code/{user}', [UserController::class, 'generate_kitchen_user_forget_password_code']);
    Route::get('/kitchen-profile/{profile}', [KitchenProfileController::class, 'show']);
    Route::patch('/active-kitchen-profile/{profile}', [KitchenProfileController::class, 'active']);
    Route::patch('/add-star-to-kitchen-profile/{kitchen}', [KitchenProfileController::class, 'add_star']);
    Route::get('/kitchen-meals/{kitchen}', [KitchenProfileController::class, 'meals'])->middleware(EnsureProfileAccepted::class);
    // account
    Route::patch('/active-kitchen-account/{user}', [UserController::class, 'active']);
    Route::get('/kitchen-accounts', [UserController::class, 'kitchen_users']);
    // meals
    // Route::post('/approve-meal/{meal}', [MealController::class, 'approveMeal']);
    // Route::get('/all-meals', [MealController::class, 'allMeals']);


    Route::get('/governments', [GovernmentController::class, 'index'])
        ->name('admin.governments.index');

    Route::delete(
        '/governments/{government}',
        [GovernmentController::class, 'destroy']
    )->name('admin.governments.destroy');

    Route::post('/governments', [GovernmentController::class, 'store'])
        ->name('admin.governments.store');


    Route::get('/areas', [AreaController::class, 'index'])
        ->name('admin.areas.index');

    Route::get('/shifts', [ShiftController::class, 'index'])
        ->name('admin.shifts.index');

    Route::post('/shifts/store', [ShiftController::class, 'store'])
        ->name('admin.shifts.store');

    Route::post('/shifts/delete/{id}', [ShiftController::class, 'delete'])
        ->name('admin.shifts.delete');

    Route::get('/admin/delivery', [DeliveryController::class, 'pending'])->name('admin.delivery.pending');
    Route::post('/{id}/accept', [DeliveryController::class, 'accept']);
    Route::post('/{id}/reject', [DeliveryController::class, 'reject']);


    Route::prefix('admin/settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/update', [SettingsController::class, 'update'])->name('admin.settings.update');

        Route::prefix('admin')
            ->name('admin.')
            ->group(function () {

                Route::resource('levels', LevelController::class);
            });
    });
});
