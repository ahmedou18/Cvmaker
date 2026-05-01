هل هذا الكود المحسن سيعمل دون مشاكل اريد التأكد ان هذا التامبليت المحسن سيكون متوافقا مثل البقية
الكود المحسن الجديد
@php
    $profile = $resume->personalDetail;
    $user = auth()->user();
    $canDownload = $user && $user->plan && $user->plan->price > 0;
    $resumeLanguage = $resume->resume_language;
    $hideActions = $hideActions ?? false;

    // رابط الصورة مع fallback
    $photoUrl = null;
    if (isset($photoAbsoluteUrl) && $photoAbsoluteUrl) {
        $photoUrl = $photoAbsoluteUrl;
    } elseif ($profile && $profile->photo_path) {
        $photoUrl = asset('storage/' . $profile->photo_path);
    }

    // معالجة extra_sections بأمان
    $extra = [];
    if (!empty($resume->extra_sections)) {
        $extra = is_string($resume->extra_sections) ? json_decode($resume->extra_sections, true) : $resume->extra_sections;
    }
@endphp

<!DOCTYPE html>
<html lang="{{ $resumeLanguage ?? 'ar' }}" dir="{{ $resumeLanguage == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profile->full_name ?? 'السيرة الذاتية' }} - Chikorita CV</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Noto+Sans+Arabic:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --theme-primary: #18774F; /* Green from the image */
            --theme-secondary: #E5F3EF; /* Very light green for background or highlights */
            --theme-text-header: #1f2937;
            --theme-text-light: #6b7280;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', 'Noto Sans Arabic', sans-serif" : "'Inter', sans-serif" }};
            background-color: #f3f4f6;
            letter-spacing: 0;
            line-height: 1.6;
            font-weight: 500;
            color: #1f2937;
        }

        h1, h2, h3, h4, .font-bold {
            font-weight: 700;
        }

        .resume-name {
            font-size: 2.2rem;
            line-height: 1.2;
            letter-spacing: -0.01em;
            color: #1f2937;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--theme-primary);
            position: relative;
            display: inline-block;
            margin-bottom: 0.75rem;
            width: 100%;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 100%;
            height: 1px;
            background-color: #e5e7eb; /* Thin line below title */
            position: absolute;
            bottom: -5px;
            left: 0;
        }

        /* Sidebar bg only on the right section now */
        .sidebar-bg {
            background-color: var(--theme-primary);
        }
        .sidebar-bg p, .sidebar-bg li, .sidebar-bg span, .sidebar-bg div {
            color: white !important;
            line-height: 1.5;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { background-color: white; margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .sidebar-bg { background-color: #18774F !important; }
            .bg-primary { background-color: #18774F !important; }
            .text-primary { color: #18774F !important; }
            .skill-dot-filled { background-color: #18774F !important; border-color: #18774F !important; }
            a { text-decoration: none; color: inherit; }
        }

        .text-primary { color: var(--theme-primary); }
        .bg-primary { background-color: var(--theme-primary); }
        .border-primary { border-color: var(--theme-primary); }
    </style>
</head>
<body class="py-8 print:py-0">

    {{-- Top Action Bar --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-4 shadow rounded-lg flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-primary hover:underline">← {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded-md text-sm font-bold">{{ __('messages.edit_data', [], $resumeLanguage) }}</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-primary text-white px-5 py-2 rounded-md text-sm font-bold btn-print">{{ __('messages.download_pdf', [], $resumeLanguage) }}</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded-md text-sm font-bold">{{ __('messages.upgrade_to_download', [], $resumeLanguage) }}</button>
            @endif
        </div>
    </div>
    @endif

    {{-- Main Container --}}
    <div class="print-container max-w-5xl mx-auto bg-white shadow-2xl rounded-lg overflow-hidden flex flex-col min-h-[1122px]">
        
        {{-- Header Area (Full Width) --}}
        <div class="p-10 pb-6 flex flex-col md:flex-row items-center border-b border-gray-200 gap-8">
            {{-- Profile Photo --}}
            @if($photoUrl)
                <img src="{{ $photoUrl }}" class="w-36 h-36 rounded-full object-cover border-4 border-gray-100 shadow-md" alt="Profile Photo" onerror="this.style.display='none'">
            @else
                <div class="w-36 h-36 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-4xl border border-dashed border-gray-300">
                    👤
                </div>
            @endif

            {{-- Name, Title, and Contact Info --}}
            <div class="flex-1 text-center md:text-start">
                <div class="resume-name font-extrabold text-gray-800">{{ $profile->full_name }}</div>
                <p class="text-primary text-xl font-semibold mt-1">{{ $profile->job_title }}</p>
                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm justify-center md:justify-start text-gray-600">
                    @if($profile->email)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span class="break-all">{{ $profile->email }}</span>
                        </div>
                    @endif
                    @if($profile->phone)
                        <div class="flex items-center gap-1.5" dir="ltr">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ $profile->phone }}</span>
                        </div>
                    @endif
                    @if($profile->address)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $profile->address }}</span>
                        </div>
                    @endif
                    {{-- إضافة أيقونات إضافية مثل LinkedIn إذا كانت متاحة في البيانات --}}
                    @if($profile->linkedin)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            <span>{{ $profile->linkedin }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Main Flex Container for Left/Right content --}}
        <div class="flex flex-col md:flex-row flex-1">
            
            {{-- Main Content (Left, white background) --}}
            <div class="w-full md:w-[68%] p-10 space-y-9 border-r border-gray-100">
                
                {{-- Online Presence (إذا كانت متوفرة) --}}
                {{-- Professional Summary --}}
                @if($profile->summary)
                <section>
                    <h2 class="section-title">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
                    <p class="text-gray-700 leading-relaxed text-[15px]">{!! nl2br(e($profile->summary)) !!}</p>
                </section>
                @endif

                {{-- Technical Skills (with custom dot bars) --}}
                @if($resume->skills->count())
                <section>
                    <h2 class="section-title mb-4">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        @foreach($resume->skills as $skill)
                        <div class="flex items-start gap-3">
                            {{-- استخدام أيقونة <> أو {} بناءً على نوع المهارة كـ fallback --}}
                            <div class="text-primary text-xl font-bold mt-0.5"><></div>
                            <div class="flex-1">
                                <div class="flex justify-between items-baseline">
                                    <span class="font-bold text-[15px]">{{ $skill->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $skill->proficiency }}</span>
                                </div>
                                <div class="flex items-center gap-0.5 mt-1">
                                    {{-- Custom Dot Bar (5 blocks) --}}
                                    @php $fillBlocks = ceil(($skill->percentage ?? 80) / 20); @endphp
                                    @for($i=1; $i<=5; $i++)
                                        <div class="w-3.5 h-3.5 border-2 border-primary {{ $i <= $fillBlocks ? 'bg-primary skill-dot-filled' : 'bg-gray-100' }}"></div>
                                    @endfor
                                </div>
                                @if($skill->description)
                                    <p class="text-[13px] text-gray-600 mt-1.5 leading-snug">{{ $skill->description }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Education --}}
                @if($resume->educations->count())
                <section>
                    <h2 class="section-title">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                    <div class="space-y-6">
                        @foreach($resume->educations as $edu)
                        <div class="grid grid-cols-1 md:grid-cols-[1fr,auto] items-start gap-1">
                            <div>
                                <h3 class="font-extrabold text-[16px] text-gray-800">{{ $edu->institution }}</h3>
                                <p class="text-gray-700 text-[15px] font-medium">{{ $edu->degree }} - {{ $edu->field_of_study }}</p>
                                @if($edu->gpa) <p class="text-sm text-gray-600">GPA: {{ $edu->gpa }}</p> @endif
                                @if($edu->coursework) <p class="text-[13px] text-gray-600 mt-1 leading-snug">Concentration: {{ $edu->coursework }}</p> @endif
                            </div>
                            <div class="text-gray-600 text-[14px] font-semibold text-right">
                                {{ $edu->graduation_year }}
                                <p class="text-[13px] font-normal text-gray-500">{{ $profile->address_city ?? '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Professional Experience --}}
                @if($resume->experiences->count())
                <section>
                    <h2 class="section-title">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                    <div class="space-y-7">
                        @foreach($resume->experiences as $exp)
                        <div class="grid grid-cols-1 md:grid-cols-[1fr,auto] items-start gap-1">
                            <div>
                                <h3 class="font-extrabold text-[16px] text-gray-800">{{ $exp->company }}</h3>
                                <p class="text-primary text-[15px] font-bold">{{ $exp->position }}</p>
                                @if($exp->description)
                                    <ul class="list-disc list-outside text-gray-700 text-[14px] mt-2 space-y-1 pr-4">
                                        {{-- معالجة الوصف كقائمة نقطية إذا كان يحتوي على نقاط --}}
                                        @foreach(explode("\n", $exp->description) as $line)
                                            @if(trim($line)) <li>{{ trim($line) }}</li> @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="text-gray-600 text-[14px] font-semibold text-right">
                                {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('Y/m') : '' }} – {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('Y/m') : '') }}
                                <p class="text-[13px] font-normal text-gray-500">{{ $exp->city ?? ($profile->address_city ?? '') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>

            {{-- Sidebar (Right, Green background) --}}
            <div class="w-full md:w-[32%] sidebar-bg p-10 space-y-8 text-white">
                
                {{-- Certifications --}}
                {{-- Awards --}}
                {{-- Extra Sections (Mapping to Certifications/Awards if not present in main data) --}}
                @php 
                    $certificationSection = collect($extra)->firstWhere('title', 'Certifications');
                    $awardsSection = collect($extra)->firstWhere('title', 'Awards & Recognition');
                @endphp

                @if($certificationSection)
                <div class="mb-6">
                    <h2 class="text-lg font-bold border-b border-white/20 pb-1 mb-2">{{ $certificationSection['title'] }}</h2>
                    <div class="text-[13.5px] leading-relaxed space-y-2">{!! nl2br(e($certificationSection['content'] ?? '')) !!}</div>
                </div>
                @endif

                @if($awardsSection)
                <div class="mb-6">
                    <h2 class="text-lg font-bold border-b border-white/20 pb-1 mb-2">{{ $awardsSection['title'] }}</h2>
                    <div class="text-[13.5px] leading-relaxed space-y-2">{!! nl2br(e($awardsSection['content'] ?? '')) !!}</div>
                </div>
                @endif

                {{-- Languages --}}
                @if($resume->languages->count())
                <div>
                    <h2 class="text-lg font-bold border-b border-white/20 pb-1 mb-2">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
                    <div class="grid grid-cols-2 gap-x-3 gap-y-2 text-[14px]">
                        @foreach($resume->languages as $lang)
                        <div>
                            <span class="font-semibold block">{{ $lang->name }}</span>
                            <span class="text-xs opacity-90">{{ $lang->proficiency }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Hobbies / Interests (as listed in image) --}}
                @if($resume->hobbies->count())
                <div>
                    <h2 class="text-lg font-bold border-b border-white/20 pb-1 mb-2">{{ __('messages.hobbies', [], $resumeLanguage) ?? 'الهوايات' }}</h2>
                    <div class="space-y-3">
                        @foreach($resume->hobbies as $hobby)
                        <div class="flex items-start gap-2.5">
                            {{-- استخدام أيقونة 💡 أو أيقونة مخصصة --}}
                            <div class="text-xl mt-0.5">💡</div>
                            <div class="text-[13px]">
                                <span class="font-semibold text-white">{{ $hobby->name }}</span>
                                @if($hobby->description) <p class="opacity-90 leading-snug">{{ $hobby->description }}</p> @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Publications (if present in extra) --}}
                @php $pubSection = collect($extra)->firstWhere('title', 'Publications & Talks'); @endphp
                @if($pubSection)
                <div>
                    <h2 class="text-lg font-bold border-b border-white/20 pb-1 mb-2">{{ $pubSection['title'] }}</h2>
                    <div class="text-[13.5px] leading-relaxed">{!! nl2br(e($pubSection['content'] ?? '')) !!}</div>
                </div>
                @endif

            </div>
        </div>
    </div>

    @if(!$hideActions)
    <script>
        function openModal() { document.getElementById('plansModal')?.classList.remove('hidden'); }
        function closeModal() { document.getElementById('plansModal')?.classList.add('hidden'); }
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeModal(); });
    </script>
    <x-plans-modal id="plansModal" class="hidden" closeAction="closeModal()" :resume-uuid="$resume->uuid" :currentLang="$resumeLanguage" />
    @endif

