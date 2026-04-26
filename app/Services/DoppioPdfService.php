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
        $payload = [
            'page' => [
                'pdf' => array_merge([
                    'printBackground' => true,
                    'format' => 'A4',
                ], $options),
                'goto' => [
                    'url' => $url,
                    'options' => [
                        'waitUntil' => ['networkidle0', 'load'],
                        'timeout' => 30000, // مهلة 30 ثانية
                    ],
                ],
                'viewport' => [
                    'width' => 1280,
                    'height' => 800,
                    'deviceScaleFactor' => 1,
                ],
            ],
        ];

        try {
            $response = Http::withToken($this->authToken)
                ->timeout(60)
                ->accept('application/pdf')
                ->post($this->apiEndpoint, $payload);

            // تسجيل معلومات الاستجابة للمساعدة في التصحيح
            Log::info('Doppio response', [
                'status' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'body_preview' => substr($response->body(), 0, 200),
            ]);

            // نجاح فقط إذا كان المحتوى PDF حقيقياً
            if ($response->successful() && str_contains($response->header('Content-Type'), 'application/pdf')) {
                return $response->body();
            }

            // في حال فشل Doppio أو أعاد HTML، نلقي استثناءً بالتفاصيل
            throw new \Exception(
                sprintf(
                    'Doppio API error [%d]: %s',
                    $response->status(),
                    substr($response->body(), 0, 300)
                )
            );
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Doppio connection failed', ['url' => $url, 'error' => $e->getMessage()]);
            throw new \Exception('تعذر الاتصال بخدمة Doppio، تحقق من اتصال الإنترنت أو إعدادات الـ API.');
        }
    }
}