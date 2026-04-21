@php
    $profile = $resume->personalDetail;
    $lang = $resume->resume_language; // اللغة المخزنة للسيرة (ar, en, fr)
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ in_array($lang, ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $profile->full_name ?? __('messages.full_name', [], $lang) }} - Modern Sidebar CV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'sidebar-bg': '#f2edeb',
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: white; }
        .whitespace-pre-line { white-space: pre-line; }
        
        @media print {
            @page { margin: 0; size: auto; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .no-print, .no-print * { display: none !important; }
            body { background-color: white !important; margin: 0 !important; padding: 0 !important; }
            .page-container { padding: 0 !important; max-width: 100% !important; }
            .bg-sidebar-bg { background-color: #f2edeb !important; }
        }
    </style>
</head>
<body class="text-gray-900 relative">

    {{-- شريط الإجراءات العلوي (لا يطبع) --}}
    <div class="no-print bg-white shadow-sm border-b mb-10 max-w-4xl mx-auto rounded-md mt-6">
        <div class="px-6 py-4 flex justify-between items-center flex-wrap gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-blue-600 flex items-center transition">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                {{ __('messages.back_to_dashboard', [], $lang) }}
            </a>
            <div class="flex gap-2">
                <a href="{{ route('resume.edit', $resume->uuid) }}" class="text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 flex items-center transition rounded">
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    {{ __('messages.edit_data', [], $lang) }}
                </a>
                @if(auth()->check() && auth()->user()->hasActivePlan())
                    <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition cursor-pointer">
                        {{ __('messages.download_pdf', [], $lang) }}
                    </button>
                @else
                    <button onclick="openModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition cursor-pointer">
                        {{ __('messages.download_pdf', [], $lang) }}
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- التخطيط الرئيسي (عمودين) --}}
    <div class="page-container max-w-4xl mx-auto shadow-2xl relative overflow-hidden flex text-[13px] break-inside-avoid">
        
        {{-- الشريط الجانبي (يسار) --}}
        <div class="w-[33%] bg-sidebar-bg flex flex-col p-8 gap-8">
            
            {{-- صورة دائرية --}}
            @if($profile && $profile->photo_path)
                <div class="flex justify-center">
                    <img src="{{ asset($profile->photo_path) }}" alt="{{ __('messages.profile_photo', [], $lang) }}"
                         class="w-36 h-36 rounded-full object-cover border-4 border-white shadow-sm">
                </div>
            @endif
            
            {{-- الاسم والمنصب --}}
            <header class="text-center break-inside-avoid">
                <h1 class="text-2xl font-bold uppercase tracking-wider mb-1">
                    {{ $profile->full_name ?? __('messages.full_name', [], $lang) }}
                </h1>
                <p class="text-[14px] font-semibold text-gray-700">
                    {{ $profile->job_title ?? __('messages.job_title', [], $lang) }}
                </p>
            </header>
            
            {{-- المعلومات الشخصية --}}
            <section class="break-inside-avoid">
                <h2 class="text-lg font-bold mb-3 border-b border-gray-400 pb-1">{{ __('messages.personal_info', [], $lang) }}</h2>
                <div class="flex flex-col gap-3 text-gray-800">
                    @if($profile->email)
                        <div class="flex items-center gap-2 justify-start">
                            <i class="fas fa-envelope text-gray-600"></i>
                            <span dir="ltr">{{ $profile->email }}</span>
                        </div>
                    @endif
                    @if($profile->phone)
                        <div class="flex items-center gap-2 justify-start">
                            <i class="fas fa-phone text-gray-600"></i>
                            <span dir="ltr">{{ $profile->phone }}</span>
                        </div>
                    @endif
                    @if($profile->address)
                        <div class="flex items-center gap-2 justify-start">
                            <i class="fas fa-map-marker-alt text-gray-600"></i>
                            <span>{{ $profile->address }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2 justify-start">
                        <i class="fab fa-linkedin-in text-gray-600"></i>
                        <span>/LinkedIn</span>
                    </div>
                </div>
            </section>
            
            {{-- اللغات --}}
            @if($resume->languages->count() > 0)
            <section class="break-inside-avoid">
                <h2 class="text-lg font-bold mb-3 border-b border-gray-400 pb-1">{{ __('messages.languages', [], $lang) }}</h2>
                <div class="flex flex-col gap-3 text-gray-800">
                    @foreach($resume->languages as $langItem)
                        <div class="flex items-center justify-between break-inside-avoid">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                                <span><strong>{{ $langItem->name }}</strong></span>
                            </div>
                            <span class="text-xs text-gray-600">{{ $langItem->proficiency }}</span>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif
            
            {{-- الاهتمامات --}}
            <section class="break-inside-avoid">
                <h2 class="text-lg font-bold mb-3 border-b border-gray-400 pb-1">{{ __('messages.interests', [], $lang) }}</h2>
                <div class="flex items-center gap-2 justify-start">
                    <i class="fas fa-rugby-ball text-gray-600"></i>
                    <span>{{ __('messages.rugby', [], $lang) }}</span>
                </div>
            </section>
            
            {{-- الصفات --}}
            <section class="break-inside-avoid">
                <h2 class="text-lg font-bold mb-3 border-b border-gray-400 pb-1">{{ __('messages.qualities', [], $lang) }}</h2>
                <div class="flex flex-col gap-3 text-gray-800">
                    <div class="flex items-center gap-2 justify-start">
                        <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                        <span>{{ __('messages.creative', [], $lang) }}</span>
                    </div>
                    <div class="flex items-center gap-2 justify-start">
                        <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                        <span>{{ __('messages.autonomous', [], $lang) }}</span>
                    </div>
                    <div class="flex items-center gap-2 justify-start">
                        <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                        <span>{{ __('messages.organized', [], $lang) }}</span>
                    </div>
                </div>
            </section>
        </div>

        {{-- المحتوى الرئيسي (يمين) --}}
        <div class="w-[67%] bg-white flex flex-col p-8 gap-8 border-gray-300 md:border-s md:ps-8">
            
            {{-- الملف الشخصي (سياق) --}}
            @if($profile && $profile->summary)
            <section class="break-inside-avoid">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.summary', [], $lang) }}</h2>
                <p class="text-gray-700 leading-relaxed text-justify whitespace-pre-line">{!! nl2br(e($profile->summary)) !!}</p>
            </section>
            @endif
            
            {{-- الخبرات المهنية --}}
            @if($resume->experiences->count() > 0)
            <section>
                <h2 class="text-xl font-bold mb-6 break-inside-avoid">{{ __('messages.experience', [], $lang) }}</h2>
                <div class="flex flex-col gap-8">
                    @foreach($resume->experiences as $exp)
                    <div class="break-inside-avoid grid grid-cols-[150px_1fr] gap-x-6 gap-y-2 text-gray-800">
                        <div class="break-inside-avoid text-gray-700 font-semibold" dir="ltr">
                            @if($exp->start_date)
                                <span>
                                    {{ \Carbon\Carbon::parse($exp->start_date)->format('M Y') }} – 
                                    @if($exp->end_date)
                                        {{ \Carbon\Carbon::parse($exp->end_date)->format('M Y') }}
                                    @elseif($exp->is_current)
                                        {{ __('messages.present', [], $lang) }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold uppercase text-[14px]">{{ $exp->company }} | {{ $exp->position }}</h3>
                            @if($exp->description)
                                <div class="text-gray-700 leading-relaxed whitespace-pre-line ml-4 mt-2">
                                    <ul class="list-disc pl-5">
                                        <li>{{ $exp->description }}</li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif
            
            {{-- التكوين --}}
            @if($resume->educations->count() > 0)
            <section>
                <h2 class="text-xl font-bold mb-6 break-inside-avoid">{{ __('messages.education', [], $lang) }}</h2>
                <div class="flex flex-col gap-6">
                    @foreach($resume->educations as $edu)
                    <div class="break-inside-avoid grid grid-cols-[150px_1fr] gap-x-6 gap-y-2 text-gray-800">
                        <div class="break-inside-avoid text-gray-700 font-semibold" dir="ltr">
                            @if($edu->graduation_year)
                                <span>{{ $edu->graduation_year }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="font-bold uppercase text-[14px]">
                                {{ $edu->degree }} / {{ $edu->institution }}
                            </h3>
                            @if($edu->field_of_study)
                                <p class="text-gray-700 leading-relaxed mt-1">
                                    {{ __('messages.field_of_study', [], $lang) }} {{ $edu->field_of_study }}
                                </p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif
            
            {{-- المهارات --}}
            @if($resume->skills->count() > 0)
            <section class="break-inside-avoid">
                <h2 class="text-xl font-bold mb-4">{{ __('messages.skills', [], $lang) }}</h2>
                <div class="grid grid-cols-[150px_1fr] gap-x-6 gap-y-3 text-gray-800">
                    @foreach($resume->skills as $skill)
                        <div class="flex items-center break-inside-avoid font-bold">
                            <span>{{ $skill->name }}</span>
                        </div>
                        <span class="text-xs text-gray-600">{{ __('messages.excellent', [], $lang) }}</span>
                    @endforeach
                </div>
            </section>
            @endif
            
            {{-- الصفات المهنية (مثال ثابت لكن يمكن جعله ديناميكيًا) --}}
            <section>
                <h2 class="text-xl font-bold mb-6 break-inside-avoid">{{ __('messages.professional_certifications', [], $lang) }}</h2>
                <div class="flex flex-col gap-6">
                    <div class="break-inside-avoid grid grid-cols-[150px_1fr] gap-x-6 gap-y-2 text-gray-800">
                        <div class="break-inside-avoid text-gray-700 font-semibold" dir="ltr">
                            <span>2010</span>
                        </div>
                        <div>
                            <h3 class="font-bold uppercase text-[14px]">{{ __('messages.certification_hmonp', [], $lang) }}</h3>
                            <p class="text-gray-700 leading-relaxed mt-1">{{ __('messages.certification_hmonp_school', [], $lang) }}</p>
                        </div>
                    </div>
                    <div class="break-inside-avoid grid grid-cols-[150px_1fr] gap-x-6 gap-y-2 text-gray-800">
                        <div class="break-inside-avoid text-gray-700 font-semibold" dir="ltr">
                            <span>{{ __('messages.certification_date_range', [], $lang) }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold uppercase text-[14px]">{{ __('messages.certification_member_architect', [], $lang) }}</h3>
                        </div>
                    </div>
                </div>
            </section>
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