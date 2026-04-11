@php 
    $profile = $resume->personalDetail; 
@endphp
<!DOCTYPE html>
<html lang="{{ $resume->resume_language }}" dir="{{ in_array($resume->resume_language, ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $profile->full_name ?? 'سيرة ذاتية' }} - Modern CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; }
        /* ستايل لضمان عدم تمرير الصفحة عند فتح النافذة المنبثقة */
        .modal-active { overflow: hidden; }
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
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }

            /* فرض لون الشريط الجانبي الداكن عند الطباعة */
            .bg-gray-900 { background-color: #111827 !important; color: white !important; }
            .bg-gray-800 { background-color: #1f2937 !important; color: white !important; }
            .bg-gray-100 { background-color: #f3f4f6 !important; }
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col items-center text-gray-800 pb-10">
    
    <div class="no-print w-full bg-white shadow-sm border-b mb-10" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
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

    {{-- تمت إضافة print-container لحاوية السيرة الذاتية --}}
    <div class="print-container w-full max-w-4xl flex min-h-[1056px] shadow-lg border border-gray-200 bg-white">
        
        {{-- الشريط الجانبي الداكن --}}
        <aside class="w-[35%] bg-gray-900 text-white p-8 border-e border-gray-800">
            <div class="mb-8 text-center break-inside-avoid">
                @if($profile && $profile->photo_path)
                    <div class="w-32 h-32 mx-auto mb-4 border-4 border-gray-700 rounded-full overflow-hidden bg-gray-800">
                        <img src="{{ asset($profile->photo_path) }}" alt="Photo" class="w-full h-full object-cover">
                    </div>
                @endif
                <h1 class="text-2xl font-bold mb-1">{{ $profile->full_name ?? __('messages.full_name', [], $resume->resume_language) }}</h1>
                <p class="text-gray-400 text-sm font-semibold">{{ $profile->job_title ?? __('messages.job_title', [], $resume->resume_language) }}</p>
            </div>

            <div class="mb-6 text-sm space-y-3 border-t border-gray-700 pt-6 break-inside-avoid">
                <h2 class="text-lg font-bold tracking-wider uppercase mb-3 text-gray-300">{{ __('messages.contact', [], $resume->resume_language) ?? 'Contact' }}</h2>
                @if($profile->email) <p dir="ltr" class="text-gray-300">{{ $profile->email }} ✉️</p> @endif
                @if($profile->phone) <p dir="ltr" class="text-gray-300">{{ $profile->phone }} 📞</p> @endif
                @if($profile->address) <p class="text-gray-300">📍 {{ $profile->address }}</p> @endif
            </div>

            @if($resume->skills->count() > 0)
            <div class="mb-6 text-sm border-t border-gray-700 pt-6 break-inside-avoid">
                <h2 class="text-lg font-bold tracking-wider uppercase mb-3 text-gray-300">{{ __('messages.skills', [], $resume->resume_language) ?? 'Skills' }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($resume->skills as $skill)
                        <span class="bg-gray-800 px-3 py-1 rounded text-gray-200">{{ $skill->name }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($resume->languages->count() > 0)
            <div class="text-sm border-t border-gray-700 pt-6 break-inside-avoid">
                <h2 class="text-lg font-bold tracking-wider uppercase mb-3 text-gray-300">{{ __('messages.languages', [], $resume->resume_language) ?? 'Languages' }}</h2>
                <ul class="space-y-2">
                    @foreach($resume->languages as $lang)
                        <li class="flex justify-between text-gray-300">
                            <span>{{ $lang->name }}</span>
                            <span class="text-gray-500">{{ $lang->proficiency }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </aside>

        {{-- المحتوى الرئيسي الفاتح --}}
        <main class="w-[65%] p-10 bg-white">
            
            {{-- الملخص المهني --}}
            @if($profile && $profile->summary)
            <section class="mb-8 break-inside-avoid">
                <h2 class="text-2xl font-bold text-gray-900 border-b-2 border-gray-200 pb-2 mb-4">{{ __('messages.summary', [], $resume->resume_language) ?? 'Profile' }}</h2>
                <p class="text-[13px] leading-relaxed text-gray-700 text-justify whitespace-pre-line">
                    {!! nl2br(e($profile->summary)) !!}
                </p>
            </section>
            @endif

            {{-- الخبرات المهنية --}}
            @if($resume->experiences->count() > 0)
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 border-b-2 border-gray-200 pb-2 mb-4">{{ __('messages.experience', [], $resume->resume_language) ?? 'Experience' }}</h2>
                <div class="flex flex-col gap-6">
                    @foreach($resume->experiences as $exp)
                    <div class="break-inside-avoid">
                        <div class="flex justify-between items-baseline mb-1">
                            <h3 class="font-bold text-lg text-gray-900">{{ $exp->position }}</h3>
                            @if($exp->start_date)
                                <span class="text-sm font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded" dir="ltr">
                                    {{ \Carbon\Carbon::parse($exp->start_date)->format('Y/m') }}
                                    @if($exp->end_date)
                                        - {{ \Carbon\Carbon::parse($exp->end_date)->format('Y/m') }}
                                    @elseif($exp->is_current)
                                        - {{ __('messages.present', [], $resume->resume_language) }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-blue-600 mb-2">{{ $exp->company }}</p>
                        @if($exp->description)
                            <div class="text-[13px] text-gray-700 leading-relaxed whitespace-pre-line ml-2">{!! nl2br(e($exp->description)) !!}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- التعليم --}}
            @if($resume->educations->count() > 0)
            <section class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 border-b-2 border-gray-200 pb-2 mb-4">{{ __('messages.education', [], $resume->resume_language) ?? 'Education' }}</h2>
                <div class="flex flex-col gap-4">
                    @foreach($resume->educations as $edu)
                    <div class="flex justify-between items-start break-inside-avoid">
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $edu->degree }} - {{ $edu->field_of_study }}</h3>
                            <p class="text-sm text-gray-600">{{ $edu->institution }}</p>
                        </div>
                        @if($edu->graduation_year)
                            <span class="text-sm font-bold text-gray-500" dir="ltr">{{ $edu->graduation_year }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- ✨ قسم ديناميكي للأقسام الإضافية مدمج في الجزء الأبيض ✨ --}}
            @php
                $extraSections = is_string($resume->extra_sections) 
                                 ? json_decode($resume->extra_sections, true) 
                                 : $resume->extra_sections;
            @endphp

            @if(!empty($extraSections) && is_array($extraSections))
                @foreach($extraSections as $section)
                    @if(!empty($section['title']) && !empty($section['content']))
                    <section class="mb-8 break-inside-avoid">
                        <h2 class="text-2xl font-bold text-gray-900 border-b-2 border-gray-200 pb-2 mb-4">
                            {{ $section['title'] }}
                        </h2>
                        <div class="text-[13px] text-gray-700 leading-relaxed whitespace-pre-line ml-2">
                            {!! nl2br(e($section['content'])) !!}
                        </div>
                    </section>
                    @endif
                @endforeach
            @endif

        </main>
    </div>

    {{-- استدعاء مودال الباقات الموحد متوافق مع Vanilla Javascript --}}
    <x-plans-modal id="plansModal" class="hidden" close-action="onclick='closeModal()'" :resume-uuid="$resume->uuid" />

    <script>
        function openModal() {
            document.getElementById('plansModal').classList.remove('hidden');
            document.body.classList.add('modal-active');
        }

        function closeModal() {
            document.getElementById('plansModal').classList.add('hidden');
            document.body.classList.remove('modal-active');
        }

        // إغلاق النافذة عند الضغط على زر ESC في لوحة المفاتيح
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>

</body>
</html>