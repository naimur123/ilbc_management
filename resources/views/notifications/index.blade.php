@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
<h4 class="mb-3">Notifications</h4>
<div class="kpi-card p-0">
    <table class="table table-sm table-hover mb-0 align-middle">
        <tbody>
        @forelse($notifications as $n)
            <tr class="{{ $n->read_at ? '' : 'table-light fw-semibold' }}">
                <td>
                    <div>{{ $n->data['title'] ?? '' }}</div>
                    <div class="text-muted small">{{ $n->data['body'] ?? '' }}</div>
                    <div class="text-muted small">{{ $n->created_at->diffForHumans() }}</div>
                </td>
                <td class="text-end">
                    @if(!empty($n->data['url']))<a href="{{ $n->data['url'] }}" class="btn btn-sm btn-outline-primary">Open</a>@endif
                    @if(!$n->read_at)
                    <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-light">Mark Read</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td class="text-center text-muted py-4">No notifications.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-3">{{ $notifications->links() }}</div>
@endsection
