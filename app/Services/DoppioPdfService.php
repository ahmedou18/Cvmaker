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
    $payload = [
        'page' => [
            'goto' => ['url' => $url],
            'pdf' => [
                'printBackground' => $options['printBackground'] ?? true,
                'format' => $options['format'] ?? 'A4',
            ]
        ]
    ];

    $response = Http::withToken($this->authToken)
        ->timeout(60)
        ->post($this->apiEndpoint, $payload);  // لا نستخدم accept('application/pdf')

    // نفحص أولاً إذا كان الاستجابة تبدأ بـ %PDF (توقيع PDF)
    $body = $response->body();
    $isPdf = $response->successful() && (str_starts_with($body, '%PDF') || str_contains($response->header('Content-Type'), 'application/pdf'));

    if ($isPdf) {
        return $body;
    }

    // في حال الفشل، نسجل ونلقي استثناء
    Log::error('Doppio failed to generate PDF', [
        'status' => $response->status(),
        'content_type' => $response->header('Content-Type'),
        'body_preview' => substr($body, 0, 200),
    ]);

    throw new \Exception('Doppio returned non-PDF content: ' . substr($body, 0, 300));
}
}