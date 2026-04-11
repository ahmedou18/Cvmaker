<!DOCTYPE html>
<html lang="{{ $resume->resume_language }}" dir="{{ in_array($resume->resume_language, ['ar']) ? 'rtl' : 'ltr' }}">

@php $profile = $resume->personalDetail; @endphp
<head>
    <meta charset="UTF-8">
    <title>{{ $profile->full_name ?? 'سيرة ذاتية' }} - Classic CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: white; }
        .modal-active { overflow: hidden; }
        /* تحسين عرض النصوص الطويلة */
        .whitespace-pre-line { white-space: pre-line; }

        /* ====== إعدادات الطباعة الاحترافية ====== */
        @media print {
            @page {
                margin: 0; 
                size: A4 portrait;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print, .no-print * {
                display: none !important;
            }

            body {
                background-color: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .print-container {
                padding: 2.5rem !important; 
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="print-container p-10 max-w-4xl mx-auto text-gray-800 relative bg-white">

    {{-- شريط الإجراءات العلوي (للطباعة والتحكم) --}}
    <div class="no-print bg-white shadow-sm border-b mb-10">
        <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center flex-wrap gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-blue-600 flex items-center transition">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                العودة للوحة التحكم
            </a>
            <div class="flex gap-2">
                <a href="{{ route('resume.edit', $resume->uuid) }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 flex items-center transition rounded">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    تعديل البيانات
                </a>
                @if(auth()->check() && auth()->user()->hasActivePlan())
    {{-- إذا كان المستخدم مشتركاً، افتح نافذة الطباعة مباشرة --}}
    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition cursor-pointer">
        تحميل كـ PDF
    </button>
@else
    {{-- إذا لم يكن مشتركاً، افتح المودال الخاص بالباقات --}}
    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition cursor-pointer">
        تحميل كـ PDF
    </button>
@endif
            </div>
        </div>
    </div>

    @php $profile = $resume->personalDetail; @endphp

    {{-- الهيدر مع الصورة الشخصية --}}
    <header class="border-b-2 border-gray-800 pb-4 mb-6 text-center break-inside-avoid">
        @if($profile && $profile->photo_path)
            <img src="{{ asset($profile->photo_path) }}" alt="صورة شخصية"
                 class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-2 border-gray-300 bg-gray-100">
        @endif
        <h1 class="text-4xl font-bold uppercase">{{ $profile->full_name ?? __('messages.full_name', [], $resume->resume_language) }}</h1>
        <p class="text-xl text-gray-600 mt-2">{{ $profile->job_title ?? __('messages.job_title', [], $resume->resume_language) }}</p>
        <div class="flex flex-wrap justify-center gap-4 mt-3 text-sm text-gray-500 font-medium">
            @if($profile->email) <span dir="ltr">{{ $profile->email }}</span> @endif
            @if($profile->phone) <span class="hidden sm:inline">|</span> <span dir="ltr">{{ $profile->phone }}</span> @endif
            @if($profile->address) <span class="hidden sm:inline">|</span> <span>{{ $profile->address }}</span> @endif
        </div>
    </header>

    {{-- الملخص المهني --}}
    @if($profile && $profile->summary)
    <section class="mb-6 break-inside-avoid">
        <h2 class="text-xl font-bold border-b border-gray-300 mb-3 pb-1">{{ __('messages.summary', [], $resume->resume_language) }}</h2>
        <p class="text-gray-700 leading-relaxed text-sm text-justify whitespace-pre-line">
            {{ $profile->summary }}
        </p>
    </section>
    @endif

    {{-- الخبرات العملية --}}
    @if($resume->experiences->count() > 0)
    <section class="mb-6">
        <h2 class="text-xl font-bold border-b border-gray-300 mb-3 pb-1 break-inside-avoid">{{ __('messages.experience', [], $resume->resume_language) }}</h2>
        <div class="space-y-4">
            @foreach($resume->experiences as $exp)
            <div class="break-inside-avoid">
                <div class="flex justify-between font-bold text-gray-900 flex-wrap gap-2 mb-1">
                    <h3>{{ $exp->position }}</h3>
                    @if($exp->start_date)
                        <span class="text-sm text-gray-600 bg-gray-100 px-2 rounded" dir="ltr">
                            {{ \Carbon\Carbon::parse($exp->start_date)->format('Y/m') }}
                            @if($exp->end_date)
                                - {{ \Carbon\Carbon::parse($exp->end_date)->format('Y/m') }}
                            @elseif($exp->is_current)
                                - {{ __('messages.present', [], $resume->resume_language) }}
                            @endif
                        </span>
                    @endif
                </div>
                <p class="text-blue-600 font-semibold text-sm mb-1">{{ $exp->company }}</p>
                @if($exp->description)
                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{!! nl2br(e($exp->description)) !!}</div>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- المؤهلات الدراسية --}}
    @if($resume->educations->count() > 0)
    <section class="mb-6">
        <h2 class="text-xl font-bold border-b border-gray-300 mb-3 pb-1 break-inside-avoid">{{ __('messages.education', [], $resume->resume_language) }}</h2>
        <div class="space-y-4">
            @foreach($resume->educations as $edu)
            <div class="flex justify-between text-sm flex-wrap gap-2 break-inside-avoid">
                <div>
                    <span class="font-bold text-gray-900">{{ $edu->degree }} - {{ $edu->field_of_study }}</span>
                    <p class="text-gray-600 mt-1">{{ $edu->institution }}</p>
                </div>
                @if($edu->graduation_year)
                    <span class="text-sm font-bold text-gray-500" dir="ltr">{{ $edu->graduation_year }}</span>
                @endif
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- الأقسام الإضافية (ديناميكية) --}}
    @php
        // تحويل النص إلى مصفوفة في حال لم يتم تعريفه في الـ Casts داخل الموديل
        $extraSections = is_string($resume->extra_sections) 
                         ? json_decode($resume->extra_sections, true) 
                         : $resume->extra_sections;
    @endphp

    @if(!empty($extraSections) && is_array($extraSections))
        @foreach($extraSections as $section)
            @if(!empty($section['title']) && !empty($section['content']))
            <section class="mb-6 break-inside-avoid">
                <h2 class="text-xl font-bold border-b border-gray-300 mb-3 pb-1">
                    {{ $section['title'] }}
                </h2>
                <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                    {!! nl2br(e($section['content'])) !!}
                </div>
            </section>
            @endif
        @endforeach
    @endif

    {{-- المهارات واللغات جنباً إلى جنب --}}
    <div class="flex flex-wrap gap-10 break-inside-avoid">
        @if($resume->skills->count() > 0)
        <section class="flex-1 min-w-[200px]">
            <h2 class="text-xl font-bold border-b border-gray-300 mb-3 pb-1">{{ __('messages.skills', [], $resume->resume_language) }}</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($resume->skills as $skill)
                    <span class="bg-gray-100 border border-gray-200 text-gray-800 text-xs px-2 py-1 rounded">{{ $skill->name }}</span>
                @endforeach
            </div>
        </section>
        @endif

        @if($resume->languages->count() > 0)
        <section class="flex-1 min-w-[200px]">
            <h2 class="text-xl font-bold border-b border-gray-300 mb-3 pb-1">{{ __('messages.languages', [], $resume->resume_language) }}</h2>
            <ul class="text-sm text-gray-700 space-y-2">
                @foreach($resume->languages as $lang)
                    <li class="flex justify-between border-b border-gray-100 pb-1">
                        <strong>{{ $lang->name }}</strong> 
                        <span class="text-gray-500">{{ $lang->proficiency }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
        @endif
    </div>

    {{-- مودال الباقات --}}
    <x-plans-modal id="plansModal" class="hidden" close-action="onclick='closeModal()'" :resume-uuid="$resume->uuid" />

    <script>
        function openModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('modal-active');
            }
        }

        function closeModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('modal-active');
            }
        }

        // إغلاق المودال عند الضغط على ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>