</body>
</html>

التامبليت الاصلي كمثال @php 
    $profile = $resume->personalDetail; 
    $user = auth()->user();
    $canDownload = $user && $user->plan && $user->plan->price > 0;
    $resumeLanguage = $resume->resume_language;
    $hideActions = $hideActions ?? false;

    // رابط الصورة مع fallback
    $photoUrl = null;
    if (isset($photoAbsoluteUrl) && $photoAbsoluteUrl) {
        $photoUrl = $photoAbsoluteUrl;
    } elseif ($profile && $profile->photo_path) {
        $photoUrl = asset('storage/' . $profile->photo_path);
    }

    // معالجة extra_sections بأمان
    $extra = [];
    if (!empty($resume->extra_sections)) {
        $extra = is_string($resume->extra_sections) ? json_decode($resume->extra_sections, true) : $resume->extra_sections;
    }
@endphp

<!DOCTYPE html>
<html lang="{{ $resumeLanguage ?? 'ar' }}" dir="{{ $resumeLanguage == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $profile->full_name ?? 'السيرة الذاتية' }} - Modern CV</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Noto+Sans+Arabic:wght@400;500;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --theme-primary: #4c2882;
            --theme-secondary: #e6e1f0;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', 'Noto Sans Arabic', sans-serif" : "'Inter', sans-serif" }};
            background-color: #f3f4f6;
            letter-spacing: 0;
            line-height: 1.7;
            font-weight: 500;
            color: #1f2937;
        }

        h1, h2, h3, h4, .font-bold, .step-link {
            font-weight: 700;
        }

        .resume-name {
            font-size: 1.8rem;
            line-height: 1.3;
            letter-spacing: 0;
        }
        @media (min-width: 768px) {
            .resume-name {
                font-size: 2.4rem;
            }
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--theme-primary);
            position: relative;
            display: inline-block;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--theme-primary);
        }

        .sidebar-bg {
            background-color: var(--theme-primary);
        }
        .sidebar-bg p, .sidebar-bg li, .sidebar-bg span, .sidebar-bg div:not(.flex) {
            font-weight: 500;
            line-height: 1.6;
        }

        .btn-print {
            transition: all 0.2s ease;
        }
        .btn-print:hover {
            opacity: 0.85;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body {
                background-color: white;
                margin: 0;
                padding: 0;
                font-family: 'Tajawal', 'Noto Sans Arabic', sans-serif;
                font-weight: 500;
                line-height: 1.5;
                color: black;
            }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .sidebar-bg { background-color: #4c2882 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .skill-bar-fill, .bg-primary { background-color: #4c2882 !important; }
            a, button {
                text-decoration: none;
            }
        }

        .skill-bar-bg { background-color: var(--theme-secondary); }
        .skill-bar-fill { background-color: var(--theme-primary); }
        .text-primary { color: var(--theme-primary); }
        .border-primary { border-color: var(--theme-primary); }
    </style>
</head>
<body class="py-8 print:py-0">

    {{-- Top Action Bar --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-4 shadow rounded-lg flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-primary hover:underline">← {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded-md text-sm font-bold">{{ __('messages.edit_data', [], $resumeLanguage) }}</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-primary text-white px-5 py-2 rounded-md text-sm font-bold btn-print">{{ __('messages.download_pdf', [], $resumeLanguage) }}</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded-md text-sm font-bold">{{ __('messages.upgrade_to_download', [], $resumeLanguage) }}</button>
            @endif
        </div>
    </div>
    @endif

    {{-- Main Container --}}
    <div class="print-container max-w-5xl mx-auto bg-white shadow-2xl rounded-lg overflow-hidden flex flex-col md:flex-row min-h-[1122px]">
        
        {{-- Sidebar (Left) --}}
        <div class="w-full md:w-[35%] sidebar-bg text-white p-8 print:p-6">
            
            {{-- Profile Photo --}}
            @if($photoUrl)
            <div class="flex justify-center mb-6">
                <img src="{{ $photoUrl }}" class="w-40 h-40 rounded-lg object-cover border-4 border-white/20 shadow-lg" alt="Profile Photo" onerror="this.style.display='none'">
            </div>
            @else
            <div class="flex justify-center mb-6">
                <div class="w-40 h-40 rounded-lg bg-white/10 flex items-center justify-center text-white/40 text-4xl border-2 border-dashed border-white/30">
                    👤
                </div>
            </div>
            @endif

            {{-- Name & Title --}}
            <div class="mb-6 text-center md:text-start">
                <div class="resume-name font-extrabold">{{ $profile->full_name }}</div>
                <p class="text-purple-200 text-lg font-light mt-1">{{ $profile->job_title }}</p>
            </div>

            {{-- Contact Details --}}
            <div class="mb-6 space-y-3 text-sm">
                @if($profile->email)
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="break-all">{{ $profile->email }}</span>
                </div>
                @endif
                @if($profile->phone)
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span dir="ltr">{{ $profile->phone }}</span>
                </div>
                @endif
                @if($profile->address)
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>{{ $profile->address }}</span>
                </div>
                @endif
            </div>

            {{-- Languages --}}
            @if($resume->languages->count())
            <div class="mb-6">
                <h2 class="text-lg font-bold border-b border-white/30 pb-1 mb-3">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
                <div class="space-y-3">
                    @foreach($resume->languages as $lang)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold">{{ $lang->name }}</span>
                            <div class="flex gap-1">
                                @for($i=1; $i<=5; $i++)
                                    <span class="text-sm {{ $i <= ($lang->level ?? 3) ? 'text-yellow-300' : 'text-white/30' }}">★</span>
                                @endfor
                            </div>
                        </div>
                        @if($lang->proficiency)
                            <p class="text-xs opacity-80 mt-1">{{ $lang->proficiency }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Extra Sections (Sidebar) --}}
            @if(!empty($extra))
                @foreach($extra as $section)
                    @if(!empty($section['title']))
                    <div class="mb-6">
                        <h2 class="text-lg font-bold border-b border-white/30 pb-1 mb-2">{{ $section['title'] }}</h2>
                        <div class="text-sm leading-relaxed">{!! nl2br(e($section['content'] ?? '')) !!}</div>
                    </div>
                    @endif
                @endforeach
            @endif

        </div>

        {{-- Main Content (Right) --}}
        <div class="w-full md:w-[65%] p-8 space-y-8">
            
            {{-- Summary --}}
            @if($profile->summary)
            <section>
                <h2 class="section-title mb-3">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
                <p class="text-gray-700 leading-relaxed">{!! nl2br(e($profile->summary)) !!}</p>
            </section>
            @endif

            {{-- Skills (with percentage bar) --}}
            @if($resume->skills->count())
            <section>
                <h2 class="section-title mb-4">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                <div class="space-y-3">
                    @foreach($resume->skills as $skill)
                    <div>
                        <div class="flex justify-between text-sm font-medium">
                            <span>{{ $skill->name }}</span>
                            <span>{{ $skill->percentage ?? 80 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                            <div class="bg-primary h-2 rounded-full" style="width: {{ $skill->percentage ?? 80 }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Experience --}}
            @if($resume->experiences->count())
            <section>
                <h2 class="section-title mb-4">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                <div class="space-y-6">
                    @foreach($resume->experiences as $exp)
                    <div class="relative pr-4 border-r-2 border-gray-200">
                        <div class="absolute w-3 h-3 bg-primary rounded-full -right-[7px] top-1.5 border-2 border-white"></div>
                        <div class="flex flex-wrap justify-between items-baseline">
                            <h3 class="text-lg font-bold">{{ $exp->position }}</h3>
                            <span class="text-sm bg-primary/10 text-primary px-2 py-0.5 rounded">
                                {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('Y/m') : '' }}
                                – {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('Y/m') : '') }}
                            </span>
                        </div>
                        <p class="text-gray-600 font-semibold text-sm">{{ $exp->company }}</p>
                        @if($exp->description)
                            <p class="text-gray-700 text-sm mt-1 leading-relaxed">{!! nl2br(e($exp->description)) !!}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Education --}}
            @if($resume->educations->count())
            <section>
                <h2 class="section-title mb-4">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                @foreach($resume->educations as $edu)
                <div class="mb-4">
                    <div class="flex flex-wrap justify-between items-baseline">
                        <h3 class="font-bold text-lg">{{ $edu->degree }} - {{ $edu->field_of_study }}</h3>
                        <span class="text-sm text-gray-500">{{ $edu->graduation_year }}</span>
                    </div>
                    <p class="text-primary font-semibold text-sm">{{ $edu->institution }}</p>
                </div>
                @endforeach
            </section>
            @endif

            {{-- Hobbies --}}
            @if($resume->hobbies->count())
            <section>
                <h2 class="section-title mb-3">{{ __('messages.hobbies', [], $resumeLanguage) ?? 'الهوايات' }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($resume->hobbies as $hobby)
                        <span class="bg-gray-100 px-3 py-1 rounded-full flex items-center gap-1 text-sm">
                            @if($hobby->icon) {{ $hobby->icon }} @endif
                            {{ $hobby->name }}
                            @if($hobby->description) <span class="text-gray-500 text-xs">({{ $hobby->description }})</span> @endif
                        </span>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- References --}}
            @if($resume->references->count())
            <section>
                <h2 class="section-title mb-3">{{ __('messages.references', [], $resumeLanguage) ?? 'المراجع' }}</h2>
                <div class="space-y-4">
                    @foreach($resume->references as $ref)
                    <div class="border-r-2 border-gray-200 pr-4">
                        <p class="font-bold text-gray-800">{{ $ref->full_name }}</p>
                        <p class="text-sm text-gray-600">{{ $ref->job_title }} @if($ref->company) - {{ $ref->company }} @endif</p>
                        @if($ref->email || $ref->phone)
                            <p class="text-xs text-gray-500 mt-1">
                                @if($ref->email) {{ $ref->email }} @endif
                                @if($ref->email && $ref->phone) | @endif
                                @if($ref->phone) {{ $ref->phone }} @endif
                            </p>
                        @endif
                        @if($ref->notes)
                            <p class="text-sm text-gray-700 italic mt-1">{{ $ref->notes }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

        </div>
    </div>

    @if(!$hideActions)
    <script>
        function openModal() { document.getElementById('plansModal')?.classList.remove('hidden'); }
        function closeModal() { document.getElementById('plansModal')?.classList.add('hidden'); }
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeModal(); });
    </script>
    <x-plans-modal id="plansModal" class="hidden" closeAction="closeModal()" :resume-uuid="$resume->uuid" :currentLang="$resumeLanguage" />
    @endif

</body>
</html>