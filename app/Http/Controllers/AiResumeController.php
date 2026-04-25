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
     * النموذج المستخدم لاستخراج البيانات (يفضل استخدام نموذج قوي).
     */
    private const DEFAULT_MODEL = 'command-r-plus';

    /**
     * استخراج البيانات من ملف PDF باستخدام Cohere.
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|mimes:pdf|max:5120',
            'lang'    => 'nullable|string|in:ar,en,fr'
        ]);

        try {
            $parser = new Parser();
            $pdf = $parser->parseFile($request->file('cv_file')->getPathname());
            $rawText = $pdf->getText();

            if (empty(trim($rawText))) {
                return response()->json(['error' => 'تعذر استخراج النص. تأكد أن الملف ليس عبارة عن صور (Scanned PDF).'], 422);
            }

            // 1. إصلاح النص العربي المعكوس
            $text = $this->fixArabicText($rawText);

            // 2. تحديد اللغة (الطلب -> الجلسة -> لغة التطبيق)
            $currentLang = $request->input('lang') ?? session('resume_language') ?? app()->getLocale();

            // 3. بناء الـ Prompt المحسن وفقاً للغة
            $prompt = $this->buildParsingPrompt($text, $currentLang);

            // 4. الاتصال بـ Cohere مع تنسيق JSON مضمون
            $response = Http::withToken(config('services.cohere.key'))
                ->timeout(120)
                ->post('https://api.cohere.ai/v1/chat', [
                    'model' => 'command-a-03-2025',
                    'message'         => $prompt,
                    'temperature'     => 0.1,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                Log::error('Cohere parsing API error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'user_id' => auth()->id(),
                ]);
                return response()->json(['error' => 'حدث خطأ أثناء الاتصال بخدمة الذكاء الاصطناعي.'], 500);
            }

            $aiOutput = $response->json('text');
            $aiOutput = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiOutput));
            
            // استخراج JSON من النص
            preg_match('/\{.*\}/s', $aiOutput, $matches);
            if (empty($matches)) {
                Log::error('No valid JSON found in AI response', ['output' => substr($aiOutput, 0, 500)]);
                return response()->json(['error' => 'لم يتم العثور على JSON صالح في استجابة الذكاء الاصطناعي.'], 500);
            }

            $jsonString = $matches[0];
            $aiData = json_decode($jsonString, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error in parseFile', ['error' => json_last_error_msg(), 'json' => $jsonString]);
                return response()->json(['error' => 'فشل تحليل بيانات JSON: ' . json_last_error_msg()], 500);
            }

            // ✅ التحقق من صلاحية الرصيد وخصمه (بنفس المنطق القديم)
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'يجب تسجيل الدخول أولاً.'], 401);
            }

            if ($user->ai_credits_balance <= 0) {
                return response()->json([
                    'error' => 'رصيد الذكاء الاصطناعي غير كافٍ لاستخراج البيانات. يرجى الاشتراك في باقة.'
                ], 403);
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
                'data' => $aiData,
                'remaining_credits' => $remainingCredits
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
     * إصلاح النص العربي المعكوس الناتج عن استخراج PDF (محسّن مع الاحتفاظ بجميع الدوال المساعدة).
     */
    private function fixArabicText(string $text): string
    {
        $lines = explode("\n", $text);
        $fixedLines = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                $fixedLines[] = '';
                continue;
            }

            $words = preg_split('/(\s+)/u', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
            $fixedWords = [];

            foreach ($words as $word) {
                if ($this->containsArabic($word)) {
                    $fixedWords[] = $this->reverseWord($word);
                } else {
                    $fixedWords[] = $word;
                }
            }

            $fixedLine = implode('', array_reverse($fixedWords));
            $fixedLines[] = $fixedLine;
        }

        $fixedText = implode("\n", $fixedLines);

        if ($this->isStillReversed($fixedText)) {
            $fixedText = $this->reverseWholeText($text);
        }

        return $fixedText;
    }

    private function isStillReversed(string $text): bool
    {
        $lines = array_filter(explode("\n", $text));
        if (empty($lines)) return false;

        $firstLine = trim(reset($lines));
        return !preg_match('/^[\x{0600}-\x{06FF}]/u', $firstLine);
    }

    private function reverseWholeText(string $text): string
    {
        return implode('', array_reverse(preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY)));
    }

    private function containsArabic(string $text): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    private function reverseWord(string $word): string
    {
        return implode('', array_reverse(preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY)));
    }

    /**
     * بناء الـ Prompt لاستخراج البيانات بصيغة JSON مع تعليمات صارمة للترجمة.
     */
    private function buildParsingPrompt(string $text, string $lang): string
    {
        $languages = [
            'ar' => 'Arabic (العربية)',
            'en' => 'English (الإنجليزية)',
            'fr' => 'French (الفرنسية)'
        ];
        $targetLang = $languages[$lang] ?? 'Arabic (العربية)';

        return "
        [STRICT INSTRUCTION]
        استخرج البيانات من السيرة الذاتية التالية وأعدها بصيغة JSON فقط باللغة {$targetLang} ودون أي نصوص توضيحية أخرى.

        [RULES]
        1. يجب أن يكون ناتجك JSON صالحاً تماماً.
        2. قم بترجمة جميع القيم إلى {$targetLang} حتى لو كان النص الأصلي بلغة أخرى.
        3. إذا كان حقل غير موجود، اتركه فارغاً (مصفوفة فارغة أو سلسلة فارغة).
        4. تواريخ البداية والنهاية بالصيغة YYYY-MM.
        5. لا تضع أياً من النص داخل علامات ```json أو أي تنسيق Markdown.

        [JSON STRUCTURE]
        {
            \"personal_details\": {
                \"full_name\": \"\",
                \"job_title\": \"\",
                \"email\": \"\",
                \"phone\": \"\",
                \"address\": \"\",
                \"summary\": \"\"
            },
            \"experiences\": [
                {
                    \"company\": \"\",
                    \"position\": \"\",
                    \"start_date\": \"\",
                    \"end_date\": \"\",
                    \"is_current\": false,
                    \"description\": \"\"
                }
            ],
            \"educations\": [
                {
                    \"institution\": \"\",
                    \"degree\": \"\",
                    \"field_of_study\": \"\",
                    \"graduation_year\": \"\"
                }
            ],
            \"skills\": [\"Skill 1\", \"Skill 2\"],
            \"languages\": [
                {
                    \"name\": \"Language Name in {$targetLang}\",
                    \"proficiency\": \"Proficiency Level in {$targetLang}\"
                }
            ],
            \"extra_sections\": []
        }

        [CV TEXT]
        {$text}
        ";
    }
}