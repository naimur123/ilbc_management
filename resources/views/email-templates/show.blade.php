@extends('layouts.app')

@section('content')
    <div class="mb-3 d-flex">
        <a href="{{ route('email-templates.index') }}" class="btn">Back</a>
        <a href="{{ route('email-templates.edit', $template) }}" class="btn btn-primary">Edit</a>
    </div>

    <div class="card">
        <p><strong>Name:</strong> {{ $template->name }}</p>
        <p><strong>Slug:</strong> {{ $template->slug }}</p>
        <p><strong>Subject:</strong> {{ $template->subject }}</p>
        <p><strong>From Name:</strong> {{ $template->from_name }}</p>
        <p><strong>From Email:</strong> {{ $template->from_email }}</p>
        <p><strong>CC:</strong> {{ is_array($template->cc) ? implode(', ', $template->cc) : '' }}</p>
        <p><strong>BCC:</strong> {{ is_array($template->bcc) ? implode(', ', $template->bcc) : '' }}</p>
        <p><strong>Reply To:</strong> {{ is_array($template->reply_to) ? implode(', ', $template->reply_to) : '' }}</p>
        <p><strong>Active:</strong> {{ $template->is_active ? 'Yes' : 'No' }}</p>
    </div>

    <div class="card">
        <h3>Body Preview</h3>
        <div>{!! $template->body !!}</div>
    </div>

    <div class="card">
        <h3>Send Test Email</h3>
        <form method="POST" action="{{ route('email-templates.send-test', $template) }}">
            @csrf
            <div class="mb-3">
                <label>Send To</label>
                <input type="email" name="to" class="form-control" placeholder="test@example.com" required>
                @error('to') <div class="error">{{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn btn-success">Send Test</button>
        </form>
    </div>

    <div class="card">
        <h3>Available Placeholders</h3>
        <ul>
            @foreach($placeholders as $key => $label)
                <li><strong>{{ $key }}</strong> - {{ $label }}</li>
            @endforeach
        </ul>
    </div>
@endsection