@php($user = auth()->user())
<div class="nav flex-column pb-4">
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid-1x2"></i> Dashboard</a>

    @can('request.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuRequests"><i class="bi bi-inboxes"></i> Requests <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('requests.*') ? 'show' : '' }}" id="menuRequests">
        <a class="nav-link" href="{{ route('requests.index') }}">All Requests</a>
        <a class="nav-link" href="{{ route('requests.index', ['mine' => 1]) }}">My Requests</a>
        @can('request.create')<a class="nav-link" href="{{ route('requests.create') }}">Create New Request</a>@endcan
        <a class="nav-link" href="{{ route('requests.index', ['status' => 'DRAFT']) }}">Draft Requests</a>
        <a class="nav-link" href="{{ route('requests.index', ['status' => 'REVIEWER_RETURNED']) }}">Returned Requests</a>
    </div>
    @endcan

    @can('billing.clearance.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuBilling"><i class="bi bi-shield-check"></i> Billing Clearance <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('billing-clearance.*') ? 'show' : '' }}" id="menuBilling">
        <a class="nav-link" href="{{ route('billing-clearance.index', ['status' => 'pending']) }}">Pending Clearance</a>
        <a class="nav-link" href="{{ route('billing-clearance.index', ['status' => 'cleared']) }}">Cleared Requests</a>
        <a class="nav-link" href="{{ route('billing-clearance.index', ['status' => 'rejected']) }}">Rejected / Hold</a>
        <a class="nav-link" href="{{ route('billing-clearance.index', ['status' => 'outstanding']) }}">Credit / Outstanding</a>
    </div>
    @endcan

    @can('review.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuReview"><i class="bi bi-person-check"></i> Reviewer Approval <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('reviewer.*') ? 'show' : '' }}" id="menuReview">
        <a class="nav-link" href="{{ route('reviewer.index', ['status' => 'pending']) }}">Pending Review</a>
        <a class="nav-link" href="{{ route('reviewer.index', ['status' => 'approved']) }}">Approved Requests</a>
        <a class="nav-link" href="{{ route('reviewer.index', ['status' => 'returned']) }}">Returned / Rejected</a>
        <a class="nav-link" href="{{ route('reviewer.index', ['status' => 'hold']) }}">On Hold</a>
    </div>
    @endcan

    @can('loading.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuLoading"><i class="bi bi-box-seam"></i> Loading / Installation <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('loading.*') ? 'show' : '' }}" id="menuLoading">
        <a class="nav-link" href="{{ route('loading.index', ['status' => 'pending']) }}">Pending Loading</a>
        <a class="nav-link" href="{{ route('loading.index', ['status' => 'mine']) }}">My Loading</a>
        <a class="nav-link" href="{{ route('loading.index', ['status' => 'completed']) }}">Completed Loading</a>
    </div>
    @endcan

    @can('audit.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuAudit"><i class="bi bi-clipboard-data"></i> Audit <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('audit.*') ? 'show' : '' }}" id="menuAudit">
        <a class="nav-link" href="{{ route('audit.index', ['status' => 'pending']) }}">Audit Pending</a>
        <a class="nav-link" href="{{ route('audit.index', ['status' => 'approved']) }}">Approved</a>
        <a class="nav-link" href="{{ route('audit.index', ['status' => 'returned']) }}">Returned for Correction</a>
        <a class="nav-link" href="{{ route('audit.index', ['status' => 'hold']) }}">On Hold</a>
    </div>
    @endcan

    @can('invoice.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuInvoice"><i class="bi bi-receipt"></i> Billing & Invoice <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('billing-invoice.*') ? 'show' : '' }}" id="menuInvoice">
        <a class="nav-link" href="{{ route('billing-invoice.index', ['status' => 'pending']) }}">Pending Invoice</a>
        <a class="nav-link" href="{{ route('billing-invoice.index', ['status' => 'generated']) }}">Invoice Generated</a>
        <a class="nav-link" href="{{ route('billing-invoice.index', ['status' => 'sent']) }}">Invoice Sent</a>
        <a class="nav-link" href="{{ route('billing-invoice.index', ['status' => 'done']) }}">Billing Done</a>
    </div>
    @endcan

    @can('closure.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuClosure"><i class="bi bi-lock"></i> Closure <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('closure.*') ? 'show' : '' }}" id="menuClosure">
        <a class="nav-link" href="{{ route('closure.index', ['status' => 'ready']) }}">Ready for Closure</a>
        <a class="nav-link" href="{{ route('closure.index', ['status' => 'closed']) }}">Closed Requests</a>
        <a class="nav-link" href="{{ route('closure.index', ['status' => 'reopened']) }}">Reopened Requests</a>
    </div>
    @endcan

    @can('vendor.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuVendor"><i class="bi bi-truck"></i> Vendor Management <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('vendors.*') || request()->routeIs('vendor-prices.*') ? 'show' : '' }}" id="menuVendor">
        <a class="nav-link" href="{{ route('vendors.index') }}">Vendors</a>
        <a class="nav-link" href="{{ route('vendor-prices.index') }}">Vendor Product Price</a>
        <a class="nav-link" href="{{ route('vendor-prices.history') }}">Price History</a>
        <a class="nav-link" href="{{ route('vendors.performance') }}">Vendor Performance</a>
    </div>
    @endcan

    <a class="nav-link" data-bs-toggle="collapse" href="#menuProduct"><i class="bi bi-boxes"></i> Product / SKU <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('product-categories.*') || request()->routeIs('products.*') || request()->routeIs('product-skus.*') ? 'show' : '' }}" id="menuProduct">
        <a class="nav-link" href="{{ route('product-categories.index') }}">Categories</a>
        <a class="nav-link" href="{{ route('products.index') }}">Products</a>
        <a class="nav-link" href="{{ route('product-skus.index') }}">SKU</a>
    </div>

    <a class="nav-link" data-bs-toggle="collapse" href="#menuMaster"><i class="bi bi-database"></i> Master Data <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu" id="menuMaster">
        <a class="nav-link" href="{{ route('customers.index') }}">Customers</a>
        <a class="nav-link" href="{{ route('departments.index') }}">Departments</a>
        <a class="nav-link" href="{{ route('salespersons.index') }}">Salespersons</a>
        <a class="nav-link" href="{{ route('billing-types.index') }}">Billing Types</a>
        <a class="nav-link" href="{{ route('subscription-types.index') }}">Subscription Types</a>
        <a class="nav-link" href="{{ route('payment-terms.index') }}">Payment Terms</a>
        <a class="nav-link" href="{{ route('currencies.index') }}">Currencies</a>
        <a class="nav-link" href="{{ route('taxes.index') }}">Taxes</a>
    </div>

    @can('report.view')
    <a class="nav-link" data-bs-toggle="collapse" href="#menuReports"><i class="bi bi-bar-chart"></i> Reports <i class="bi bi-chevron-down float-end"></i></a>
    <div class="collapse submenu {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="menuReports">
        <a class="nav-link" href="{{ route('reports.sales') }}">Sales Report</a>
        <a class="nav-link" href="{{ route('reports.vendor') }}">Vendor Report</a>
        <a class="nav-link" href="{{ route('reports.profit') }}">Profit / Margin Report</a>
        <a class="nav-link" href="{{ route('reports.sla') }}">SLA / Aging Report</a>
    </div>
    @endcan

    @canany(['user.manage', 'role.manage', 'workflow.manage', 'settings.manage', 'audit_log.view'])
    <hr class="text-secondary mx-3">
    <div class="px-3 text-uppercase text-secondary" style="font-size:.7rem;">Administration</div>
    @can('user.manage')<a class="nav-link" href="{{ route('users.index') }}"><i class="bi bi-people"></i> User & Role Management</a>@endcan
    @can('workflow.manage')<a class="nav-link" href="{{ route('workflow-settings.edit') }}"><i class="bi bi-diagram-3"></i> Workflow & Settings</a>@endcan
    @can('settings.manage')<a class="nav-link" href="{{ route('settings.edit') }}"><i class="bi bi-gear"></i> System Settings</a>@endcan
    @can('audit_log.view')<a class="nav-link" href="{{ route('audit-logs.index') }}"><i class="bi bi-journal-text"></i> Audit Logs</a>@endcan
    @endcanany
</div>
