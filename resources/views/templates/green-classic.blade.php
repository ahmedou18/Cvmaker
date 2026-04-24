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
    <title>{{ $profile->full_name ?? __('messages.full_name', [], $resumeLanguage) }} - CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        theme: '#1b7a5a',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: white; }
        .modal-active { overflow: hidden; }
        .whitespace-pre-line { white-space: pre-line; }

        /* تحسينات الطباعة */
        @media print {
            @page { margin: 0; size: A4 portrait; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print, .no-print * { display: none !important; }
            body { background-color: white !important; margin: 0 !important; padding: 0 !important; }
            .print-container { padding: 2rem !important; max-width: 100% !important; }
            .bg-theme { background-color: #1b7a5a !important; color: white !important; }
            .text-theme { color: #1b7a5a !important; }
        }

        /* تحسينات للهواتف */
        @media (max-width: 640px) {
            .print-container { padding: 1rem !important; }
            .header-title { font-size: 1.8rem !important; }
            .action-buttons { flex-direction: column; align-items: stretch; gap: 0.75rem; }
            .action-buttons a, .action-buttons button { width: 100%; justify-content: center; }
            .contact-grid { grid-template-columns: 1fr !important; gap: 0.75rem !important; }
            .info-row { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
            .section-content { padding: 1rem !important; }
            .exp-item { flex-direction: column; gap: 0.5rem; }
            .exp-date { width: 100% !important; text-align: left !important; }
        }
    </style>
</head>
<body class="print-container p-6 sm:p-8 max-w-[850px] mx-auto text-gray-900 relative bg-white">

    {{-- شريط الإجراءات العلوي (للطباعة والتحكم) --}}
    <div class="no-print bg-white shadow-sm border-b mb-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex flex-col sm:flex-row justify-between items-center gap-3">
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

    {{-- الإسم --}}
    <header class="mb-8 text-center break-inside-avoid">
        <h1 class="header-title text-[1.8rem] sm:text-[2.2rem] text-gray-900 font-bold uppercase">
            {{ $profile->full_name ?? __('messages.full_name', [], $resumeLanguage) }}
        </h1>
    </header>

    {{-- المعلومات الشخصية --}}
    <section class="mb-6 break-inside-avoid">
        <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium">{{ __('messages.personal_info', [], $resumeLanguage) }}</h2>
        <div class="border border-gray-400 border-t-0 p-4 flex flex-wrap sm:flex-nowrap justify-between gap-4">
            <div class="contact-grid flex-1 grid grid-cols-[120px_1fr] sm:grid-cols-[150px_1fr] gap-y-3 text-[13px]">
                @if($profile->email)
                    <div class="text-gray-800">{{ __('messages.email_label', [], $resumeLanguage) ?? 'البريد الإلكتروني' }}</div>
                    <div dir="ltr" class="text-left text-gray-900">{{ $profile->email }}</div>
                @endif
                @if($profile->phone)
                    <div class="text-gray-800">{{ __('messages.phone_label', [], $resumeLanguage) ?? 'رقم الهاتف' }}</div>
                    <div dir="ltr" class="text-left text-gray-900">{{ $profile->phone }}</div>
                @endif
                @if($profile->address)
                    <div class="text-gray-800">{{ __('messages.address_label', [], $resumeLanguage) ?? 'العنوان' }}</div>
                    <div class="text-gray-900">{{ $profile->address }}</div>
                @endif
            </div>
            @if($profile && $profile->photo_path)
            <div class="flex-shrink-0">
                <img src="{{ asset($profile->photo_path) }}" alt="{{ __('messages.profile_photo', [], $resumeLanguage) }}" class="w-[90px] h-[110px] object-cover">
            </div>
            @endif
        </div>
    </section>

    {{-- الملخص المهني --}}
    @if($profile && $profile->summary)
    <section class="mb-6 break-inside-avoid">
        <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
        <div class="border border-gray-400 border-t-0 p-4">
            <p class="text-gray-900 leading-relaxed text-[13px] text-justify whitespace-pre-line">{{ $profile->summary }}</p>
        </div>
    </section>
    @endif

    {{-- الخبرات العملية --}}
    @if($resume->experiences->count() > 0)
    <section class="mb-6">
        <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium break-inside-avoid">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
        <div class="border border-gray-400 border-t-0 p-4 space-y-5">
            @foreach($resume->experiences as $exp)
            <div class="exp-item flex flex-col sm:flex-row gap-4 text-[13px] break-inside-avoid">
                <div class="exp-date sm:w-[150px] flex-shrink-0 text-gray-900">
                    @if($exp->start_date)
                        <span dir="ltr">
                            {{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') }} 
                            @if($exp->end_date)
                                - {{ \Carbon\Carbon::parse($exp->end_date)->format('M Y') }}
                            @elseif($exp->is_current)
                                - {{ __('messages.present', [], $resumeLanguage) }}
                            @endif
                        </span>
                    @endif
                </div>
                <div class="sm:w-full">
                    <h3 class="font-bold text-gray-900">{{ $exp->position }}</h3>
                    <p class="text-theme mb-2">{{ $exp->company }}</p>
                    @if($exp->description)
                        <div class="text-gray-900 leading-relaxed whitespace-pre-line">{!! nl2br(e($exp->description)) !!}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- المؤهلات الدراسية --}}
    @if($resume->educations->count() > 0)
    <section class="mb-6">
        <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium break-inside-avoid">{{ __('messages.education', [], $resumeLanguage) }}</h2>
        <div class="border border-gray-400 border-t-0 p-4 space-y-4">
            @foreach($resume->educations as $edu)
            <div class="flex flex-col sm:flex-row gap-4 text-[13px] break-inside-avoid">
                <div class="sm:w-[150px] flex-shrink-0 text-gray-900">
                    @if($edu->graduation_year)
                        <span dir="ltr">{{ $edu->graduation_year }}</span>
                    @endif
                </div>
                <div class="sm:w-full">
                    <span class="text-gray-900 font-bold">{{ $edu->degree }} @if($edu->field_of_study) - {{ $edu->field_of_study }} @endif</span>
                    <p class="text-theme">{{ $edu->institution }}</p>
                </div>
            </div>
            @endforeach
        </div>
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
                <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium">{{ $section['title'] }}</h2>
                <div class="border border-gray-400 border-t-0 p-4">
                    <div class="text-[13px] text-gray-900 leading-relaxed whitespace-pre-line">{!! nl2br(e($section['content'])) !!}</div>
                </div>
            </section>
            @endif
        @endforeach
    @endif

    {{-- المهارات --}}
    @if($resume->skills->count() > 0)
    <section class="mb-6 break-inside-avoid">
        <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
        <div class="border border-gray-400 border-t-0 p-4">
            <ul class="flex flex-col gap-2">
                @foreach($resume->skills as $skill)
                    <li class="flex items-center gap-3 text-[13px] text-gray-900">
                        <span class="w-[8px] h-[8px] bg-theme block"></span>
                        <span>{{ $skill->name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    @endif

    {{-- اللغات --}}
    @if($resume->languages->count() > 0)
    <section class="mb-6 break-inside-avoid">
        <h2 class="bg-theme text-white px-4 py-1.5 text-lg font-medium">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
        <div class="border border-gray-400 border-t-0 p-4">
            <ul class="flex flex-col gap-2">
                @foreach($resume->languages as $lang)
                    <li class="flex items-center gap-3 text-[13px] text-gray-900">
                        <span class="w-[8px] h-[8px] bg-theme block"></span>
                        <span><strong>{{ $lang->name }}</strong> - {{ $lang->proficiency }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
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