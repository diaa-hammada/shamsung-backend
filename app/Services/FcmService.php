<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private array $credentials = [];
    private string $projectId = '';

    public function __construct()
    {
        $path = storage_path('app/firebase-credentials.json');

        if (! file_exists($path)) {
            Log::error('FcmService: credentials file not found', ['path' => $path]);
            return;
        }

        $decoded = json_decode(file_get_contents($path), true);

        if (! is_array($decoded)) {
            Log::error('FcmService: credentials file is not valid JSON');
            return;
        }

        $this->credentials = $decoded;
        $this->projectId   = $decoded['project_id'] ?? '';
    }

    /**
     * Send a push notification to a single device.
     * Silently no-ops if token is blank or credentials are missing.
     */
    public function send(string $token, string $title, string $body, array $data = []): void
    {
        if (empty(trim($token)) || $this->projectId === '') {
            return;
        }

        $this->dispatch($token, $title, $body, $data);
    }

    /**
     * Send the same push notification to multiple device tokens.
     * Silently skips blank/null tokens.
     */
    public function sendMultiple(array $tokens, string $title, string $body, array $data = []): void
    {
        if ($this->projectId === '') {
            return;
        }

        foreach (array_filter($tokens, fn ($t) => ! empty(trim((string) $t))) as $token) {
            $this->dispatch($token, $title, $body, $data);
        }
    }

    // -------------------------------------------------------------------------

    private function dispatch(string $token, string $title, string $body, array $data): void
    {
        $accessToken = $this->getAccessToken();

        if ($accessToken === null) {
            return;
        }

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'token'        => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                // FCM data values must all be strings
                'data' => array_map('strval', $data),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)->timeout(10)->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('FcmService: send failed', [
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('FcmService: dispatch exception', ['message' => $e->getMessage()]);
        }
    }

    private function getAccessToken(): ?string
    {
        // Cache for 55 minutes — Google tokens expire after 60 minutes
        return Cache::remember('fcm_access_token', 3300, fn () => $this->fetchAccessToken());
    }

    private function fetchAccessToken(): ?string
    {
        try {
            $jwt      = $this->buildJwt(time());
            $response = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('FcmService: OAuth2 token fetch failed', ['body' => $response->body()]);
            return null;
        } catch (\Throwable $e) {
            Log::error('FcmService: OAuth2 exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    private function buildJwt(int $iat): string
    {
        $header  = $this->b64u(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->b64u(json_encode([
            'iss'   => $this->credentials['client_email'],
            'sub'   => $this->credentials['client_email'],
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $iat,
            'exp'   => $iat + 3600,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        ]));

        $signingInput = "{$header}.{$payload}";
        $key          = openssl_pkey_get_private($this->credentials['private_key']);

        openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);

        return "{$signingInput}.{$this->b64u($signature)}";
    }

    /** Base64url encode (RFC 4648 §5) */
    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
