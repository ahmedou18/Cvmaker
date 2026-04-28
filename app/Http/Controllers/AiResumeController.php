<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class AiResumeController extends Controller
{
    // ✅ استخدم النموذج الذي اخترته
    private const MODEL = 'nvidia/nemotron-3-super-120b-a12b:free';

    public function parseFile(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|mimes:pdf|max:5120',
            'lang'    => 'nullable|string|in:ar,en,fr'
        ]);

        try {
            // استخراج النص من PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($request->file('cv_file')->getPathname());
            $rawText = $pdf->getText();

            if (empty(trim($rawText))) {
                return response()->json(['error' => 'تعذر استخراج النص من ملف PDF.'], 422);
            }

            $currentLang = $request->input('lang') ?? session('resume_language') ?? app()->getLocale();
            $prompt = $this->buildParsingPrompt($rawText, $currentLang);

            // الاتصال بـ OpenRouter
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                'Content-Type'  => 'application/json',
            ])->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "أنت مستخرج بيانات خبير. استخرج المعلومات من النص وحولها إلى JSON بدقة 100%. قاعدة صارمة: لا تخترع أي معلومات (No Hallucination). إذا لم تجد المعلومة، اترك الحقل نصاً فارغاً أو مصفوفة فارغة. أخرج فقط JSON بدون أي نص إضافي أو علامات Markdown."
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ],
                ],
                'temperature' => 0.1,
                'max_tokens'  => 3000,
            ]);

            if ($response->failed()) {
                Log::error('OpenRouter parsing API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'user_id' => auth()->id(),
                ]);
                return response()->json(['error' => 'فشل الاتصال بخدمة الذكاء الاصطناعي.'], 500);
            }

            $result = $response->json();
            $aiContent = $result['choices'][0]['message']['content'] ?? '';

            // تنظيف الاستجابة للحصول على JSON خالص
            $cleanJson = $this->cleanJsonResponse($aiContent);
            $aiData = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'raw'   => substr($cleanJson, 0, 500),
                    'user_id' => auth()->id(),
                ]);
                return response()->json(['error' => 'البيانات المستخرجة غير صالحة.'], 500);
            }

            // خصم الرصيد
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'يجب تسجيل الدخول.'], 401);
            }
            if ($user->ai_credits_balance <= 0) {
                return response()->json(['error' => 'رصيد الذكاء الاصطناعي غير كافٍ.'], 403);
            }

            DB::transaction(function () use ($user) {
                $user = $user->fresh();
                if ($user->ai_credits_balance > 0) {
                    $user->decrement('ai_credits_balance');
                }
            });

            $remainingCredits = $user->fresh()->ai_credits_balance;

            return response()->json([
                'success' => true,
                'data'    => $aiData,
                'remaining_credits' => $remainingCredits,
            ]);

        } catch (\Exception $e) {
            Log::error('PDF parsing exception', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'حدث خطأ غير متوقع.'], 500);
        }
    }

    private function buildParsingPrompt(string $text, string $lang): string
    {
        $languageMap = ['ar' => 'Arabic (العربية)', 'en' => 'English', 'fr' => 'French'];
        $targetLang = $languageMap[$lang] ?? 'English';

        return <<<PROMPT
قم باستخراج جميع المعلومات من نص السيرة الذاتية التالي وتحويلها إلى JSON وفق الهيكل المحدد بدقة. أخرج JSON فقط، بدون أي نص إضافي أو تعليقات أو علامات Markdown.

الهيكل المطلوب:
{
  "personal_details": {
    "full_name": "",
    "job_title": "",
    "email": "",
    "phone": "",
    "address": "",
    "summary": ""
  },
  "experiences": [
    {
      "company": "",
      "position": "",
      "start_date": "YYYY-MM",
      "end_date": "",
      "is_current": false,
      "description": ""
    }
  ],
  "educations": [
    {
      "institution": "",
      "degree": "",
      "field_of_study": "",
      "graduation_year": ""
    }
  ],
  "skills": [
    {"name": "", "percentage": 70}
  ],
  "languages": [
    {"name": "", "proficiency": "", "level": 3}
  ],
  "hobbies": [
    {"name": "", "icon": "", "description": ""}
  ],
  "references": [
    {
      "full_name": "",
      "job_title": "",
      "company": "",
      "email": "",
      "phone": "",
      "notes": ""
    }
  ],
  "extra_sections": [
    {"title": "", "content": ""}
  ]
}

قواعد صارمة:
- لا تخترع أي معلومات غير موجودة في النص.
- إذا لم يوجد حقل، اتركه نصاً فارغاً "" أو مصفوفة فارغة [].
- قم بترجمة كل النصوص إلى {$targetLang}.
- الصيغة للتواريخ: YYYY-MM (مثال 2023-01). إذا كان هناك سنة فقط، استخدم YYYY-01.
- بالنسبة للمهارات: قدِّر النسبة المئوية بشكل معقول (مثلاً 90 لمتقدم، 70 لمتوسط، 50 مبتدئ).
- بالنسبة للغات: المستوى (level) من 1 (مبتدئ) إلى 5 (لغة أم). استنتجه من النص.
- للهوايات: يمكن ترك رمز (icon) فارغاً إن لم يتوفر.
- للخبرات: اجعل is_current = true إذا لم يوجد تاريخ انتهاء.
- الأقسام الإضافية: تشمل الشهادات، المشاريع، أو أي معلومات أخرى لا تناسب الأقسام السابقة.

نص السيرة الذاتية:
{$text}
PROMPT;
    }

    private function cleanJsonResponse(string $content): string
    {
        $content = trim($content);
        // إزالة كتل Markdown مثل ```json ... ```
        $content = preg_replace('/^```json\s*|\s*```$/i', '', $content);
        // إذا لم يبدأ بـ {، حاول استخراج أول { ... }
        if (!str_starts_with($content, '{')) {
            preg_match('/\{.*\}/s', $content, $matches);
            if (!empty($matches)) {
                $content = $matches[0];
            }
        }
        return $content;
    }
}