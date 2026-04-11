@php 
    $profile = $resume->personalDetail; 
@endphp
<!DOCTYPE html>
<html lang="{{ $resume->resume_language }}" dir="{{ in_array($resume->resume_language, ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $profile->full_name ?? 'سيرة ذاتية' }} - Modern Split CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-teal': '#8cb7bd',  /* اللون الأزرق/الأخضر الفاتح */
                        'brand-dark': '#5e6267',  /* اللون الرمادي الغامق للترويسة */
                        'brand-bg': '#e6e7e9',    /* لون الخلفية العام */
                        'brand-text': '#333333',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #525659; } /* خلفية داكنة خارج الورقة */
        .page-container { background-color: #e6e7e9; } /* لون ورقة السيرة الذاتية */
        .modal-active { overflow: hidden; }
        .whitespace-pre-line { white-space: pre-line; }
        
        /* تنسيق العناوين للقسمين */
        .section-title {
            color: #4a4a4a;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            border-bottom: 3px solid #8cb7bd;
            padding-bottom: 0.5rem;
            margin-bottom: 1.25rem;
            letter-spacing: 1px;
        }
        
        /* مربعات القوائم */
        .square-bullet {
            display: inline-block;
            width: 6px;
            height: 6px;
            background-color: #4a4a4a;
            margin-inline-end: 8px;
            vertical-align: middle;
        }

        /* ====== إعدادات الطباعة الاحترافية ====== */
        @media print {
            @page {
                margin: 0; 
                size: auto; /* السماح للمتصفح بتحديد الحجم لتجنب القص العرضي */
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
            
            .page-container {
                box-shadow: none !important;
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
                /* التخلص من الحواف الدائرية إن وجدت في الطباعة */
                border-radius: 0 !important; 
            }

            /* تثبيت الألوان الأساسية أثناء الطباعة في بعض المتصفحات العنيدة */
            .bg-brand-teal { background-color: #8cb7bd !important; }
            .bg-brand-dark { background-color: #5e6267 !important; }
        }
    </style>
</head>
<body class="p-4 md:p-10 text-gray-800 relative">

    {{-- شريط الإجراءات العلوي (للطباعة والتحكم) --}}
    <div class="no-print bg-white shadow-sm border-b mb-8 max-w-[850px] mx-auto rounded-md">
        <div class="px-6 py-4 flex justify-between items-center flex-wrap gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-blue-600 flex items-center transition">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                العودة للوحة التحكم
            </a>
            <div class="flex gap-2">
                <a href="{{ route('resume.edit', $resume->uuid) }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 flex items-center transition rounded">
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

    {{-- ورقة السيرة الذاتية --}}
    <div class="page-container max-w-[850px] mx-auto shadow-2xl relative overflow-hidden flex flex-col text-[13px]">
        
        {{-- الترويسة العلوية --}}
        {{-- أضفنا break-inside-avoid لضمان عدم انفصال الترويسة العلوية في الطباعة --}}
        <header class="relative flex h-[180px] w-full mt-10 break-inside-avoid">
            @if($profile && $profile->photo_path)
                <div class="absolute -top-12 left-1/2 transform -translate-x-1/2 z-10">
                    <img src="{{ asset($profile->photo_path) }}" alt="صورة شخصية"
                         class="w-36 h-36 rounded-full object-cover border-[6px] border-[#e6e7e9] shadow-sm bg-[#e6e7e9]">
                </div>
            @endif

            <div class="w-1/2 bg-brand-teal text-white flex flex-col justify-end p-6 pb-8">
                @if($profile->phone)
                    <div class="flex items-center gap-3 mb-2 justify-start">
                        <i class="fas fa-phone"></i>
                        <span dir="ltr">{{ $profile->phone }}</span>
                    </div>
                @endif
                @if($profile->email)
                    <div class="flex items-center gap-3 justify-start">
                        <i class="fas fa-envelope"></i>
                        <span dir="ltr">{{ $profile->email }}</span>
                    </div>
                @endif
            </div>

            <div class="w-1/2 bg-brand-dark text-white flex flex-col justify-end p-6 pb-8">
                @if($profile->address)
                    <div class="flex items-center gap-3 mb-2 justify-end">
                        <span>{{ $profile->address }}</span>
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                @endif
                <div class="flex items-center gap-3 justify-end">
                    <span>/LinkedIn</span>
                    <i class="fab fa-linkedin-in"></i>
                </div>
            </div>

            <div class="absolute bottom-[-20px] left-1/2 transform -translate-x-1/2 w-full text-center z-10">
                <h1 class="text-3xl font-bold text-white uppercase tracking-wider drop-shadow-md">
                    {{ $profile->full_name ?? __('messages.full_name', [], $resume->resume_language) }}
                </h1>
                <p class="text-[15px] font-semibold text-white mt-1 drop-shadow-md">
                    {{ $profile->job_title ?? __('messages.job_title', [], $resume->resume_language) }}
                </p>
            </div>
        </header>

        {{-- محتوى السيرة الذاتية (عمودين) --}}
        <div class="flex flex-col md:flex-row p-8 gap-8 mt-4">
            
            {{-- العمود الجانبي --}}
            <div class="w-full md:w-[38%] flex flex-col gap-8">
                
                {{-- الملف الشخصي --}}
                @if($profile && $profile->summary)
                <section class="break-inside-avoid">
                    <h2 class="section-title text-lg">{{ __('messages.summary', [], $resume->resume_language) ?? 'Profil' }}</h2>
                    <p class="text-gray-700 leading-relaxed text-justify whitespace-pre-line">{!! nl2br(e($profile->summary)) !!}</p>
                </section>
                @endif

                {{-- المهارات --}}
                @if($resume->skills->count() > 0)
                <section class="break-inside-avoid">
                    <h2 class="section-title text-lg">{{ __('messages.skills', [], $resume->resume_language) ?? 'Compétences' }}</h2>
                    <div class="grid grid-cols-2 gap-y-3 gap-x-2 text-gray-700">
                        @foreach($resume->skills as $skill)
                            <div class="flex items-center break-inside-avoid">
                                <span class="square-bullet"></span>
                                <span>{{ $skill->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- اللغات --}}
                @if($resume->languages->count() > 0)
                <section class="break-inside-avoid">
                    <h2 class="section-title text-lg">{{ __('messages.languages', [], $resume->resume_language) ?? 'Langues' }}</h2>
                    <div class="flex flex-col gap-3 text-gray-700">
                        @foreach($resume->languages as $lang)
                            <div class="flex items-center break-inside-avoid">
                                <span class="square-bullet"></span>
                                <span><strong>{{ $lang->name }}</strong> : {{ $lang->proficiency }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- الأقسام الإضافية (ديناميكية معالجة JSON) --}}
                @php
                    $extraSections = is_string($resume->extra_sections) 
                                     ? json_decode($resume->extra_sections, true) 
                                     : $resume->extra_sections;
                @endphp

                @if(!empty($extraSections) && is_array($extraSections))
                    @foreach($extraSections as $section)
                        @if(!empty($section['title']) && !empty($section['content']))
                        <section class="break-inside-avoid">
                            <h2 class="section-title text-lg">{{ $section['title'] }}</h2>
                            <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                                {!! nl2br(e($section['content'])) !!}
                            </div>
                        </section>
                        @endif
                    @endforeach
                @endif
            </div>

            {{-- العمود الرئيسي --}}
            <div class="w-full md:w-[62%] flex flex-col gap-8 border-gray-300 md:border-s md:ps-8">
                
                {{-- الخبرات المهنية --}}
                @if($resume->experiences->count() > 0)
                <section>
                    <h2 class="section-title text-lg break-inside-avoid">{{ __('messages.experience', [], $resume->resume_language) ?? 'Expérience Professionnelle' }}</h2>
                    <div class="flex flex-col gap-6">
                        @foreach($resume->experiences as $exp)
                        {{-- تمت إضافة الكلاس هنا لكل خبرة على حدة لتجنب انقسامها --}}
                        <div class="break-inside-avoid">
                            <h3 class="font-bold text-gray-800 uppercase text-[14px]">{{ $exp->company }} | {{ $exp->position }}</h3>
                            @if($exp->start_date)
                                <p class="text-gray-600 mb-2 mt-1">
                                    {{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') }} – 
                                    @if($exp->end_date)
                                        {{ \Carbon\Carbon::parse($exp->end_date)->format('M Y') }}
                                    @elseif($exp->is_current)
                                        {{ __('messages.present', [], $resume->resume_language) }}
                                    @endif
                                </p>
                            @endif
                            @if($exp->description)
                                <div class="text-gray-700 leading-relaxed whitespace-pre-line mt-1">{!! nl2br(e($exp->description)) !!}</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- التكوين / التعليم --}}
                @if($resume->educations->count() > 0)
                <section>
                    <h2 class="section-title text-lg break-inside-avoid">{{ __('messages.education', [], $resume->resume_language) ?? 'Formation' }}</h2>
                    <div class="flex flex-col gap-6">
                        @foreach($resume->educations as $edu)
                        {{-- تمت إضافة الكلاس هنا لكل مرحلة تعليمية على حدة --}}
                        <div class="break-inside-avoid">
                            <h3 class="font-bold text-gray-800 uppercase text-[14px]">
                                {{ $edu->degree }} / {{ $edu->institution }}
                            </h3>
                            @if($edu->graduation_year)
                                <p class="text-gray-600 mb-2 mt-1" dir="ltr">{{ $edu->graduation_year }}</p>
                            @endif
                            @if($edu->field_of_study)
                                <p class="text-gray-700 leading-relaxed">
                                    {{ __('Field of study:') }} {{ $edu->field_of_study }}
                                </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>
        </div>
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

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>