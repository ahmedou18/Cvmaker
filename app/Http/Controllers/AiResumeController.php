<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class AiResumeController extends Controller
{
    /**
     * استخراج البيانات من ملف PDF باستخدام Cohere.
     */
    public function parseFile(Request $request)
    {
        $request->validate([
            'cv_file' => 'required|mimes:pdf|max:5120',
            'lang'    => 'nullable|string'
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

            // 2. تحديد اللغة بدقة أعلى (الطلب -> الجلسة -> لغة التطبيق الافتراضية)
            $currentLang = $request->input('lang') ?? session('resume_language') ?? app()->getLocale();

            // 3. الاتصال بـ Cohere
            $response = Http::withToken(env('COHERE_API_KEY'))
                ->timeout(120)
                ->post('https://api.cohere.ai/v1/chat', [
                    'model' => 'command-a-03-2025', // تم تصحيح اسم الموديل هنا إلى الموديل المعتمد السريع (أو استخدم command-r-plus للأدق)
                    'preamble' => $this->getSystemPrompt($currentLang), 
                    'message' => "استخرج البيانات من السيرة التالية وقم بإعادتها بصيغة JSON فقط باللغة ({$currentLang}) وبدون أي نصوص توضيحية أخرى:\n\n" . $text,
                    'temperature' => 0.1,
                ]);

            if ($response->successful()) {
                $aiOutput = $response->json('text');
                $aiOutput = preg_replace('/^```json\s*|\s*```$/i', '', trim($aiOutput));
                
                preg_match('/\{.*\}/s', $aiOutput, $matches);
                if (empty($matches)) {
                    return response()->json(['error' => 'لم يتم العثور على JSON صالح في استجابة الذكاء الاصطناعي.'], 500);
                }

                $jsonString = $matches[0];
                $aiData = json_decode($jsonString, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['error' => 'فشل في تحليل بيانات JSON: ' . json_last_error_msg()], 500);
                }

                // ✅ خصم الرصيد بشكل آمن (مع قفل الصف)
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

                // إرجاع البيانات مع الرصيد المتبقي
                return response()->json([
                    'success' => true,
                    'message' => 'تم استخراج البيانات بنجاح',
                    'data' => $aiData,
                    'remaining_credits' => $remainingCredits
                ]);
            }

            return response()->json([
                'error' => 'حدث خطأ أثناء الاتصال بـ Cohere API.',
                'details' => $response->json() 
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json(['error' => 'حدث خطأ غير متوقع: ' . $e->getMessage()], 500);
        }
    }

    /**
     * إصلاح النص العربي المعكوس الناتج عن استخراج PDF.
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
     * دالة التوجيه الصارمة لضمان الترجمة الكاملة للبيانات
     */
    private function getSystemPrompt($lang = 'ar')
    {
        $languages = [
            'ar' => 'Arabic (العربية)',
            'en' => 'English (الإنجليزية)',
            'fr' => 'French (الفرنسية)'
        ];
        $targetLang = $languages[$lang] ?? 'Arabic (العربية)';

        return "
        [STRICT INSTRUCTION / تعليمات صارمة]
        1. YOU MUST OUTPUT ALL CONTENT IN {$targetLang} ONLY.
        2. Even if the input CV text is in French, Arabic, or English, you MUST translate everything to {$targetLang}.
        3. The JSON keys must remain exactly as defined below in English.
        4. ALL VALUES (names, titles, descriptions, skills, etc.) must be in {$targetLang}.
        5. For 'languages' section: Translate the language names themselves. (e.g., if you find 'Français', write it as 'French' if target is English, or 'الفرنسية' if target is Arabic).
        6. NO MARKDOWN: Do not use ```json or any other formatting outside the JSON brackets.

        [JSON STRUCTURE]:
        {
            \"personal_details\": {
                \"full_name\": \"\",
                \"job_title\": \"Translated Job Title\",
                \"email\": \"\",
                \"phone\": \"\",
                \"address\": \"Translated Address\",
                \"summary\": \"Translated Summary\"
            },
            \"experiences\": [
                {
                    \"company\": \"Translated Company Name\",
                    \"position\": \"Translated Position\",
                    \"start_date\": \"YYYY-MM\",
                    \"end_date\": \"YYYY-MM or Present in {$targetLang}\",
                    \"is_current\": false,
                    \"description\": \"Translated Description in bullet points\"
                }
            ],
            \"educations\": [
                {
                    \"institution\": \"Translated Institution\",
                    \"degree\": \"Translated Degree\",
                    \"field_of_study\": \"Translated Field\",
                    \"graduation_year\": \"YYYY\"
                }
            ],
            \"skills\": [\"Translated Skill 1\", \"Translated Skill 2\"],
            \"languages\": [
                {
                    \"name\": \"Language Name in {$targetLang}\",
                    \"proficiency\": \"Proficiency Level in {$targetLang}\"
                }
            ],
            \"extra_sections\": [
                {
                    \"title\": \"Section Title in {$targetLang}\",
                    \"content\": \"Content in {$targetLang}\"
                }
            ]
        }";
    }
}