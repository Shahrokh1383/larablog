<?php

namespace Tests\Support;

use Illuminate\Support\Facades\Http;

class SmtpSinkService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.smtp_sink_api_url', env('SMTP_SINK_API_URL'));
    }

    public function getAllEmails(): array
    {
        $response = Http::get("{$this->apiUrl}/emails");
        if ($response->successful()) {
            return $response->json();
        }
        throw new \Exception("Failed to fetch emails from SMTP Sink.");
    }

    public function getEmail(int $id): array
    {
        $response = Http::get("{$this->apiUrl}/emails/{$id}");
        if ($response->successful()) {
            return $response->json();
        }
        throw new \Exception("Email not found.");
    }

    public function findEmailForRecipient(string $email): ?array
    {
        $emails = $this->getAllEmails();
        foreach ($emails as $emailRecord) {
            if ($emailRecord['recipient'] === $email) {
                return $this->getEmail($emailRecord['id']);
            }
        }
        return null;
    }

    public function purgeAll(): bool
    {
        $response = Http::post("{$this->apiUrl}/purge");
        return $response->successful();
    }
}