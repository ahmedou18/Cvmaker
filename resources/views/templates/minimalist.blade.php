@php 
    $profile = $resume->personalDetail; 
    $user = auth()->user();
    $canDownload = $user && $user->plan && $user->plan->price > 0;
    $resumeLanguage = $resume->resume_language;
@endphp
<!DOCTYPE html>
<html lang="{{ $resumeLanguage }}" dir="{{ in_array($resumeLanguage, ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $profile->full_name ?? __('messages.full_name', [], $resumeLanguage) }} - Minimalist CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Century Gothic', 'Cairo', sans-serif; background-color: white; color: #000; }
        .modal-active { overflow: hidden; }
        .whitespace-pre-line { white-space: pre-line; }
        .bullet-list { list-style-type: disc; padding-inline-start: 1.5rem; }
        .bullet-list li { margin-bottom: 0.25rem; }

        /* تحسينات الطباعة */
        @media print {
            @page { margin: 0; size: A4 portrait; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print, .no-print * { display: none !important; }
            body { background-color: white !important; margin: 0 !important; padding: 0 !important; }
            .print-container { padding: 2rem !important; max-width: 100% !important; }
        }

        /* تحسينات للهواتف */
        @media (max-width: 640px) {
            .print-container { padding: 1.5rem !important; }
            .header-title { font-size: 1.8rem !important; }
            .job-subtitle { font-size: 1rem !important; }
            .contact-info { font-size: 10px !important; gap: 0.5rem !important; }
            .section-title { font-size: 1.25rem !important; }
            .action-buttons { flex-direction: column; align-items: stretch; gap: 0.75rem; }
            .action-buttons a, .action-buttons button { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body class="print-container p-6 sm:p-10 max-w-4xl mx-auto relative bg-white">

    {{-- شريط الإجراءات العلوي (للطباعة والتحكم) --}}
    <div class="no-print bg-gray-50 shadow-sm border-b mb-8 -mx-6 sm:-mx-10 px-6 sm:px-10 py-4">
        <div class="max-w-5xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-blue-600 flex items-center transition">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}
            </a>
            <div class="action-buttons flex gap-2">
                <a href="{{ route('resume.edit', $resume->uuid) }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 flex items-center justify-center transition rounded-md">
                    {{ __('messages.edit_data', [], $resumeLanguage) }}
                </a>
                @if($canDownload)
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md transition cursor-pointer">
                        {{ __('messages.download_pdf', [], $resumeLanguage) }}
                    </button>
                @else
                    <button onclick="openModal()" class="bg-gray-400 hover:bg-gray-500 text-white font-bold py-2 px-4 rounded-md transition cursor-pointer">
                        {{ __('messages.upgrade_to_download', [], $resumeLanguage) ?? 'رقّي باقتك لتحميل السيرة' }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- الترويسة العلوية --}}
    <header class="mb-8 border-b-2 border-black pb-4 text-center break-inside-avoid">
        <h1 class="header-title text-3xl sm:text-4xl font-bold uppercase tracking-widest mb-1">
            {{ $profile->full_name ?? __('messages.full_name', [], $resumeLanguage) }}
        </h1>
        <p class="job-subtitle text-base sm:text-lg uppercase tracking-widest text-gray-600 mb-4">
            {{ $profile->job_title ?? __('messages.job_title', [], $resumeLanguage) }}
        </p>
        <div class="contact-info text-[11px] sm:text-[12px] flex flex-wrap justify-center gap-x-4 gap-y-1 text-gray-700">
            @if($profile->phone)
                <span dir="ltr">{{ $profile->phone }}</span>
            @endif
            @if($profile->email)
                <span>•</span> <span dir="ltr">{{ $profile->email }}</span>
            @endif
            @if($profile->address)
                <span>•</span> <span>{{ $profile->address }}</span>
            @endif
        </div>
    </header>

    {{-- الملف الشخصي --}}
    @if($profile && $profile->summary)
    <section class="mb-6 break-inside-avoid">
        <h2 class="section-title text-xl font-bold mb-2">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
        <p class="text-[13px] leading-relaxed text-justify whitespace-pre-line">{!! nl2br(e($profile->summary)) !!}</p>
    </section>
    @endif

    {{-- المهارات --}}
    @if($resume->skills->count() > 0)
    <section class="mb-6 break-inside-avoid">
        <h2 class="section-title text-xl font-bold mb-2">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
        <p class="text-[13px] leading-relaxed">
            {{ $resume->skills->pluck('name')->implode(' • ') }}
        </p>
    </section>
    @endif

    {{-- التعليم --}}
    @if($resume->educations->count() > 0)
    <section class="mb-6">
        <h2 class="section-title text-xl font-bold mb-3">{{ __('messages.education', [], $resumeLanguage) }}</h2>
        <div class="flex flex-col gap-4">
            @foreach($resume->educations as $edu)
            <div class="break-inside-avoid">
                <div class="flex flex-col sm:flex-row justify-between items-start">
                    <h3 class="font-bold text-[14px] uppercase">{{ $edu->degree }} - {{ $edu->institution }}</h3>
                    @if($edu->graduation_year)
                        <span class="text-[13px] font-bold min-w-[80px] text-end" dir="ltr">{{ $edu->graduation_year }}</span>
                    @endif
                </div>
                @if($edu->field_of_study)
                    <p class="text-[13px] text-gray-700 italic">{{ $edu->field_of_study }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- الخبرات المهنية --}}
    @if($resume->experiences->count() > 0)
    <section class="mb-6">
        <h2 class="section-title text-xl font-bold mb-3">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
        <div class="flex flex-col gap-5">
            @foreach($resume->experiences as $exp)
            <div class="break-inside-avoid">
                <div class="flex flex-col sm:flex-row justify-between items-start mb-1">
                    <h3 class="font-bold text-[14px] uppercase">{{ $exp->position }}</h3>
                    @if($exp->start_date)
                        <span class="text-[13px] font-bold min-w-[150px] text-end" dir="ltr">
                            {{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') }} – 
                            @if($exp->end_date)
                                {{ \Carbon\Carbon::parse($exp->end_date)->format('M Y') }}
                            @elseif($exp->is_current)
                                {{ __('messages.present', [], $resumeLanguage) }}
                            @endif
                        </span>
                    @endif
                </div>
                <h4 class="text-[13px] font-semibold text-gray-700 mb-2">{{ $exp->company }}</h4>
                @if($exp->description)
                    <div class="text-[13px] leading-relaxed whitespace-pre-line ml-0 sm:ml-4">{!! nl2br(e($exp->description)) !!}</div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- اللغات --}}
    @if($resume->languages->count() > 0)
    <section class="mb-6 break-inside-avoid">
        <h2 class="section-title text-xl font-bold mb-2">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
        <ul class="bullet-list text-[13px]">
            @foreach($resume->languages as $lang)
                <li><strong>{{ $lang->name }}</strong> - {{ $lang->proficiency }}</li>
            @endforeach
        </ul>
    </section>
    @endif

    {{-- الأقسام الإضافية --}}
    @php
        $extraSections = is_string($resume->extra_sections) ? json_decode($resume->extra_sections, true) : $resume->extra_sections;
    @endphp
    @if(!empty($extraSections) && is_array($extraSections))
        @foreach($extraSections as $section)
            @if(!empty($section['title']) && !empty($section['content']))
            <section class="mb-6 break-inside-avoid">
                <h2 class="section-title text-xl font-bold mb-2">{{ $section['title'] }}</h2>
                <div class="text-[13px] leading-relaxed whitespace-pre-line ml-0 sm:ml-4">
                    {!! nl2br(e($section['content'])) !!}
                </div>
            </section>
            @endif
        @endforeach
    @endif

    {{-- مودال الباقات --}}
    <x-plans-modal id="plansModal" class="hidden" closeAction="closeModal()" :resume-uuid="$resume->uuid" :currentLang="$resumeLanguage" />

    <script>
        function openModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
                document.body.classList.add('modal-active');
            }
        }

        function closeModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
                document.body.classList.remove('modal-active');
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>