<x-app-layout>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    @include('resumes.partials.styles')

@if($errors->any())
<div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

    @php
        $personal = $resume->personalDetail;
        $currentLang = $resume->resume_language ?? app()->getLocale();

        // تحويل المهارات إلى مصفوفة كائنات مع نسبة مئوية
        $skillsArray = $resume->skills->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'percentage' => $s->percentage ?? 80,
        ])->toArray();
        if (empty($skillsArray)) {
            $skillsArray = [['id' => time(), 'name' => '', 'percentage' => 80]];
        }

        // تحويل الهوايات
        $hobbies = $resume->hobbies->map(fn($h) => [
            'id' => $h->id,
            'name' => $h->name,
            'icon' => $h->icon,
            'description' => $h->description,
        ])->toArray();

        // تحويل المراجع
        $references = $resume->references->map(fn($r) => [
            'id' => $r->id,
            'full_name' => $r->full_name,
            'job_title' => $r->job_title,
            'company' => $r->company,
            'email' => $r->email,
            'phone' => $r->phone,
            'notes' => $r->notes,
        ])->toArray();

        // تحويل extra_sections (موجودة بالفعل كمصفوفة أو JSON)
        $extraSections = [];
        if (!empty($resume->extra_sections)) {
            if (is_string($resume->extra_sections)) {
                $extraSections = json_decode($resume->extra_sections, true) ?? [];
            } else {
                $extraSections = $resume->extra_sections;
            }
        }

        $initialData = [
            'full_name' => $personal->full_name ?? '',
            'job_title' => $personal->job_title ?? '',
            'email' => $personal->email ?? '',
            'phone' => $personal->phone ?? '',
            'address' => $personal->address ?? '',
            'summary' => $personal->summary ?? '',
            'skills' => $resume->skills->pluck('name')->implode('، '),
            'skillsArray' => $skillsArray,
            'educations' => $resume->educations->map(fn($e) => [
                'id' => $e->id,
                'institution' => $e->institution ?? '',
                'degree' => $e->degree ?? '',
                'field_of_study' => $e->field_of_study ?? '',
                'graduation_year' => $e->graduation_year ?? ''
            ])->toArray(),
            'experiences' => $resume->experiences->map(fn($e) => [
                'id' => $e->id,
                'company' => $e->company ?? '',
                'position' => $e->position ?? '',
                'start_date' => $e->start_date ?? '',
                'end_date' => $e->end_date ?? '',
                'is_current' => (bool)($e->is_current ?? false),
                'description' => $e->description ?? ''
            ])->toArray(),
            'languages' => $resume->languages->map(fn($l) => [
                'id' => $l->id,
                'name' => $l->name ?? '',
                'proficiency' => $l->proficiency ?? __('messages.intermediate', [], $currentLang),
                'level' => $l->level ?? 3
            ])->toArray(),
            'hobbies' => $hobbies,
            'references' => $references,
            'extra_sections' => $extraSections,
            'existingPhoto' => $personal->photo_path ? asset('storage/' . $personal->photo_path) : '',
        ];

        // ضمان وجود عنصر واحد على الأقل لكل مجموعة لتجنب الخرائط الفارغة
        if (empty($initialData['educations'])) $initialData['educations'] = [['id' => time(), 'institution' => '', 'degree' => '', 'field_of_study' => '', 'graduation_year' => '']];
        if (empty($initialData['experiences'])) $initialData['experiences'] = [['id' => time(), 'company' => '', 'position' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '']];
        if (empty($initialData['languages'])) $initialData['languages'] = [['id' => time(), 'name' => '', 'proficiency' => __('messages.intermediate', [], $currentLang), 'level' => 3]];
        if (empty($initialData['skillsArray'])) $initialData['skillsArray'] = [['id' => time(), 'name' => '', 'percentage' => 80]];
    @endphp

    <div class="page-content py-10" dir="rtl" x-data="resumeForm({{ json_encode($initialData) }})" x-cloak>
        <div class="max-w-[1400px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                
                @include('resumes.partials.steps-sidebar', [
                    'title' => __('messages.edit_resume', [], $currentLang) ?? 'تعديل السيرة',
                    'currentLang' => $currentLang
                ])

                <main class="w-full lg:w-3/4">
                    <form action="{{ route('resume.update', $resume->uuid) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="extra_sections" :value="JSON.stringify(extra_sections)">

                        @include('resumes.partials.step-personal', [
                            'resume' => $resume,
                            'isEdit' => true,
                            'currentLang' => $currentLang
                        ])
                        @include('resumes.partials.step-education', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-experience', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-skills-summary', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-languages', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-hobbies', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-references', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-final', ['currentLang' => $currentLang])

                        @include('resumes.partials.navigation-buttons', [
                            'submitText' => __('messages.update_resume', [], $currentLang) ?? 'تحديث السيرة ✅',
                            'currentLang' => $currentLang
                        ])
                    </form>
                </main>
            </div>
        </div>

        @include('resumes.partials.cropper-modal', ['currentLang' => $currentLang])
        <x-plans-modal x-show="showPlansModal" x-cloak x-transition closeAction="@click='showPlansModal = false'" :currentLang="$currentLang" />
    </div>

    @include('resumes.partials.scripts', [
        'isEdit' => true,
        'nameLocked' => $resume->is_name_locked,
        'nameChangesLeft' => $resume->name_changes_left,
        'currentLang' => $currentLang
    ])
</x-app-layout>