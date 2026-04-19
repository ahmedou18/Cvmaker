<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PDF;
use Smalot\PdfParser\Parser;

class CoverLetterController extends Controller
{
    /**
     * عرض نموذج إنشاء خطاب تغطية جديد.
     */
    public function create()
    {
        $resumes = Resume::where('user_id', Auth::id())->latest()->get();
        return view('cover-letters.create', compact('resumes'));
    }

    /**
     * حفظ خطاب التغطية وتوليد المحتوى بالذكاء الاصطناعي.
     */
    public function store(Request $request)
    {
        $request->validate([
            'resume_id'          => 'nullable|exists:resumes,id',
            'uploaded_file'      => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'target_job_title'   => 'required|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'job_description'    => 'nullable|string',
            'job_description_url'=> 'nullable|url',
            'language'           => 'required|in:ar,en,fr',
        ]);

        if (!$request->filled('resume_id') && !$request->hasFile('uploaded_file')) {
            return back()->withErrors(['uploaded_file' => 'يرجى اختيار سيرة ذاتية موجودة أو رفع ملف (PDF/Word).'])->withInput();
        }

        $user = Auth::user();
        if ($user->ai_credits_balance <= 0) {
            return back()->withErrors(['error' => 'رصيد الذكاء الاصطناعي غير كافٍ. يرجى الاشتراك في باقة أو تجديد رصيدك.'])->withInput();
        }

        $context = $this->extractContext($request);

        DB::beginTransaction();

        try {
            $coverLetter = CoverLetter::create([
                'user_id'          => $user->id,
                'target_job_title' => $request->target_job_title,
                'company_name'     => $request->company_name,
                'content'          => '',
            ]);

            $generatedContent = $this->generateCoverLetterWithAI(
                context: $context,
                targetJobTitle: $request->target_job_title,
                companyName: $request->company_name,
                jobDescription: $request->job_description ?? $request->job_description_url ?? '',
                language: $request->language
            );

            $coverLetter->update(['content' => $generatedContent]);

            DB::transaction(function () use ($user) {
                $user = $user->fresh();
                if ($user->ai_credits_balance > 0) {
                    $user->decrement('ai_credits_balance');
                }
            });

            DB::commit();

            return redirect()->route('cover-letters.show', $coverLetter->id)
                ->with('success', 'تم إنشاء خطاب التغطية بنجاح! 🎉');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cover letter creation failed: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ أثناء إنشاء خطاب التغطية: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * عرض خطاب التغطية المُولد.
     */
    public function show($id)
    {
        $coverLetter = CoverLetter::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        return view('cover-letters.show', compact('coverLetter'));
    }

    /**
     * تصدير خطاب التغطية كملف PDF.
     */
    public function downloadPdf($id)
    {
        $coverLetter = CoverLetter::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $pdf = PDF::loadView('cover-letters.pdf', compact('coverLetter'), [], [
            'format'         => 'A4',
            'default_font'   => 'xbriyaz',
            'directionality' => 'rtl',
            'margin_left'    => 20,
            'margin_right'   => 20,
            'margin_top'     => 20,
            'margin_bottom'  => 20,
        ]);

        $fileName = 'cover_letter_' . time() . '.pdf';
        return $pdf->download($fileName);
    }

    /**
     * استخراج السياق من الطلب (سيرة ذاتية أو ملف مرفوع).
     */
    protected function extractContext(Request $request): array
    {
        $context = [
            'full_name'   => '',
            'job_title'   => '',
            'email'       => '',
            'phone'       => '',
            'summary'     => '',
            'experiences' => [],
            'educations'  => [],
            'skills'      => [],
            'uploaded_text' => '',
        ];

        // 1. إذا اختار سيرة ذاتية موجودة
        if ($request->filled('resume_id')) {
            $resume = Resume::where('id', $request->resume_id)
                ->where('user_id', Auth::id())
                ->with(['personalDetail', 'experiences', 'educations', 'skills'])
                ->first();
            if ($resume && $resume->personalDetail) {
                $context['full_name']   = $resume->personalDetail->full_name ?? '';
                $context['job_title']   = $resume->personalDetail->job_title ?? '';
                $context['email']       = $resume->personalDetail->email ?? '';
                $context['phone']       = $resume->personalDetail->phone ?? '';
                $context['summary']     = $resume->personalDetail->summary ?? '';
                $context['experiences'] = $resume->experiences->toArray();
                $context['educations']  = $resume->educations->toArray();
                $context['skills']      = $resume->skills->pluck('name')->toArray();
            }
        }

        // 2. إذا رفع ملف – نحاول استخراج بيانات منظمة
        if ($request->hasFile('uploaded_file')) {
            $file = $request->file('uploaded_file');
            $structuredData = $this->extractStructuredDataFromFile($file);
            
            if ($structuredData && !empty($structuredData['personal_details'])) {
                // تحويل أي كائنات إلى مصفوفات
                $pd = (array) $structuredData['personal_details'];
                $context['full_name']   = $pd['full_name'] ?? $context['full_name'];
                $context['job_title']   = $pd['job_title'] ?? $context['job_title'];
                $context['email']       = $pd['email'] ?? $context['email'];
                $context['phone']       = $pd['phone'] ?? $context['phone'];
                $context['summary']     = $pd['summary'] ?? $context['summary'];
                
                $context['experiences'] = $this->toArray($structuredData['experiences'] ?? []);
                $context['educations']  = $this->toArray($structuredData['educations'] ?? []);
                $context['skills']      = $this->toArray($structuredData['skills'] ?? []);
            } else {
                $context['uploaded_text'] = $this->extractTextFromFile($file);
            }
        }

        return $context;
    }

    /**
     * تحويل البيانات (مصفوفة أو كائن) إلى مصفوفة.
     */
    protected function toArray($data): array
    {
        if (is_array($data)) {
            // التأكد من تحويل العناصر الداخلية إذا كانت كائنات
            return array_map(function($item) {
                return is_object($item) ? (array) $item : $item;
            }, $data);
        }
        if (is_object($data)) {
            $array = (array) $data;
            return array_map(function($item) {
                return is_object($item) ? (array) $item : $item;
            }, $array);
        }
        return [];
    }

    /**
     * استخراج بيانات منظمة من ملف مرفوع باستخدام AiResumeController.
     */
    protected function extractStructuredDataFromFile($file): ?array
    {
        try {
            $aiResumeController = app(AiResumeController::class);
            $mockRequest = new \Illuminate\Http\Request();
            $mockRequest->files->set('cv_file', $file);
            $mockRequest->merge(['lang' => session('resume_language', 'ar')]);
            
            $response = $aiResumeController->parseFile($mockRequest);
            $responseData = $response->getData();
            
            if ($responseData && ($responseData->success ?? false)) {
                return (array) $responseData->data;
            }
        } catch (\Exception $e) {
            Log::warning('Structured data extraction failed: ' . $e->getMessage());
        }
        return null;
    }

    /**
     * استخراج النص الخام من ملف PDF أو Word.
     */
    protected function extractTextFromFile($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = '';

        if ($extension === 'pdf') {
            try {
                $parser = new Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $content = $pdf->getText();
            } catch (\Exception $e) {
                Log::warning('Failed to extract text from PDF: ' . $e->getMessage());
            }
        } elseif (in_array($extension, ['doc', 'docx'])) {
            try {
                $content = file_get_contents($file->getRealPath());
                $content = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $content);
                if (strlen($content) < 50) {
                    $content = 'لا يمكن استخراج النص من ملف Word بشكل تلقائي. يرجى رفع ملف PDF بدلاً من ذلك.';
                }
            } catch (\Exception $e) {
                Log::warning('Failed to extract text from Word: ' . $e->getMessage());
                $content = 'حدث خطأ أثناء قراءة ملف Word. يرجى استخدام ملف PDF.';
            }
        }

        return $content;
    }

    /**
     * توليد خطاب التغطية باستخدام Cohere API.
     */
    protected function generateCoverLetterWithAI(array $context, string $targetJobTitle, ?string $companyName, string $jobDescription, string $language): string
    {
        $langName = match($language) {
            'ar' => 'Arabic (العربية)',
            'fr' => 'French (الفرنسية)',
            default => 'English (الإنجليزية)',
        };

        // تحويل أي بيانات قد تكون كائنات إلى مصفوفات
        $skills = $this->toArray($context['skills'] ?? []);
        $experiences = $this->toArray($context['experiences'] ?? []);
        $educations = $this->toArray($context['educations'] ?? []);

        // بناء نص السياق
        $contextText = "الاسم: {$context['full_name']}\n";
        $contextText .= "المسمى الحالي: {$context['job_title']}\n";
        $contextText .= "الملخص: {$context['summary']}\n";
        
        if (!empty($skills)) {
            $contextText .= "المهارات: " . implode(', ', $skills) . "\n";
        }
        
        if (!empty($experiences)) {
            $contextText .= "الخبرات:\n";
            foreach ($experiences as $exp) {
                $contextText .= "- {$exp['position']} في {$exp['company']} ({$exp['start_date']} - {$exp['end_date']})\n";
                if (!empty($exp['description'])) {
                    $contextText .= "  المهام: {$exp['description']}\n";
                }
            }
        }
        
        if (!empty($educations)) {
            $contextText .= "التعليم:\n";
            foreach ($educations as $edu) {
                $contextText .= "- {$edu['degree']} في {$edu['field_of_study']} من {$edu['institution']} ({$edu['graduation_year']})\n";
            }
        }
        
        if (!empty($context['uploaded_text'])) {
            $contextText .= "\nنص إضافي من الملف المرفوع:\n" . substr($context['uploaded_text'], 0, 1500);
        }

        $prompt = "اكتب خطاب تغطية احترافي باللغة {$langName} بناءً على المعلومات التالية:\n\n";
        $prompt .= "المرشح:\n{$contextText}\n";
        $prompt .= "الوظيفة المستهدفة: {$targetJobTitle}\n";
        if ($companyName) {
            $prompt .= "اسم الشركة: {$companyName}\n";
        }
        if ($jobDescription) {
            $prompt .= "وصف الوظيفة:\n{$jobDescription}\n";
        }
        $prompt .= "\n[تعليمات صارمة]:\n";
        $prompt .= "- يجب أن يكون الخطاب بالكامل باللغة {$langName}.\n";
        $prompt .= "- ابدأ بتاريخ اليوم (اختياري)، ثم تحية رسمية.\n";
        $prompt .= "- أبرز المهارات والخبرات ذات الصلة بالوظيفة.\n";
        $prompt .= "- عبر عن الحماس والقيمة التي سيقدمها المرشح.\n";
        $prompt .= "- اختتم بعبارة شكر وتقدير.\n";
        $prompt .= "- الطول المثالي: 200-300 كلمة.\n";
        $prompt .= "- أخرج نص الخطاب فقط بدون أي إضافات.";

        try {
            $response = Http::withToken(env('COHERE_API_KEY'))
                ->timeout(45)
                ->post('https://api.cohere.ai/v1/chat', [
                    'model'       => 'command-r',
                    'preamble'    => "أنت كاتب خطابات تغطية محترف. مهمتك كتابة خطاب مميز وجذاب.",
                    'message'     => $prompt,
                    'temperature' => 0.7,
                    'max_tokens'  => 800,
                ]);

            if ($response->successful()) {
                $generated = $response->json('text');
                $generated = trim($generated);
                if (strlen($generated) < 50) {
                    throw new \Exception('النص المُولَّد قصير جدًا أو فارغ.');
                }
                return $generated;
            }

            Log::error('Cohere API error for cover letter', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \Exception('فشل الاتصال بالذكاء الاصطناعي: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Cover letter AI exception: ' . $e->getMessage());
            session()->flash('warning', 'تعذر استخدام الذكاء الاصطناعي، تم إنشاء خطاب أولي. حاول مرة أخرى لاحقاً.');
            return $this->enhancedFallbackCoverLetter($context, $targetJobTitle, $companyName, $language);
        }
    }

    /**
     * خطاب احتياطي محسّن (يستخدم البيانات الحقيقية للمستخدم قدر الإمكان).
     */
    protected function enhancedFallbackCoverLetter(array $context, string $targetJobTitle, ?string $companyName, string $language): string
    {
        $name = $context['full_name'] ?: 'المرشح';
        $company = $companyName ?: 'الشركة المحترمة';
        $jobTitle = $targetJobTitle;
        
        $skills = $this->toArray($context['skills'] ?? []);
        $skillsList = !empty($skills) ? implode('، ', array_slice($skills, 0, 3)) : '';
        
        $experiences = $this->toArray($context['experiences'] ?? []);
        $latestExp = !empty($experiences) ? $experiences[0] : null;
        $expText = '';
        if ($latestExp) {
            $expText = "خبرتي كـ {$latestExp['position']} في {$latestExp['company']} " . 
                       ($latestExp['description'] ? "حيث قمت بـ {$latestExp['description']}" : '');
        }

        if ($language === 'ar') {
            $body = "السادة في {$company}،

تحية طيبة وبعد،

أتقدم إليكم باسمي {$name} للتقدم لوظيفة {$jobTitle}. ";
            
            if ($expText) {
                $body .= "أمتلك {$expText}. ";
            }
            if ($skillsList) {
                $body .= "كما أمتلك مهارات في: {$skillsList}. ";
            }
            if (empty($expText) && empty($skillsList) && !empty($context['uploaded_text'])) {
                $body .= "أرفق معلومات إضافية من سيرتي الذاتية: " . substr($context['uploaded_text'], 0, 200) . ". ";
            }
            
            $body .= "

أؤمن بأن خبراتي ومهاراتي ستضيف قيمة كبيرة لفريقكم. أرفق سيرتي الذاتية وأتطلع إلى فرصة للمقابلة.

وتفضلوا بقبول فائق الاحترام،
{$name}";
            return $body;
        } 
        elseif ($language === 'fr') {
            $body = "À l'attention de {$company},

Madame, Monsieur,

Je soussigné(e) {$name}, vous adresse ma candidature pour le poste de {$jobTitle}. ";
            if ($expText) {
                $body .= "Je dispose d'une expérience en tant que {$latestExp['position']} chez {$latestExp['company']}. ";
            }
            if ($skillsList) {
                $body .= "Mes compétences incluent : {$skillsList}. ";
            }
            $body .= "

Je serais ravi de vous rencontrer pour discuter de ma motivation.

Veuillez agréer, Madame, Monsieur, l'expression de mes salutations distinguées.
{$name}";
            return $body;
        }

        // English
        $body = "Dear Hiring Manager at {$company},

I am writing to express my interest in the {$jobTitle} position. ";
        if ($expText) {
            $body .= "I have experience as a {$latestExp['position']} at {$latestExp['company']}. ";
        }
        if ($skillsList) {
            $body .= "My skills include: {$skillsList}. ";
        }
        $body .= "

I look forward to the opportunity to discuss how I can contribute to your team.

Sincerely,
{$name}";
        return $body;
    }
}