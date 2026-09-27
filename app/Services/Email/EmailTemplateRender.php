<?php

namespace App\Services\Email;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

class EmailTemplateRender
{
    public function __construct(protected PlaceholderRegistry $registry) {

    }

    /**
     * Extract placeholders like @company, @email from content.
     */
    public function extractPlaceholders(?string $content): array
    {
        if (blank($content)) {
            return [];
        }

        preg_match_all('/@\w+/', $content, $matches);

        return array_values(array_unique($matches[0] ?? []));
    }

    /**
     * Return unknown placeholders found in content.
     */
    public function unknownPlaceholders(?string $content): array
    {
        $found = $this->extractPlaceholders($content);
        $allowed = $this->registry->keys();

        return array_values(array_diff($found, $allowed));
    }

    /**
     * Validate content contains only allowed placeholders.
     */
    public function validateContent(?string $content): void
    {
        $unknown = $this->unknownPlaceholders($content);

        if (!empty($unknown)) {
            throw new InvalidArgumentException(
                'Unknown placeholders found: ' . implode(', ', $unknown)
            );
        }
    }

    /**
     * Replace placeholders in content.
     *
     * $data keys should be without @
     * Example:
     * [
     *   'company' => 'ABC Ltd',
     *   'email' => 'john@example.com'
     * ]
     */
    public function renderString(?string $content, array $data = []): string
    {
        if ($content === null) {
            return '';
        }

        $this->validateContent($content);

        $replace = [];
        foreach ($data as $key => $value) {
            $replace['@' . ltrim($key, '@')] = (string) ($value ?? '');
        }

        return strtr($content, $replace);
    }

    /**
     * Render array of emails/strings.
     */
    public function renderArray(?array $items, array $data = []): array
    {
        if (empty($items)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) use ($data) {
            $rendered = trim($this->renderString((string) $item, $data));
            return $rendered !== '' ? $rendered : null;
        }, $items)));
    }

    /**
     * Render a full template payload.
     */
    public function renderTemplate(array $template, array $data = []): array
    {
        return [
            'subject' => $this->renderString(Arr::get($template, 'subject'), $data),
            'body' => $this->renderString(Arr::get($template, 'body'), $data),
            'from_name' => $this->renderString(Arr::get($template, 'from_name'), $data),
            'from_email' => $this->renderString(Arr::get($template, 'from_email'), $data),
            'cc' => $this->renderArray(Arr::get($template, 'cc', []), $data),
            'bcc' => $this->renderArray(Arr::get($template, 'bcc', []), $data),
            'reply_to' => $this->renderArray(Arr::get($template, 'reply_to', []), $data),
        ];
    }
}