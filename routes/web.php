<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

use App\Http\Controllers\StoreAuthController;

Route::get('/register-store', [StoreAuthController::class, 'showRegistrationForm'])->name('store.register.form');
Route::post('/register-store', [StoreAuthController::class, 'register'])->name('store.register');

Route::get('/login', [StoreAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [StoreAuthController::class, 'login']);
Route::post('/logout', [StoreAuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneralSettingsController;

// Employee Routes
Route::middleware(['auth:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\EmployeeDashboardController::class, 'index'])->name('dashboard');
});

// Owner Dashboard (Strictly Web Guard)
Route::middleware(['auth:web'])->group(function () {
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', function () {
        return redirect('/admin');
    })->name('dashboard');
});

// Shared Store Routes (accessible by both web and employee)
Route::middleware(['auth:web,employee'])->group(function () {
    // Route::get('/dashboard', ...); // MOVED UP


    // ... other shared routes ...

    Route::get('/settings/general', [GeneralSettingsController::class, 'index'])->name('settings.general');
    Route::post('/settings/update', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/access', [GeneralSettingsController::class, 'access'])->name('settings.access');
    Route::get('/settings/users', [\App\Http\Controllers\UserAccessController::class, 'index'])->name('settings.users');
    Route::post('/settings/users/{id}', [\App\Http\Controllers\UserAccessController::class, 'update'])->name('settings.users.update');
    Route::get('/settings/reminder', [GeneralSettingsController::class, 'reminder'])->name('settings.reminder');
    Route::get('/settings/employees', [GeneralSettingsController::class, 'employees'])->name('settings.employees');
    Route::get('settings/employees-meta', [\App\Http\Controllers\EmployeeController::class, 'getMeta']);
    Route::resource('settings/employees-api', \App\Http\Controllers\EmployeeController::class);

    // New Employee Management Routes
    Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'webIndex'])->name('employees.web.index');
    Route::post('/employees/store', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}/edit', [\App\Http\Controllers\EmployeeController::class, 'webEdit'])->name('employees.edit');
    Route::post('/employees/{id}/update', [\App\Http\Controllers\EmployeeController::class, 'webUpdate'])->name('employees.update');

    Route::get('/cash-sales/create', [\App\Http\Controllers\CashSalesController::class, 'create'])->name('cash-sales.create');
    Route::get('/cash-sales/search', [\App\Http\Controllers\CashSalesController::class, 'searchItems']); // Added
    Route::post('/cash-sales/store', [\App\Http\Controllers\CashSalesController::class, 'store'])->name('cash-sales.store');
    Route::get('/cash-sales/{id}/print', [\App\Http\Controllers\CashSalesController::class, 'show'])->name('cash-sales.show');

    Route::get('/sales/pos', [App\Http\Controllers\SalesController::class, 'pos'])->name('sales.pos');
    Route::get('/sales/history', [App\Http\Controllers\SalesController::class, 'history'])->name('sales.history'); // Added
    Route::post('/sales/store', [App\Http\Controllers\SalesController::class, 'store'])->name('sales.store');
    Route::get('/sales/{id}/print', [App\Http\Controllers\SalesController::class, 'print'])->name('sales.print');
    Route::get('/api/products/search', [App\Http\Controllers\SalesController::class, 'searchProducts']);
    Route::get('/api/customers/{id}', [App\Http\Controllers\SalesController::class, 'apiCustomer']);
    // Route::get('/api/products/search', [App\Http\Controllers\SalesController::class, 'apiProduct']); // Replaced by searchProducts
    // Route::resource('debit-sales', \App\Http\Controllers\Store\DebitSaleController::class); // OLD
    Route::get('/debit-sales', [\App\Http\Controllers\DebitSalesController::class, 'index'])->name('debit-sales.index'); // FIX: Added Index
    Route::get('/debit-sales/create', [\App\Http\Controllers\DebitSalesController::class, 'create'])->name('debit-sales.create');
    Route::get('/debit-sales/search', [\App\Http\Controllers\DebitSalesController::class, 'searchItems']);
    Route::post('/debit-sales/store', [\App\Http\Controllers\DebitSalesController::class, 'store'])->name('debit-sales.store');
    Route::get('/debit-sales/{id}/print', [\App\Http\Controllers\DebitSalesController::class, 'show'])->name('debit-sales.show');
    Route::post('/customers/quick-store', [\App\Http\Controllers\Store\CustomerController::class, 'quickStore'])->name('customers.quick-store');
    Route::resource('customers', \App\Http\Controllers\Store\CustomerController::class);
    Route::resource('refunds', \App\Http\Controllers\Store\RefundController::class);

    Route::get('/refunds', [\App\Http\Controllers\Store\RefundController::class, 'index'])->name('refunds.index');
    Route::get('/refunds/create', [\App\Http\Controllers\Store\RefundController::class, 'create'])->name('refunds.create');
    Route::post('/refunds/store', [\App\Http\Controllers\Store\RefundController::class, 'store'])->name('refunds.store');
    Route::get('/refunds/{id}/print', [\App\Http\Controllers\Store\RefundController::class, 'print'])->name('refunds.print');

    // Receipts / Payments
    Route::get('/payments/create', [\App\Http\Controllers\PaymentController::class, 'create'])->name('payments.create');
    Route::post('/payments/store', [\App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');

    Route::get('/receipts', [\App\Http\Controllers\Store\ReceiptController::class, 'index'])->name('receipts.index');
    Route::get('/receipts/create', [\App\Http\Controllers\Store\ReceiptController::class, 'create'])->name('receipts.create');
    Route::post('/receipts/store', [\App\Http\Controllers\Store\ReceiptController::class, 'store'])->name('receipts.store');
    Route::get('/receipts/{id}/print', [\App\Http\Controllers\Store\ReceiptController::class, 'print'])->name('receipts.print');
    Route::get('/receipts/pending-invoices/{customer}', [\App\Http\Controllers\Store\ReceiptController::class, 'getPendingInvoices'])->name('receipts.pending');
    Route::get('/todo', [\App\Http\Controllers\TaskController::class, 'index'])->name('todo');
    Route::post('/tasks', [\App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
    Route::post('/tasks/update-status', [\App\Http\Controllers\TaskController::class, 'updateStatus'])->name('tasks.update-status');

    // Internal Transfers
    Route::get('/transfers/create', [\App\Http\Controllers\TransferController::class, 'create'])->name('transfers.create');
    Route::post('/transfers/store', [\App\Http\Controllers\TransferController::class, 'store'])->name('transfers.store');
    Route::get('/transfers/{id}/print', [\App\Http\Controllers\TransferController::class, 'print'])->name('transfers.print');

    // Barcode Generator
    Route::get('/barcodes', [\App\Http\Controllers\BarcodeController::class, 'index'])->name('barcodes.index');
    Route::post('/barcodes/print', [\App\Http\Controllers\BarcodeController::class, 'print'])->name('barcodes.print');

    // Stock Adjustments
    Route::get('/adjustments/create', [\App\Http\Controllers\AdjustmentController::class, 'create'])->name('adjustments.create');
    Route::post('/adjustments/store', [\App\Http\Controllers\AdjustmentController::class, 'store'])->name('adjustments.store');
    Route::get('/adjustments/{id}/print', [\App\Http\Controllers\AdjustmentController::class, 'print'])->name('adjustments.print');


    // Reminders
    Route::get('/reminders', [\App\Http\Controllers\ReminderController::class, 'index'])->name('reminders.index');
    Route::post('/reminders/store', [\App\Http\Controllers\ReminderController::class, 'store'])->name('reminders.store');
    Route::get('/reminders/{id}/edit', [\App\Http\Controllers\ReminderController::class, 'edit'])->name('reminders.edit');
    Route::post('/reminders/{id}/update', [\App\Http\Controllers\ReminderController::class, 'update'])->name('reminders.update');
    Route::post('/reminders/{id}/delete', [\App\Http\Controllers\ReminderController::class, 'destroy'])->name('reminders.destroy');
    Route::get('/reminders/check', [\App\Http\Controllers\ReminderController::class, 'checkDueReminders'])->name('reminders.check');



    // General Ledger (Chart of Accounts)
    Route::group(['prefix' => 'general-ledger', 'as' => 'general-ledger.'], function () {
        Route::get('/', [\App\Http\Controllers\GeneralLedgerController::class, 'index'])->name('index');
        Route::post('/store', [\App\Http\Controllers\GeneralLedgerController::class, 'store'])->name('store');
    });

    // Accounting (General Journal)
    Route::group(['prefix' => 'journals', 'as' => 'journals.'], function () {
        Route::get('/create', [\App\Http\Controllers\JournalController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\JournalController::class, 'store'])->name('store');
    });

    // Supplier Categories
    Route::resource('supplier-categories', \App\Http\Controllers\SupplierCategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    // Supplier Management (Master)
    Route::get('/suppliers/sample-excel', [\App\Http\Controllers\SupplierController::class, 'sampleExcel'])->name('suppliers.sample-excel');
    Route::post('/suppliers/import', [\App\Http\Controllers\SupplierController::class, 'import'])->name('suppliers.import');
    Route::post('/suppliers/quick-store', [\App\Http\Controllers\SupplierController::class, 'quickStore'])->name('suppliers.quick-store');
    Route::get('/suppliers/{id}/ledger', [\App\Http\Controllers\SupplierController::class, 'ledger'])->name('suppliers.ledger');
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);

    // Supplier Payments
    Route::get('/suppliers/{id}/payments', [\App\Http\Controllers\SupplierController::class, 'paymentIndex'])->name('suppliers.payments');
    Route::post('/suppliers/{id}/payments', [\App\Http\Controllers\SupplierController::class, 'storePayment'])->name('suppliers.payments.store');

    // Supplier Credit API (used by purchase form to auto-apply credit)
    Route::get('/api/supplier/{id}/credit', [\App\Http\Controllers\SupplierController::class, 'getCredit'])->name('api.supplier.credit');

    // Accounts API (used by purchase form payment split dropdown)
    Route::get('/api/accounts', function () {
        return response()->json(\App\Models\Account::where('type', 'Asset')->orderBy('name')->get(['id', 'name', 'code']));
    })->name('api.accounts');

    // Tax / Charge Types (used by purchase form — AJAX quick-add)
    Route::post('/tax-charge-types', [\App\Http\Controllers\TaxChargeTypeController::class, 'store'])->name('tax-charge-types.store');


    // Supplier Returns (Expiry & Return Management)
    Route::prefix('supplier-returns')->name('supplier-returns.')->group(function () {
        Route::get('/',        [\App\Http\Controllers\SupplierReturnController::class, 'index'])->name('index');
        Route::get('/create',  [\App\Http\Controllers\SupplierReturnController::class, 'create'])->name('create');
        Route::post('/store',  [\App\Http\Controllers\SupplierReturnController::class, 'store'])->name('store');
    });

    // Bank Account Management
    Route::get('/banks', [\App\Http\Controllers\BankController::class, 'index'])->name('banks.index');
    Route::post('/banks/store', [\App\Http\Controllers\BankController::class, 'store'])->name('banks.store');
    Route::get('/banks/{id}', [\App\Http\Controllers\BankController::class, 'show'])->name('banks.show');

    // Value Search / Audit
    Route::get('/values', [\App\Http\Controllers\ValueSearchController::class, 'index'])->name('values.index');
    Route::post('/values/search', [\App\Http\Controllers\ValueSearchController::class, 'search'])->name('values.search');

    // Report Center
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'accountReports'])->name('reports.index');
    Route::get('/reports/view/{id}', [\App\Http\Controllers\ReportController::class, 'view'])->name('reports.view');
    Route::post('/reports/generate', [\App\Http\Controllers\ReportController::class, 'generate'])->name('reports.generate');

    // Account Reports Tree
    Route::get('/account/reports', function () {
        return redirect()->route('reports.index');
    })->name('account.reports');
    Route::get('/account/reports/open', [\App\Http\Controllers\ReportController::class, 'openReport'])->name('account.reports.open');

    // Universal Add Child
    Route::get('/child/create', [\App\Http\Controllers\ChildController::class, 'create'])->name('child.create');
    Route::post('/child/store', [\App\Http\Controllers\ChildController::class, 'store'])->name('child.store');

    // Delete Feature
    Route::get('/delete/confirm', [\App\Http\Controllers\DeleteController::class, 'confirm'])->name('delete.confirm');
    Route::post('/delete/destroy', [\App\Http\Controllers\DeleteController::class, 'destroy'])->name('delete.destroy');

    // Report Layout Editor
    Route::get('/reports/layout', [\App\Http\Controllers\ReportLayoutController::class, 'index'])->name('reports.layout');
    Route::post('/reports/layout/store', [\App\Http\Controllers\ReportLayoutController::class, 'store'])->name('reports.layout.store');

    // Report Permissions Manager
    Route::get('/reports/restrict', [\App\Http\Controllers\ReportPermissionController::class, 'index'])->name('reports.restrict');
    Route::post('/reports/restrict/update', [\App\Http\Controllers\ReportPermissionController::class, 'update'])->name('reports.restrict.update');


    // Purchase Module
    Route::group(['prefix' => 'purchases', 'as' => 'purchases.'], function () {
        Route::get('/create', [\App\Http\Controllers\PurchaseController::class, 'create'])->name('create');
        Route::get('/create-credit', [\App\Http\Controllers\PurchaseController::class, 'createCredit'])->name('create-credit');
        Route::post('/store', [\App\Http\Controllers\PurchaseController::class, 'store'])->name('store');
        Route::get('/{id}/print', [\App\Http\Controllers\PurchaseController::class, 'print'])->name('print');
        Route::get('/{id}', [\App\Http\Controllers\PurchaseController::class, 'show'])->name('show');
    });

    // Purchase Orders (Drafts)
    Route::group(['prefix' => 'purchase-orders', 'as' => 'purchase-orders.'], function () {
        Route::get('/create', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('store');
    });

    // Purchase Returns (Debit Note)
    Route::group(['prefix' => 'purchase-returns', 'as' => 'purchase-returns.'], function () {
        Route::get('/create', [\App\Http\Controllers\PurchaseReturnController::class, 'create'])->name('create');
        Route::post('/store', [\App\Http\Controllers\PurchaseReturnController::class, 'store'])->name('store');
    });

    // Hospitality (Hotel & Restaurant)
    Route::group(['prefix' => 'hotel', 'as' => 'hotel.'], function () {
        Route::get('/', [\App\Http\Controllers\HotelController::class, 'index'])->name('index');
        Route::post('/kot/store', [\App\Http\Controllers\HotelController::class, 'storeKot'])->name('kot.store');
        Route::post('/room/status', [\App\Http\Controllers\HotelController::class, 'updateRoomStatus'])->name('room.status');
        Route::get('/kot/{id}/print', [\App\Http\Controllers\HotelController::class, 'printKot'])->name('kot.print');
        Route::get('/kot/{id}/bill', [\App\Http\Controllers\HotelController::class, 'printBill'])->name('kot.bill');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [App\Http\Controllers\Store\ReportController::class, 'sales'])->name('sales');
        Route::get('/purchases', [App\Http\Controllers\Store\ReportController::class, 'purchases'])->name('purchases');
        Route::get('/accounts', [App\Http\Controllers\Store\ReportController::class, 'accounts'])->name('accounts');
    });

    // Item Management
    Route::get('/items', [\App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [\App\Http\Controllers\ItemController::class, 'create'])->name('items.create');
    Route::post('/items/store', [\App\Http\Controllers\ItemController::class, 'store'])->name('items.store');
    Route::post('/items/import', [\App\Http\Controllers\ItemController::class, 'import'])->name('items.import');
    Route::get('/items/{id}/edit', [\App\Http\Controllers\ItemController::class, 'edit'])->name('items.edit');
    Route::post('/items/{id}/update', [\App\Http\Controllers\ItemController::class, 'update'])->name('items.update');

    // Batch Management (Stock)
    Route::get('/items/{id}/add-stock', [\App\Http\Controllers\BatchController::class, 'create'])->name('batches.create');
    Route::post('/batches/store', [\App\Http\Controllers\BatchController::class, 'store'])->name('batches.store');
    Route::get('/batches/{id}/edit', [\App\Http\Controllers\BatchController::class, 'edit'])->name('batches.edit');
    Route::post('/batches/{id}/update', [\App\Http\Controllers\BatchController::class, 'update'])->name('batches.update');

    // Stock Alerts Route (Moved inside the auth middleware group)
    Route::get('/stock/low-stock', [App\Http\Controllers\StockController::class, 'lowStock'])->name('stock.low-stock');
});


use App\Http\Controllers\SuperAdmin\AuthController as SuperAuthController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboardController;

Route::prefix('super')->group(function () {
    // Super Admin Login
    Route::get('/login', [SuperAuthController::class, 'showLoginForm'])->name('super.login');
    Route::post('/login', [SuperAuthController::class, 'login'])->name('super.login.submit');

    // Super Admin Register
    Route::get('/register', [SuperAuthController::class, 'showRegistrationForm'])->name('super.register');
    Route::post('/register', [SuperAuthController::class, 'register'])->name('super.register.submit');

    // Logout
    Route::post('/logout', [SuperAuthController::class, 'logout'])->name('super.logout');

    Route::middleware(['auth:super_admin'])->group(function () {
        // Dynamic PIN Generation
        Route::post('/pins/generate', [SuperDashboardController::class, 'generatePin'])->name('super.pins.generate');
        Route::get('/dashboard', [SuperDashboardController::class, 'index'])->name('super.dashboard');

        // Store Requests
        Route::get('/requests', [SuperDashboardController::class, 'storeRequests'])->name('super.requests.index');
        Route::get('/requests/{id}', [SuperDashboardController::class, 'storeRequestShow'])->name('super.requests.show');
        Route::post('/requests/{id}/approve', [SuperDashboardController::class, 'approveStore'])->name('super.requests.approve');
        Route::post('/requests/{id}/reject', [SuperDashboardController::class, 'rejectStore'])->name('super.requests.reject');

        // Active Stores (Tenants)
        Route::get('/tenants', [SuperDashboardController::class, 'tenants'])->name('super.tenants');
        Route::post('/tenants/{id}/suspend', [SuperDashboardController::class, 'suspendTenant'])->name('super.tenants.suspend');
        Route::post('/tenants/{id}/login-as', [SuperDashboardController::class, 'loginAsOwner'])->name('super.tenants.loginAs');
        Route::post('/tenants/{id}/backup', [SuperDashboardController::class, 'backupTenant'])->name('super.tenants.backup');

        // Secondary Pages
        Route::get('/plans', [SuperDashboardController::class, 'plans'])->name('super.plans');
        Route::get('/logs', [SuperDashboardController::class, 'logs'])->name('super.logs');
        Route::get('/settings', [SuperDashboardController::class, 'settings'])->name('super.settings');
        Route::post('/settings/update', [SuperDashboardController::class, 'updateSettings'])->name('super.settings.update');

        // Super Admin User Management
        Route::get('/users', [SuperDashboardController::class, 'users'])->name('super.users');
        Route::get('/users/create', [SuperDashboardController::class, 'createUser'])->name('super.users.create');
        Route::post('/users/store', [SuperDashboardController::class, 'storeUser'])->name('super.users.store');
        Route::get('/users/{id}/edit', [SuperDashboardController::class, 'editUser'])->name('super.users.edit');
        Route::post('/users/{id}/update', [SuperDashboardController::class, 'updateUser'])->name('super.users.update');
        Route::post('/users/{id}/delete', [SuperDashboardController::class, 'destroyUser'])->name('super.users.destroy');
        Route::post('/users/{id}/toggle', [SuperDashboardController::class, 'toggleUser'])->name('super.users.toggle');
    });
});
