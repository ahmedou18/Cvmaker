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
    // ... جميع الدوال الأخرى (showTemplates, startWithTemplate, create, show, edit, pdfPreview, downloadPdf) تبقى كما هي ...

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
            'skills_array' => 'nullable|array',
            'skills_array.*.name' => 'required_with:skills_array|string|max:255',
            'skills_array.*.percentage' => 'nullable|integer|min:0|max:100',
            'skills_array.*.level' => 'nullable|string|max:50',
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
            // إنشاء السيرة الرئيسية
            $resume = Resume::create([
                'uuid'        => (string) Str::uuid(),
                'user_id'     => auth()->id(),
                'template_id' => session('selected_template_id', 1),
                'title'       => $request->job_title ? 'سيرة ' . $request->job_title : 'سيرة ذاتية',
                'resume_language' => session('resume_language', 'ar'),
                'is_published'=> false,
                'extra_sections' => $request->has('extra_sections') ? (is_string($request->extra_sections) ? json_decode($request->extra_sections, true) : $request->extra_sections) : null,
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

            // ========== المهارات ==========
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

            // ========== اللغات ==========
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

            // ========== الهوايات ==========
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

            // ========== المراجع ==========
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

            // إزالة أي بيانات قديمة من الجلسة لتخفيف الـ header
            session()->forget('_old_input');

            // رسالة نجاح قصيرة جداً (بدون إيموجي طويل)
            return redirect()->route('resume.show', $resume->uuid)
                             ->with('success', 'تم الحفظ');

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
     * تحديث السيرة الذاتية (مع حماية الاسم والعلاقات).
     */
    public function update(Request $request, $uuid)
    {
        // ========== تصفية العناصر الفارغة قبل التحقق ==========
        // المهارات
        if ($request->has('skills_array')) {
            $filtered = array_filter($request->skills_array, fn($s) => !empty($s['name']));
            $request->merge(['skills_array' => array_values($filtered)]);
        }
        // الخبرات
        if ($request->has('experiences')) {
            $filtered = array_filter($request->experiences, fn($e) => !empty($e['company']) || !empty($e['position']));
            $request->merge(['experiences' => array_values($filtered)]);
        }
        // اللغات
        if ($request->has('languages')) {
            $filtered = array_filter($request->languages, fn($l) => !empty($l['name']));
            $request->merge(['languages' => array_values($filtered)]);
        }
        // الهوايات
        if ($request->has('hobbies')) {
            $filtered = array_filter($request->hobbies, fn($h) => !empty($h['name']));
            $request->merge(['hobbies' => array_values($filtered)]);
        }
        // المراجع
        if ($request->has('references')) {
            $filtered = array_filter($request->references, fn($r) => !empty($r['full_name']));
            $request->merge(['references' => array_values($filtered)]);
        }
        // =====================================================

        \Log::info('Update request received', $request->all());

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
            // 1. حماية الهوية
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

            // تحديث extra_sections
            if ($request->has('extra_sections')) {
                $resume->update([
                    'extra_sections' => is_string($request->extra_sections) 
                        ? json_decode($request->extra_sections, true) 
                        : $request->extra_sections
                ]);
            }

            // حذف العلاقات القديمة وإعادة إنشائها
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

            // المهارات
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

            // اللغات
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

            // الهوايات
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

            // المراجع
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

            // إزالة أي بيانات قديمة من الجلسة لتخفيف الـ header
            session()->forget('_old_input');

            // رسالة نجاح قصيرة جداً
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
}