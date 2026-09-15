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
use App\Http\Controllers\Admin\AdminUserController;

Route::get('/kitchen/register', [KitchenController::class, 'public_create'])->name('kitchens.public.create');
Route::post('/kitchen/register', [KitchenController::class, 'public_store'])->name('kitchens.public.store');

Route::get('/client/privacy-policy', [SettingsController::class, 'privacyPolicyclient']);
Route::get('/kitchen/privacy-policy', [SettingsController::class, 'privacyPolicykitchen']);

Route::get('/driver/privacy-policy', [SettingsController::class, 'privacyPolicydriver']);

Route::get('/client/terms', [SettingsController::class, 'terms']);
Route::get('/kitchen/terms', [SettingsController::class, 'termskitchen']);

Route::get('/client/deleteaccount', [SettingsController::class, 'deleteUserByEmailPage'])->name('admin.delete-user-email.page');
Route::delete('/client/deleteaccount', [SettingsController::class, 'deleteUserByEmail'])->name('admin.delete-user-email');

Route::get('/driver/deleteaccount', [SettingsController::class, 'deletedriverByEmailPage'])->name('admin.delete-driver-email.page');
Route::delete('/driver/deleteaccount', [SettingsController::class, 'deletedriverByEmail'])->name('admin.delete-driver-email');



Route::get('/driver/terms', [SettingsController::class, 'termsdriver']);


Route::get(
    '/kitchen/register/areas/{governmentId}',
    [KitchenController::class, 'public_areas']
)->name('kitchens.public.areas');

Route::match(
    ['get', 'post'],
    '/payment/callback',
    function () {
        return response()
            ->view(
                'payments.kashier-callback',
                [],
                200
            )
            ->header(
                'Content-Type',
                'text/html; charset=UTF-8'
            );
    }
)->name('kashier.payment.callback');

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
    Route::get('/admin/login', [UserController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [UserController::class, 'adminLogin'])->name('admin.login.submit');
});


/*
|--------------------------------------------------------------------------
| Sales Authentication
|--------------------------------------------------------------------------
*/

Route::middleware('guest:sales')->group(function () {
    Route::get('/sales/login', [SalesAuthController::class, 'showLogin'])->name('sales.login');
    Route::post('/sales/login', [SalesAuthController::class, 'login'])->name('sales.login.submit');
});



