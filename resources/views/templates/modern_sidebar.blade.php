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
    <title>{{ $profile->full_name ?? 'السيرة الذاتية' }} - Modern CV</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --theme-primary: #4c2882;
            --theme-secondary: #e6e1f0;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Cairo', sans-serif" : "'Inter', sans-serif" }};
            background-color: #f3f4f6;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { background-color: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .sidebar-bg { background-color: #4c2882 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .skill-bar-fill, .bg-primary { background-color: #4c2882 !important; }
        }

        .skill-bar-bg { background-color: var(--theme-secondary); }
        .skill-bar-fill { background-color: var(--theme-primary); }
        .sidebar-bg { background-color: var(--theme-primary); }
        .text-primary { color: var(--theme-primary); }
        .border-primary { border-color: var(--theme-primary); }
    </style>
</head>
<body class="py-8 print:py-0">

    {{-- Top Action Bar --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-4 shadow rounded-lg flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-primary hover:underline">← العودة للوحة</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded-md text-sm font-bold">تعديل</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-primary text-white px-5 py-2 rounded-md text-sm font-bold">تحميل PDF</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded-md text-sm font-bold">رقّي باقتك لتحميل PDF</button>
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
                <h1 class="text-3xl font-extrabold">{{ $profile->full_name }}</h1>
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
                <h2 class="text-lg font-bold border-b border-white/30 pb-1 mb-3">اللغات</h2>
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
                <div class="bg-secondary px-3 py-1.5 mb-4 border-r-4 border-primary" style="background-color: var(--theme-secondary);">
                    <h2 class="text-xl font-bold text-primary">الملخص المهني</h2>
                </div>
                <p class="text-gray-700 leading-relaxed">{!! nl2br(e($profile->summary)) !!}</p>
            </section>
            @endif

            {{-- Skills (with percentage bar) --}}
            @if($resume->skills->count())
            <section>
                <div class="bg-secondary px-3 py-1.5 mb-4 border-r-4 border-primary" style="background-color: var(--theme-secondary);">
                    <h2 class="text-xl font-bold text-primary">المهارات التقنية</h2>
                </div>
                <div class="space-y-3">
                    @foreach($resume->skills as $skill)
                    <div>
                        <div class="flex justify-between text-sm">
                            <span class="font-medium">{{ $skill->name }}</span>
                            <span>{{ $skill->percentage ?? 80 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
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
                <div class="bg-secondary px-3 py-1.5 mb-4 border-r-4 border-primary" style="background-color: var(--theme-secondary);">
                    <h2 class="text-xl font-bold text-primary">الخبرات المهنية</h2>
                </div>
                <div class="space-y-6">
                    @foreach($resume->experiences as $exp)
                    <div class="relative pr-4 border-r-2 border-gray-200">
                        <div class="absolute w-3 h-3 bg-primary rounded-full -right-[7px] top-1.5 border-2 border-white"></div>
                        <div class="flex flex-wrap justify-between items-baseline">
                            <h3 class="text-lg font-bold">{{ $exp->position }}</h3>
                            <span class="text-sm bg-primary/10 text-primary px-2 py-0.5 rounded">
                                {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('Y/m') : '' }}
                                – {{ $exp->is_current ? 'الآن' : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('Y/m') : '') }}
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
                <div class="bg-secondary px-3 py-1.5 mb-4 border-r-4 border-primary" style="background-color: var(--theme-secondary);">
                    <h2 class="text-xl font-bold text-primary">التعليم</h2>
                </div>
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
                <div class="bg-secondary px-3 py-1.5 mb-4 border-r-4 border-primary" style="background-color: var(--theme-secondary);">
                    <h2 class="text-xl font-bold text-primary">الهوايات</h2>
                </div>
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
                <div class="bg-secondary px-3 py-1.5 mb-4 border-r-4 border-primary" style="background-color: var(--theme-secondary);">
                    <h2 class="text-xl font-bold text-primary">المراجع</h2>
                </div>
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