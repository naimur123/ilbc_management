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
    <h4 class="mb-1">Create Tables & Starting Data</h4>
    <p class="text-muted">Step 3 of 4 — this runs all database migrations (Section 37's full schema) and seeds roles, permissions, the default workflow, SLA rules, approval-rule thresholds and the Microsoft product catalog.</p>

    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <form method="POST" action="{{ route('install.migrate.run') }}">
        @csrf
        <div class="form-check mb-3">
            <input type="checkbox" class="form-check-input" name="seed" value="1" id="seedDemo" checked>
            <label class="form-check-label" for="seedDemo">Also seed demo data (sample vendors, one customer, one demo request, and a login for every role — recommended for evaluation; uncheck for a clean production install with roles/permissions only)</label>
        </div>
        <button class="btn btn-primary">Run Installation</button>
    </form>
</div>
</body>
</html>
