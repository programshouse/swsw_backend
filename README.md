# SWSW Backend API (Laravel 12)

Backend API for a multi‑role food ordering / kitchen marketplace, built with Laravel 12 and PHP 8.2.  
It powers:

- Client mobile/web app (browse kitchens & meals, place orders, manage profile and addresses)
- Kitchen dashboard (manage profile, availability, meals, and orders)
- Admin dashboard (manage areas, governments, kitchens, clients, wallets, carousels, etc.)

---

## Tech stack

- PHP ^8.2
- Laravel ^12.0
- Laravel Sanctum (token‑based authentication)
- Laravel Reverb (real‑time capabilities)
- MySQL (or any Laravel‑supported relational DB)
- Node + Vite (asset bundling)

---

## Getting started

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js (LTS) and npm
- MySQL (or another database supported by Laravel)

### Installation

```bash
git clone <your-repo-url> swsw_backend_php
cd swsw_backend_php

composer install
cp .env.example .env  # or copy manually on Windows

# Configure DB connection and other environment variables in .env

php artisan key:generate
php artisan migrate

npm install
npm run dev           # or "npm run build" for production
```

You can also use the prepared Composer scripts:

```bash
# One‑shot initial setup (install, env, key, migrate, npm build)
composer setup

# Dev mode: app server + queue listener + Vite
composer dev
```

### Running the application

```bash
php artisan serve
```

The API will be available at:

- **Base URL (local)**: `http://localhost:8000`
- **API base path**: `http://localhost:8000/api`

### Running tests

```bash
composer test
```

---

## Authentication

This project uses **Laravel Sanctum**.  
Obtain a token via the auth endpoints, then send it in the `Authorization` header:

```http
Authorization: Bearer <access_token>
```

### Register

- **Method / URL**: `POST /api/register`
- **Body (JSON)**:
  - `name` (string, required)
  - `email` (string, nullable, unique)
  - `phone` (string, required, unique per role)
  - `government_id` (integer, nullable, existing `governments.id`)
  - `area_id` (integer, nullable, existing `areas.id`)
  - `role` (string, required, one of: `admin`, `client`, `kitchen`, `delivery`)
  - `password` (string, required, min 8)
  - `password_confirmation` (string, required)
- **Response**:
  - `message`
  - `user`
  - `access_token`
  - `token_type` (`Bearer`)

### Login (client / kitchen)

- **Method / URL**: `POST /api/login`
- **Body (JSON)**:
  - `phone` (string, required)
  - `password` (string, required)
  - `role` (string, required, `client` or `kitchen`)
- **Response**:
  - `message`
  - `user` (includes `have_profile` flag)
  - `access_token`
  - `token_type`

### Admin login

- **Method / URL**: `POST /api/admin-login`
- **Body (JSON)**:
  - `email` (string, required)
  - `password` (string, required)

### Kitchen password reset (admin‑assisted flow)

- **Verify reset code**: `POST /api/verifiy-kitchen-user-forget-password`
- **Reset password**: `POST /api/kitchen-user-forget-password`
- **Generate reset code (admin only, auth required)**:  
  `POST /api/dashboard-generate-kitchen-user-forget-password-code/{user}`

---

## Public reference data

These endpoints do **not** require authentication (unless you change middleware):

### Governments

- `GET /api/governments` – list governments
- `POST /api/create-governments` – create a government
- `DELETE /api/government/{government}/delete` – delete

### Areas

- `GET /api/areas` – list areas
- `POST /api/create-area` – create area
- `DELETE /api/area/{area}/delete` – delete

### Workdays

- `GET /api/workdays` – list workdays
- `POST /api/create-workday` – create workday
- `DELETE /api/workday/{workday}/delete` – delete

### Categories

- `GET /api/categories` – list categories
- `POST /api/create-category` – create category
- `DELETE /api/category/{workday}/delete` – delete category

---

## Authenticated API (Sanctum)

All endpoints below are inside `Route::middleware('auth:sanctum')`.  
Send `Authorization: Bearer <token>` with each request.

### Kitchen profile & dashboard

