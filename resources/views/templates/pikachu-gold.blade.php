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
    <title>{{ $profile->full_name ?? 'السيرة الذاتية' }} - Pikachu Template</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --theme-gold: #c29d3c;
            --text-main: #374151;
            --text-light: #6b7280;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Inter', sans-serif" }};
            background-color: #f3f4f6;
            color: var(--text-main);
            line-height: 1.5;
        }

        .font-serif-custom {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Lora', serif" }};
        }

        .section-title {
            color: var(--theme-gold);
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            border-bottom: 1px solid rgba(194, 157, 60, 0.3);
            padding-bottom: 0.25rem;
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Lora', serif" }};
        }

        .gold-bg {
            background-color: var(--theme-gold);
        }

        .icon-gold {
            color: var(--theme-gold);
            width: 1rem;
            height: 1rem;
            display: inline-block;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { background-color: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .gold-bg { background-color: #c29d3c !important; color: white !important; }
            .text-gold-theme { color: #c29d3c !important; }
            .section-title { border-bottom-color: rgba(194, 157, 60, 0.3) !important; color: #c29d3c !important; }
        }
    </style>
</head>
<body class="py-8 print:py-0">

    {{-- شريط الإجراءات (متوافق مع جميع القوالب) --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-4 shadow flex justify-between items-center rounded-lg">
        <a href="{{ route('dashboard') }}" class="text-gray-600 font-bold hover:text-[#c29d3c] transition">← {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded text-sm font-bold">{{ __('messages.edit_data', [], $resumeLanguage) }}</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-[#c29d3c] text-white px-6 py-2 rounded font-bold hover:opacity-90 transition">{{ __('messages.download_pdf', [], $resumeLanguage) }}</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded text-sm font-bold">{{ __('messages.upgrade_to_download', [], $resumeLanguage) }}</button>
            @endif
        </div>
    </div>
    @endif

    {{-- الحاوية الرئيسية للسيرة الذاتية --}}
    <div class="print-container max-w-5xl mx-auto bg-white shadow-2xl flex flex-col md:flex-row min-h-[1122px] p-10 gap-8">
        
        {{-- العمود الأيسر (الضيق) --}}
        <div class="w-full md:w-[32%] space-y-8">
            
            {{-- الصورة الشخصية --}}
            <div class="flex justify-center md:justify-start">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" class="w-full aspect-square object-cover rounded-xl shadow-sm border border-gray-100" alt="Profile Photo">
                @else
                    <div class="w-full aspect-square bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 text-5xl border border-gray-200">👤</div>
                @endif
            </div>

            {{-- الشهادات (Certifications) --}}
            @php $certs = collect($extra)->firstWhere('title', 'Certifications'); @endphp
            @if($certs)
            <section>
                <h2 class="section-title">{{ $certs['title'] }}</h2>
                <div class="text-[13px] space-y-3 text-gray-800 font-medium">
                    {!! nl2br(e($certs['content'] ?? '')) !!}
                </div>
            </section>
            @endif

            {{-- الاهتمامات (Interests / Hobbies) --}}
            @if($resume->hobbies->count())
            <section>
                <h2 class="section-title">{{ __('messages.hobbies', [], $resumeLanguage) ?? 'Interests' }}</h2>
                <div class="space-y-4">
                    @foreach($resume->hobbies as $hobby)
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="text-[#c29d3c] font-bold text-lg leading-none">⚲</span>
                            <span class="font-bold text-[13.5px] text-gray-800">{{ $hobby->name }}</span>
                        </div>
                        @if($hobby->description)
                            <p class="text-[12.5px] text-gray-600 leading-snug">{{ $hobby->description }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- الجوائز (Awards & Recognition) --}}
            @php $awards = collect($extra)->firstWhere('title', 'Awards & Recognition'); @endphp
            @if($awards)
            <section>
                <h2 class="section-title">{{ $awards['title'] }}</h2>
                <div class="text-[13px] space-y-3 text-gray-800 font-medium">
                    {!! nl2br(e($awards['content'] ?? '')) !!}
                </div>
            </section>
            @endif

            {{-- اللغات (Languages) --}}
            @if($resume->languages->count())
            <section>
                <h2 class="section-title">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
                <div class="space-y-2">
                    @foreach($resume->languages as $lang)
                    <div class="text-[13px]">
                        <span class="font-bold text-gray-800 block">{{ $lang->name }}</span>
                        <span class="text-gray-500">{{ $lang->proficiency ?? 'Fluent' }}</span>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

        </div>

        {{-- العمود الأيمن (العريض) --}}
        <div class="w-full md:w-[68%] space-y-7">
            
            {{-- صندوق الرأس الذهبي (Header Box) --}}
            <div class="gold-bg text-white p-7 rounded-xl shadow-sm">
                <h1 class="text-4xl font-bold font-serif-custom mb-1 tracking-wide">{{ $profile->full_name }}</h1>
                @if($profile->job_title)
                    <p class="text-[15px] font-medium mb-5 opacity-90 tracking-wide">{{ $profile->job_title }}</p>
                @endif
                
                <div class="flex flex-wrap gap-x-5 gap-y-2 text-[12.5px] font-medium opacity-95">
                    @if($profile->email)
                    <div class="flex items-center gap-1.5">
                        <span>✉</span> <span class="break-all">{{ $profile->email }}</span>
                    </div>
                    @endif
                    @if($profile->phone)
                    <div class="flex items-center gap-1.5" dir="ltr">
                        <span>✆</span> <span>{{ $profile->phone }}</span>
                    </div>
                    @endif
                    @if($profile->address)
                    <div class="flex items-center gap-1.5">
                        <span>⚲</span> <span>{{ $profile->address }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- التواجد على الإنترنت (Online Presence) --}}
            @if(!empty($profile->linkedin) || !empty($profile->github) || !empty($profile->website))
            <section>
                <h2 class="section-title">Online Presence</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-[12.5px] text-gray-700">
                    @if(!empty($profile->linkedin))
                    <div class="flex items-center gap-1.5">
                        <span class="text-[#c29d3c]">▣</span>
                        <div>
                            <p class="font-bold text-gray-800">LinkedIn</p>
                            <p class="text-gray-500">{{ str_replace(['https://www.', 'https://'], '', $profile->linkedin) }}</p>
                        </div>
                    </div>
                    @endif
                    @if(!empty($profile->github))
                    <div class="flex items-center gap-1.5">
                        <span class="text-[#c29d3c]">▣</span>
                        <div>
                            <p class="font-bold text-gray-800">GitHub</p>
                            <p class="text-gray-500">{{ str_replace(['https://www.', 'https://'], '', $profile->github) }}</p>
                        </div>
                    </div>
                    @endif
                    @if(!empty($profile->website))
                    <div class="flex items-center gap-1.5">
                        <span class="text-[#c29d3c]">▣</span>
                        <div>
                            <p class="font-bold text-gray-800">Website</p>
                            <p class="text-gray-500">{{ str_replace(['https://www.', 'https://'], '', $profile->website) }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </section>
            @endif

            {{-- الملخص المهني (Professional Summary) --}}
            @if($profile->summary)
            <section>
                <h2 class="section-title">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
                <p class="text-[13.5px] text-gray-700 leading-relaxed font-medium">
                    {!! nl2br(e($profile->summary)) !!}
                </p>
            </section>
            @endif

            {{-- المهارات التقنية (Technical Skills) --}}
            @if($resume->skills->count())
            <section>
                <h2 class="section-title">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    @foreach($resume->skills as $skill)
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="text-[#c29d3c] font-bold text-sm">⟨/⟩</span>
                            <span class="font-bold text-[13.5px] text-gray-800">{{ $skill->name }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-1">
                            @if($skill->proficiency)
                                <span class="text-[12px] text-gray-600">{{ $skill->proficiency }}</span>
                            @endif
                        </div>
                        @if($skill->description)
                            <p class="text-[12px] text-gray-500 leading-tight mb-1">{{ $skill->description }}</p>
                        @endif
                        {{-- النجوم --}}
                        <div class="flex gap-1">
                            @php 
                                $rating = isset($skill->percentage) ? round($skill->percentage / 20) : 4; 
                            @endphp
                            @for($i=1; $i<=5; $i++)
                                <span class="text-sm {{ $i <= $rating ? 'text-[#c29d3c]' : 'text-gray-200' }}">✦</span>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- الخبرات المهنية (Professional Experience) --}}
            @if($resume->experiences->count())
            <section>
                <h2 class="section-title">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                <div class="space-y-6">
                    @foreach($resume->experiences as $exp)
                    <div>
                        <div class="flex justify-between items-baseline mb-0.5">
                            <h3 class="font-bold text-[14.5px] text-gray-800">{{ $exp->company }}</h3>
                            <span class="text-[13px] text-gray-600">{{ $exp->city ?? '' }}</span>
                        </div>
                        <div class="flex justify-between items-baseline mb-2">
                            <p class="text-[13.5px] font-semibold text-gray-700">{{ $exp->position }}</p>
                            <span class="text-[12.5px] text-gray-500 font-medium">
                                {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('F Y') : '' }} - 
                                {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('F Y') : '') }}
                            </span>
                        </div>
                        @if($exp->description)
                            <ul class="list-disc list-outside ml-4 text-[13px] text-gray-700 space-y-1.5 leading-relaxed">
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

            {{-- التعليم (Education) --}}
            @if($resume->educations->count())
            <section>
                <h2 class="section-title">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                <div class="space-y-4">
                    @foreach($resume->educations as $edu)
                    <div>
                        <div class="flex justify-between items-baseline mb-0.5">
                            <h3 class="font-bold text-[14px] text-gray-800">{{ $edu->institution }}</h3>
                            <span class="text-[13px] text-gray-700 font-medium">{{ $edu->degree }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <p class="text-[13px] text-gray-600">{{ $edu->field_of_study }}</p>
                            <span class="text-[12.5px] text-gray-500">
                                {{ $edu->graduation_year ? $edu->graduation_year : '' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

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