<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BusinessProfileController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CateringTransactionController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\InventoryItemController;
use App\Http\Controllers\Api\InventoryMutationController;
use App\Http\Controllers\Api\LandingPageController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\MenuScheduleController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StockOpnameController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\PaymentMethodController;
use App\Http\Controllers\Api\V1\TransactionPaymentController;
use App\Http\Controllers\Api\VariantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Endpoint publik
    Route::post('/login', [AuthController::class, 'login']);

    // Endpoint yang membutuhkan token
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Categories Routes
        Route::prefix('categories')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])
                ->middleware('permission:category.view');

            Route::post('/', [CategoryController::class, 'store'])
                ->middleware('permission:category.create');

            Route::get('/{category}', [CategoryController::class, 'show'])
                ->middleware('permission:category.view');

            Route::put('/{category}', [CategoryController::class, 'update'])
                ->middleware('permission:category.update');

            Route::patch('/{category}', [CategoryController::class, 'update'])
                ->middleware('permission:category.update');

            Route::delete('/{category}', [CategoryController::class, 'destroy'])
                ->middleware('permission:category.delete');
        });

        // Menus Routes
        Route::prefix('menus')->group(function () {
            Route::get('/', [MenuController::class, 'index'])
                ->middleware('permission:menu.view');

            Route::post('/', [MenuController::class, 'store'])
                ->middleware('permission:menu.create');

            Route::get('/{menu}', [MenuController::class, 'show'])
                ->middleware('permission:menu.view');

            Route::put('/{menu}', [MenuController::class, 'update'])
                ->middleware('permission:menu.update');

            Route::patch('/{menu}', [MenuController::class, 'update'])
                ->middleware('permission:menu.update');

            Route::delete('/{menu}', [MenuController::class, 'destroy'])
                ->middleware('permission:menu.delete');
        });

        // Variants Routes
        Route::prefix('variants')->group(function () {
            Route::get('/', [VariantController::class, 'index'])
                ->middleware('permission:variant.view');

            Route::post('/', [VariantController::class, 'store'])
                ->middleware('permission:variant.create');

            Route::get('/{variant}', [VariantController::class, 'show'])
                ->middleware('permission:variant.view');

            Route::put('/{variant}', [VariantController::class, 'update'])
                ->middleware('permission:variant.update');

            Route::patch('/{variant}', [VariantController::class, 'update'])
                ->middleware('permission:variant.update');

            Route::delete('/{variant}', [VariantController::class, 'destroy'])
                ->middleware('permission:variant.delete');
        });

        // Menu Schedules Routes
        Route::prefix('menu-schedules')->group(function () {
            Route::get('/', [MenuScheduleController::class, 'index'])
                ->middleware('permission:menu_schedule.view');

            Route::post('/', [MenuScheduleController::class, 'store'])
                ->middleware('permission:menu_schedule.create');

            Route::get('/{menuSchedule}', [MenuScheduleController::class, 'show'])
                ->middleware('permission:menu_schedule.view');

            Route::put('/{menuSchedule}', [MenuScheduleController::class, 'update'])
                ->middleware('permission:menu_schedule.update');

            Route::patch('/{menuSchedule}', [MenuScheduleController::class, 'update'])
                ->middleware('permission:menu_schedule.update');

            Route::delete('/{menuSchedule}', [MenuScheduleController::class, 'destroy'])
                ->middleware('permission:menu_schedule.delete');
        });


        Route::prefix('inventory-items')->group(function () {
            Route::get('/', [InventoryItemController::class, 'index'])
                ->middleware('permission:inventory.view');

            Route::post('/', [InventoryItemController::class, 'store'])
                ->middleware('permission:inventory.create');

            Route::get('/{inventoryItem}', [InventoryItemController::class, 'show'])
                ->middleware('permission:inventory.view');

            Route::put('/{inventoryItem}', [InventoryItemController::class, 'update'])
                ->middleware('permission:inventory.update');

            Route::patch('/{inventoryItem}', [InventoryItemController::class, 'update'])
                ->middleware('permission:inventory.update');

            Route::delete('/{inventoryItem}', [InventoryItemController::class, 'destroy'])
                ->middleware('permission:inventory.delete');
        });

        Route::prefix('inventory-mutations')->group(function () {
            Route::get('/', [InventoryMutationController::class, 'index'])
                ->middleware('permission:inventory_mutation.view');

            Route::post('/', [InventoryMutationController::class, 'store'])
                ->middleware('permission:inventory_mutation.create');

            Route::get('/{inventoryMutation}', [InventoryMutationController::class, 'show'])
                ->middleware('permission:inventory_mutation.view');
        });


        Route::prefix('stock-opnames')->group(function () {
            Route::get('/', [StockOpnameController::class, 'index'])
                ->middleware('permission:stock_opname.view');

            Route::post('/', [StockOpnameController::class, 'store'])
                ->middleware('permission:stock_opname.create');

            Route::get('/{stockOpname}', [StockOpnameController::class, 'show'])
                ->middleware('permission:stock_opname.view');

            Route::put(
                '/{stockOpname}/items/{stockOpnameItem}',
                [StockOpnameController::class, 'updateItem']
            )->middleware('permission:stock_opname.update');

            Route::patch(
                '/{stockOpname}/items/{stockOpnameItem}',
                [StockOpnameController::class, 'updateItem']
            )->middleware('permission:stock_opname.update');

            Route::post(
                '/{stockOpname}/complete',
                [StockOpnameController::class, 'complete']
            )->middleware('permission:stock_opname.complete');

            Route::post(
                '/{stockOpname}/cancel',
                [StockOpnameController::class, 'cancel']
            )->middleware('permission:stock_opname.cancel');
        });


        Route::prefix('transactions')->group(function () {
            Route::get('/', [CateringTransactionController::class, 'index'])
                ->middleware('permission:transaction.view');

            Route::post('/', [CateringTransactionController::class, 'store'])
                ->middleware('permission:transaction.create');

            Route::get('/{cateringTransaction}', [CateringTransactionController::class, 'show'])
                ->middleware('permission:transaction.view');

            Route::put('/{cateringTransaction}', [CateringTransactionController::class, 'update'])
                ->middleware('permission:transaction.update');

            Route::patch('/{cateringTransaction}', [CateringTransactionController::class, 'update'])
                ->middleware('permission:transaction.update');

            Route::patch(
                '/transactions/{cateringTransaction}/status',
                [CateringTransactionController::class, 'updateStatus']
            )->middleware('permission:transaction.status_update');
        });

        Route::prefix('payment-methods')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index'])
                ->middleware('permission:payment_method.view');

            Route::post('/', [PaymentMethodController::class, 'store'])
                ->middleware('permission:payment_method.create');

            Route::get('/{paymentMethod}', [PaymentMethodController::class, 'show'])
                ->middleware('permission:payment_method.view');

            Route::put('/{paymentMethod}', [PaymentMethodController::class, 'update'])
                ->middleware('permission:payment_method.update');

            Route::patch('/{paymentMethod}', [PaymentMethodController::class, 'update'])
                ->middleware('permission:payment_method.update');
        });

        Route::prefix('transaction-payments')->group(function () {
            Route::get('/', [TransactionPaymentController::class, 'index'])
                ->middleware('permission:transaction_payment.view');

            Route::post('/', [TransactionPaymentController::class, 'store'])
                ->middleware('permission:transaction_payment.create');

            Route::get('/{transactionPayment}', [
                TransactionPaymentController::class,
                'show',
            ])->middleware('permission:transaction_payment.view');

            Route::patch('/{transactionPayment}/status', [
                TransactionPaymentController::class,
                'updateStatus',
            ])->middleware('permission:transaction_payment.status_update');
        });

        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])
                ->middleware('permission:customer.view');

            Route::post('/', [CustomerController::class, 'store'])
                ->middleware('permission:customer.create');

            Route::get('/{customer}', [CustomerController::class, 'show'])
                ->middleware('permission:customer.view');

            Route::put('/{customer}', [CustomerController::class, 'update'])
                ->middleware('permission:customer.update');

            Route::patch('/{customer}', [CustomerController::class, 'update'])
                ->middleware('permission:customer.update');

            Route::get(
                '/customers/{customer}/transactions',
                [CustomerController::class, 'transactions']
            )->middleware('permission:customer.view');
        });

        Route::prefix('expense-categories')->group(function () {
            Route::get('/', [ExpenseCategoryController::class, 'index'])
                ->middleware('permission:expense_category.view');

            Route::post('/', [ExpenseCategoryController::class, 'store'])
                ->middleware('permission:expense_category.create');

            Route::get('/{expenseCategory}', [ExpenseCategoryController::class, 'show'])
                ->middleware('permission:expense_category.view');

            Route::put('/{expenseCategory}', [ExpenseCategoryController::class, 'update'])
                ->middleware('permission:expense_category.update');

            Route::patch('/{expenseCategory}', [ExpenseCategoryController::class, 'update'])
                ->middleware('permission:expense_category.update');

            Route::delete('/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])
                ->middleware('permission:expense_category.delete');
        });

        Route::prefix('expenses')->group(function () {
            Route::get('/summary', [ExpenseController::class, 'summary'])
                ->middleware('permission:expense.view');

            Route::get('/', [ExpenseController::class, 'index'])
                ->middleware('permission:expense.view');

            Route::post('/', [ExpenseController::class, 'store'])
                ->middleware('permission:expense.create');

            Route::get('/{expense}', [ExpenseController::class, 'show'])
                ->middleware('permission:expense.view');

            Route::put('/{expense}', [ExpenseController::class, 'update'])
                ->middleware('permission:expense.update');

            Route::patch('/{expense}', [ExpenseController::class, 'update'])
                ->middleware('permission:expense.update');

            Route::delete('/{expense}', [ExpenseController::class, 'destroy'])
                ->middleware('permission:expense.delete');
        });

        Route::prefix('reviews')->group(function () {
            Route::get('/summary', [ReviewController::class, 'summary'])
                ->middleware('permission:review.view');

            Route::get('/', [ReviewController::class, 'index'])
                ->middleware('permission:review.view');

            Route::post('/', [ReviewController::class, 'store'])
                ->middleware('permission:review.create');

            Route::get('/{review}', [ReviewController::class, 'show'])
                ->middleware('permission:review.view');

            Route::put('/{review}', [ReviewController::class, 'update'])
                ->middleware('permission:review.update');

            Route::patch('/{review}', [ReviewController::class, 'update'])
                ->middleware('permission:review.update');

            Route::delete('/{review}', [ReviewController::class, 'destroy'])
                ->middleware('permission:review.delete');
        });

        Route::prefix('analytics')->group(function () {
            Route::get('/dashboard', [
                AnalyticsController::class,
                'dashboard',
            ])->middleware('permission:analytics.view');

            Route::get('/charts/daily', [
                AnalyticsController::class,
                'dailyChart',
            ])->middleware('permission:analytics.view');

            Route::get('/transaction-statuses', [
                AnalyticsController::class,
                'transactionStatuses',
            ])->middleware('permission:analytics.view');
        });

        Route::prefix('reports')->group(function () {
            Route::get('/summary', [ReportController::class, 'summary'])
                ->middleware('permission:report.view');

            Route::get('/transactions', [ReportController::class, 'transactions'])
                ->middleware('permission:report.view');

            Route::get('/payments', [ReportController::class, 'payments'])
                ->middleware('permission:report.view');

            Route::get('/expenses', [ReportController::class, 'expenses'])
                ->middleware('permission:report.view');

            Route::get('/reviews', [ReportController::class, 'reviews'])
                ->middleware('permission:report.view');
        });

        // Business Profile Routes
        Route::prefix('business-profile')->group(function () {
            Route::get('/', [BusinessProfileController::class, 'show'])
                ->middleware('permission:business_profile.view');

            Route::put('/', [BusinessProfileController::class, 'update'])
                ->middleware('permission:business_profile.update');

            Route::patch('/', [BusinessProfileController::class, 'update'])
                ->middleware('permission:business_profile.update');
        });
    });
});

Route::prefix('v1/public')
    ->name('public.')
    ->group(function () {
        Route::get('/home', [
            LandingPageController::class,
            'home',
        ]);

        Route::get('/menus', [
            LandingPageController::class,
            'menus',
        ]);

        Route::get('/menus/{slug}', [
            LandingPageController::class,
            'menuDetail',
        ]);

        Route::get('/categories', [
            LandingPageController::class,
            'categories',
        ]);

        Route::get('/schedules', [
            LandingPageController::class,
            'schedules',
        ]);

        Route::get('/reviews', [
            LandingPageController::class,
            'reviews',
        ]);

        Route::get('/reviews/summary', [
            LandingPageController::class,
            'reviewSummary',
        ]);

        Route::post('/requests', [
            LandingPageController::class,
            'storeRequest',
        ]);

        // Public Business Profile
        Route::get('/business-profile', [
            BusinessProfileController::class,
            'publicProfile',
        ]);
    });
