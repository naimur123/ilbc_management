<?php

namespace App\Http\Requests;

use App\Services\Email\EmailTemplateRender;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'cc' => $this->normalizeEmails($this->input('cc')),
            'bcc' => $this->normalizeEmails($this->input('bcc')),
            'reply_to' => $this->normalizeEmails($this->input('reply_to')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('email_template')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('email_templates', 'slug')->ignore($id),
            ],
            'subject' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'from_name' => ['nullable', 'string', 'max:255'],
            'from_email' => ['nullable', 'string', 'max:255'],
            'cc' => ['nullable', 'array'],
            'cc.*' => ['nullable', 'string', 'max:255'],
            'bcc' => ['nullable', 'array'],
            'bcc.*' => ['nullable', 'string', 'max:255'],
            'reply_to' => ['nullable', 'array'],
            'reply_to.*' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
            'attachments.*' => 'file|max:10240',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            /** @var EmailTemplateRender $renderer */
            $renderer = app(EmailTemplateRender::class);

            $fields = [
                'subject' => $this->input('subject'),
                'body' => $this->input('body'),
                'from_name' => $this->input('from_name'),
                'from_email' => $this->input('from_email'),
            ];

            foreach ($fields as $field => $content) {
                $unknown = $renderer->unknownPlaceholders($content);
                if (!empty($unknown)) {
                    $validator->errors()->add(
                        $field,
                        'Unknown placeholders: ' . implode(', ', $unknown)
                    );
                }
            }

            foreach (['cc', 'bcc', 'reply_to'] as $field) {
                foreach (($this->input($field) ?? []) as $index => $value) {
                    $unknown = $renderer->unknownPlaceholders($value);
                    if (!empty($unknown)) {
                        $validator->errors()->add(
                            "{$field}.{$index}",
                            'Unknown placeholders: ' . implode(', ', $unknown)
                        );
                    }
                }
            }
        });
    }

    private function normalizeEmails($value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn ($v) => $v !== ''));
        }

        if (is_string($value)) {
            return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
        }

        return [];
    }
}