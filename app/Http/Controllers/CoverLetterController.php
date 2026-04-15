<?php

namespace App\Http\Controllers;

use App\Models\CoverLetter;
use App\Models\Resume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;
use Smalot\PdfParser\Parser;

class CoverLetterController extends Controller
{
    /**
     * عرض نموذج إنشاء خطاب تغطية جديد.
     */
    public function create()
    {
        $this->authorize('create', CoverLetter::class);

        $resumes = Resume::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('cover-letters.create', compact('resumes'));
    }

    /**
     * حفظ خطاب التغطية وتوليد المحتوى بالذكاء الاصطناعي.
     */
    public function store(Request $request)
    {
        $this->authorize('create', CoverLetter::class);

        $request->validate([
            'resume_id'          => 'nullable|exists:resumes,id',
            'uploaded_file'      => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'target_job_title'   => 'required|string|max:255',
            'company_name'       => 'nullable|string|max:255',
            'job_description'    => 'nullable|string',
            'job_description_url'=> 'nullable|url',
            'language'           => 'required|in:ar,en,fr',
        ], [], [
            'resume_id'          => 'السيرة الذاتية',
            'uploaded_file'      => 'الملف المرفوع',
            'target_job_title'   => 'المسمى الوظيفي المستهدف',
            'company_name'       => 'اسم الشركة',
            'job_description'    => 'وصف الوظيفة',
            'job_description_url'=> 'رابط وصف الوظيفة',
            'language'           => 'اللغة',
        ]);

        // يجب توفير إما سيرة ذاتية موجودة أو ملف مرفوع
        if (!$request->filled('resume_id') && !$request->hasFile('uploaded_file')) {
            return back()
                ->withErrors(['uploaded_file' => 'يرجى اختيار سيرة ذاتية موجودة أو رفع ملف (PDF/Word).'])
                ->withInput();
        }

        $context = $this->extractContext($request);

        DB::beginTransaction();

        try {
            $coverLetter = CoverLetter::create([
                'user_id'          => Auth::id(),
                'target_job_title' => $request->target_job_title,
                'company_name'     => $request->company_name,
                'content'          => '', // سيتم ملؤه بالذكاء الاصطناعي
            ]);

            // توليد المحتوى بالذكاء الاصطناعي
            $generatedContent = $this->generateWithAI(
                coverLetter: $coverLetter,
                context: $context,
                language: $request->language,
                jobDescription: $request->job_description ?? $request->job_description_url ?? '',
            );

            $coverLetter->update(['content' => $generatedContent]);

            DB::commit();

            return redirect()->route('cover-letters.show', $coverLetter->id)
                ->with('success', 'تم إنشاء خطاب التغطية بنجاح! 🎉');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cover letter creation failed: ' . $e->getMessage());

            return back()
                ->with('error', 'حدث خطأ أثناء إنشاء خطاب التغطية: ' . $e->getMessage())
                ->withInput();
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

        $this->authorize('download', $coverLetter);

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
        ];

        // استخراج السياق من سيرة ذاتية موجودة
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

        // استخراج النص من ملف مرفوع (PDF أو Word)
        if ($request->hasFile('uploaded_file')) {
            $file = $request->file('uploaded_file');
            $extractedText = $this->extractTextFromFile($file);
            $context['uploaded_text'] = $extractedText;
        }

        return $context;
    }

    /**
     * استخراج النص من ملف PDF أو Word.
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
            // استخراج نص بسيط من ملفات Word (fallback)
            try {
                $content = file_get_contents($file->getRealPath());
                // تنظيف النص من الرموز غير المرئية
                $content = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $content);
            } catch (\Exception $e) {
                Log::warning('Failed to extract text from Word: ' . $e->getMessage());
            }
        }

        return $content;
    }

    /**
     * توليد خطاب التغطية بالذكاء الاصطناعي.
     *
     * @param  \App\Models\CoverLetter  $coverLetter
     * @param  array  $context
     * @param  string  $language
     * @param  string  $jobDescription
     * @return string
     */
    protected function generateWithAI(CoverLetter $coverLetter, array $context, string $language, string $jobDescription): string
    {
        // ============================================================
        // PLACEHOLDER: استبدل هذا بتنفيذك الفعلي للذكاء الاصطناعي
        // ============================================================
        // مثال على التكامل:
        // $response = Http::withToken(config('services.openai.api_key'))
        //     ->post('https://api.openai.com/v1/chat/completions', [
        //         'model' => 'gpt-4',
        //         'messages' => [
        //             ['role' => 'system', 'content' => 'أنت كاتب خطابات تغطية محترف.'],
        //             ['role' => 'user', 'content' => $this->buildPrompt($context, $jobDescription, $language)],
        //         ],
        //     ]);
        // return $response->json('choices.0.message.content');

        // محتوى افتراضي حتى يتم ربط الذكاء الاصطناعي
        $name = $context['full_name'] ?: 'المرشح';
        $jobTitle = $context['target_job_title'] ?? 'الوظيفة المستهدفة';
        $company = $coverLetter->company_name ?: 'الشركة المحترمة';

        if ($language === 'ar') {
            return "السادة في {$company}،

تحية طيبة وبعد،

أتقدم إليكم باسمي {$name} للتقدم لوظيفة {$jobTitle}. أتمنى أن أكون عند حسن ظنكم.

أتمنى منكم التكرم بالنظر في طلبي.

وتفضلوا بقبول فائق الاحترام والتقدير،
{$name}";
        } elseif ($language === 'fr') {
            return "À l'attention de {$company},

Madame, Monsieur,

Je soussigné(e) {$name}, vous adresse ma candidature pour le poste de {$jobTitle}.

Je reste à votre disposition pour un entretien.

Veuillez agréer, Madame, Monsieur, l'expression de mes salutations distinguées.
{$name}";
        }

        // English fallback
        return "Dear Hiring Manager at {$company},

I am writing to express my interest in the {$jobTitle} position.

My name is {$name} and I believe I would be a great fit for this role.

Thank you for your consideration.

Sincerely,
{$name}";
    }

    /**
     * بناء الـ Prompt للذكاء الاصطناعي (مستخدم عند التكامل الفعلي).
     */
    protected function buildPrompt(array $context, string $jobDescription, string $language): string
    {
        $prompt = "Write a professional cover letter.\n\n";
        $prompt .= "Candidate: {$context['full_name']}\n";
        $prompt .= "Current Title: {$context['job_title']}\n";
        $prompt .= "Email: {$context['email']}\n";
        $prompt .= "Phone: {$context['phone']}\n";
        $prompt .= "Summary: {$context['summary']}\n";

        if (!empty($context['experiences'])) {
            $prompt .= "\nExperience:\n";
            foreach ($context['experiences'] as $exp) {
                $prompt .= "- {$exp['position']} at {$exp['company']} ({$exp['start_date']} to {$exp['end_date']})\n";
            }
        }

        if (!empty($context['skills'])) {
            $prompt .= "\nSkills: " . implode(', ', $context['skills']) . "\n";
        }

        if ($jobDescription) {
            $prompt .= "\nJob Description: {$jobDescription}\n";
        }

        $prompt .= "\nLanguage: {$language}\n";
        $prompt .= "Keep it professional, concise, and tailored to the position.";

        return $prompt;
    }
}