- `POST /api/kitchen-profile` – create/update kitchen profile
- `GET /api/kitchen-profile/{profile}` – show kitchen profile
- `GET /api/my-kitchen-profile` – show authenticated kitchen profile (requires accepted profile)
- `GET /api/kitchen-meals/{kitchen}` – list meals for a kitchen (requires accepted profile)
- `PATCH /api/active-kitchen-profile/{profile}` – activate/deactivate profile
- `PATCH /api/open-status-kitchen-profile/{kitchen}` – toggle open/closed status
- `PATCH /api/add-star-to-kitchen-profile/{kitchen}` – adjust kitchen rating stars
- `GET /api/kitchen-app-home` – data for kitchen home screen

### User accounts (admin / kitchen)

- `PATCH /api/active-kitchen-account/{user}` – activate/deactivate kitchen user
- `GET /api/kitchen-accounts` – list kitchen users (with optional `search` query)

### Meals

- `POST /api/create-meal` – create meal (kitchen, profile must be accepted)
- `DELETE /api/meal/{meal}/delete` – delete meal
- `POST /api/approve-meal/{meal}` – approve a meal (admin)
- `GET /api/my-meals` – list meals for authenticated kitchen
- `GET /api/all-meals` – list all meals
- `PATCH /api/meal-availability/{meal}` – toggle availability

### Orders

- `GET /api/kitchen-orders` – list orders for authenticated kitchen
- `GET /api/client-orders` – list orders for authenticated client
- `GET /api/orders` – list all orders (typically admin)
- `POST /api/orders` – create order
- `GET /api/orders/{order}` – show order details
- `PATCH /api/orders/{order}` – update order
- `DELETE /api/orders/{order}` – delete order
- `PATCH /api/orders/{order}/change-status` – accept / change status
- `PATCH /api/orders/{order}/deliver` – mark order as delivered
- `POST /api/order/{order}/cancel` – cancel order

### Client addresses

- `GET /api/client-address` – list client addresses
- `POST /api/create-client-address` – create address
- `DELETE /api/delete-client-address/{address}` – delete address
- `PATCH /api/set-default-client-address/{address}` – set default address

### Client app (home & search)

- `GET /api/client-home` – client home screen data
- `GET /api/client-kitchen-details/{kitchen}` – kitchen details for clients
- `GET /api/client-meal-by-category/{category}` – meals filtered by category
- `GET /api/client-my-profile` – current client profile
- `GET /api/meal-search` – search meals (query params)
- `GET /api/kitchen-search` – search kitchens (query params)

### Carousel

- `GET /api/carusel` – list carousel items
- `POST /api/create-carusel` – create carousel item
- `DELETE /api/carusel/{carusel}/delete` – delete carousel item

### Wallet

- `GET /api/my-wallet` – wallet summary for authenticated user/kitchen
- `POST /api/create-debit-request` – request withdrawal/debit
- `GET /api/my-debit-requests` – list own debit requests
- `PATCH /api/approve-debit-request/{debit_request}` – approve debit request (admin)
- `GET /api/all-debit-requests` – list all debit requests
- `GET /api/dash-kitchen-wallet/{kitchen}` – wallet transactions for a kitchen (dashboard)

### Ratings

- `POST /api/rate-kitchen` – rate a kitchen

### Dashboard (admin)

- `GET /api/all-clients` – list all clients
- `GET /api/client/{user}` – client profile details

---

## Project structure

High‑level structure of this Laravel backend:

