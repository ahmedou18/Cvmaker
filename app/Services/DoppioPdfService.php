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
     * توليد PDF من رابط معين باستخدام Doppio API
     *
     * @param string $url الرابط الكامل للصفحة (يُفضل أن يكون موقعاً بـ signed URL)
     * @param array $options إعدادات إضافية للـ PDF
     * @return string محتوى PDF الخام
     * @throws \Exception
     */
    public function generatePdfFromUrl(string $url, array $options = []): string
{
    // بناء payload وفقاً لمستندات Doppio الرسمية (بدون الحقول غير المدعومة)
    $payload = [
        'page' => [
            'pdf' => [
                'printBackground' => $options['printBackground'] ?? true,
                'format' => $options['format'] ?? 'A4',
                // marginTop, marginBottom, ... غير مدعومة – نزيلها
            ],
            'goto' => [
                'url' => $url,
                'options' => [
                    'waitUntil' => ['networkidle0', 'load'],
                    // 'timeout' غير مدعوم – نزيله
                ],
            ],
            // 'viewport' غير مدعوم – نزيله
        ],
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