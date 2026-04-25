<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DoppioPdfService
{
    protected $apiEndpoint;
    protected $authToken;

    public function __construct()
    {
        $this->apiEndpoint = 'https://api.doppio.sh/v1/render/pdf/direct';
        $this->authToken = env('DOPPIO_AUTH_TOKEN');
    }

    public function generatePdfFromUrl(string $url, array $options = []): ?string
    {
        $payload = [
            'page' => [
                'pdf' => array_merge([
                    'printBackground' => true,
                    'format' => 'A4',
                ], $options),
                'goto' => [
                    'url' => $url,
                    'options' => [
                        'waitUntil' => ['networkidle0'],
                    ],
                ],
            ],
        ];

        $response = Http::withToken($this->authToken)
            ->timeout(60)
            ->post($this->apiEndpoint, $payload);

        if ($response->successful()) {
            return $response->body(); // PDF content
        }

        Log::error('Doppio PDF generation failed', [
            'url' => $url,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \Exception('Failed to generate PDF via Doppio: ' . $response->body());
    }
}