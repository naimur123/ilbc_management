<?php

namespace App\Http\Controllers;

use App\Models\FailedMailLog;
use App\Services\Email\FailedMailRetryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class FailedMailLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('settings.manage');

        $status = $request->query('status', 'failed');
        $statuses = ['failed', 'processing', 'sent'];

        if (! in_array($status, $statuses, true)) {
            $status = 'failed';
        }

        $failedMails = FailedMailLog::query()
            ->where('status', $status)
            ->latest('failed_at')
            ->paginate(20)
            ->withQueryString();

        return view('failed-mail-logs.index', compact('failedMails', 'status'));
    }

    public function resend(FailedMailLog $failedMailLog, FailedMailRetryService $retryService)
    {
        Gate::authorize('settings.manage');

        if ($retryService->retry($failedMailLog)) {
            return back()->with('success', 'Email sent successfully.');
        }

        return back()->with('error', 'Email was not sent. Check its status and latest error before trying again.');
    }
}