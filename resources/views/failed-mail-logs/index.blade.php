@extends('layouts.app')

@section('content')
<div class="container-fluid mt-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 mb-0">Failed Emails</h1>
        <form method="GET" action="{{ route('failed-mail-logs.index') }}" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" aria-label="Email status">
                @foreach(['failed' => 'Failed', 'processing' => 'Processing', 'sent' => 'Sent'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-outline-secondary" type="submit">Filter</button>
        </form>
    </div>

    <div class="table-responsive bg-white border rounded">
        <table class="table table-hover table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Failed at</th>
                    <th>Template</th>
                    <th>Recipient</th>
                    <th>Transport</th>
                    <th>Status</th>
                    <th>Retries</th>
                    <th>Latest error</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($failedMails as $mail)
                    <tr>
                        <td class="text-nowrap">{{ $mail->failed_at?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                        <td>{{ $mail->template_slug ?? 'N/A' }}</td>
                        <td>{{ $mail->to_email ?? 'N/A' }}</td>
                        <td>{{ $mail->transport ?? 'smtp' }}</td>
                        <td><span class="badge {{ $mail->status === 'sent' ? 'bg-success' : ($mail->status === 'processing' ? 'bg-warning text-dark' : 'bg-danger') }}">{{ ucfirst($mail->status ?? 'failed') }}</span></td>
                        <td>{{ $mail->attempt_count ?? 0 }}</td>
                        <td style="min-width: 220px; max-width: 380px;">
                            <span title="{{ $mail->error_message }}">{{ \Illuminate\Support\Str::limit($mail->error_message, 140) ?: 'N/A' }}</span>
                        </td>
                        <td class="text-end text-nowrap">
                            @if($mail->status === 'failed')
                                <form method="POST" action="{{ route('failed-mail-logs.resend', $mail) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="bi bi-send me-1" aria-hidden="true"></i>Send again
                                    </button>
                                </form>
                            @elseif($mail->status === 'processing')
                                <span class="text-muted small">In progress</span>
                            @else
                                <span class="text-muted small">Sent {{ $mail->sent_at?->format('Y-m-d H:i') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No {{ $status }} emails.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-end mt-3">
        {{ $failedMails->links() }}
    </div>
</div>
@endsection