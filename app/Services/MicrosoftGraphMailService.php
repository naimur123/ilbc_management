<?php

namespace App\Services;

use App\Mail\DynamicTemplateMail;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class MicrosoftGraphMailService
{
    private const GRAPH_BASE_URL = 'https://graph.microsoft.com/v1.0';

    private const TOKEN_SCOPE = 'https://graph.microsoft.com/.default';

    protected string $tenantId;

    protected string $clientId;

    protected string $clientSecret;

    protected string $sender;

    public function __construct()
    {
        $this->tenantId = trim(
            (string) config('services.microsoft_graph.tenant_id')
        );

        $this->clientId = trim(
            (string) config('services.microsoft_graph.client_id')
        );

        $this->clientSecret = trim(
            (string) config('services.microsoft_graph.client_secret')
        );

        $this->sender = trim(
            (string) config('services.microsoft_graph.sender')
        );

        $this->validateConfiguration();
    }

    /**
     * Validate required Microsoft Graph configuration.
     */
    protected function validateConfiguration(): void
    {
        $missing = [];

        if ($this->tenantId === '') {
            $missing[] = 'MICROSOFT_GRAPH_TENANT_ID';
        }

        if ($this->clientId === '') {
            $missing[] = 'MICROSOFT_GRAPH_CLIENT_ID';
        }

        if ($this->clientSecret === '') {
            $missing[] = 'MICROSOFT_GRAPH_CLIENT_SECRET';
        }

        if ($this->sender === '') {
            $missing[] = 'MICROSOFT_GRAPH_SENDER';
        }

        if ($missing !== []) {
            throw new RuntimeException(
                'Missing Microsoft Graph configuration: '
                . implode(', ', $missing)
            );
        }

        if (! filter_var($this->sender, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException(
                'Invalid Microsoft Graph sender address.'
            );
        }
    }

    /**
     * Get an app-only Microsoft Graph access token.
     */
    protected function getAccessToken(): string
    {
        $tokenUrl = sprintf(
            'https://login.microsoftonline.com/%s/oauth2/v2.0/token',
            rawurlencode($this->tenantId)
        );

        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 500)
            ->post($tokenUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'scope' => self::TOKEN_SCOPE,
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            Log::channel('custom_daily')->error(
                'Microsoft Graph token request failed',
                [
                    'status' => $response->status(),
                    'response' => $response->json()
                        ?? $response->body(),
                    'tenant_id' => $this->tenantId,
                    'client_id' => $this->clientId,
                ]
            );

            throw new RuntimeException(
                'Failed to obtain Microsoft Graph access token: '
                . $this->extractGraphError($response)
            );
        }

        $accessToken = $response->json('access_token');

        if (! is_string($accessToken) || $accessToken === '') {
            throw new RuntimeException(
                'Microsoft Graph token response did not contain an access token.'
            );
        }

        return $accessToken;
    }

    /**
     * Create an authenticated JSON Graph request.
     */
    protected function graphRequest(string $accessToken): PendingRequest
    {
        return Http::withToken($accessToken)
            ->acceptJson()
            ->asJson()
            ->timeout(60)
            ->retry(2, 500);
    }

    /**
     * Send an existing DynamicTemplateMail object.
     *
     * @param string|array<int, string> $to
     */
    public function sendDynamicTemplateMail(string|array $to, DynamicTemplateMail $mailable): void {
        $recipients = $this->buildRecipients($to);

        $attachments = $this->buildAttachments(
            $mailable->getAttachmentsData()
        );

        $payload = [
            'message' => [
                'subject' => $mailable->getSubjectLine(),
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $mailable->getBodyHtml(),
                ],
                'toRecipients' => $recipients,
            ],
            'saveToSentItems' => true,
        ];

        /*
         * Avoid sending an empty attachments property.
         */
        if ($attachments !== []) {
            $payload['message']['attachments'] = $attachments;
        }

        $this->sendPayload($payload, $to);
    }

    /**
     * Send a basic message without creating a Mailable.
     *
     * @param string|array<int, string> $to
     */
    public function send(string|array $to, string $subject, string $htmlBody): void {
        $payload = [
            'message' => [
                'subject' => $subject,
                'body' => [
                    'contentType' => 'HTML',
                    'content' => $htmlBody,
                ],
                'toRecipients' => $this->buildRecipients($to),
            ],
            'saveToSentItems' => true,
        ];

        $this->sendPayload($payload, $to);
    }

    /**
     * Send the JSON payload to Microsoft Graph.
     *
     * @param array<string, mixed> $payload
     * @param string|array<int, string> $to
     */
    protected function sendPayload(array $payload, string|array $to): void {
        $accessToken = $this->getAccessToken();

        $endpoint = self::GRAPH_BASE_URL
            . '/users/'
            . rawurlencode($this->sender)
            . '/sendMail';

        try {
            $response = $this->graphRequest($accessToken)
                ->post($endpoint, $payload);
        } catch (Throwable $exception) {
            Log::channel('custom_daily')->error(
                'Microsoft Graph HTTP request failed',
                [
                    'sender' => $this->sender,
                    'recipients' => is_array($to) ? $to : [$to],
                    'error' => $exception->getMessage(),
                ]
            );

            throw new RuntimeException(
                'Unable to connect to Microsoft Graph.',
                previous: $exception
            );
        }

        /*
         * Graph sendMail normally returns HTTP 202 with no body.
         */
        if ($response->status() !== 202) {
            Log::channel('custom_daily')->error(
                'Microsoft Graph sendMail failed',
                [
                    'status' => $response->status(),
                    'response' => $response->json()
                        ?? $response->body(),
                    'sender' => $this->sender,
                    'recipients' => is_array($to) ? $to : [$to],
                ]
            );

            throw new RuntimeException(
                'Failed to send Microsoft Graph mail: '
                . $this->extractGraphError($response)
            );
        }

        Log::channel('custom_daily')->info(
            'Microsoft Graph accepted email',
            [
                'status' => $response->status(),
                'sender' => $this->sender,
                'recipients' => is_array($to) ? $to : [$to],
                'subject' => data_get(
                    $payload,
                    'message.subject'
                ),
            ]
        );
    }

    /**
     * Convert addresses to Microsoft Graph recipients.
     *
     * @param string|array<int, string> $addresses
     * @return array<int, array<string, array<string, string>>>
     */
    protected function buildRecipients(string|array $addresses): array {
        $addresses = is_array($addresses)
            ? $addresses
            : [$addresses];

        $recipients = collect($addresses)
            ->map(fn ($email) => trim((string) $email))
            ->filter()
            ->unique()
            ->map(function (string $email): array {
                if (! filter_var(
                    $email,
                    FILTER_VALIDATE_EMAIL
                )) {
                    throw new RuntimeException(
                        "Invalid recipient address: {$email}"
                    );
                }

                return [
                    'emailAddress' => [
                        'address' => $email,
                    ],
                ];
            })
            ->values()
            ->all();

        if ($recipients === []) {
            throw new RuntimeException(
                'At least one valid recipient is required.'
            );
        }

        return $recipients;
    }

    /**
     * Build Graph file attachments.
     *
     * @param array<int, array<string, mixed>> $attachmentsData
     * @return array<int, array<string, mixed>>
     */
    protected function buildAttachments(array $attachmentsData): array {
        $attachments = [];

        foreach ($attachmentsData as $attachment) {
            $relativePath = $attachment['path'] ?? null;

            if (
                ! is_string($relativePath)
                || trim($relativePath) === ''
            ) {
                Log::channel('custom_daily')->warning(
                    'Microsoft Graph attachment path missing',
                    [
                        'attachment' => $attachment,
                    ]
                );

                continue;
            }

            $path = storage_path(
                'app/public/' . ltrim($relativePath, '/')
            );

            if (! is_file($path) || ! is_readable($path)) {
                Log::channel('custom_daily')->error(
                    'Microsoft Graph attachment unavailable',
                    [
                        'path' => $path,
                        'name' => $attachment['name'] ?? null,
                    ]
                );

                continue;
            }

            $contents = file_get_contents($path);

            if ($contents === false) {
                Log::channel('custom_daily')->error(
                    'Microsoft Graph attachment read failed',
                    [
                        'path' => $path,
                    ]
                );

                continue;
            }

            $mimeType = $attachment['mime']
                ?? mime_content_type($path)
                ?: 'application/octet-stream';

            $attachments[] = [
                '@odata.type' =>
                    '#microsoft.graph.fileAttachment',
                'name' => $attachment['name']
                    ?? basename($path),
                'contentType' => $mimeType,
                'contentBytes' => base64_encode($contents),
            ];
        }

        return $attachments;
    }

    /**
     * Extract a readable Microsoft Graph error.
     */
    protected function extractGraphError(Response $response): string {
        $code = $response->json('error.code');
        $message = $response->json('error.message');

        if (is_string($code) && is_string($message)) {
            return "{$code}: {$message}";
        }

        $errorDescription = $response->json(
            'error_description'
        );

        if (is_string($errorDescription)) {
            return $errorDescription;
        }

        return $response->body() !== ''
            ? $response->body()
            : "HTTP {$response->status()}";
    }
}