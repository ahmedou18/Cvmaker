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
use App\Models\Hobby;
use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
    /**
 * حفظ سيرة ذاتية جديدة (جميع البيانات).
 */
/**
 * حفظ سيرة ذاتية جديدة (جميع البيانات).
 */
public function store(Request $request)
{
    // التحقق من صلاحية الإنشاء (باستخدام ResumePolicy الجديد الذي يعتمد على الرصيد)
    if (!auth()->user()->can('create', Resume::class)) {
        return redirect()->route('dashboard')
            ->with('error', 'لا يمكنك إنشاء سيرة ذاتية جديدة. رصيدك غير كافٍ أو اشتراكك منتهٍ.');
    }

    // تحويل extra_sections من JSON string إلى مصفوفة قبل التحقق
    if ($request->filled('extra_sections') && is_string($request->extra_sections)) {
        $request->merge(['extra_sections' => json_decode($request->extra_sections, true)]);
    }

    $validated = $request->validate([
        // البيانات الشخصية الأساسية
        'full_name'   => 'required|string|max:255',
        'job_title'   => 'nullable|string|max:255',
        'email'       => 'nullable|email|max:255',
        'phone'       => 'nullable|string|max:20',
        'address'     => 'nullable|string|max:255',
        'summary'     => 'nullable|string',
        
        // المهارات (الوضع القديم والجديد)
        'skills'      => 'nullable|string',
        'skills_array' => 'nullable|array',
        'skills_array.*.name' => 'nullable|string|max:255',
        'skills_array.*.percentage' => 'nullable|integer|min:0|max:100',
        'skills_array.*.level' => 'nullable|string|max:50',
        
        // الصورة
        'photo'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        'cropped_photo_base64' => 'nullable|string|regex:/^data:image\/[^;]+;base64,/',
        
        // الخبرات المهنية
        'experiences' => 'nullable|array',
        'experiences.*.company' => 'nullable|string|max:255',
        'experiences.*.position' => 'nullable|string|max:255',
        'experiences.*.start_date' => 'nullable|date',
        'experiences.*.end_date'   => 'nullable|date',
        'experiences.*.is_current' => 'nullable|boolean',
        'experiences.*.description' => 'nullable|string',
        
        // المؤهلات الدراسية
        'educations'   => 'nullable|array',
        'educations.*.institution' => 'nullable|string|max:255',
        'educations.*.degree' => 'nullable|string|max:255',
        'educations.*.field_of_study' => 'nullable|string|max:255',
        'educations.*.graduation_year' => 'nullable|string|max:10',
        
        // اللغات
        'languages'    => 'nullable|array',
        'languages.*.name' => 'nullable|string|max:255',
        'languages.*.proficiency' => 'nullable|string|max:50',
        'languages.*.level' => 'nullable|integer|min:1|max:5',
        'languages.*.percentage' => 'nullable|integer|min:0|max:100',
        
        // الهوايات
        'hobbies'      => 'nullable|array',
        'hobbies.*.name' => 'nullable|string|max:255',
        'hobbies.*.icon' => 'nullable|string|max:50',
        'hobbies.*.description' => 'nullable|string',
        
        // المراجع
        'references'   => 'nullable|array',
        'references.*.full_name' => 'nullable|string|max:255',
        'references.*.job_title' => 'nullable|string|max:255',
        'references.*.company' => 'nullable|string|max:255',
        'references.*.email' => 'nullable|email|max:255',
        'references.*.phone' => 'nullable|string|max:20',
        'references.*.notes' => 'nullable|string',
        
        // الأقسام الإضافية
        'extra_sections' => 'nullable|array',
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
            'extra_sections' => $request->extra_sections,
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

        // المهارات (بصيغة جديدة أو قديمة)
        if ($request->has('skills_array') && is_array($request->skills_array)) {
            foreach ($request->skills_array as $index => $skillData) {
                if (!empty($skillData['name'])) {
                    $resume->skills()->create([
                        'name'       => $skillData['name'],
                        'percentage' => $skillData['percentage'] ?? 80,
                        'level'      => $skillData['level'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }
        } elseif ($request->filled('skills')) {
            $skillsArray = explode(',', $request->skills);
            foreach ($skillsArray as $index => $skillName) {
                $skillName = trim($skillName);
                if ($skillName !== '') {
                    $resume->skills()->create([
                        'name'       => $skillName,
                        'percentage' => 100,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // اللغات
        if ($request->has('languages') && is_array($request->languages)) {
            foreach ($request->languages as $index => $lang) {
                if (!empty($lang['name'])) {
                    $resume->languages()->create([
                        'name'        => $lang['name'],
                        'proficiency' => $lang['proficiency'] ?? null,
                        'level'       => $lang['level'] ?? null,
                        'percentage'  => $lang['percentage'] ?? null,
                        'sort_order'  => $index,
                    ]);
                }
            }
        }

        // الهوايات
        if ($request->has('hobbies') && is_array($request->hobbies)) {
            foreach ($request->hobbies as $index => $hobby) {
                if (!empty($hobby['name'])) {
                    $resume->hobbies()->create([
                        'name'        => $hobby['name'],
                        'icon'        => $hobby['icon'] ?? null,
                        'description' => $hobby['description'] ?? null,
                        'sort_order'  => $index,
                    ]);
                }
            }
        }

        // المراجع
        if ($request->has('references') && is_array($request->references)) {
            foreach ($request->references as $index => $ref) {
                if (!empty($ref['full_name'])) {
                    $resume->references()->create([
                        'full_name'  => $ref['full_name'],
                        'job_title'  => $ref['job_title'] ?? null,
                        'company'    => $ref['company'] ?? null,
                        'email'      => $ref['email'] ?? null,
                        'phone'      => $ref['phone'] ?? null,
                        'notes'      => $ref['notes'] ?? null,
                        'sort_order' => $index,
                    ]);
                }
            }
        }

        // ✅ خصم رصيد الإنشاءات (resume_creations_remaining) بعد نجاح كل العمليات
        $user = auth()->user();
        if ($user->resume_creations_remaining <= 0) {
            throw new \Exception('رصيد الإنشاءات منتهٍ، لا يمكن إكمال العملية.');
        }
        $user->decrement('resume_creations_remaining');

        DB::commit();

        // تنظيف الجلسة
        session()->forget('_old_input');
        session()->forget('url.intended');

        return redirect()->route('resume.show', $resume->uuid);

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
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages', 'hobbies', 'references', 'template'])
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
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages', 'hobbies', 'references'])
            ->firstOrFail();

        $skillsArray = $resume->skills->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'percentage' => $s->percentage,
            'level' => $s->level,
        ])->toArray();

        $languages = $resume->languages->map(fn($l) => [
            'id' => $l->id,
            'name' => $l->name,
            'proficiency' => $l->proficiency,
            'level' => $l->level,
            'percentage' => $l->percentage,
        ])->toArray();

        $hobbies = $resume->hobbies->map(fn($h) => [
            'id' => $h->id,
            'name' => $h->name,
            'icon' => $h->icon,
            'description' => $h->description,
        ])->toArray();

        $references = $resume->references->map(fn($r) => [
            'id' => $r->id,
            'full_name' => $r->full_name,
            'job_title' => $r->job_title,
            'company' => $r->company,
            'email' => $r->email,
            'phone' => $r->phone,
            'notes' => $r->notes,
        ])->toArray();

        $extraSections = $resume->extra_sections ?? [];
        if (is_string($extraSections)) {
            $extraSections = json_decode($extraSections, true) ?? [];
        }

        return view('resumes.edit', compact('resume', 'skillsArray', 'languages', 'hobbies', 'references', 'extraSections'));
    }

    /**
     * تحديث السيرة الذاتية (مع حماية الاسم والعلاقات).
     */
    public function update(Request $request, $uuid)
    {
        // تصفية العناصر الفارغة
        if ($request->has('skills_array')) {
            $filtered = array_filter($request->skills_array, fn($s) => !empty($s['name']));
            $request->merge(['skills_array' => array_values($filtered)]);
        }
        if ($request->has('experiences')) {
            $filtered = array_filter($request->experiences, fn($e) => !empty($e['company']) || !empty($e['position']));
            $request->merge(['experiences' => array_values($filtered)]);
        }
        if ($request->has('languages')) {
            $filtered = array_filter($request->languages, fn($l) => !empty($l['name']));
            $request->merge(['languages' => array_values($filtered)]);
        }
        if ($request->has('hobbies')) {
            $filtered = array_filter($request->hobbies, fn($h) => !empty($h['name']));
            $request->merge(['hobbies' => array_values($filtered)]);
        }
        if ($request->has('references')) {
            $filtered = array_filter($request->references, fn($r) => !empty($r['full_name']));
            $request->merge(['references' => array_values($filtered)]);
        }

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
            'skills_array' => 'nullable|array',
            'skills_array.*.name' => 'required_with:skills_array|string|max:255',
            'skills_array.*.percentage' => 'nullable|integer|min:0|max:100',
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
            'educations.*.institution' => 'required_with:educations|string|max:255',
            'educations.*.degree' => 'required_with:educations|string|max:255',
            'languages'    => 'nullable|array',
            'languages.*.name' => 'required|string|max:255',
            'languages.*.proficiency' => 'nullable|string|max:50',
            'languages.*.level' => 'nullable|integer|min:1|max:5',
            'languages.*.percentage' => 'nullable|integer|min:0|max:100',
            'hobbies'      => 'nullable|array',
            'hobbies.*.name' => 'required_with:hobbies|string|max:255',
            'hobbies.*.icon' => 'nullable|string|max:50',
            'hobbies.*.description' => 'nullable|string',
            'references'   => 'nullable|array',
            'references.*.full_name' => 'required_with:references|string|max:255',
            'references.*.job_title' => 'nullable|string|max:255',
            'references.*.company' => 'nullable|string|max:255',
            'references.*.email' => 'nullable|email|max:255',
            'references.*.phone' => 'nullable|string|max:20',
            'references.*.notes' => 'nullable|string',
            'extra_sections' => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            // حماية الاسم
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

            // تحديث البيانات الشخصية
            $personalData = [
                'full_name' => ($resume->is_name_locked && mb_strtolower($newName) !== mb_strtolower($oldName)) ? $oldName : $newName,
                'job_title' => $request->job_title,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'summary'   => $request->summary,
            ];

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

            if ($request->has('extra_sections')) {
                $resume->update([
                    'extra_sections' => is_string($request->extra_sections) 
                        ? json_decode($request->extra_sections, true) 
                        : $request->extra_sections
                ]);
            }

            // إعادة إنشاء العلاقات
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
            if ($request->has('skills_array') && is_array($request->skills_array)) {
                foreach ($request->skills_array as $index => $skillData) {
                    if (!empty($skillData['name'])) {
                        $resume->skills()->create([
                            'name'       => $skillData['name'],
                            'percentage' => $skillData['percentage'] ?? 80,
                            'level'      => $skillData['level'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            } elseif ($request->filled('skills')) {
                $skillsArray = explode(',', $request->skills);
                foreach ($skillsArray as $index => $skillName) {
                    $skillName = trim($skillName);
                    if ($skillName !== '') {
                        $resume->skills()->create([
                            'name'       => $skillName,
                            'percentage' => 100,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            $resume->languages()->delete();
            if ($request->has('languages') && is_array($request->languages)) {
                foreach ($request->languages as $index => $lang) {
                    if (!empty($lang['name'])) {
                        $resume->languages()->create([
                            'name'        => $lang['name'],
                            'proficiency' => $lang['proficiency'] ?? null,
                            'level'       => $lang['level'] ?? null,
                            'percentage'  => $lang['percentage'] ?? null,
                            'sort_order'  => $index,
                        ]);
                    }
                }
            }

            $resume->hobbies()->delete();
            if ($request->has('hobbies') && is_array($request->hobbies)) {
                foreach ($request->hobbies as $index => $hobby) {
                    if (!empty($hobby['name'])) {
                        $resume->hobbies()->create([
                            'name'        => $hobby['name'],
                            'icon'        => $hobby['icon'] ?? null,
                            'description' => $hobby['description'] ?? null,
                            'sort_order'  => $index,
                        ]);
                    }
                }
            }

            $resume->references()->delete();
            if ($request->has('references') && is_array($request->references)) {
                foreach ($request->references as $index => $ref) {
                    if (!empty($ref['full_name'])) {
                        $resume->references()->create([
                            'full_name'  => $ref['full_name'],
                            'job_title'  => $ref['job_title'] ?? null,
                            'company'    => $ref['company'] ?? null,
                            'email'      => $ref['email'] ?? null,
                            'phone'      => $ref['phone'] ?? null,
                            'notes'      => $ref['notes'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();

            session()->forget('_old_input');
            return redirect()->route('resume.show', $resume->uuid)
                             ->with('success', 'تم التحديث');

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
        $resume = Resume::where('uuid', $uuid)
            ->with(['user.plan', 'personalDetail', 'experiences', 'educations', 'skills', 'languages', 'hobbies', 'references', 'template'])
            ->firstOrFail();

        return view('resumes.pdf-preview', compact('resume'));
    }

    /**
     * تحميل السيرة الذاتية كملف PDF عبر Doppio
     */
    public function downloadPdf($uuid, DoppioPdfService $pdfService)
    {
        $resume = Resume::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();

        $previewUrl = URL::temporarySignedRoute('resume.pdf-preview',
            now()->addMinutes(10),
            ['uuid' => $resume->uuid]
        );

        try {
            $pdfContent = $pdfService->generatePdfFromUrl($previewUrl, [
                'printBackground' => true,
                'format' => 'A4'
            ]);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="cv.pdf"',
            ]);
        } catch (\Exception $e) {
            Log::error('Doppio PDF failed', ['uuid' => $resume->uuid, 'error' => $e->getMessage()]);
            return back()->with('error', 'فشل إنشاء PDF: ' . $e->getMessage());
        }
    }
}