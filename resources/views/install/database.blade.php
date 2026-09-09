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
    <h4 class="mb-1">Database Setup</h4>
    <p class="text-muted">Step 2 of 3 — enter the MySQL database details from cPanel &gt; MySQL Databases (create the database and a user with all privileges on it first, if you haven't already).</p>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('install.database.save') }}">
        @csrf
        <div class="mb-3"><label class="form-label">Site URL *</label><input class="form-control" name="app_url" value="{{ old('app_url', $env['APP_URL'] ?? 'https://') }}" required></div>
        <div class="mb-3"><label class="form-label">Database Host *</label><input class="form-control" name="db_host" value="{{ old('db_host', $env['DB_HOST'] ?? 'localhost') }}" required></div>
        <div class="mb-3"><label class="form-label">Database Port *</label><input class="form-control" name="db_port" value="{{ old('db_port', $env['DB_PORT'] ?? '3306') }}" required></div>
        <div class="mb-3"><label class="form-label">Database Name *</label><input class="form-control" name="db_database" value="{{ old('db_database', $env['DB_DATABASE'] ?? '') }}" required></div>
        <div class="mb-3"><label class="form-label">Database Username *</label><input class="form-control" name="db_username" value="{{ old('db_username', $env['DB_USERNAME'] ?? '') }}" required></div>
        <div class="mb-3"><label class="form-label">Database Password</label><input type="password" class="form-control" name="db_password"></div>
        <button class="btn btn-primary">Test Connection & Continue</button>
    </form>
</div>
</body>
</html>
