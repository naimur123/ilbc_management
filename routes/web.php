<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BillingClearanceController;
use App\Http\Controllers\BillingInvoiceController;
use App\Http\Controllers\ClosureController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Lookups\BillingTypeController;
use App\Http\Controllers\Lookups\CurrencyController;
use App\Http\Controllers\Lookups\DepartmentController;
use App\Http\Controllers\Lookups\PaymentTermController;
use App\Http\Controllers\Lookups\SubscriptionTypeController;
use App\Http\Controllers\Lookups\TaxController;
use App\Http\Controllers\LoadingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSkuController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReviewerController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalespersonController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SlaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VendorPriceController;
use App\Http\Controllers\VendorProvisioningController;
use App\Http\Controllers\WorkflowSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // if (! file_exists(storage_path('installed.lock'))) {
    //     return redirect()->route('install.welcome');
    // }

    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    // Section 38: login throttling — 5 attempts/minute per IP+email pair,
    // Laravel's built-in throttle middleware backed by the cache store.
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('search', SearchController::class)->name('search');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // ---- Requests / Sales Entry --------------------------------------
    Route::resource('requests', RequestController::class);
    Route::post('requests/{request}/submit', [RequestController::class, 'submit'])->name('requests.submit');
    Route::post('requests/{request}/cancel', [RequestController::class, 'cancel'])->name('requests.cancel');
    Route::post('requests/{request}/reopen', [RequestController::class, 'requestReopen'])->name('requests.reopen');
    Route::post('requests/{request}/reopen/{reopen}/approve', [RequestController::class, 'approveReopen'])->name('requests.reopen.approve');
    Route::post('requests/{request}/comments', [RequestController::class, 'addComment'])->name('requests.comments.store');
    Route::get('requests/{request}/items/{item}/vendor-comparison', [ReviewerController::class, 'vendorComparison'])->name('requests.items.vendor-comparison');
    Route::post('requests/{request}/items/{item}/vendor-selection', [ReviewerController::class, 'selectVendor'])->name('requests.items.vendor-selection');

    // ---- Billing Clearance --------------------------------------------
    Route::get('billing-clearance', [BillingClearanceController::class, 'index'])->name('billing-clearance.index');
    Route::get('billing-clearance/{request}', [BillingClearanceController::class, 'show'])->name('billing-clearance.show');
    Route::post('billing-clearance/{request}', [BillingClearanceController::class, 'decide'])->name('billing-clearance.decide');

    // ---- Reviewer Approval ----------------------------------------------
    Route::get('reviewer', [ReviewerController::class, 'index'])->name('reviewer.index');
    Route::get('reviewer/{request}', [ReviewerController::class, 'show'])->name('reviewer.show');
    Route::post('reviewer/{request}/checklist', [ReviewerController::class, 'saveChecklist'])->name('reviewer.checklist');
    Route::post('reviewer/{request}/decide', [ReviewerController::class, 'decide'])->name('reviewer.decide');

    // ---- Loading / Installation -----------------------------------------
    Route::get('loading', [LoadingController::class, 'index'])->name('loading.index');
    // Customer-wise / order-wise vendor & SKU assignment screen (change
    // request, Sept 2026) — one request at a time, listing every item in it.
    Route::get('loading/{request}/review', [LoadingController::class, 'review'])->name('loading.review');
    Route::post('loading/{request}/assign', [LoadingController::class, 'saveAssignments'])->name('loading.assign');
    Route::get('loading/{item}', [LoadingController::class, 'show'])->name('loading.show');
    Route::post('loading/{item}/draft', [LoadingController::class, 'saveDraft'])->name('loading.draft');
    Route::post('loading/{item}/complete', [LoadingController::class, 'complete'])->name('loading.complete');

    // ---- SLA Management (change request, Sept 2026) -----------------------
    // Sits between Loading / Installation and Audit — an SLA record is
    // created automatically the moment Loading finishes (see
    // LoadingController::complete()), then monitored here until it's
    // completed, waived, or overdue.
    Route::get('sla', [SlaController::class, 'index'])->name('sla.index');
    Route::post('sla', [SlaController::class, 'store'])->name('sla.store');
    Route::get('sla-configuration', [SlaController::class, 'configuration'])->name('sla.configuration');
    Route::post('sla-configuration', [SlaController::class, 'storeConfiguration'])->name('sla.configuration.store');
    Route::put('sla-configuration/{slaConfiguration}', [SlaController::class, 'updateConfiguration'])->name('sla.configuration.update');
    Route::delete('sla-configuration/{slaConfiguration}', [SlaController::class, 'destroyConfiguration'])->name('sla.configuration.destroy');
    Route::post('sla-configuration/settings', [SlaController::class, 'updateSettings'])->name('sla.configuration.settings');
    Route::get('sla-report', [SlaController::class, 'report'])->name('sla.report');
    Route::get('sla/{requestSla}', [SlaController::class, 'show'])->name('sla.show');
    Route::post('sla/{requestSla}/complete', [SlaController::class, 'complete'])->name('sla.complete');
    Route::post('sla/{requestSla}/waive', [SlaController::class, 'waive'])->name('sla.waive');

    // ---- Audit -----------------------------------------------------------
    Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    Route::get('audit/{item}', [AuditController::class, 'show'])->name('audit.show');
    Route::post('audit/{item}/decide', [AuditController::class, 'decide'])->name('audit.decide');

    // ---- Billing & Invoice ------------------------------------------------
    Route::get('billing-invoice', [BillingInvoiceController::class, 'index'])->name('billing-invoice.index');
    Route::get('billing-invoice/{request}', [BillingInvoiceController::class, 'show'])->name('billing-invoice.show');
    Route::post('billing-invoice/{request}/generate', [BillingInvoiceController::class, 'generate'])->name('billing-invoice.generate');
    Route::post('billing-invoice/{request}/sent', [BillingInvoiceController::class, 'markSent'])->name('billing-invoice.sent');
    Route::post('billing-invoice/{request}/done', [BillingInvoiceController::class, 'markDone'])->name('billing-invoice.done');
    Route::get('billing-invoice/{invoice}/pdf', [BillingInvoiceController::class, 'pdf'])->name('billing-invoice.pdf');

    // ---- Closure -----------------------------------------------------------
    Route::get('closure', [ClosureController::class, 'index'])->name('closure.index');
    Route::get('closure/{request}', [ClosureController::class, 'show'])->name('closure.show');
    Route::post('closure/{request}/collection', [ClosureController::class, 'saveCollection'])->name('closure.collection');
    Route::post('closure/{request}/checklist', [ClosureController::class, 'saveChecklist'])->name('closure.checklist');
    Route::post('closure/{request}/close', [ClosureController::class, 'close'])->name('closure.close');

    // ---- Vendor Management ---------------------------------------------
    Route::resource('vendors', VendorController::class);
    Route::get('vendors-performance', [VendorController::class, 'performance'])->name('vendors.performance');
    Route::put('vendors/{vendor}/provisioning', [VendorProvisioningController::class, 'update'])->name('vendors.provisioning.update');

    Route::get('vendor-prices', [VendorPriceController::class, 'index'])->name('vendor-prices.index');
    Route::get('vendor-prices/history', [VendorPriceController::class, 'history'])->name('vendor-prices.history');
    Route::get('vendor-prices/create', [VendorPriceController::class, 'create'])->name('vendor-prices.create');
    Route::post('vendor-prices', [VendorPriceController::class, 'store'])->name('vendor-prices.store');

    // ---- Product / SKU ----------------------------------------------------
    Route::resource('product-categories', ProductCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('products', ProductController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('product-skus', ProductSkuController::class)->only(['index', 'store', 'update', 'destroy']);

    // ---- Master Data --------------------------------------------------------
    Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('salespersons', SalespersonController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('departments', DepartmentController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('billing-types', BillingTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('subscription-types', SubscriptionTypeController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('payment-terms', PaymentTermController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('currencies', CurrencyController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('taxes', TaxController::class)->only(['index', 'store', 'update', 'destroy']);

    // ---- Reports --------------------------------------------------------
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/vendor', [ReportController::class, 'vendor'])->name('reports.vendor');
    Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('reports/sla', [ReportController::class, 'sla'])->name('reports.sla');

    // ---- Administration ----------------------------------------------------
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);
    Route::get('workflow-settings', [WorkflowSettingsController::class, 'edit'])->name('workflow-settings.edit');
    Route::put('workflow-settings', [WorkflowSettingsController::class, 'update'])->name('workflow-settings.update');
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});
