<x-app-layout>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    @include('resumes.partials.styles')

    @php
        $currentLang = session('resume_language', 'ar');
        $initialData = [
            'full_name' => old('full_name', ''),
            'job_title' => old('job_title', ''),
            'email' => old('email', ''),
            'phone' => old('phone', ''),
            'address' => old('address', ''),
            'summary' => old('summary', ''),
            'skills' => old('skills', ''),
            'skillsArray' => [['id' => time(), 'name' => '', 'percentage' => 80]],
            'educations' => [['id' => time(), 'institution' => '', 'degree' => '', 'field_of_study' => '', 'graduation_year' => '']],
            'experiences' => [['id' => time(), 'company' => '', 'position' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '']],
            'languages' => [['id' => time(), 'name' => '', 'proficiency' => __('messages.intermediate', [], $currentLang), 'level' => 3]],
            'hobbies' => [],
            'references' => [],
            'extra_sections' => [],
            'existingPhoto' => '',
        ];
    @endphp

    <div class="page-content py-10" dir="rtl" x-data="resumeForm({{ json_encode($initialData) }})" x-cloak>
        <div class="max-w-[1400px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                
                @include('resumes.partials.steps-sidebar', ['title' => __('messages.build_resume', [], $currentLang), 'currentLang' => $currentLang])

                <main class="w-full lg:w-3/4">
                    <form action="{{ route('resume.store') }}" method="POST" enctype="multipart/form-data" id="mainResumeForm">
                        @csrf
                        <input type="hidden" name="extra_sections" :value="JSON.stringify(extra_sections)">

                        @include('resumes.partials.step-personal', ['isEdit' => false, 'currentLang' => $currentLang])
                        @include('resumes.partials.step-education', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-experience', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-skills-summary', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-languages', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-hobbies', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-references', ['currentLang' => $currentLang])
                        @include('resumes.partials.step-final', ['currentLang' => $currentLang])

                        @include('resumes.partials.navigation-buttons', ['submitText' => __('messages.save_publish', [], $currentLang), 'currentLang' => $currentLang])
                    </form>
                </main>
            </div>
        </div>

        @include('resumes.partials.cropper-modal', ['currentLang' => $currentLang])
        <x-plans-modal x-show="showPlansModal" x-cloak x-transition close-action="@click='showPlansModal = false'" />
    </div>

    @include('resumes.partials.scripts', [
        'isEdit' => false,
        'nameLocked' => false,
        'nameChangesLeft' => null,
        'currentLang' => $currentLang,
    ])
</x-app-layout>