<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks every /install/* route once storage/installed.lock exists, so the
 * installer can never be re-run (and its DB-credential-writing endpoint
 * never re-exposed) after go-live. See InstallController for the flow this
 * guards.
 */
class InstallGuard
{
    public function handle(Request $request, Closure $next): Response
    {
        if (file_exists(storage_path('installed.lock'))) {
            abort(404);
        }

        return $next($request);
    }
}
