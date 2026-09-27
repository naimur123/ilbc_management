@extends('layouts.app')

@section('content')
  @php
    $template = $template ?? null;
    $isEdit = isset($template) && $template->exists;
  @endphp

  <div class="mb-3">
      <a href="{{ route('email-templates.index') }}" class="btn btn-primary">Back</a>
  </div>

  <form id="template-form" method="POST"
    action="{{ $isEdit ? route('email-templates.update', $template) : route('email-templates.store') }}"
    enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
      @method('PUT')
    @endif

    <div class="kpi-card">
      <div class="row">
        <!-- Left: Form fields -->
        <div class="col-md-8">
          <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $template->name ?? '') }}">
            @error('name') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>Slug</label>
            <input type="text" name="slug" class="form-control" value="{{ old('slug', $template->slug ?? '') }}"
              placeholder="welcome-email">
            @error('slug') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>Subject</label>
            <input type="text" name="subject" id="subject" class="form-control insert-target"
              value="{{ old('subject', $template->subject ?? '') }}">
            @error('subject') <div class="error">{{ $message }}</div> @enderror
          </div>

          <!-- Body (Quill) -->
          <div class="mb-3">
            <label>Body</label>
            <div id="body-editor" style="min-height:320px; border:1px solid #ced4da; border-radius:4px; padding:10px;">
              {!! old('body', $template->body ?? '') !!}
            </div>
            <input type="hidden" name="body" id="body" value="{{ old('body', $template->body ?? '') }}">
            @error('body') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>From Name</label>
            <input type="text" name="from_name" id="from_name" class="form-control insert-target"
              value="{{ old('from_name', $template->from_name ?? '') }}">
            @error('from_name') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>From Email</label>
            <input type="text" name="from_email" id="from_email" class="form-control insert-target"
              value="{{ old('from_email', $template->from_email ?? '') }}">
            @error('from_email') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>CC (comma separated or placeholders)</label>
            <input type="text" name="cc" id="cc" class="form-control insert-target"
              value="{{ old('cc', isset($template) && is_array($template->cc) ? implode(',', $template->cc) : '') }}">
            @error('cc') <div class="error">{{ $message }}</div> @enderror
            @error('cc.*') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>BCC (comma separated or placeholders)</label>
            <input type="text" name="bcc" id="bcc" class="form-control insert-target"
              value="{{ old('bcc', isset($template) && is_array($template->bcc) ? implode(',', $template->bcc) : '') }}">
            @error('bcc') <div class="error">{{ $message }}</div> @enderror
            @error('bcc.*') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>Reply To (comma separated or placeholders)</label>
            <input type="text" name="reply_to" id="reply_to" class="form-control insert-target"
              value="{{ old('reply_to', isset($template) && is_array($template->reply_to) ? implode(',', $template->reply_to) : '') }}">
            @error('reply_to') <div class="error">{{ $message }}</div> @enderror
            @error('reply_to.*') <div class="error">{{ $message }}</div> @enderror
          </div>

          <div class="mb-3">
            <label>
              <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
              Active
            </label>
            @error('is_active') <div class="error">{{ $message }}</div> @enderror
          </div>

          <!-- Attachments (optional, for sending) -->
          {{-- Existing attachments list (show on edit page) --}}
          <div class="mb-3">
            @if (isset($template) && $template && $template->email_attachments && $template->email_attachments->isNotEmpty())
            <label>Existing Attachments</label>
              <ul class="list-unstyled mb-0">
                @foreach ($template->email_attachments as $attachment)
                  <li class="d-flex align-items-center mb-2 attachment-item" data-attachment-id="{{ $attachment->id }}">
                    <a href="{{ asset('storage/' . $attachment->path) }}" target="_blank">
                      {{ $attachment->name ?? basename($attachment->path) }}
                    </a>
                    <button type="button"
                            class="btn btn-sm btn-danger delete-attachment-btn ms-2"
                            data-url="{{ route('email-template-attachments.destroy', $attachment) }}"
                            title="Delete attachment">
                      <i class="fa-solid fa-delete-left"></i>
                    </button>
                  </li>
                @endforeach
              </ul>
            @else
              <span class="text-muted">No attachments yet.</span>
            @endif
          </div>

          {{-- Attachments (existing form input to add new attachments) --}}
          <div class="mb-3">
            <label>Attachments (optional, for sending)</label>
            <input type="file" name="attachments[]" multiple class="form-control">
          </div>
        </div>

        <!-- Right: Placeholders -->
        <div class="col-md-4">
          <div class="card" style="border:1px solid #ddd; padding:16px; border-radius:6px;">
            <h3>Available Placeholders</h3>
            <div class="mb-2">
              @foreach($placeholders as $key => $label)
                <button type="button" class="placeholder-btn btn btn-light btn-sm me-1 mb-1"
                  onclick="insertPlaceholder('{{ $key }}')">
                  {{ $key }} - {{ $label }}
                </button>
              @endforeach
            </div>
            <small class="d-block text-muted">Click a placeholder to insert it into the body.</small>
          </div>
        </div>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">
          {{ $isEdit ? 'Update Template' : 'Create Template' }}
        </button>
      </div>
    </div>


  </form>

  <script>
    // Initialize Quill editor
    let quillEditor = null;

    document.addEventListener('DOMContentLoaded', function () {
      quillEditor = new Quill('#body-editor', {
        theme: 'snow',
        modules: {
          toolbar: [
            'bold', 'italic', 'underline', 'strike', 'link',
            { 'list': 'ordered' }, { 'list': 'bullet' }, 'clean'
          ]
        }
      });

      // Load initial content from the hidden input if any
      const initialHtml = document.getElementById('body').value;
      if (initialHtml) {
        quillEditor.clipboard.dangerouslyPasteHTML(initialHtml);
      }

      // On form submit, push HTML back to the hidden input
      const form = document.getElementById('template-form');
      if (form) {
        form.addEventListener('submit', function () {
          const html = quillEditor.root.innerHTML;
          document.getElementById('body').value = html;
        });
      }
    });

    // Insert placeholder into body content regardless of focus
    function insertPlaceholder(value) {
      if (quillEditor) {
        const range = quillEditor.getSelection(true);
        quillEditor.insertText(range.index, value);
        quillEditor.setSelection(range.index + value.length);
        return;
      }

      // Fallback: append to the textarea (rare)
      const field = document.getElementById('body');
      if (!field) return;
      field.value = (field.value || '') + value;
    }

    document.addEventListener('click', function (event) {
      const deleteBtn = event.target.closest('.delete-attachment-btn');
      if (!deleteBtn) return;

      const confirmed = window.confirm('Delete this attachment?');
      if (!confirmed) return;

      const url = deleteBtn.dataset.url;
      const csrfToken = document.querySelector('input[name="_token"]')?.value;

      if (!url || !csrfToken) {
        alert('Unable to delete attachment right now.');
        return;
      }

      const formData = new FormData();
      formData.append('_token', csrfToken);
      formData.append('_method', 'DELETE');

      fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData,
      })
      .then(response => {
        if (!response.ok) {
          throw new Error('Request failed');
        }
        const item = deleteBtn.closest('.attachment-item');
        if (item) item.remove();
      })
      .catch(() => {
        alert('Failed to delete attachment.');
      });
    });
  </script>

@endsection