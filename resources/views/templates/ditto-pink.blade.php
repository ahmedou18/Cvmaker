@php 
    $profile = $resume->personalDetail; 
    $user = auth()->user();
    $canDownload = $user && $user->plan && $user->plan->price > 0;
    $resumeLanguage = $resume->resume_language;
    $hideActions = $hideActions ?? false;

    // معالجة الصورة
    $photoUrl = null;
    if (isset($photoAbsoluteUrl) && $photoAbsoluteUrl) {
        $photoUrl = $photoAbsoluteUrl;
    } elseif ($profile && $profile->photo_path) {
        $photoUrl = asset('storage/' . $profile->photo_path);
    }

    // معالجة الأقسام الإضافية
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
    <title>{{ $profile->full_name ?? 'السيرة الذاتية' }} - Ditto Template</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --theme-pink: #d64585;
            --text-main: #374151;
            --text-light: #6b7280;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Inter', sans-serif" }};
            background-color: #f3f4f6;
            color: var(--text-main);
            line-height: 1.5;
        }

        .section-title {
            color: var(--theme-pink);
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .icon-pink {
            color: var(--theme-pink);
            width: 1rem;
            height: 1rem;
            display: inline-block;
        }

        .progress-track {
            background-color: #fce7f3;
            height: 6px;
            width: 100%;
            margin-top: 6px;
        }

        .progress-fill {
            background-color: var(--theme-pink);
            height: 100%;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { background-color: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .header-bg { background-color: #d64585 !important; }
            .text-pink-theme { color: #d64585 !important; }
            .progress-track { background-color: #fce7f3 !important; }
            .progress-fill { background-color: #d64585 !important; }
        }
    </style>
</head>
<body class="py-8 print:py-0">

    {{-- شريط الإجراءات (متوافق مع جميع القوالب) --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-4 shadow rounded-lg flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-[var(--theme-pink)] font-bold transition">← {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded text-sm font-bold">{{ __('messages.edit_data', [], $resumeLanguage) }}</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-[#d64585] text-white px-5 py-2 rounded text-sm font-bold shadow hover:opacity-90">{{ __('messages.download_pdf', [], $resumeLanguage) }}</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded text-sm font-bold">{{ __('messages.upgrade_to_download', [], $resumeLanguage) }}</button>
            @endif
        </div>
    </div>
    @endif

    {{-- الحاوية الرئيسية للسيرة الذاتية --}}
    <div class="print-container max-w-5xl mx-auto bg-white shadow-2xl flex flex-col min-h-[1122px]">
        
        {{-- الرأس (Header) باللون الوردي --}}
        <div class="header-bg bg-[#d64585] text-white py-8 px-10 flex flex-col md:flex-row">
            <div class="hidden md:block w-[30%]"></div>
            <div class="w-full md:w-[70%] md:px-4 text-center md:text-start">
                <h1 class="text-3xl font-bold tracking-wide">{{ $profile->full_name }}</h1>
                <p class="text-sm font-medium mt-1 opacity-95 uppercase tracking-wider">{{ $profile->job_title }}</p>
            </div>
        </div>

        {{-- المحتوى الرئيسي مقسم لعمودين --}}
        <div class="flex flex-col md:flex-row px-10 py-6 gap-x-8 flex-1">
            
            {{-- العمود الأيسر (الضيق) --}}
            <div class="w-full md:w-[30%] space-y-7">
                
                {{-- الصورة الشخصية --}}
                <div class="-mt-14 mb-4 flex justify-center md:justify-start">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" class="w-32 h-32 object-cover border-4 border-white shadow-sm" alt="Profile Photo">
                    @else
                        <div class="w-32 h-32 bg-gray-100 border-4 border-white shadow-sm flex items-center justify-center text-gray-400 text-3xl">👤</div>
                    @endif
                </div>

                {{-- الشهادات (Certifications) --}}
                @php $certs = collect($extra)->firstWhere('title', 'Certifications'); @endphp
                @if($certs)
                <section>
                    <h2 class="section-title text-pink-theme">{{ $certs['title'] }}</h2>
                    <div class="text-[13px] space-y-3 leading-tight">
                        {!! nl2br(e($certs['content'] ?? '')) !!}
                    </div>
                </section>
                @endif

                {{-- الجوائز (Awards & Recognition) --}}
                @php $awards = collect($extra)->firstWhere('title', 'Awards & Recognition'); @endphp
                @if($awards)
                <section>
                    <h2 class="section-title text-pink-theme">{{ $awards['title'] }}</h2>
                    <div class="text-[13px] space-y-3 leading-tight">
                        {!! nl2br(e($awards['content'] ?? '')) !!}
                    </div>
                </section>
                @endif

                {{-- اللغات (Languages) --}}
                @if($resume->languages->count())
                <section>
                    <h2 class="section-title text-pink-theme">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
                    <div class="space-y-4">
                        @foreach($resume->languages as $lang)
                        <div>
                            <div class="flex flex-col text-[13px]">
                                <span class="font-bold text-gray-800">{{ $lang->name }}</span>
                                <span class="text-gray-500">{{ $lang->proficiency }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width: {{ ($lang->level ?? 3) * 20 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- الاهتمامات (Interests / Hobbies) --}}
                @if($resume->hobbies->count())
                <section>
                    <h2 class="section-title text-pink-theme">{{ __('messages.hobbies', [], $resumeLanguage) ?? 'Interests' }}</h2>
                    <div class="space-y-4">
                        @foreach($resume->hobbies as $hobby)
                        <div>
                            <div class="flex items-center gap-1.5 mb-0.5">
                                <svg class="icon-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                <span class="font-bold text-[13px] text-gray-800">{{ $hobby->name }}</span>
                            </div>
                            @if($hobby->description)
                                <p class="text-[12px] text-gray-600 leading-snug">{{ $hobby->description }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>

            {{-- العمود الأيمن (العريض) --}}
            <div class="w-full md:w-[70%] space-y-7 md:px-2">
                
                {{-- معلومات الاتصال --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-y-2 gap-x-4 text-[13px] font-medium text-gray-700">
                    @if($profile->email)
                    <div class="flex items-center gap-1.5">
                        <svg class="icon-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="break-all">{{ $profile->email }}</span>
                    </div>
                    @endif
                    @if($profile->phone)
                    <div class="flex items-center gap-1.5" dir="ltr">
                        <svg class="icon-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <span>{{ $profile->phone }}</span>
                    </div>
                    @endif
                    @if($profile->address)
                    <div class="flex items-center gap-1.5">
                        <svg class="icon-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ $profile->address }}</span>
                    </div>
                    @endif
                </div>

                {{-- التواجد على الإنترنت (الروابط الاجتماعية) --}}
                @if(!empty($profile->linkedin) || !empty($profile->github) || !empty($profile->website))
                <section>
                    <h2 class="section-title text-pink-theme">Online Presence</h2>
                    <div class="grid grid-cols-2 gap-4 text-[13px] text-gray-700">
                        @if(!empty($profile->linkedin))
                        <div>
                            <p class="text-gray-500 mb-0.5">LinkedIn</p>
                            <p class="font-medium">{{ $profile->linkedin }}</p>
                        </div>
                        @endif
                        @if(!empty($profile->github))
                        <div>
                            <p class="text-gray-500 mb-0.5">GitHub</p>
                            <p class="font-medium">{{ $profile->github }}</p>
                        </div>
                        @endif
                        @if(!empty($profile->website))
                        <div>
                            <p class="text-gray-500 mb-0.5">Website</p>
                            <p class="font-medium">{{ $profile->website }}</p>
                        </div>
                        @endif
                    </div>
                </section>
                @endif

                {{-- الملخص المهني (Professional Summary) --}}
                @if($profile->summary)
                <section>
                    <h2 class="section-title text-pink-theme">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
                    <p class="text-[13.5px] text-gray-700 leading-relaxed font-medium">
                        {!! nl2br(e($profile->summary)) !!}
                    </p>
                </section>
                @endif

                {{-- المهارات التقنية (Technical Skills) --}}
                @if($resume->skills->count())
                <section>
                    <h2 class="section-title text-pink-theme">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        @foreach($resume->skills as $skill)
                        <div>
                            <div class="flex justify-between items-baseline mb-0.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[var(--theme-pink)] font-bold text-lg leading-none">⟨/⟩</span>
                                    <span class="font-bold text-[14px] text-gray-800">{{ $skill->name }}</span>
                                </div>
                                @if($skill->proficiency)
                                    <span class="text-[12px] text-gray-600">{{ $skill->proficiency }}</span>
                                @endif
                            </div>
                            @if($skill->description)
                                <p class="text-[12px] text-gray-700 leading-snug mb-2 font-medium">{{ $skill->description }}</p>
                            @endif
                            <div class="progress-track">
                                <div class="progress-fill" style="width: {{ $skill->percentage ?? 85 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- التعليم (Education) --}}
                @if($resume->educations->count())
                <section>
                    <h2 class="section-title text-pink-theme">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                    <div class="space-y-4">
                        @foreach($resume->educations as $edu)
                        <div>
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h3 class="font-bold text-[14px] text-gray-800">{{ $edu->institution }}</h3>
                                <span class="text-[13px] text-gray-700 font-medium">{{ $edu->degree }} {{ $edu->gpa ? '• ' . $edu->gpa : '' }}</span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <p class="text-[13px] text-gray-600">{{ $edu->field_of_study }}</p>
                                <span class="text-[12.5px] text-gray-500">
                                    {{ $profile->address_city ?? '' }} {{ $edu->graduation_year ? ' • ' . $edu->graduation_year : '' }}
                                </span>
                            </div>
                            @if($edu->coursework)
                                <p class="text-[12.5px] text-gray-600 mt-1.5 leading-relaxed font-medium">Relevant Coursework: {{ $edu->coursework }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- الخبرات المهنية (Professional Experience) --}}
                @if($resume->experiences->count())
                <section>
                    <h2 class="section-title text-pink-theme">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                    <div class="space-y-5">
                        @foreach($resume->experiences as $exp)
                        <div>
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h3 class="font-bold text-[14px] text-gray-800">{{ $exp->company }}</h3>
                                <span class="text-[13px] text-gray-600">{{ $exp->city ?? ($profile->address_city ?? '') }}</span>
                            </div>
                            <div class="flex justify-between items-baseline mb-2">
                                <p class="text-[13.5px] font-medium text-gray-700">{{ $exp->position }}</p>
                                <span class="text-[12.5px] text-gray-500 font-medium">
                                    {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('F Y') : '' }} - 
                                    {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('F Y') : '') }}
                                </span>
                            </div>
                            @if($exp->description)
                                <ul class="list-disc list-outside ml-4 text-[13px] text-gray-700 space-y-1.5 font-medium">
                                    @foreach(explode("\n", $exp->description) as $line)
                                        @if(trim($line)) <li>{{ trim($line) }}</li> @endif
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

            </div>
        </div>
    </div>

    {{-- المودال للترقية (أسفل الصفحة) --}}
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