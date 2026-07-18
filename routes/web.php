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
use App\Http\Controllers\Admin\PendingDeliveryController;
use App\Http\Middleware\Admin;
use App\Http\Middleware\EnsureGovernrateArea;
use App\Http\Controllers\Admin\LevelController;
use App\Http\Controllers\Admin\UserRateController;
use App\Http\Controllers\Admin\OrderHistoryController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\PointController;
use App\Http\Controllers\Admin\DeliveryPointController;
use App\Http\Controllers\Admin\IssueTypeController;
use App\Http\Controllers\Admin\ReserveDeliveryController;
use App\Http\Controllers\Admin\VehicleController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\ReferralPointRuleController;
use App\Http\Controllers\Admin\AdminRateController;
use App\Http\Controllers\Admin\MealOfferController;
use App\Http\Controllers\Admin\AppPageController;
use App\Http\Controllers\Admin\DeliveryOfferController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CashCodeController;
use App\Http\Controllers\Admin\OrderPricingController;
use App\Http\Controllers\Admin\OrderFinancialReportController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\Sales\KitchenController;
use App\Http\Controllers\Admin\Sales\SalesAuthController;





Route::get('/', function () {
    return view('welcome');
});
/////////login

/*
|--------------------------------------------------------------------------
| Choose Login Type
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    if (auth('web')->check()) {
        return redirect()->route('admin.dashboard');
    }

    if (auth('sales')->check()) {
        return redirect()->route('admin.sales.kitchens.index');
    }

    return view('admin.auth.choose-login');
})->name('login');


/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest:web')->group(function () {
    Route::get('/admin/login',[UserController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [UserController::class, 'adminLogin'])->name('admin.login.submit');
});


/*
|--------------------------------------------------------------------------
| Sales Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest:sales')->group(function () {
    Route::get('/sales/login', [SalesAuthController::class, 'showLogin'])->name('sales.login');
    Route::post('/sales/login',[SalesAuthController::class, 'login'])->name('sales.login.submit');
});



Route::middleware(['auth:web', 'admin'])->group(function () {

    Route::get('/admin/dashboard', [DashboardInsightsController::class, 'index'])->name('admin.dashboard');   
    Route::post('/admin/logout', [UserController::class, 'adminLogout'])->name('admin.logout');
    Route::get('/admin/clients', [ClientController::class, 'all_clients'])->name('admin.clients.index');     //المستخدمين 
    Route::get('/admin/clients/{user}', [ClientController::class, 'client_profile'])->name('admin.clients.show');    //profile client


    Route::get('/admin/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');

    Route::post('/admin/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('admin.orders.cancel');

    Route::get('/admin/kitchens', [KitchenProfileController::class, 'kitchens'])->name('admin.kitchens.index');     //قائمة المطابخ
    Route::get('/admin/kitchens/{profile}', [KitchenProfileController::class, 'kitchen_show'])->name('admin.kitchens.show');  //تفاصيل المطبخ
    Route::post('/admin/kitchens/{user}/generate-code', [KitchenProfileController::class, 'generate_kitchen_user_forget_password_code'])->name('admin.kitchens.generate-code');
    Route::get('/admin/kitchens/profile/{kitchen}/meals', [KitchenProfileController::class, 'meals'])->name('admin.kitchens.meals');
    Route::post('/admin/kitchens/{user}/active', [UserController::class, 'active'])->name('admin.kitchens.active');
    Route::post('/admin/kitchens/{profile}/status', [KitchenProfileController::class, 'active'])->name('admin.kitchens.status');
    Route::post('/admin/kitchens/{kitchen}/star', [KitchenProfileController::class, 'add_star'])->name('admin.kitchens.star');



    Route::get('/governments', [GovernmentController::class, 'index'])->name('admin.governments.index');
    Route::post('/governments/{government}/toggle-status', [GovernmentController::class, 'toggleStatus'])->name('admin.governments.toggle-status');
    Route::delete('/governments/{government}', [GovernmentController::class, 'destroy'])->name('admin.governments.destroy');
    Route::post('/governments', [GovernmentController::class, 'store'])->name('admin.governments.store');
    Route::get('/areas', [AreaController::class, 'index'])->name('admin.areas.index');
    Route::post('/areas/store', [AreaController::class, 'store'])->name('admin.areas.store');
    Route::post('/areas/{area}/location', [AreaController::class, 'updateLocation'])->name('admin.areas.update-location');
    Route::post('/admin/areas/{area}/delete', [AreaController::class, 'destroy'])->name('admin.areas.destroy');
    Route::post('/areas/{area}/toggle-status', [AreaController::class, 'toggleStatus'])->name('admin.areas.toggle-status');

    ////انواع المشاكل
    Route::get('/admin/issue-types', [IssueTypeController::class, 'index'])->name('admin.issue-types.index');
    Route::post('/admin/issue-types', [IssueTypeController::class, 'store'])->name('admin.issue-types.store');
    Route::put('/admin/issue-types/{issueType}', [IssueTypeController::class, 'update'])->name('admin.issue-types.update');
    Route::delete('/admin/issue-types/{issueType}', [IssueTypeController::class, 'destroy'])->name('admin.issue-types.destroy');
    ////////المشاكل   
    Route::get('/tickets', [TicketController::class, 'index'])->name('admin.tickets.index');
    Route::delete('/admin/tickets/{ticket}/delete', [TicketController::class, 'destroy'])->name('admin.tickets.destroy');

    /////shifts
    Route::get('/shifts', [ShiftController::class, 'index'])->name('admin.shifts.index');
    Route::post('/shifts/store', [ShiftController::class, 'store'])->name('admin.shifts.store');
    Route::post('/shifts/delete/{id}', [ShiftController::class, 'delete'])->name('admin.shifts.delete');

    ////////categories
    Route::get('/admin/categories', [CategoryController::class, 'GetAll'])->name('admin.categories.index');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
    Route::post('/admin/categories/{category}/delete', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');

    /////meals
    Route::get('/admin/meals', [MealController::class, 'allMeals'])->name('admin.meals.index');
    Route::post('/admin/meals/{meal}/approve', [MealController::class, 'approveMeal'])->name('admin.meals.approve');
    //Route::get('/admin/kitchens/{kitchen}/meals', [KitchenProfileController::class, 'meals'])->name('admin.kitchens.meals');
    //sliders
    Route::get('/admin/sliders', [CaruselController::class, 'GetAll'])->name('admin.sliders.index');
    Route::post('/admin/sliders', [CaruselController::class, 'store'])->name('admin.sliders.store');
    Route::post('/admin/sliders/{carusel}/delete', [CaruselController::class, 'destroy'])->name('admin.sliders.delete');
    ////workdays
    Route::get('/admin/workdays', [WorkingDayController::class, 'GetAll'])->name('admin.workdays.index');
    Route::post('/admin/workdays', [WorkingDayController::class, 'store'])->name('admin.workdays.store');
    Route::post('/admin/workdays/{workday}/delete', [WorkingDayController::class, 'destroy'])->name('admin.workdays.delete');
    ////العروض                                        
    Route::resource('admin/offers', OfferController::class)->names('admin.offers');
    //////////////وسائل التوصيل 
    Route::get('admin/vehicles', [VehicleController::class, 'index'])->name('admin.vehicles.index');
    Route::get('admin/vehicles/create', [VehicleController::class, 'create'])->name('admin.vehicles.create');
    Route::post('admin/vehicles/store', [VehicleController::class, 'store'])->name('admin.vehicles.store');
    Route::get('admin/vehicles/{id}/edit', [VehicleController::class, 'edit'])->name('admin.vehicles.edit');
    Route::put('admin/vehicles/{id}', [VehicleController::class, 'update'])->name('admin.vehicles.update');
    Route::delete('admin/vehicles/{id}', [VehicleController::class, 'destroy'])->name('admin.vehicles.destroy');
    //======================== اسئلة التقييمات 
    Route::prefix('admin/rates')->group(function () {
        Route::get('/', [UserRateController::class, 'index'])->name('admin.rates.index');
        Route::get('/create', [UserRateController::class, 'create'])->name('admin.rates.create');
        Route::post('/save', [UserRateController::class, 'store'])->name('admin.rates.store');
        Route::get('/{rate}/edit', [UserRateController::class, 'edit'])->name('admin.rates.edit');
        Route::post('/{rate}/update', [UserRateController::class, 'update'])->name('admin.rates.update');
        Route::DELETE('/{rate}/delete', [UserRateController::class, 'destroy'])->name('admin.rates.destroy');
    });
    //==============================  points
    Route::prefix('points')->group(function () {

        Route::get('/', [PointController::class, 'index'])->name('admin.points.index');
        Route::get('/create', [PointController::class, 'create'])->name('admin.points.create');
        Route::post('/save', [PointController::class, 'store'])->name('admin.points.store');
        Route::get('/{point}/edit', [PointController::class, 'edit'])->name('admin.points.edit');
        Route::put('/{point}/update', [PointController::class, 'update'])->name('admin.points.update');
        Route::post('/{point}/delete', [PointController::class, 'destroy'])->name('admin.points.destroy');
    });
    ///=====================================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('levels', LevelController::class);
    });



    Route::prefix('delivery-offer-requests')->name('delivery-offer-requests.')->group(function () {
        Route::get('/', [DeliveryOfferController::class, 'index'])->name('index');
        Route::post('{requestOffer}/approve', [DeliveryOfferController::class, 'approve'])->name('approve');
        Route::post('{requestOffer}/reject', [DeliveryOfferController::class, 'reject'])->name('reject');
    });

    Route::resource('app-pages', AppPageController::class);

    Route::get('/admin/wallet/debit-requests', [WalletController::class, 'all_debit_requests'])->name('admin.wallet.debit-requests');
    Route::post('/admin/wallet/debit-requests/{debit_request}/status', [WalletController::class, 'approve_debit_request'])->name('admin.wallet.debit-requests.status');
    Route::get('/admin/kitchens/{kitchen}/wallet-transactions', [WalletController::class, 'wallet_transactions_for_kitchen'])->name('admin.kitchens.wallet-transactions');

    // Route::get('/client/{user}', [ClientController::class, 'client_profile']);
    Route::get('/dash-kitchen-wallet/{kitchen}', [WalletController::class, 'wallet_transactions_for_kitchen']);
    Route::get('/all-debit-requests', [WalletController::class, 'all_debit_requests']);
    Route::patch('/approve-debit-request/{debit_request}', [WalletController::class, 'approve_debit_request']);


    Route::post('/meals/{meal}/update-image', [MealController::class, 'updateImage'])->name('admin.meals.update-image');

    // check order history
    Route::get('/orders', [OrderHistoryController::class, 'index'])->name('admin.orders.index');



    ///////////////////////===========================================================

    Route::prefix('admin/delivery')->group(function () {

        Route::get('/pending', [DeliveryController::class, 'pending'])->name('admin.delivery.pending');
        Route::post('/{id}/accept', [DeliveryController::class, 'accept']);
        Route::post('/{id}/reject', [DeliveryController::class, 'reject']);
        Route::get('/orders', [DeliveryController::class, 'ordersIndex'])->name('admin.deliveries.orders.index');
        Route::post('/orders/add-points', [DeliveryController::class, 'addOrderPoints'])->name('admin.delivery.orders.add-points');
        Route::post('/{id}/generate/password', [DeliveryController::class, 'generate_delivery_user_forget_password_code'])->name('admin.delivery.generate.password');
        Route::prefix('/profile')->group(function () {
            Route::get('/pending', [PendingDeliveryController::class, 'pending'])->name('admin.delivery.profile.pending');
            Route::post('/{id}/accept', [PendingDeliveryController::class, 'accept']);
            Route::post('/{id}/reject', [PendingDeliveryController::class, 'reject']);
        });
        Route::post('/{id}/break', [DeliveryController::class, 'onBreak'])->name('admin.delivery.break');
        Route::post('{id}/add-points', [DeliveryController::class, 'addPoint'])->name('admin.delivery.add.points');
        Route::get('/approved', [DeliveryController::class, 'approved'])->name('admin.delivery.approved');
        Route::post('/{id}/promotion', [DeliveryController::class, 'promotion'])->name('admin.delivery.promotion');
    });
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('reserve-deliveries', ReserveDeliveryController::class);
    });

    Route::prefix('admin/settings')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/update', [SettingsController::class, 'update'])->name('admin.settings.update');
    });

    Route::prefix('admin/delivery-points')->group(function () {
        Route::get('/', [DeliveryPointController::class, 'index'])->name('admin.delivery.points.index');
        Route::post('/{deliveryPoint}/delete', [DeliveryPointController::class, 'destroy'])->name('admin.delivery.points.destroy');
    });



    //////////////////////////////////////  =======================

    Route::get('/admin/referral-point-rules', [ReferralPointRuleController::class, 'index'])->name('admin.referral-point-rules.index');
    Route::post('/admin/referral-point-rules', [ReferralPointRuleController::class, 'store'])->name('admin.referral-point-rules.store');
    Route::delete('/admin/referral-point-rules/{rule}', [ReferralPointRuleController::class, 'destroy'])->name('admin.referral-point-rules.destroy');
    Route::get('/kitchens/{kitchen}/rate', [AdminRateController::class, 'create'])->name('admin.kitchens.rate');
    Route::post('/kitchens/{kitchen}/rate', [AdminRateController::class, 'rateKitchenAsClient'])->name('admin.kitchens.rate.store');
    Route::get('/kitchens/{kitchen}/rates', [AdminRateController::class, 'kitchenRates'])->name('admin.kitchens.rates');
    Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('meal-offers', MealOfferController::class);
        });

    Route::resource('kitchen-packages',\App\Http\Controllers\Admin\KitchenPackageController::class)->names('admin.kitchen-packages');
    Route::patch('companies/{company}/toggle-status',[CompanyController::class, 'toggleStatus'])->name('admin.companies.toggle-status');
    Route::post('/admin/clients/{id}/add-points',[ClientController::class, 'addPoint'])->name('admin.clients.add.points');

    Route::prefix('cash-codes')->name('admin.cash-codes.')->group(function () {
        Route::get('/', [CashCodeController::class, 'index'])->name('index');
        Route::get('/create', [CashCodeController::class, 'create'])->name('create');
        Route::post('/', [CashCodeController::class, 'store'])->name('store');
        Route::patch('/{cashCode}/toggle-status', [CashCodeController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{cashCode}', [CashCodeController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin')->middleware(['auth'])->name('admin.')->group(function () {
        Route::get('/order-pricing', [OrderPricingController::class, 'index'])->name('order-pricing.index');
        Route::put('/order-pricing/settings', [OrderPricingController::class, 'updateSettings'])->name('order-pricing.settings.update');
        Route::post('/order-pricing/rules', [OrderPricingController::class, 'storeRule'])->name('order-pricing.rules.store');
        Route::put('/order-pricing/rules/{rule}', [OrderPricingController::class, 'updateRule'])->name('order-pricing.rules.update');
        Route::delete('/order-pricing/rules/{rule}', [OrderPricingController::class, 'destroyRule'])->name('order-pricing.rules.destroy');
        Route::patch('/order-pricing/rules/{rule}/toggle', [OrderPricingController::class, 'toggleRule'])->name('order-pricing.rules.toggle');
    });

    Route::get('/order-financial-reports', [OrderFinancialReportController::class, 'index'])->name('admin.order-financial-reports.index');
    Route::get('/order-financial-reports/{order}', [OrderFinancialReportController::class, 'show'])->name('admin.order-financial-reports.show');
    Route::patch('sales/{sale}/toggle-status', [SaleController::class, 'toggleStatus'])->name('admin.sales.toggle-status');
    Route::resource('sales', SaleController::class)->names('admin.sales');
});


/*
|--------------------------------------------------------------------------
| Sales Authentication
|--------------------------------------------------------------------------
*/

