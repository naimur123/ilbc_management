<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmailTemplateRequest;
use App\Models\EmailTemplateAttachment;
use App\Models\EmailTemplates;
use App\Services\Email\PlaceholderRegistry;
use App\Services\Email\TemplateMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmailTemplateController extends Controller
{
    public function __construct(protected PlaceholderRegistry $placeholderRegistry) 
    {
    }

    public function index()
    {
        $templates = EmailTemplates::latest()->paginate(15);

        return view('email-templates.index', compact('templates'));
    }

    public function create()
    {
        $placeholders = $this->placeholderRegistry->all();

        return view('email-templates._form', compact('placeholders'));
    }

    public function store(StoreEmailTemplateRequest $request)
    {

        $template = EmailTemplates::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'subject' => $request->subject,
            'body' => $request->body,
            'from_name' => $request->from_name,
            'from_email' => $request->from_email,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'reply_to' => $request->reply_to,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments', 'public');
                $template->email_attachments()->create([
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('email-templates.index')->with('success','Template created');
    }

    public function show(EmailTemplates $email_template)
    {
        return view('email-templates.show', [
            'template' => $email_template,
            'placeholders' => $this->placeholderRegistry->all(),
        ]);
    }

    public function edit(EmailTemplates $email_template)
    {
        return view('email-templates._form', [
            'template' => $email_template,
            'placeholders' => $this->placeholderRegistry->all(),
        ]);
    }

    public function update(StoreEmailTemplateRequest $request, EmailTemplates $email_template)
    {
        $email_template->update([
            'name' => $request->name,
            'slug' => $request->slug,
            'subject' => $request->subject,
            'body' => $request->body,
            'from_name' => $request->from_name,
            'from_email' => $request->from_email,
            'cc' => $request->cc,
            'bcc' => $request->bcc,
            'reply_to' => $request->reply_to,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('email_attachments', 'public');
                $email_template->email_attachments()->create([
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('email-templates.index')->with('success','Template updated');
    }

    public function destroy(EmailTemplates $email_template)
    {
        $email_template->delete();

        return redirect()
            ->route('email-templates.index')
            ->with('success', 'Email template deleted successfully.');
    }

    /**
     * Simple test send action.
     */

    public function attachment_destroy(EmailTemplateAttachment $attachment)
    {
        if ($attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        return redirect()->back()->with('success', 'Attachment deleted');
    }
}