<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use PDF;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; // للتعامل الآمن مع الملفات
use Illuminate\Support\Facades\Log;     // لتسجيل الأخطاء
use App\Models\Resume;
use App\Models\PersonalDetail;
use App\Models\Experience;
use App\Models\Education;
use App\Models\Skill;
use App\Models\Language;
use App\Models\Template;

class ResumeController extends Controller
{
    // --------------------------------------------------------
    // الدوال الخاصة باختيار القالب قبل البدء
    // --------------------------------------------------------
    public function showTemplates()
    {
        $templates = Template::all();
        return view('resumes.choose-template', compact('templates'));
    }

    public function startWithTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
            'resume_language' => 'required|in:ar,en,fr' // اللغات المسموح بها للسيرة
        ]);

        session(['selected_template_id' => $request->template_id]);
        session(['resume_language' => $request->resume_language]); // حفظ لغة السيرة

        return redirect()->route('resume.create')
                         ->with('success', 'تم اختيار القالب واللغة بنجاح!');
    }
    
    // --------------------------------------------------------

    public function downloadPdf($uuid)
    {
        $resume = Resume::where('uuid', $uuid)
            ->where('user_id', auth()->id()) // Security: Ensure user owns this resume
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages'])
            ->firstOrFail();

        $pdf = PDF::loadView('resumes.pdf_template', compact('resume'), [], [
            'format' => 'A4',
            'default_font' => 'xbriyaz',
            'directionality' => 'rtl',
        ]);

        $fileName = 'resume_' . time() . '.pdf';
        return $pdf->download($fileName);
    }

    public function create()
{
    // التحقق من أن المستخدم اختار قالباً مسبقاً
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

    public function show($uuid)
    {
        $resume = Resume::where('uuid', $uuid)
            ->where('user_id', auth()->id()) // Security: Ensure user owns this resume
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages', 'template'])
            ->firstOrFail();

        // 1. جلب جميع الباقات من قاعدة البيانات
        $plans = Plan::all(); 

        $viewPath = $resume->template->view_path ?? 'templates.classic';

        // 2. إضافة المتغير 'plans' بداخل الـ compact ليتم إرساله للواجهة
        return view($viewPath, compact('resume', 'plans'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('create', Resume::class)) {
            $limit = auth()->user()->plan?->cv_limit ?? 0;
            return redirect()->route('dashboard')
                ->with('error', "لقد وصلت الحد الأقصى من السير الذاتية المسموح بها في باقتك الحالية ({$limit} سيرة ذاتية). يرجى ترقية باقتك لإنشاء سيرة ذاتية جديدة.");
        }

        // 1. التحقق من صحة البيانات الأساسية مع التحسينات الأمنية
        $request->validate([
            'full_name'   => 'required|string|max:255',
            'job_title'   => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'summary'     => 'nullable|string',
            'skills'      => 'nullable|string',
            
            // التحقق من أن الملف المرفوع هو صورة بحجم أقصى 2 ميجابايت (الرفع العادي)
            'photo'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            
            // التحقق الأمني من حقل الصورة المقصوصة (Base64) باستخدام التعبير النمطي
            'cropped_photo_base64' => 'nullable|string|regex:/^data:image\/[^;]+;base64,/',
            
            // التحقق من المصفوفات
            'experiences'              => 'nullable|array',
            'experiences.*.company'    => 'nullable|string|max:255',
            'experiences.*.position'   => 'nullable|string|max:255',
            'experiences.*.start_date' => 'nullable|date',
            'experiences.*.end_date'   => 'nullable|date',
            'experiences.*.is_current' => 'nullable|boolean',
            'experiences.*.description'=> 'nullable|string',
            
            'educations' => 'nullable|array',
            'languages'  => 'nullable|array',
            'languages.*.name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // أ. إنشاء السيرة الذاتية الرئيسية
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

            // ب. تجهيز مصفوفة التفاصيل الشخصية
            $personalData = [
                'full_name' => $request->full_name,
                'job_title' => $request->job_title,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'summary'   => $request->summary,
            ];

            // ج. التعامل الآمن مع الصورة (التحسينات المدمجة)
            if ($request->filled('cropped_photo_base64')) {
                $imageData = $request->input('cropped_photo_base64');
                
                try {
                    // فك التشفير الآمن
                    $image = base64_decode(preg_replace('/^data:image\/[^;]+;base64,/', '', $imageData));
                    if ($image === false) {
                        throw new \Exception('Base64 decode failed.');
                    }

                    $filename = time() . '_' . Str::random(10) . '.png';
                    // الحفظ باستخدام Storage لزيادة الأمان
                    Storage::disk('public')->put('uploads/photos/' . $filename, $image);
                    
                    $personalData['photo_path'] = 'uploads/photos/' . $filename;

                } catch (\Exception $e) {
                    Log::error('Image upload error: ' . $e->getMessage());
                    throw new \Exception('حدث خطأ أثناء معالجة الصورة.');
                }

            } elseif ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '.' . $photo->getClientOriginalExtension();
                
                // الحفظ باستخدام Storage كأفضل ممارسة
                $photo->storeAs('uploads/photos', $filename, 'public');
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            }

            // د. حفظ التفاصيل الشخصية
            $resume->personalDetail()->create($personalData);

            // هـ. حفظ المؤهلات الدراسية
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

            // و. حفظ الخبرات العملية
            if ($request->has('experiences')) {
                foreach ($request->experiences as $exp) {
                    if (!empty($exp['company']) && !empty($exp['position'])) {
                        $resume->experiences()->create([
                            'company'     => $exp['company'],
                            'position'    => $exp['position'],
                            'start_date'  => $exp['start_date'] ?? null,
                            'end_date'    => $exp['end_date'] ?? null,
                            'is_current'  => isset($exp['is_current']) && $exp['is_current'] ? true : false, 
                            'description' => $exp['description'] ?? null,
                        ]);
                    }
                }
            }

            // ز. حفظ المهارات
            if ($request->filled('skills')) {
                $skillsArray = explode(',', $request->skills);
                foreach ($skillsArray as $skillName) {
                    $skillName = trim($skillName);
                    if ($skillName !== '') {
                        $resume->skills()->create([
                            'name'       => $skillName,
                            'percentage' => 100
                        ]);
                    }
                }
            }

            // ح. حفظ اللغات
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
            return back()->with('error', 'حدث خطأ أثناء حفظ السيرة: ' . $e->getMessage())->withInput();
        }
    }

    // --------------------------------------------------------
    // الدوال الجديدة: التعديل والحماية من استغلال الباقات
    // --------------------------------------------------------

    public function edit($uuid)
    {
        $resume = Resume::where('uuid', $uuid)
            ->where('user_id', auth()->id()) // أمان: المستخدم يمتلك السيرة
            ->with(['personalDetail', 'experiences', 'educations', 'skills', 'languages'])
            ->firstOrFail();

        return view('resumes.edit', compact('resume')); // نفترض أن لديك صفحة edit.blade.php مشابهة لـ create
    }

    public function update(Request $request, $uuid)
    {
        $resume = Resume::where('uuid', $uuid)->where('user_id', auth()->id())->firstOrFail();
        $personalDetail = $resume->personalDetail;

        // نفس قواعد التحقق الموجودة في Store
        $request->validate([
            'full_name'   => 'required|string|max:255',
            'job_title'   => 'nullable|string|max:255',
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:20',
            'address'     => 'nullable|string|max:255',
            'summary'     => 'nullable|string',
            'skills'      => 'nullable|string',
            
            // التحقق من أن الملف المرفوع هو صورة بحجم أقصى 2 ميجابايت (الرفع العادي)
            'photo'       => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            
            // التحقق الأمني من حقل الصورة المقصوصة (Base64) باستخدام التعبير النمطي
            'cropped_photo_base64' => 'nullable|string|regex:/^data:image\/[^;]+;base64,/',
            
            // التحقق من المصفوفات
            'experiences'              => 'nullable|array',
            'experiences.*.company'    => 'nullable|string|max:255',
            'experiences.*.position'   => 'nullable|string|max:255',
            'experiences.*.start_date' => 'nullable|date',
            'experiences.*.end_date'   => 'nullable|date',
            'experiences.*.is_current' => 'nullable|boolean',
            'experiences.*.description'=> 'nullable|string',
            
            'educations' => 'nullable|array',
            'languages'  => 'nullable|array',
            'languages.*.name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 1. خوارزمية حماية الهوية (منع إعادة تدوير السيرة)
            $newName = trim($request->full_name);
            $oldName = trim($personalDetail->full_name);

            // إذا كان المستخدم يحاول تغيير الاسم
            if (mb_strtolower($newName) !== mb_strtolower($oldName)) {
                
                // التحقق مما إذا كانت السيرة مقفلة بالفعل
                if ($resume->is_name_locked) {
                    return back()->with('error', 'عذراً، تم الوصول للحد الأقصى لتغيير الاسم في هذه السيرة. للحماية، يرجى التواصل مع الدعم الفني.');
                }

                // إذا لم تكن مقفلة، نخصم محاولة
                if ($resume->name_changes_left > 1) {
                    $resume->decrement('name_changes_left');
                } else {
                    // كانت هذه المحاولة الأخيرة، نقفل السيرة بعد هذا التغيير
                    $resume->update([
                        'name_changes_left' => 0,
                        'is_name_locked' => true
                    ]);
                }
            }

            // 2. تحديث البيانات الشخصية
            $personalData = [
                // إذا كانت السيرة مقفلة، نفرض الاسم القديم. إذا لا، نقبل الجديد.
                'full_name' => $resume->is_name_locked && (mb_strtolower($newName) !== mb_strtolower($oldName)) ? $oldName : $newName,
                'job_title' => $request->job_title,
                'email'     => $request->email,
                'phone'     => $request->phone,
                'address'   => $request->address,
                'summary'   => $request->summary,
            ];

            // معالجة الصورة عند التحديث (نفس منطق الحفظ)
            if ($request->filled('cropped_photo_base64')) {
                $imageData = $request->input('cropped_photo_base64');
                $image = base64_decode(preg_replace('/^data:image\/[^;]+;base64,/', '', $imageData));
                $filename = time() . '_' . Str::random(10) . '.png';
                Storage::disk('public')->put('uploads/photos/' . $filename, $image);
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            } elseif ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $filename = time() . '.' . $photo->getClientOriginalExtension();
                $photo->storeAs('uploads/photos', $filename, 'public');
                $personalData['photo_path'] = 'uploads/photos/' . $filename;
            }

            $personalDetail->update($personalData);

            // 3. تحديث القوائم (الطريقة الأضمن: حذف القديم وإدخال الجديد)
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
                        $exp['is_current'] = isset($exp['is_current']) && $exp['is_current'] ? true : false;
                        $resume->experiences()->create($exp);
                    }
                }
            }

            $resume->skills()->delete();
            if ($request->filled('skills')) {
                $skillsArray = explode(',', $request->skills);
                foreach ($skillsArray as $skillName) {
                    if (trim($skillName) !== '') {
                        $resume->skills()->create(['name' => trim($skillName), 'percentage' => 100]);
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

            // رسالة النجاح تتغير إذا تم قفل الحساب للتو
            $message = $resume->is_name_locked && (mb_strtolower($newName) !== mb_strtolower($oldName))
                ? 'تم حفظ التعديلات بنجاح. (ملاحظة: لقد استنفدت محاولات تغيير الاسم، تم قفل هوية هذه السيرة لحمايتها).' 
                : 'تم تحديث سيرتك الذاتية بنجاح!';

            return redirect()->route('resume.show', $resume->uuid)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء تحديث السيرة: ' . $e->getMessage())->withInput();
        }
    }
}