<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Resume;
use App\Models\Template;
use App\Models\Skill;
use App\Models\Language;
use App\Models\Education;
use App\Models\Experience;
use App\Models\PersonalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use App\Services\DoppioPdfService;

class ResumeController extends Controller
{
    /**
     * عرض القوالب المتاحة لاختيار القالب واللغة.
     */
    public function showTemplates()
    {
        $templates = Template::all();
        return view('resumes.choose-template', compact('templates'));
    }

    /**
     * بدء عملية إنشاء سيرة جديدة (حفظ القالب واللغة في الجلسة).
     */
    public function startWithTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'resume_language' => 'required|in:ar,en,fr'
        ]);

        session([
            'selected_template_id' => $request->template_id,
            'resume_language' => $request->resume_language
        ]);

        return redirect()->route('resume.create')
                         ->with('success', __('messages.template_language_selected', [], session('resume_language')));
    }

    /**
     * عرض نموذج إنشاء سيرة ذاتية جديدة.
     */
    public function create()
    {
        if (!session()->has('selected_template_id')) {
            return redirect()->route('templates.choose')
                ->with('warning', __('messages.please_select_template_first'));
        }

        if (!auth()->user()->can('create', Resume::class)) {
            $limit = auth()->user()->plan?->cv_limit ?? 0;
            return redirect()->route('dashboard')
                ->with('error', __('messages.max_resume_limit_reached', ['limit' => $limit]));
        }

        $plans = Plan::where('is_active', true)->get();
        $currentLang = session('resume_language', app()->getLocale());

        return view('resumes.create', compact('plans', 'currentLang'));
    }

    /**
     * حفظ سيرة ذاتية جديدة (جميع البيانات).
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('create', Resume::class)) {
            $limit = auth()->user()->plan?->cv_limit ?? 0;
            return redirect()->route('dashboard')
                ->with('error', "لقد وصلت الحد الأقصى من السير الذاتية المسموح بها في باقتك الحالية ({$limit} سيرة ذاتية). يرجى ترقية باقتك لإنشاء سيرة ذاتية جديدة.");
        }

        $validated = $request->validate([
            'full_name'   => 'required|string|max:255',
            'job_title'   => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'summary'     => 'nullable|string',
            'skills'      => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'cropped_photo_base64' => 'nullable|string|regex:/^data:image\/[^;]+;base64,/',
            'experiences' => 'nullable|array',
            'experiences.*.company' => 'nullable|string|max:255',
            'experiences.*.position' => 'nullable|string|max:255',
            'experiences.*.start_date' => 'nullable|date',
            'experiences.*.end_date'   => 'nullable|date',
            'experiences.*.is_current' => 'nullable|boolean',
            'experiences.*.description' => 'nullable|string',
            'educations'   => 'nullable|array',
            'languages'    => 'nullable|array',
            'languages.*.name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // إنشاء السيرة الرئيسية
            $resume = Resume::create([
                'uuid'        => (string) Str::uuid(),
                'user_id'     => auth()->id(),
                'template_id' => session('selected_template_id', 1),
                'title'       => $request->job_title ? 'سيرة ' . $request->job_title : 'سيرة ذاتية',
                'resume_language' => session('resume_language', 'ar'),
                'is_published'=> false,
                'extra_sections' => $request->has('extra_sections') ? json_decode($request->extra_sections, true) : null,
            ]);

            session()->forget('selected_template_id');

            // تجهيز البيانات الشخصية
            $personalData = [
                'full_name' => $request->full_name,
                'job_title' => $request->job_title,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'summary'   => $request->summary,
            ];

            // معالجة الصورة
            if ($request->filled('cropped_photo_base64')) {
                $imageData = $request->input('cropped_photo_base64');
                $image = base64_decode(preg_replace('/^data:image\/[^;]+;base64,/', '', $imageData));
                if ($image === false) {
                    throw new \Exception('Base64 decode failed.');
                }
                $filename = time() . '_' . Str::random(10) . '.png';
                Storage::disk('public')->put('uploads/photos/' . $filename, $image);
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            } elseif ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('uploads/photos', $filename, 'public');
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            }

            $resume->personalDetail()->create($personalData);

            // المؤهلات الدراسية
            if ($request->has('educations')) {
                foreach ($request->educations as $edu) {
                    if (!empty($edu['institution']) && !empty($edu['degree'])) {
                        $resume->educations()->create([
                            'institution'     => $edu['institution'],
                            'degree'          => $edu['degree'],
                            'field_of_study'  => $edu['field_of_study'] ?? null,
                            'graduation_year' => $edu['graduation_year'] ?? null,
                        ]);
                    }
                }
            }

            // الخبرات العملية
            if ($request->has('experiences')) {
                foreach ($request->experiences as $exp) {
                    if (!empty($exp['company']) && !empty($exp['position'])) {
                        $resume->experiences()->create([
                            'company'     => $exp['company'],
                            'position'    => $exp['position'],
                            'start_date'  => $exp['start_date'] ?? null,
                            'end_date'    => $exp['end_date'] ?? null,
                            'is_current'  => isset($exp['is_current']) && $exp['is_current'],
                            'description' => $exp['description'] ?? null,
                        ]);
                    }
                }
            }

            // المهارات
            if ($request->filled('skills')) {
                $skillsArray = explode(',', $request->skills);
                foreach ($skillsArray as $skillName) {
                    $skillName = trim($skillName);
                    if ($skillName !== '') {
                        $resume->skills()->create(['name' => $skillName]);
                    }
                }
            }

            // اللغات
            if ($request->has('languages')) {
                foreach ($request->languages as $lang) {
                    if (!empty($lang['name'])) {
                        $resume->languages()->create([
                            'name'        => $lang['name'],
                            'proficiency' => $lang['proficiency'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('resume.show', $resume->uuid)
                             ->with('success', 'تم إنشاء سيرتك الذاتية بنجاح! 🎉');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Resume store failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء حفظ السيرة: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * عرض السيرة الذاتية (قالب العرض).
     */
    public function show($uuid)
    {
        $resume = Resume::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages', 'template'])
            ->firstOrFail();

        $plans = Plan::all();
        $viewPath = $resume->template->view_path ?? 'templates.classic';

        return view($viewPath, compact('resume', 'plans'));
    }

    /**
     * عرض نموذج تعديل السيرة الذاتية.
     */
    public function edit($uuid)
    {
        $resume = Resume::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages'])
            ->firstOrFail();

        return view('resumes.edit', compact('resume'));
    }

    /**
     * تحديث السيرة الذاتية (مع حماية الاسم والعلاقات).
     */
    public function update(Request $request, $uuid)
    {
        $resume = Resume::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();
        $personalDetail = $resume->personalDetail;

        $request->validate([
            'full_name'   => 'required|string|max:255',
            'job_title'   => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'summary'     => 'nullable|string',
            'skills'      => 'nullable|string',
            'photo'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'cropped_photo_base64' => 'nullable|string|regex:/^data:image\/[^;]+;base64,/',
            'experiences' => 'nullable|array',
            'experiences.*.company'    => 'nullable|string|max:255',
            'experiences.*.position'   => 'nullable|string|max:255',
            'experiences.*.start_date' => 'nullable|date',
            'experiences.*.end_date'   => 'nullable|date',
            'experiences.*.is_current' => 'nullable|boolean',
            'experiences.*.description'=> 'nullable|string',
            'educations'   => 'nullable|array',
            'languages'    => 'nullable|array',
            'languages.*.name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. حماية الهوية (عداد 3 محاولات لتغيير الاسم)
            $newName = trim($request->full_name);
            $oldName = trim($personalDetail->full_name ?? '');

            if (mb_strtolower($newName) !== mb_strtolower($oldName)) {
                if ($resume->is_name_locked) {
                    return back()->with('error', 'عذراً، تم الوصول للحد الأقصى لتغيير الاسم في هذه السيرة. للحماية، يرجى التواصل مع الدعم الفني.')->withInput();
                }
                if ($resume->name_changes_left > 1) {
                    $resume->decrement('name_changes_left');
                } else {
                    $resume->update([
                        'name_changes_left' => 0,
                        'is_name_locked' => true
                    ]);
                }
            }

            // 2. تحديث البيانات الشخصية (مع الصورة)
            $personalData = [
                'full_name' => ($resume->is_name_locked && mb_strtolower($newName) !== mb_strtolower($oldName)) ? $oldName : $newName,
                'job_title' => $request->job_title,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'summary'   => $request->summary,
            ];

            // معالجة الصورة (حذف القديمة عند رفع جديدة)
            if ($request->filled('cropped_photo_base64')) {
                if ($personalDetail->photo_path && Storage::disk('public')->exists($personalDetail->photo_path)) {
                    Storage::disk('public')->delete($personalDetail->photo_path);
                }
                $imageData = $request->input('cropped_photo_base64');
                $image = base64_decode(preg_replace('/^data:image\/[^;]+;base64,/', '', $imageData));
                $filename = time() . '_' . Str::random(10) . '.png';
                Storage::disk('public')->put('uploads/photos/' . $filename, $image);
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            } elseif ($request->hasFile('photo')) {
                if ($personalDetail->photo_path && Storage::disk('public')->exists($personalDetail->photo_path)) {
                    Storage::disk('public')->delete($personalDetail->photo_path);
                }
                $photo = $request->file('photo');
                $filename = time() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('uploads/photos', $filename, 'public');
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            }

            $personalDetail->update($personalData);

            // 3. تحديث العلاقات (حذف + إعادة إنشاء)
            $resume->educations()->delete();
            if ($request->has('educations')) {
                foreach ($request->educations as $edu) {
                    if (!empty($edu['institution']) && !empty($edu['degree'])) {
                        $resume->educations()->create($edu);
                    }
                }
            }

            $resume->experiences()->delete();
            if ($request->has('experiences')) {
                foreach ($request->experiences as $exp) {
                    if (!empty($exp['company']) && !empty($exp['position'])) {
                        $exp['is_current'] = isset($exp['is_current']) && $exp['is_current'];
                        $resume->experiences()->create($exp);
                    }
                }
            }

            $resume->skills()->delete();
            if ($request->filled('skills')) {
                $skillsArray = explode(',', $request->skills);
                foreach ($skillsArray as $skillName) {
                    if (trim($skillName) !== '') {
                        $resume->skills()->create(['name' => trim($skillName)]);
                    }
                }
            }

            $resume->languages()->delete();
            if ($request->has('languages')) {
                foreach ($request->languages as $lang) {
                    if (!empty($lang['name'])) {
                        $resume->languages()->create($lang);
                    }
                }
            }

            DB::commit();

            $message = ($resume->is_name_locked && mb_strtolower($newName) !== mb_strtolower($oldName))
                ? 'تم حفظ التعديلات بنجاح. (ملاحظة: لقد استنفدت محاولات تغيير الاسم، تم قفل هوية هذه السيرة لحمايتها).'
                : 'تم تحديث سيرتك الذاتية بنجاح!';

            return redirect()->route('resume.show', $resume->uuid)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Resume update error (UUID: {$uuid})", [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء تحديث السيرة: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * صفحة معاينة السيرة (خالية من الأزرار، مناسبة لـ Puppeteer)
     */
    public function pdfPreview($uuid)
    {
        // لا نضيف شرط user_id لأن الرابط موقع (signed) ومؤقت
        $resume = Resume::where('uuid', $uuid)
            ->with(['user.plan', 'personalDetail', 'experiences', 'educations', 'skills', 'languages', 'template'])
            ->firstOrFail();

        return view('resumes.pdf-preview', compact('resume'));
    }

    /**
     * تحميل السيرة الذاتية كملف PDF باستخدام Puppeteer (مع تخزين مؤقت)
     */
   
public function downloadPdf($uuid, DoppioPdfService $pdfService)
{
    $resume = Resume::where('uuid', $uuid)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    // لا حاجة لـ authorize لأننا قيدنا الاستعلام بالمستخدم نفسه

    // إنشاء رابط معاينة مؤقت (صالح لمدة 5 دقائق)
    $previewUrl = URL::signedRoute('resume.pdf-preview', ['uuid' => $resume->uuid], now()->addMinutes(5));

    try {
        $pdfContent = $pdfService->generatePdfFromUrl($previewUrl, [
            'printBackground' => true,
            'format' => 'A4',
            'marginTop' => 20,
            'marginBottom' => 20,
            'marginLeft' => 15,
            'marginRight' => 15,
        ]);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="cv.pdf"');
    } catch (\Exception $e) {
        Log::error('Doppio PDF error: ' . $e->getMessage());
        return back()->with('error', 'فشل إنشاء ملف PDF، حاول مرة أخرى.');
    }
}
}