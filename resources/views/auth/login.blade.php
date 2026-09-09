<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg,#101a34,#1c2c52); min-height:100vh; display:flex; align-items:center; }
        .login-card { max-width:400px; margin:auto; border-radius:.75rem; }
        .brand-badge { width:56px;height:56px;border-radius:50%;background:#2563eb;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.6rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="card login-card shadow-lg border-0">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4">
                <div class="brand-badge mx-auto mb-2"><i class="bi bi-arrow-repeat"></i></div>
                <h5 class="fw-bold mb-0">ILBC Management System</h5>
                <small class="text-muted">Installation, Loading, Billing & Closure</small>
            </div>
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>
                @endif
                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
