<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install ILBC Management System</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
<style>body{background:#f4f6fb;} .card{max-width:640px;margin:60px auto;}</style>
</head>
<body>
<div class="card shadow-sm p-4">
    <h4 class="mb-1 text-success"><i class="bi bi-check-circle"></i> Installation Complete</h4>
    <p>The database is set up and your administrator account is ready. The installer is now permanently locked — this page (and every other <code>/install/*</code> URL) will 404 from here on.</p>
    <ul>
        <li>Log in with the administrator email and password you just created.</li>
        <li>Go to <strong>Administration &gt; User &amp; Role Management</strong> to add your real Sales, Billing, Reviewer, Loader, Auditor and Closer users and assign them roles.</li>
        <li>Go to <strong>System Settings</strong> to set your company name, currency and timezone if different from the defaults.</li>
        <li>Set up a cPanel Cron Job to run <code>php artisan schedule:run</code> every minute (see the README) so SLA escalation checks keep working.</li>
    </ul>
    <a href="{{ url('/login') }}" class="btn btn-primary">Go to Login</a>
</div>
</body>
</html>