Route::post('/sales/logout',[SalesAuthController::class, 'logout'])->middleware('auth:sales')->name('sales.logout');

/*
|--------------------------------------------------------------------------
| Sales Panel
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sales')->prefix('sales-panel')->name('admin.sales.')->group(function () {
        Route::get('governments/{government}/areas',[KitchenController::class, 'getAreas'])->name('governments.areas');
        Route::patch('kitchens/{kitchen}/toggle-status',[KitchenController::class, 'toggleStatus'])->name('kitchens.toggle-status');
        Route::resource('kitchens',KitchenController::class);
    });











// carusel
// Route::get('/carusel', [CaruselController::class, 'index']);
//  Route::post('/create-carusel', [CaruselController::class, 'store']);
//  Route::delete('/carusel/{carusel}/delete', [CaruselController::class, 'destroy']);

// kitchen
// Route::post('/dashboard-generate-kitchen-user-forget-password-code/{user}', [UserController::class, 'generate_kitchen_user_forget_password_code']);
// Route::get('/kitchen-profile/{profile}', [KitchenProfileController::class, 'show']);
// Route::patch('/active-kitchen-profile/{profile}', [KitchenProfileController::class, 'active']);
// Route::patch('/add-star-to-kitchen-profile/{kitchen}', [KitchenProfileController::class, 'add_star']);
//  Route::get('/kitchen-meals/{kitchen}', [KitchenProfileController::class, 'meals'])->middleware(EnsureProfileAccepted::class);
// account
// Route::patch('/active-kitchen-account/{user}', [UserController::class, 'active']);
Route::get('/kitchen-accounts', [UserController::class, 'kitchen_users']);
    // meals
    // Route::post('/approve-meal/{meal}', [MealController::class, 'approveMeal']);
    // Route::get('/all-meals', [MealController::class, 'allMeals']);
    //Route::delete('/meal/{meal}/delete', [MealController::class, 'destroy'])->name('admin.meals.destroy');;




    // Route::get('/tickets', [TicketController::class, 'index'])
    //     ->name('admin.tickets.index');

    // Route::delete('/admin/tickets/{ticket}/delete', [TicketController::class, 'destroy'])
    //     ->name('admin.tickets.destroy');
