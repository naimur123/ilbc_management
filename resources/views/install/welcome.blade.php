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
    <h4 class="mb-1">ILBC Management System — Installer</h4>
    <p class="text-muted">Step 1 of 3 — Requirements Check</p>
    <ul class="list-group mb-3">
        @foreach($checks as $label => $ok)
            <li class="list-group-item d-flex justify-content-between">
                {{ $label }}
                <span class="badge {{ $ok ? 'bg-success' : 'bg-danger' }}">{{ $ok ? 'OK' : 'FAIL' }}</span>
            </li>
        @endforeach
    </ul>
    @if($allOk)
        <a href="{{ route('install.database') }}" class="btn btn-primary">Continue to Database Setup</a>
    @else
        <div class="alert alert-danger">Fix the failed items above (ask your hosting provider to enable the missing PHP extension, or upload the missing <code>vendor/</code> folder from a local <code>composer install</code>), then reload this page.</div>
    @endif
</div>
</body>
</html>
