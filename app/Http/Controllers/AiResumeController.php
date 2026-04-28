<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class AiResumeController extends Controller
{
    /**
     * النموذج المستخدم من OpenRouter (مجاني وقوي للاستخراج)
     */
    private const MODEL = 'meta-llama/llama-3.1-8b-instruct:free';

    /**
     * استخراج بيانات السيرة الذاتية من ملف PDF باستخدام OpenRouter
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|mimes:pdf|max:5120',
            'lang'    => 'nullable|string|in:ar,en,fr'
        ]);

        try {
            // 1. استخراج النص من PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($request->file('cv_file')->getPathname());
            $rawText = $pdf->getText();

            if (empty(trim($rawText))) {
                return response()->json(['error' => 'تعذر استخراج النص من ملف PDF. تأكد أنه ليس ممسوحاً ضوئياً.'], 422);
            }

            // 2. اللغة المستهدفة
            $currentLang = $request->input('lang') ?? session('resume_language') ?? app()->getLocale();

            // 3. بناء البرومبت الكامل (مع الحقول الجديدة)
            $prompt = $this->buildParsingPrompt($rawText, $currentLang);

            // 4. الاتصال بـ OpenRouter API
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.openrouter.key'),
                'Content-Type'  => 'application/json',
            ])->timeout(120)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => self::MODEL,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => "أنت محرك استخراج بيانات دقيق جداً. وظيفتك تحويل نص السيرة الذاتية إلى JSON وفق البنية المطلوبة. ممنوع تماماً إضافة أي معلومات غير موجودة في النص الأصلي. إذا لم تجد المعلومة، اترك الحقل فارغاً أو استخدم [] للمصفوفات. لا تشرح الكود، أخرج فقط JSON خالص."
                    ],
                    ['role' => 'user', 'content' => $prompt],
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
                return response()->json(['error' => 'حدث خطأ أثناء الاتصال بذكاء اصطناعي.'], 500);
            }

            $result = $response->json();
            $aiContent = $result['choices'][0]['message']['content'] ?? '';

            // تنظيف الاستجابة للحصول على JSON خالص
            $cleanJson = $this->cleanJsonResponse($aiContent);
            $aiData = json_decode($cleanJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error in parseFile', [
                    'error' => json_last_error_msg(),
                    'raw'   => substr($cleanJson, 0, 500),
                    'user_id' => auth()->id(),
                ]);
                return response()->json(['error' => 'فشل تحليل البيانات المستخرجة. حاول مرة أخرى.'], 500);
            }

            // 5. خصم رصيد المستخدم (نفس المنطق القديم)
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'يجب تسجيل الدخول لاستخدام هذه الميزة.'], 401);
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
                'message' => 'تم استخراج البيانات بنجاح',
                'data'    => $aiData,
                'remaining_credits' => $remainingCredits,
            ]);

        } catch (\Exception $e) {
            Log::error('PDF parsing exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);
            return response()->json(['error' => 'حدث خطأ غير متوقع: ' . $e->getMessage()], 500);
        }
    }

    /**
     * بناء الـ Prompt لاستخراج البيانات كاملة
     */
    private function buildParsingPrompt(string $text, string $lang): string
    {
        $languageMap = ['ar' => 'Arabic', 'en' => 'English', 'fr' => 'French'];
        $targetLang = $languageMap[$lang] ?? 'English';

        return <<<PROMPT
[STRICT INSTRUCTION]
قم باستخراج جميع البيانات التالية من نص السيرة الذاتية الموجود بين <CV_TEXT> و </CV_TEXT>. أخرج النتيجة كـ JSON صالح باللغة {$targetLang} فقط.

[STRUCTURE REQUIRED]
{
  "personal_details": {
    "full_name": "الاسم الكامل",
    "job_title": "المسمى الوظيفي الحالي أو المستهدف",
    "email": "البريد الإلكتروني",
    "phone": "رقم الهاتف",
    "address": "العنوان (مدينة ودولة)",
    "summary": "نبذة مهنية مختصرة (جملتين إلى ثلاث)"
  },
  "experiences": [
    {
      "company": "اسم الشركة",
      "position": "المسمى الوظيفي",
      "start_date": "YYYY-MM",
      "end_date": "YYYY-MM أو تركها فارغة إذا كانت current",
      "is_current": true/false,
      "description": "وصف المهام والإنجازات (يُفضل تحسينه لغوياً بدون إضافة معلومات غير موجودة)"
    }
  ],
  "educations": [
    {
      "institution": "اسم الجامعة/المعهد",
      "degree": "الشهادة (بكالوريوس، ماجستير، ...)",
      "field_of_study": "التخصص",
      "graduation_year": "YYYY"
    }
  ],
  "skills": [
    {"name": "اسم المهارة", "percentage": 80}
  ],
  "languages": [
    {"name": "اسم اللغة", "proficiency": "مستوى إتقان نصي (مبتدئ، متوسط، متقدم، لغة أم)", "level": 3}
  ],
  "hobbies": [
    {"name": "اسم الهواية", "icon": "إيموجي مناسب (اختياري)", "description": "وصف قصير (اختياري)"}
  ],
  "references": [
    {
      "full_name": "اسم المرجع",
      "job_title": "المسمى الوظيفي للمرجع",
      "company": "جهة عمله",
      "email": "بريده الإلكتروني",
      "phone": "رقم هاتفه",
      "notes": "أي ملاحظات إضافية (علاقته بك مثلاً)"
    }
  ],
  "extra_sections": [
    {"title": "عنوان القسم الإضافي", "content": "محتوى القسم"}
  ]
}

[RULES]
1. **لا تخلق معلومات غير موجودة** – إذا لم تجد شيئاً، اترك السلسلة فارغة "" أو المصفوفة فارغة [].
2. **الترجمة**: أخرج جميع القيم النصية باللغة {$targetLang} (إذا كان النص الأصلي بلغة أخرى، تُرجمه إلى {$targetLang}).
3. **التواريخ**: فقط سنة وشهر بصيغة YYYY-MM. إذا وجدت سنة فقط فاجعل YYYY-01.
4. **نسبة المهارة**: قدِّر النسبة المنطقية بناءً على خبرة الشخص (إذا لم توجد نسبة فاجعل 70).
5. **مستوى اللغة**: level من 1 إلى 5. استنتجه من الكلمات (مبتدئ=1، متوسط=3، متقدم=4، لغة أم=5).
6. **الهوايات**: يمكن ترك icon فارغاً إن لم يوجد إيموجي واضح.
7. **الأقسام الإضافية**: أي معلومات لا تناسب الأقسام السابقة (مثل الشهادات، المشاريع) ضعها في هذا المصفوفة.

<CV_TEXT>
{$text}
</CV_TEXT>

أخرج فقط JSON صحيح بدون أي نص إضافي أو تفسير.
PROMPT;
    }

    /**
     * تنظيف الاستجابة من علامات Markdown والحصول على JSON خالص
     */
    private function cleanJsonResponse(string $content): string
    {
        // إزالة أي كتل ```json ... ```
        $content = preg_replace('/^```json\s*|\s*```$/i', '', trim($content));
        // إذا بدأ النص بأحرف غير JSON (مثل شرح)، نحاول التقاط أول { وآخر }
        if (!str_starts_with($content, '{')) {
            preg_match('/\{.*\}/s', $content, $matches);
            if (!empty($matches)) {
                $content = $matches[0];
            }
        }
        return $content;
    }
}