```text
.
├── app
│   ├── Events
│   │   ├── CreateOrder.php
│   │   └── UserRegister.php
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── AreaController.php
│   │   │   ├── CategoryController.php
│   │   │   ├── GovernmentController.php
│   │   │   ├── KitchenAppHomeController.php
│   │   │   ├── KitchenProfileController.php
│   │   │   ├── MealController.php
│   │   │   ├── WalletController.php
│   │   │   ├── WorkingDayController.php
│   │   │   ├── orders
│   │   │   │   └── OrdersController.php
│   │   │   ├── client
│   │   │   │   ├── ClientController.php
│   │   │   │   ├── CaruselController.php
│   │   │   │   └── UserAddressController.php
│   │   │   └── rate
│   │   │       └── RateController.php
│   │   ├── Middleware
│   │   │   └── EnsureProfileAccepted.php
│   │   └── Resources
│   ├── Http
│   │   └── Resources
│   │       ├── AreaResource.php
│   │       ├── CaruselResource.php
│   │       ├── ClientMealResource.php
│   │       ├── ClientProfileResource.php
│   │       ├── DashboardClientResource.php
│   │       ├── DashboardKitchenProfileResource.php
│   │       ├── GovernmentResource.php
│   │       ├── MealResource.php
│   │       ├── OrderItemsResource.php
│   │       ├── OrderResource.php
│   │       ├── UserAddressResource.php
│   │       ├── UserResource.php
│   │       └── WorkDayResource.php
│   ├── Models
│   │   ├── Area.php
│   │   ├── Carusel.php
│   │   ├── Category.php
│   │   ├── Government.php
│   │   ├── KitchenProfile.php
│   │   ├── Meal.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── OrderPayment.php
│   │   ├── Rate.php
│   │   ├── User.php
│   │   ├── UserAddress.php
│   │   ├── Wallet.php
│   │   ├── WalletDebitRequest.php
│   │   └── WalletTransaction.php
│   ├── Providers
│   │   ├── AppServiceProvider.php
│   │   └── BroadcastServiceProvider.php
│   ├── services
│   │   └── WalletService.php
│   └── traits
│       └── Transaction.php
├── bootstrap
│   ├── app.php
│   ├── cache
│   └── providers.php
├── config
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── logging.php
│   ├── queue.php
│   ├── reverb.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
├── database
│   ├── factories
│   │   └── UserFactory.php
│   ├── migrations
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2025_12_13_203819_create_personal_access_tokens_table.php
│   │   ├── 2025_12_13_210626_create_governments_table.php
│   │   ├── 2025_12_13_210637_create_areas_table.php
│   │   ├── 2025_12_13_210639_create_users_table.php
│   │   ├── 2025_12_13_211240_create_work_days_table.php
│   │   ├── 2025_12_13_212732_create_kitchen_profiles_table.php
│   │   ├── 2025_12_13_235041_create_categories_table.php
│   │   ├── 2025_12_14_000116_create_meals_table.php
│   │   ├── 2026_01_26_112634_create_orders_table.php
│   │   ├── 2026_01_26_112644_create_order_items_table.php
│   │   ├── 2026_01_28_131537_create_user_address_table.php
│   │   ├── 2026_02_01_104048_update_meals_table.php
│   │   ├── 2026_02_01_152621_create_order_payments_table.php
│   │   ├── 2026_02_02_213059_create_wallets_table.php
│   │   ├── 2026_02_09_175342_create_carusel_table.php
│   │   ├── 2026_02_09_201534_create_wallet_debit_request_table.php
│   │   ├── 2026_02_09_214829_update_wallet_debit_requests_table.php
│   │   ├── 2026_02_17_122255_create_notifications_table.php
│   │   ├── 2026_02_17_122806_create_rates_table.php
│   │   ├── 2026_02_17_131436_update_orders_table.php
│   │   └── 2026_02_17_153738_update_orders_table.php
│   └── seeders
│       └── AdminUserSeeder.php
├── public
│   ├── index.php
│   └── robots.txt
├── resources
│   ├── css
│   │   └── app.css
│   └── js
│       ├── app.js
│       └── bootstrap.js
├── routes
│   ├── api.php
│   ├── channels.php
│   └── console.php
├── storage
│   ├── app
│   ├── framework
│   └── logs
├── tests
│   ├── Feature
│   │   └── ExampleTest.php
│   └── Pest.php
├── composer.json
├── package.json
├── phpunit.xml
└── vite.config.js
```

---

## Extending the API

- Add new routes in `routes/api.php`
- Implement logic in controllers (and, if needed, services under `app/services`)
- Use Form Requests / validation rules similar to the existing controllers
- Expose responses via `JsonResponse` or API Resources for consistency

