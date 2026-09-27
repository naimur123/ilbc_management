@extends('layouts.app')

@section('content')
<div class="container mt-4">
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Email Templates</h5>
      <a href="{{ route('email-templates.create') }}" class="btn btn-primary btn-sm">Create Template</a>
    </div>

    <div class="card-body p-2">
      <div class="table-responsive">
        <table class="table table-striped table-hover mb-2 align-middle">
          <thead>
            <tr>
              <th scope="col">SI.</th>
              <th scope="col">Name</th>
              <th scope="col">Slug</th>
              <th scope="col">Active</th>
              <th scope="col">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($templates as $key => $template)
              <tr>
                <td>{{ ++$key }}</td>
                <td>{{ $template->name }}</td>
                <td>{{ $template->slug }}</td>
                <td>
                  <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <a href="{{ route('email-templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                  <form method="POST" action="{{ route('email-templates.destroy', $template) }}"
                        onsubmit="return confirm('Delete this template?')" style="display:inline-block;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center">No templates found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-footer d-flex justify-content-end">
      {{ $templates->links() }}
    </div>
  </div>
</div>
@endsection