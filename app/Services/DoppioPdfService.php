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

    /**
     * تحويل صفحة ويب إلى PDF باستخدام Doppio API
     *
     * @param string $url الرابط العام للصفحة (يجب أن يكون متاحاً على الإنترنت)
     * @param array $options خيارات إضافية (printBackground, format فقط)
     * @return string محتوى PDF
     * @throws \Exception
     */
    public function generatePdfFromUrl(string $url, array $options = []): string
    {
        // هذه هي البنية الصحيحة الوحيدة التي يقبلها Doppio API حالياً
        $payload = [
            'page' => [
                'goto' => [
                    'url' => $url   // فقط الرابط، لا waitUntil, timeout, viewport
                ],
                'pdf' => [
                    'printBackground' => $options['printBackground'] ?? true,
                    'format' => $options['format'] ?? 'A4',
                    // لا margins, لا header/footer, لا أي شيء آخر
                ]
            ]
        ];

        $response = Http::withToken($this->authToken)
            ->timeout(60)
            ->accept('application/pdf')
            ->post($this->apiEndpoint, $payload);

        Log::info('Doppio response', [
            'status' => $response->status(),
            'content_type' => $response->header('Content-Type'),
            'body_preview' => substr($response->body(), 0, 200),
        ]);

        if ($response->successful() && str_contains($response->header('Content-Type'), 'application/pdf')) {
            return $response->body();
        }

        throw new \Exception('Doppio API error: ' . substr($response->body(), 0, 300));
    }
}