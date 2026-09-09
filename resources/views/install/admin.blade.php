<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install ILBC Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<style>body{background:#f4f6fb;} .card{max-width:640px;margin:60px auto;}</style>
</head>
<body>
<div class="card shadow-sm p-4">
    <h4 class="mb-1">Create Your Administrator Account</h4>
    <p class="text-muted">Step 4 of 4 — this account gets the Super Admin role, which bypasses every permission check (Section 4). Use a real email and a strong password; this is the only account you'll need to sign in and start assigning roles to everyone else.</p>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('install.admin.save') }}">
        @csrf
        <div class="mb-3"><label class="form-label">Full Name *</label><input class="form-control" name="name" value="{{ old('name') }}" required></div>
        <div class="mb-3"><label class="form-label">Email *</label><input type="email" class="form-control" name="email" value="{{ old('email') }}" required></div>
        <div class="mb-3"><label class="form-label">Password *</label><input type="password" class="form-control" name="password" required minlength="8"></div>
        <div class="mb-3"><label class="form-label">Confirm Password *</label><input type="password" class="form-control" name="password_confirmation" required minlength="8"></div>
        <button class="btn btn-primary">Create Account & Finish Install</button>
    </form>
</div>
</body>
</html>
