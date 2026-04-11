<x-app-layout>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    @include('resumes.partials.styles')

    @php
        // بيانات فارغة للإنشاء
        $initialData = [
            'full_name' => old('full_name', ''),
            'job_title' => old('job_title', ''),
            'email' => old('email', ''),
            'phone' => old('phone', ''),
            'address' => old('address', ''),
            'summary' => old('summary', ''),
            'skills' => '',
            'educations' => [['id' => time(), 'institution' => '', 'degree' => '', 'field_of_study' => '', 'graduation_year' => '']],
            'experiences' => [['id' => time(), 'company' => '', 'position' => '', 'start_date' => '', 'end_date' => '', 'is_current' => false, 'description' => '']],
            'languages' => [['id' => time(), 'name' => '', 'proficiency' => 'متوسط']],
            'extra_sections' => [],
            'existingPhoto' => '',
        ];
    @endphp

    <div class="page-content py-10" dir="rtl" x-data="resumeForm({{ json_encode($initialData) }})" x-cloak>
        <div class="max-w-[1400px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                
                @include('resumes.partials.steps-sidebar', ['title' => 'بناء السيرة'])

                <main class="w-full lg:w-3/4">
                    <form action="{{ route('resume.store') }}" method="POST" enctype="multipart/form-data" id="mainResumeForm">
                        @csrf
                        <input type="hidden" name="extra_sections" :value="JSON.stringify(extra_sections)">

                        @include('resumes.partials.step-personal', ['isEdit' => false])
                        @include('resumes.partials.step-education')
                        @include('resumes.partials.step-experience')
                        @include('resumes.partials.step-skills-summary')
                        @include('resumes.partials.step-languages')
                        @include('resumes.partials.step-final')

                        @include('resumes.partials.navigation-buttons', ['submitText' => 'حفظ ونشر السيرة ✅'])
                    </form>
                </main>
            </div>
        </div>

        @include('resumes.partials.cropper-modal')
        <x-plans-modal x-show="showPlansModal" x-cloak x-transition close-action="@click='showPlansModal = false'" />
    </div>

    @include('resumes.partials.scripts', [
        'isEdit' => false,
        'nameLocked' => false,
        'nameChangesLeft' => null,
    ])
</x-app-layout>