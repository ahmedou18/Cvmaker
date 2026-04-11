<x-app-layout>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    @include('resumes.partials.styles')

    @php
        $personal = $resume->personalDetail;
        $initialData = [
            'full_name' => $personal->full_name ?? '',
            'job_title' => $personal->job_title ?? '',
            'email' => $personal->email ?? '',
            'phone' => $personal->phone ?? '',
            'address' => $personal->address ?? '',
            'summary' => $personal->summary ?? '',
            'skills' => $resume->skills->pluck('name')->implode('، '),
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
                'proficiency' => $l->proficiency ?? 'متوسط'
            ])->toArray(),
            'extra_sections' => $resume->extra_sections ?? [],
            'existingPhoto' => $personal->photo_path ? asset($personal->photo_path) : '',
        ];
        
        // ضمان وجود عنصر واحد على الأقل
        if (empty($initialData['educations'])) $initialData['educations'] = [['id' => time(), 'institution' => '', 'degree' => '', 'field_of_study' => '', 'graduation_year' => '']];
        if (empty($initialData['experiences'])) $initialData['experiences'] = [['id' => time(), 'company' => '', 'position' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '']];
        if (empty($initialData['languages'])) $initialData['languages'] = [['id' => time(), 'name' => '', 'proficiency' => 'متوسط']];
    @endphp

    <div class="page-content py-10" dir="rtl" x-data="resumeForm({{ json_encode($initialData) }})" x-cloak>
        <div class="max-w-[1400px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                
                @include('resumes.partials.steps-sidebar', ['title' => 'تعديل السيرة'])

                <main class="w-full lg:w-3/4">
                    <form action="{{ route('resume.update', $resume->uuid) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="extra_sections" :value="JSON.stringify(extra_sections)">

                        @include('resumes.partials.step-personal', ['resume' => $resume, 'isEdit' => true])
                        @include('resumes.partials.step-education')
                        @include('resumes.partials.step-experience')
                        @include('resumes.partials.step-skills-summary')
                        @include('resumes.partials.step-languages')
                        @include('resumes.partials.step-final')

                        @include('resumes.partials.navigation-buttons', ['submitText' => 'تحديث السيرة ✅'])
                    </form>
                </main>
            </div>
        </div>

        @include('resumes.partials.cropper-modal')
        <x-plans-modal x-show="showPlansModal" x-cloak x-transition close-action="@click='showPlansModal = false'" />
    </div>

    @include('resumes.partials.scripts', [
        'isEdit' => true,
        'nameLocked' => $resume->is_name_locked,
        'nameChangesLeft' => $resume->name_changes_left,
    ])
</x-app-layout>