<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --ilbc-navy: #101a34;
            --ilbc-navy-active: #1c2c52;
            --ilbc-blue: #2563eb;
            --ilbc-green: #16a34a;
            --ilbc-orange: #f59e0b;
            --ilbc-red: #dc2626;
            --ilbc-purple: #7c3aed;
            --ilbc-teal: #0d9488;
        }
        body { background: #f4f6fb; font-size: .925rem; }
        #sidebar {
            width: 250px; min-height: 100vh; background: var(--ilbc-navy); position: fixed; top:0; left:0; bottom:0;
            overflow-y: auto; z-index: 1030;
        }
        #sidebar .brand { color:#fff; font-weight:700; padding:1rem 1.1rem; display:flex; align-items:center; gap:.5rem; }
        #sidebar .nav-link { color:#c7cede; padding:.55rem 1.1rem; font-size:.87rem; border-radius:0; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: var(--ilbc-navy-active); color:#fff; }
        #sidebar .nav-link i { width:1.2rem; }
        #sidebar .submenu .nav-link { padding-left:2.5rem; font-size:.82rem; }
        #main { margin-left:250px; }
        #topbar { background:#fff; border-bottom:1px solid #e5e7eb; padding:.6rem 1.25rem; position:sticky; top:0; z-index:1020; }
        .kpi-card { border:1px solid #e5e7eb; border-radius:.6rem; background:#fff; padding:.9rem 1rem; height:100%; }
        .kpi-card .kpi-value { font-size:1.4rem; font-weight:700; }
        .badge-stage { font-weight:600; }
        .status-on-time { color: var(--ilbc-green); }
        .status-due-soon { color: var(--ilbc-orange); }
        .status-overdue { color: var(--ilbc-red); }
        .timeline-step { text-align:center; flex:1; }
        .timeline-dot { width:2rem; height:2rem; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.9rem; }
        .timeline-done .timeline-dot { background: var(--ilbc-green); }
        .timeline-current .timeline-dot { background: var(--ilbc-blue); }
        .timeline-pending .timeline-dot { background:#cbd5e1; color:#475569; }
        @media (max-width: 991px) {
            #sidebar { left:-250px; transition:left .2s; }
            #sidebar.show { left:0; }
            #main { margin-left:0; }
        }
    </style>
    @stack('styles')
</head>
<body>
<nav id="sidebar">
    <div class="brand"><i class="bi bi-arrow-repeat fs-4"></i> <span>ILBC<br><small style="font-weight:400;font-size:.65rem;">MANAGEMENT SYSTEM</small></span></div>
    @include('partials.menu')
</nav>

<div id="main">
    <div id="topbar" class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')"><i class="bi bi-list"></i></button>
            <form action="{{ route('search') }}" method="GET" class="d-none d-md-block">
                <input type="search" name="q" class="form-control form-control-sm" style="width:320px" placeholder="Search Request / Customer / Invoice..." value="{{ request('q') }}">
            </form>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <a href="#" class="position-relative text-dark" data-bs-toggle="dropdown"><i class="bi bi-bell fs-5"></i>
                    @if(($unreadNotificationCount ?? 0) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:.6rem;">{{ $unreadNotificationCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end p-2" style="width:320px;">
                    @forelse(auth()->user()->unreadNotifications()->take(6)->get() as $n)
                        <a href="{{ $n->data['url'] ?? '#' }}" class="dropdown-item small border-bottom py-2">
                            <strong>{{ $n->data['title'] }}</strong><br><span class="text-muted">{{ $n->data['body'] }}</span>
                        </a>
                    @empty
                        <span class="dropdown-item-text small text-muted">No new notifications</span>
                    @endforelse
                    <a href="{{ route('notifications.index') }}" class="dropdown-item small text-center text-primary">View All</a>
                </div>
            </div>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center gap-2 text-dark text-decoration-none" data-bs-toggle="dropdown">
                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white" style="width:32px;height:32px;">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="d-none d-md-block">
                        <div style="line-height:1;">{{ auth()->user()->name }}</div>
                        <small class="text-muted">{{ auth()->user()->getRoleNames()->first() }}</small>
                    </span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person me-2"></i>My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="p-3 p-md-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">{{ session('success') }} <button class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }} <button class="btn-close" data-bs-dismiss="alert"></button></div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script> --}}
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@stack('scripts')
</body>
</html>