Route::middleware([
    'auth:web',
    'admin',
    'admin.active',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | إدارة الأدمنز
    |--------------------------------------------------------------------------
    |
    | متاحة للسوبر أدمن فقط، والتحقق موجود داخل AdminUserController.
    |
    */

    Route::prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::resource('admins', AdminUserController::class);
        });


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/dashboard', [
        DashboardInsightsController::class,
        'index',
    ])
        ->middleware('admin.permission:dashboard.view')
        ->name('admin.dashboard');


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/admin/logout', [
        UserController::class,
        'adminLogout',
    ])->name('admin.logout');


    /*
    |--------------------------------------------------------------------------
    | Clients
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/clients', [
        ClientController::class,
        'all_clients',
    ])
        ->middleware('admin.permission:clients.view')
        ->name('admin.clients.index');

    Route::get('/admin/clients/{user}', [
        ClientController::class,
        'client_profile',
    ])
        ->middleware('admin.permission:clients.show')
        ->name('admin.clients.show');

    Route::post('/admin/clients/{id}/add-points', [
        ClientController::class,
        'addPoint',
    ])
        ->middleware('admin.permission:clients.add_points')
        ->name('admin.clients.add.points');


    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

    Route::get('/orders', [
        OrderHistoryController::class,
        'index',
    ])
        ->middleware('admin.permission:orders.view')
        ->name('admin.orders.index');

    Route::get('/admin/orders/{order}', [
        OrderController::class,
        'show',
    ])
        ->middleware('admin.permission:orders.show')
        ->name('admin.orders.show');

    Route::post('/admin/orders/{order}/cancel', [
        OrderController::class,
        'cancel',
    ])
        ->middleware('admin.permission:orders.cancel')
        ->name('admin.orders.cancel');


    /*
    |--------------------------------------------------------------------------
    | Kitchens
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/kitchens', [
        KitchenProfileController::class,
        'kitchens',
    ])
        ->middleware('admin.permission:kitchens.view')
        ->name('admin.kitchens.index');

    Route::get('/admin/kitchens/{profile}', [
        KitchenProfileController::class,
        'kitchen_show',
    ])
        ->middleware('admin.permission:kitchens.show')
        ->name('admin.kitchens.show');

    Route::post('/admin/kitchens/{user}/generate-code', [
        KitchenProfileController::class,
        'generate_kitchen_user_forget_password_code',
    ])
        ->middleware('admin.permission:kitchens.generate_code')
        ->name('admin.kitchens.generate-code');

    Route::get('/admin/kitchens/profile/{kitchen}/meals', [
        KitchenProfileController::class,
        'meals',
    ])
        ->middleware('admin.permission:kitchens.view_meals')
        ->name('admin.kitchens.meals');

    Route::post('/admin/kitchens/{user}/active', [
        UserController::class,
        'active',
    ])
        ->middleware('admin.permission:kitchens.update_status')
        ->name('admin.kitchens.active');

    Route::post('/admin/kitchens/{profile}/status', [
        KitchenProfileController::class,
        'active',
    ])
        ->middleware('admin.permission:kitchens.update_status')
        ->name('admin.kitchens.status');

    Route::post('/admin/kitchens/{kitchen}/star', [
        KitchenProfileController::class,
        'add_star',
    ])
        ->middleware('admin.permission:kitchens.add_star')
        ->name('admin.kitchens.star');

    Route::get('/kitchens/{kitchen}/rate', [
        AdminRateController::class,
        'create',
    ])
        ->middleware('admin.permission:kitchens.rate')
        ->name('admin.kitchens.rate');

    Route::post('/kitchens/{kitchen}/rate', [
        AdminRateController::class,
        'rateKitchenAsClient',
    ])
        ->middleware('admin.permission:kitchens.rate')
        ->name('admin.kitchens.rate.store');

    Route::get('/kitchens/{kitchen}/rates', [
        AdminRateController::class,
        'kitchenRates',
    ])
        ->middleware('admin.permission:kitchens.view_rates')
        ->name('admin.kitchens.rates');


    /*
    |--------------------------------------------------------------------------
    | Governments
    |--------------------------------------------------------------------------
    */

    Route::get('/governments', [
        GovernmentController::class,
        'index',
    ])
        ->middleware('admin.permission:governments.view')
        ->name('admin.governments.index');

    Route::post('/governments', [
        GovernmentController::class,
        'store',
    ])
        ->middleware('admin.permission:governments.create')
        ->name('admin.governments.store');

    Route::post('/governments/{government}/toggle-status', [
        GovernmentController::class,
        'toggleStatus',
    ])
        ->middleware('admin.permission:governments.update')
        ->name('admin.governments.toggle-status');

    Route::delete('/governments/{government}', [
        GovernmentController::class,
        'destroy',
    ])
        ->middleware('admin.permission:governments.delete')
        ->name('admin.governments.destroy');


    /*
    |--------------------------------------------------------------------------
    | Areas
    |--------------------------------------------------------------------------
    */

    Route::get('/areas', [
        AreaController::class,
        'index',
    ])
        ->middleware('admin.permission:areas.view')
        ->name('admin.areas.index');

    Route::post('/areas/store', [
        AreaController::class,
        'store',
    ])
        ->middleware('admin.permission:areas.create')
        ->name('admin.areas.store');

    Route::post('/areas/{area}/location', [
        AreaController::class,
        'updateLocation',
    ])
        ->middleware('admin.permission:areas.update')
        ->name('admin.areas.update-location');

    Route::post('/areas/{area}/toggle-status', [
        AreaController::class,
        'toggleStatus',
    ])
        ->middleware('admin.permission:areas.update')
        ->name('admin.areas.toggle-status');

    Route::delete('/admin/areas/{area}/delete', [
        AreaController::class,
        'destroy',
    ])
        ->middleware('admin.permission:areas.delete')
        ->name('admin.areas.destroy');


    /*
    |--------------------------------------------------------------------------
    | Issue Types
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/issue-types', [
        IssueTypeController::class,
        'index',
    ])
        ->middleware('admin.permission:issue_types.view')
        ->name('admin.issue-types.index');

    Route::post('/admin/issue-types', [
        IssueTypeController::class,
        'store',
    ])
        ->middleware('admin.permission:issue_types.create')
        ->name('admin.issue-types.store');

    Route::put('/admin/issue-types/{issueType}', [
        IssueTypeController::class,
        'update',
    ])
        ->middleware('admin.permission:issue_types.update')
        ->name('admin.issue-types.update');

    Route::delete('/admin/issue-types/{issueType}', [
        IssueTypeController::class,
        'destroy',
    ])
        ->middleware('admin.permission:issue_types.delete')
        ->name('admin.issue-types.destroy');


    /*
    |--------------------------------------------------------------------------
    | Tickets
    |--------------------------------------------------------------------------
    */

    Route::get('/tickets', [
        TicketController::class,
        'index',
    ])
        ->middleware('admin.permission:tickets.view')
        ->name('admin.tickets.index');

    Route::delete('/admin/tickets/{ticket}/delete', [
        TicketController::class,
        'destroy',
    ])
        ->middleware('admin.permission:tickets.delete')
        ->name('admin.tickets.destroy');


    /*
    |--------------------------------------------------------------------------
    | Shifts
    |--------------------------------------------------------------------------
    */

    Route::get('/shifts', [
        ShiftController::class,
        'index',
    ])
        ->middleware('admin.permission:shifts.view')
        ->name('admin.shifts.index');

    Route::post('/shifts/store', [
        ShiftController::class,
        'store',
    ])
        ->middleware('admin.permission:shifts.create')
        ->name('admin.shifts.store');

    Route::post('/shifts/delete/{id}', [
        ShiftController::class,
        'delete',
    ])
        ->middleware('admin.permission:shifts.delete')
        ->name('admin.shifts.delete');


    /*
    |--------------------------------------------------------------------------
    | Categories
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/categories', [
        CategoryController::class,
        'GetAll',
    ])
        ->middleware('admin.permission:categories.view')
        ->name('admin.categories.index');

    Route::post('/admin/categories', [
        CategoryController::class,
        'store',
    ])
        ->middleware('admin.permission:categories.create')
        ->name('admin.categories.store');

    Route::post('/admin/categories/{category}/delete', [
        CategoryController::class,
        'destroy',
    ])
        ->middleware('admin.permission:categories.delete')
        ->name('admin.categories.destroy');


    /*
    |--------------------------------------------------------------------------
    | Meals
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/meals', [
        MealController::class,
        'allMeals',
    ])
        ->middleware('admin.permission:meals.view')
        ->name('admin.meals.index');

    Route::post('/admin/meals/{meal}/approve', [
        MealController::class,
        'approveMeal',
    ])
        ->middleware('admin.permission:meals.approve')
        ->name('admin.meals.approve');

    Route::post('/meals/{meal}/update-image', [
        MealController::class,
        'updateImage',
    ])
        ->middleware('admin.permission:meals.update_image')
        ->name('admin.meals.update-image');


    /*
    |--------------------------------------------------------------------------
    | Sliders
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/sliders', [
        CaruselController::class,
        'GetAll',
    ])
        ->middleware('admin.permission:sliders.view')
        ->name('admin.sliders.index');

    Route::post('/admin/sliders', [
        CaruselController::class,
        'store',
    ])
        ->middleware('admin.permission:sliders.create')
        ->name('admin.sliders.store');

    Route::post('/admin/sliders/{carusel}/delete', [
        CaruselController::class,
        'destroy',
    ])
        ->middleware('admin.permission:sliders.delete')
        ->name('admin.sliders.delete');


    /*
    |--------------------------------------------------------------------------
    | Working Days
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/workdays', [
        WorkingDayController::class,
        'GetAll',
    ])
        ->middleware('admin.permission:workdays.view')
        ->name('admin.workdays.index');

    Route::post('/admin/workdays', [
        WorkingDayController::class,
        'store',
    ])
        ->middleware('admin.permission:workdays.create')
        ->name('admin.workdays.store');

    Route::post('/admin/workdays/{workday}/delete', [
        WorkingDayController::class,
        'destroy',
    ])
        ->middleware('admin.permission:workdays.delete')
        ->name('admin.workdays.delete');


    /*
    |--------------------------------------------------------------------------
    | Offers
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/offers', [
        OfferController::class,
        'index',
    ])
        ->middleware('admin.permission:offers.view')
        ->name('admin.offers.index');

    Route::get('/admin/offers/create', [
        OfferController::class,
        'create',
    ])
        ->middleware('admin.permission:offers.create')
        ->name('admin.offers.create');

    Route::post('/admin/offers', [
        OfferController::class,
        'store',
    ])
        ->middleware('admin.permission:offers.create')
        ->name('admin.offers.store');

    Route::get('/admin/offers/{offer}', [
        OfferController::class,
        'show',
    ])
        ->middleware('admin.permission:offers.view')
        ->name('admin.offers.show');

    Route::get('/admin/offers/{offer}/edit', [
        OfferController::class,
        'edit',
    ])
        ->middleware('admin.permission:offers.update')
        ->name('admin.offers.edit');

    Route::put('/admin/offers/{offer}', [
        OfferController::class,
        'update',
    ])
        ->middleware('admin.permission:offers.update')
        ->name('admin.offers.update');

    Route::delete('/admin/offers/{offer}', [
        OfferController::class,
        'destroy',
    ])
        ->middleware('admin.permission:offers.delete')
        ->name('admin.offers.destroy');


    /*
    |--------------------------------------------------------------------------
    | Vehicles
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/vehicles', [
        VehicleController::class,
        'index',
    ])
        ->middleware('admin.permission:vehicles.view')
        ->name('admin.vehicles.index');

    Route::get('/admin/vehicles/create', [
        VehicleController::class,
        'create',
    ])
        ->middleware('admin.permission:vehicles.create')
        ->name('admin.vehicles.create');

    Route::post('/admin/vehicles/store', [
        VehicleController::class,
        'store',
    ])
        ->middleware('admin.permission:vehicles.create')
        ->name('admin.vehicles.store');

    Route::get('/admin/vehicles/{id}/edit', [
        VehicleController::class,
        'edit',
    ])
        ->middleware('admin.permission:vehicles.update')
        ->name('admin.vehicles.edit');

    Route::put('/admin/vehicles/{id}', [
        VehicleController::class,
        'update',
    ])
        ->middleware('admin.permission:vehicles.update')
        ->name('admin.vehicles.update');

    Route::delete('/admin/vehicles/{id}', [
        VehicleController::class,
        'destroy',
    ])
        ->middleware('admin.permission:vehicles.delete')
        ->name('admin.vehicles.destroy');


    /*
    |--------------------------------------------------------------------------
    | Rating Questions
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/rates')->group(function () {
        Route::get('/', [
            UserRateController::class,
            'index',
        ])
            ->middleware('admin.permission:rates.view')
            ->name('admin.rates.index');

        Route::get('/create', [
            UserRateController::class,
            'create',
        ])
            ->middleware('admin.permission:rates.create')
            ->name('admin.rates.create');

        Route::post('/save', [
            UserRateController::class,
            'store',
        ])
            ->middleware('admin.permission:rates.create')
            ->name('admin.rates.store');

        Route::get('/{rate}/edit', [
            UserRateController::class,
            'edit',
        ])
            ->middleware('admin.permission:rates.update')
            ->name('admin.rates.edit');

        Route::post('/{rate}/update', [
            UserRateController::class,
            'update',
        ])
            ->middleware('admin.permission:rates.update')
            ->name('admin.rates.update');

        Route::delete('/{rate}/delete', [
            UserRateController::class,
            'destroy',
        ])
            ->middleware('admin.permission:rates.delete')
            ->name('admin.rates.destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | Points
    |--------------------------------------------------------------------------
    */

    Route::prefix('points')->group(function () {
        Route::get('/', [
            PointController::class,
            'index',
        ])
            ->middleware('admin.permission:points.view')
            ->name('admin.points.index');

        Route::get('/create', [
            PointController::class,
            'create',
        ])
            ->middleware('admin.permission:points.create')
            ->name('admin.points.create');

        Route::post('/save', [
            PointController::class,
            'store',
        ])
            ->middleware('admin.permission:points.create')
            ->name('admin.points.store');

        Route::get('/{point}/edit', [
            PointController::class,
            'edit',
        ])
            ->middleware('admin.permission:points.update')
            ->name('admin.points.edit');

        Route::put('/{point}/update', [
            PointController::class,
            'update',
        ])
            ->middleware('admin.permission:points.update')
            ->name('admin.points.update');

        Route::post('/{point}/delete', [
            PointController::class,
            'destroy',
        ])
            ->middleware('admin.permission:points.delete')
            ->name('admin.points.destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | Levels
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/levels', [
        LevelController::class,
        'index',
    ])
        ->middleware('admin.permission:levels.view')
        ->name('admin.levels.index');

    Route::get('/admin/levels/create', [
        LevelController::class,
        'create',
    ])
        ->middleware('admin.permission:levels.create')
        ->name('admin.levels.create');

    Route::post('/admin/levels', [
        LevelController::class,
        'store',
    ])
        ->middleware('admin.permission:levels.create')
        ->name('admin.levels.store');

    Route::get('/admin/levels/{level}', [
        LevelController::class,
        'show',
    ])
        ->middleware('admin.permission:levels.view')
        ->name('admin.levels.show');

    Route::get('/admin/levels/{level}/edit', [
        LevelController::class,
        'edit',
    ])
        ->middleware('admin.permission:levels.update')
        ->name('admin.levels.edit');

    Route::put('/admin/levels/{level}', [
        LevelController::class,
        'update',
    ])
        ->middleware('admin.permission:levels.update')
        ->name('admin.levels.update');

    Route::delete('/admin/levels/{level}', [
        LevelController::class,
        'destroy',
    ])
        ->middleware('admin.permission:levels.delete')
        ->name('admin.levels.destroy');


    /*
    |--------------------------------------------------------------------------
    | Delivery Offer Requests
    |--------------------------------------------------------------------------
    */

    Route::prefix('delivery-offer-requests')
        ->name('delivery-offer-requests.')
        ->group(function () {
            Route::get('/', [
                DeliveryOfferController::class,
                'index',
            ])
                ->middleware('admin.permission:delivery_offer_requests.view')
                ->name('index');

            Route::post('/{requestOffer}/approve', [
                DeliveryOfferController::class,
                'approve',
            ])
                ->middleware('admin.permission:delivery_offer_requests.approve')
                ->name('approve');

            Route::post('/{requestOffer}/reject', [
                DeliveryOfferController::class,
                'reject',
            ])
                ->middleware('admin.permission:delivery_offer_requests.reject')
                ->name('reject');
        });


    /*
    |--------------------------------------------------------------------------
    | App Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/app-pages', [
        AppPageController::class,
        'index',
    ])
        ->middleware('admin.permission:app_pages.view')
        ->name('app-pages.index');

    Route::get('/app-pages/create', [
        AppPageController::class,
        'create',
    ])
        ->middleware('admin.permission:app_pages.create')
        ->name('app-pages.create');

    Route::post('/app-pages', [
        AppPageController::class,
        'store',
    ])
        ->middleware('admin.permission:app_pages.create')
        ->name('app-pages.store');

    Route::get('/app-pages/{app_page}', [
        AppPageController::class,
        'show',
    ])
        ->middleware('admin.permission:app_pages.view')
        ->name('app-pages.show');

    Route::get('/app-pages/{app_page}/edit', [
        AppPageController::class,
        'edit',
    ])
        ->middleware('admin.permission:app_pages.update')
        ->name('app-pages.edit');

    Route::put('/app-pages/{app_page}', [
        AppPageController::class,
        'update',
    ])
        ->middleware('admin.permission:app_pages.update')
        ->name('app-pages.update');

    Route::delete('/app-pages/{app_page}', [
        AppPageController::class,
        'destroy',
    ])
        ->middleware('admin.permission:app_pages.delete')
        ->name('app-pages.destroy');


    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */

    // Route::get('/admin/wallet/debit-requests', [
    //     WalletController::class,
    //     'all_debit_requests',
    // ])
    //     ->middleware('admin.permission:wallet.view')
    //     ->name('admin.wallet.debit-requests');

    // Route::post('/admin/wallet/debit-requests/{debit_request}/status', [
    //     WalletController::class,
    //     'approve_debit_request',
    // ])
    //     ->middleware('admin.permission:wallet.update')
    //     ->name('admin.wallet.debit-requests.status');

    // Route::get('/admin/kitchens/{kitchen}/wallet-transactions', [
    //     WalletController::class,
    //     'wallet_transactions_for_kitchen',
    // ])
    //     ->middleware('admin.permission:kitchens.view_wallet')
    //     ->name('admin.kitchens.wallet-transactions');

    // Route::get('/dash-kitchen-wallet/{kitchen}', [
    //     WalletController::class,
    //     'wallet_transactions_for_kitchen',
    // ])->middleware('admin.permission:kitchens.view_wallet');

    // Route::get('/all-debit-requests', [
    //     WalletController::class,
    //     'all_debit_requests',
    // ])->middleware('admin.permission:wallet.view');

    // Route::patch('/approve-debit-request/{debit_request}', [
    //     WalletController::class,
    //     'approve_debit_request',
    // ])->middleware('admin.permission:wallet.update');



    // Route::get(
    //     '/wallet/kashier-payout/account',
    //     [
    //         WalletController::class,
    //         'kashierPayoutAccount',
    //     ]
    // )->name('admin.wallet.kashier-payout.account');


    // Route::get(
    //     '/wallet/debit-requests',
    //     [WalletController::class, 'all_debit_requests']
    // )->name('admin.wallet.debit-requests.index');

    // Route::patch(
    //     '/wallet/debit-requests/{debitRequest}',
    //     [
    //         WalletController::class,
    //         'approve_debit_request',
    //     ]
    // )->name('wallet.debit-requests.update');




    /*
|--------------------------------------------------------------------------
| Wallet & Kashier Payout
|--------------------------------------------------------------------------
*/
Route::get(
    'wallet/debit-requests/export',
    [WalletController::class, 'export_debit_requests']
)->name('admin.wallet.debit-requests.export');
    Route::prefix('admin/wallet')
        ->name('admin.wallet.')
        ->group(function () {


            Route::post(
                '/debit-requests/{debitRequest}/sync',
                [
                    WalletController::class,
                    'syncDebitRequest',
                ]
            )
                ->middleware(
                    'admin.permission:wallet.update'
                )
                ->name(
                    'debit-requests.sync'
                );

            /*
        |--------------------------------------------------------------------------
        | Debit requests
        |--------------------------------------------------------------------------
        */

            Route::get('/debit-requests', [
                WalletController::class,
                'all_debit_requests',
            ])
                ->middleware('admin.permission:wallet.view')
                ->name('debit-requests.index');

            Route::patch('/debit-requests/{debitRequest}', [
                WalletController::class,
                'approve_debit_request',
            ])
                ->middleware('admin.permission:wallet.update')
                ->name('debit-requests.update');

            /*
        |--------------------------------------------------------------------------
        | Kashier payout account
        |--------------------------------------------------------------------------
        */

            Route::get('/kashier-payout/account', [
                WalletController::class,
                'kashierPayoutAccount',
            ])
                ->middleware('admin.permission:wallet.view')
                ->name('kashier-payout-account');

            /*
        |--------------------------------------------------------------------------
        | Kashier transfers
        |--------------------------------------------------------------------------
        */

            Route::get('/kashier-payout/transfers', [
                WalletController::class,
                'kashierTransfers',
            ])
                ->middleware('admin.permission:wallet.view')
                ->name('kashier-transfers');
        });

    /*
|--------------------------------------------------------------------------
| Kitchen wallet transactions
|--------------------------------------------------------------------------
*/

    Route::get('/admin/kitchens/{kitchen}/wallet-transactions', [
        WalletController::class,
        'wallet_transactions_for_kitchen',
    ])
        ->middleware('admin.permission:kitchens.view_wallet')
        ->name('admin.kitchens.wallet-transactions');


    /*
    |--------------------------------------------------------------------------
    | Delivery
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin/delivery')->group(function () {
        Route::get('/pending', [
            DeliveryController::class,
            'pending',
        ])
            ->middleware('admin.permission:deliveries.view')
            ->name('admin.delivery.pending');

        Route::post('/{id}/accept', [
            DeliveryController::class,
            'accept',
        ])->middleware('admin.permission:deliveries.approve');

        Route::post('/{id}/reject', [
            DeliveryController::class,
            'reject',
        ])->middleware('admin.permission:deliveries.reject');

        Route::get('/orders', [
            DeliveryController::class,
            'ordersIndex',
        ])
            ->middleware('admin.permission:deliveries.view_orders')
            ->name('admin.deliveries.orders.index');

        Route::post('/orders/add-points', [
            DeliveryController::class,
            'addOrderPoints',
        ])
            ->middleware('admin.permission:deliveries.add_points')
            ->name('admin.delivery.orders.add-points');

        Route::post('/{id}/generate/password', [
            DeliveryController::class,
            'generate_delivery_user_forget_password_code',
        ])
            ->middleware('admin.permission:deliveries.generate_code')
            ->name('admin.delivery.generate.password');

        Route::prefix('/profile')->group(function () {
            Route::get('/pending', [
                PendingDeliveryController::class,
                'pending',
            ])
                ->middleware('admin.permission:deliveries.view')
                ->name('admin.delivery.profile.pending');

            Route::post('/{id}/accept', [
                PendingDeliveryController::class,
                'accept',
            ])->middleware('admin.permission:deliveries.approve');

            Route::post('/{id}/reject', [
                PendingDeliveryController::class,
                'reject',
            ])->middleware('admin.permission:deliveries.reject');
        });
        Route::post('/delivery/{delivery}/toggle-status', [DeliveryController::class, 'toggleStatus'])
            ->name('admin.delivery.toggle.status');
        Route::post('/{id}/break', [
            DeliveryController::class,
            'onBreak',
        ])
            ->middleware('admin.permission:deliveries.break')
            ->name('admin.delivery.break');

        Route::post('/{id}/add-points', [
            DeliveryController::class,
            'addPoint',
        ])
            ->middleware('admin.permission:deliveries.add_points')
            ->name('admin.delivery.add.points');

        Route::get('/approved', [
            DeliveryController::class,
            'approved',
        ])
            ->middleware('admin.permission:deliveries.view')
            ->name('admin.delivery.approved');

        Route::post('/{id}/promotion', [
            DeliveryController::class,
            'promotion',
        ])
            ->middleware('admin.permission:deliveries.promotion')
            ->name('admin.delivery.promotion');
    });


    /*
    |--------------------------------------------------------------------------
    | Reserve Deliveries
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/reserve-deliveries', [
        ReserveDeliveryController::class,
        'index',
    ])
        ->middleware('admin.permission:reserve_deliveries.view')
        ->name('admin.reserve-deliveries.index');

    Route::get('/admin/reserve-deliveries/create', [
        ReserveDeliveryController::class,
        'create',
    ])
        ->middleware('admin.permission:reserve_deliveries.create')
        ->name('admin.reserve-deliveries.create');

    Route::post('/admin/reserve-deliveries', [
        ReserveDeliveryController::class,
        'store',
    ])
        ->middleware('admin.permission:reserve_deliveries.create')
        ->name('admin.reserve-deliveries.store');

    Route::get('/admin/reserve-deliveries/{reserve_delivery}', [
        ReserveDeliveryController::class,
        'show',
    ])
        ->middleware('admin.permission:reserve_deliveries.view')
        ->name('admin.reserve-deliveries.show');

    Route::get('/admin/reserve-deliveries/{reserve_delivery}/edit', [
        ReserveDeliveryController::class,
        'edit',
    ])
        ->middleware('admin.permission:reserve_deliveries.update')
        ->name('admin.reserve-deliveries.edit');

    Route::put('/admin/reserve-deliveries/{reserve_delivery}', [
        ReserveDeliveryController::class,
        'update',
    ])
        ->middleware('admin.permission:reserve_deliveries.update')
        ->name('admin.reserve-deliveries.update');

    Route::delete('/admin/reserve-deliveries/{reserve_delivery}', [
        ReserveDeliveryController::class,
        'destroy',
    ])
        ->middleware('admin.permission:reserve_deliveries.delete')
        ->name('admin.reserve-deliveries.destroy');


    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/settings', [
        SettingsController::class,
        'index',
    ])
        ->middleware('admin.permission:settings.view')
        ->name('admin.settings.index');

    Route::post('/admin/settings/update', [
        SettingsController::class,
        'update',
    ])
        ->middleware('admin.permission:settings.update')
        ->name('admin.settings.update');


    /*
    |--------------------------------------------------------------------------
    | Delivery Points
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/delivery-points', [
        DeliveryPointController::class,
        'index',
    ])
        ->middleware('admin.permission:delivery_points.view')
        ->name('admin.delivery.points.index');

    Route::post('/admin/delivery-points/{deliveryPoint}/delete', [
        DeliveryPointController::class,
        'destroy',
    ])
        ->middleware('admin.permission:delivery_points.delete')
        ->name('admin.delivery.points.destroy');


    /*
    |--------------------------------------------------------------------------
    | Referral Point Rules
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/referral-point-rules', [
        ReferralPointRuleController::class,
        'index',
    ])
        ->middleware('admin.permission:referral_rules.view')
        ->name('admin.referral-point-rules.index');

    Route::post('/admin/referral-point-rules', [
        ReferralPointRuleController::class,
        'store',
    ])
        ->middleware('admin.permission:referral_rules.create')
        ->name('admin.referral-point-rules.store');

    Route::delete('/admin/referral-point-rules/{rule}', [
        ReferralPointRuleController::class,
        'destroy',
    ])
        ->middleware('admin.permission:referral_rules.delete')
        ->name('admin.referral-point-rules.destroy');


    /*
    |--------------------------------------------------------------------------
    | Meal Offers
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/meal-offers', [
        MealOfferController::class,
        'index',
    ])
        ->middleware('admin.permission:meal_offers.view')
        ->name('admin.meal-offers.index');

    Route::get('/admin/meal-offers/create', [
        MealOfferController::class,
        'create',
    ])
        ->middleware('admin.permission:meal_offers.create')
        ->name('admin.meal-offers.create');

    Route::post('/admin/meal-offers', [
        MealOfferController::class,
        'store',
    ])
        ->middleware('admin.permission:meal_offers.create')
        ->name('admin.meal-offers.store');

    Route::get('/admin/meal-offers/{meal_offer}', [
        MealOfferController::class,
        'show',
    ])
        ->middleware('admin.permission:meal_offers.view')
        ->name('admin.meal-offers.show');

    Route::get('/admin/meal-offers/{meal_offer}/edit', [
        MealOfferController::class,
        'edit',
    ])
        ->middleware('admin.permission:meal_offers.update')
        ->name('admin.meal-offers.edit');

    Route::put('/admin/meal-offers/{meal_offer}', [
        MealOfferController::class,
        'update',
    ])
        ->middleware('admin.permission:meal_offers.update')
        ->name('admin.meal-offers.update');

    Route::delete('/admin/meal-offers/{meal_offer}', [
        MealOfferController::class,
        'destroy',
    ])
        ->middleware('admin.permission:meal_offers.delete')
        ->name('admin.meal-offers.destroy');


    /*
    |--------------------------------------------------------------------------
    | Kitchen Packages
    |--------------------------------------------------------------------------
    */

    Route::get('/kitchen-packages', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'index',
    ])
        ->middleware('admin.permission:kitchen_packages.view')
        ->name('admin.kitchen-packages.index');

    Route::get('/kitchen-packages/create', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'create',
    ])
        ->middleware('admin.permission:kitchen_packages.create')
        ->name('admin.kitchen-packages.create');

    Route::post('/kitchen-packages', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'store',
    ])
        ->middleware('admin.permission:kitchen_packages.create')
        ->name('admin.kitchen-packages.store');

    Route::get('/kitchen-packages/{kitchen_package}', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'show',
    ])
        ->middleware('admin.permission:kitchen_packages.view')
        ->name('admin.kitchen-packages.show');

    Route::get('/kitchen-packages/{kitchen_package}/edit', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'edit',
    ])
        ->middleware('admin.permission:kitchen_packages.update')
        ->name('admin.kitchen-packages.edit');

    Route::put('/kitchen-packages/{kitchen_package}', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'update',
    ])
        ->middleware('admin.permission:kitchen_packages.update')
        ->name('admin.kitchen-packages.update');

    Route::delete('/kitchen-packages/{kitchen_package}', [
        \App\Http\Controllers\Admin\KitchenPackageController::class,
        'destroy',
    ])
        ->middleware('admin.permission:kitchen_packages.delete')
        ->name('admin.kitchen-packages.destroy');


    Route::post(
        'kitchens/{kitchen}/points',
        [KitchenController::class, 'addPoints']
    )->name('admin.kitchens.points.add');
    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    Route::patch('/companies/{company}/toggle-status', [
        CompanyController::class,
        'toggleStatus',
    ])
        ->middleware('admin.permission:companies.update_status')
        ->name('admin.companies.toggle-status');


    /*
    |--------------------------------------------------------------------------
    | Cash Codes
    |--------------------------------------------------------------------------
    */

    Route::prefix('cash-codes')
        ->name('admin.cash-codes.')
        ->group(function () {
            Route::get('/', [
                CashCodeController::class,
                'index',
            ])
                ->middleware('admin.permission:cash_codes.view')
                ->name('index');

            Route::get('/create', [
                CashCodeController::class,
                'create',
            ])
                ->middleware('admin.permission:cash_codes.create')
                ->name('create');

            Route::post('/', [
                CashCodeController::class,
                'store',
            ])
                ->middleware('admin.permission:cash_codes.create')
                ->name('store');

            Route::patch('/{cashCode}/toggle-status', [
                CashCodeController::class,
                'toggleStatus',
            ])
                ->middleware('admin.permission:cash_codes.update_status')
                ->name('toggle-status');

            Route::delete('/{cashCode}', [
                CashCodeController::class,
                'destroy',
            ])
                ->middleware('admin.permission:cash_codes.delete')
                ->name('destroy');
        });


    /*
    |--------------------------------------------------------------------------
    | Order Pricing
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/order-pricing', [
        OrderPricingController::class,
        'index',
    ])
        ->middleware('admin.permission:order_pricing.view')
        ->name('admin.order-pricing.index');

    Route::put('/admin/order-pricing/settings', [
        OrderPricingController::class,
        'updateSettings',
    ])
        ->middleware('admin.permission:order_pricing.update_settings')
        ->name('admin.order-pricing.settings.update');

    Route::post('/admin/order-pricing/rules', [
        OrderPricingController::class,
        'storeRule',
    ])
        ->middleware('admin.permission:order_pricing.create_rule')
        ->name('admin.order-pricing.rules.store');

    Route::put('/admin/order-pricing/rules/{rule}', [
        OrderPricingController::class,
        'updateRule',
    ])
        ->middleware('admin.permission:order_pricing.update_rule')
        ->name('admin.order-pricing.rules.update');

    Route::delete('/admin/order-pricing/rules/{rule}', [
        OrderPricingController::class,
        'destroyRule',
    ])
        ->middleware('admin.permission:order_pricing.delete_rule')
        ->name('admin.order-pricing.rules.destroy');

    Route::patch('/admin/order-pricing/rules/{rule}/toggle', [
        OrderPricingController::class,
        'toggleRule',
    ])
        ->middleware('admin.permission:order_pricing.toggle_rule')
        ->name('admin.order-pricing.rules.toggle');


    /*
    |--------------------------------------------------------------------------
    | Financial Reports
    |--------------------------------------------------------------------------
    */

    Route::get('/order-financial-reports', [
        OrderFinancialReportController::class,
        'index',
    ])
        ->middleware('admin.permission:financial_reports.view')
        ->name('admin.order-financial-reports.index');

    Route::get('/order-financial-reports/{order}', [
        OrderFinancialReportController::class,
        'show',
    ])
        ->middleware('admin.permission:financial_reports.show')
        ->name('admin.order-financial-reports.show');


    /*
    |--------------------------------------------------------------------------
    | Sales
    |--------------------------------------------------------------------------
    */

    Route::get('/sales', [
        SaleController::class,
        'index',
    ])
        ->middleware('admin.permission:sales.view')
        ->name('admin.sales.index');

    Route::get('/sales/create', [
        SaleController::class,
        'create',
    ])
        ->middleware('admin.permission:sales.create')
        ->name('admin.sales.create');

    Route::post('/sales', [
        SaleController::class,
        'store',
    ])
        ->middleware('admin.permission:sales.create')
        ->name('admin.sales.store');

    Route::get('/sales/{sale}', [
        SaleController::class,
        'show',
    ])
        ->middleware('admin.permission:sales.view')
        ->name('admin.sales.show');

    Route::get('/sales/{sale}/edit', [
        SaleController::class,
        'edit',
    ])
        ->middleware('admin.permission:sales.update')
        ->name('admin.sales.edit');

    Route::put('/sales/{sale}', [
        SaleController::class,
        'update',
    ])
        ->middleware('admin.permission:sales.update')
        ->name('admin.sales.update');

    Route::delete('/sales/{sale}', [
        SaleController::class,
        'destroy',
    ])
        ->middleware('admin.permission:sales.delete')
        ->name('admin.sales.destroy');

    Route::patch('/sales/{sale}/toggle-status', [
        SaleController::class,
        'toggleStatus',
    ])
        ->middleware('admin.permission:sales.update_status')
        ->name('admin.sales.toggle-status');
});




/*
|--------------------------------------------------------------------------
| Sales Authentication
|--------------------------------------------------------------------------
*/

Route::post('/sales/logout', [SalesAuthController::class, 'logout'])->middleware('auth:sales')->name('sales.logout');

/*
|--------------------------------------------------------------------------
| Sales Panel
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sales')->prefix('sales-panel')->name('admin.sales.')->group(function () {
    Route::get('governments/{government}/areas', [KitchenController::class, 'getAreas'])->name('governments.areas');
    Route::patch('kitchens/{kitchen}/toggle-status', [KitchenController::class, 'toggleStatus'])->name('kitchens.toggle-status');
    Route::resource('kitchens', KitchenController::class);
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
