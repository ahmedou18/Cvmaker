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
    <title>{{ $profile->full_name ?? 'السيرة الذاتية' }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-orange: #d84315; /* اللون البرتقالي الغامق المحدث */
            --skill-bg: #dfe7ef;      /* لون خلفية المهارات */
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Montserrat', sans-serif" }};
            background-color: #f3f4f6;
            color: #374151;
        }

        .section-header {
            color: var(--primary-orange);
            font-weight: 800;
            text-transform: uppercase;
            border-top: 2px solid var(--primary-orange);
            padding-top: 0.5rem;
            margin-bottom: 1rem;
            font-size: 1rem;
            letter-spacing: 0.05em;
        }

        .skill-pill {
            background-color: var(--skill-bg);
            border-radius: 4px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            display: inline-block;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .summary-box {
            background-color: var(--primary-orange);
            color: white;
            padding: 2.5rem;
            border-radius: 4px;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { background-color: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .print-container { box-shadow: none !important; margin: 0 !important; border-radius: 0 !important; }
            .summary-box { background-color: #d84315 !important; color: white !important; }
            .section-header { border-top-color: #d84315 !important; color: #d84315 !important; }
            .skill-pill { background-color: #dfe7ef !important; }
            .star-filled { color: #d84315 !important; }
        }
    </style>
</head>
<body class="py-8 print:py-0">

    {{-- Action Bar --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-4 shadow flex justify-between items-center rounded-lg">
        <a href="{{ route('dashboard') }}" class="text-gray-600 font-bold">← {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded text-sm font-bold">{{ __('messages.edit_data', [], $resumeLanguage) }}</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-[#d84315] text-white px-6 py-2 rounded font-bold">{{ __('messages.download_pdf', [], $resumeLanguage) }}</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded text-sm font-bold">{{ __('messages.upgrade_to_download', [], $resumeLanguage) }}</button>
            @endif
        </div>
    </div>
    @endif

    {{-- Main Paper --}}
    <div class="print-container max-w-5xl mx-auto bg-white shadow-2xl flex flex-col md:flex-row min-h-[1122px] p-10 gap-10">
        
        {{-- Sidebar (Left) --}}
        <div class="w-full md:w-[32%] space-y-8">
            
            {{-- Photo --}}
            <div class="flex justify-center md:justify-start">
                <div class="w-48 h-48 rounded-full overflow-hidden border-8 border-gray-100 shadow-sm">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="Profile">
                    @else
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center text-4xl text-gray-400">👤</div>
                    @endif
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="space-y-3 text-sm font-medium text-gray-700">
                @if($profile->phone)
                <div class="flex items-center gap-3">
                    <span class="text-[#d84315]">📞</span> <span dir="ltr">{{ $profile->phone }}</span>
                </div>
                @endif
                @if($profile->website)
                <div class="flex items-center gap-3">
                    <span class="text-[#d84315]">🌐</span> <span>{{ $profile->website }}</span>
                </div>
                @endif
                @if($profile->email)
                <div class="flex items-center gap-3">
                    <span class="text-[#d84315]">✉️</span> <span class="break-all">{{ $profile->email }}</span>
                </div>
                @endif
                @if($profile->address)
                <div class="flex items-center gap-3">
                    <span class="text-[#d84315]">📍</span> <span>{{ $profile->address }}</span>
                </div>
                @endif
            </div>

            {{-- Skills --}}
            @if($resume->skills->count())
            <section>
                <h2 class="section-header">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                <div class="space-y-1">
                    @foreach($resume->skills as $skill)
                        <div class="skill-pill">{{ $skill->name }}</div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Languages --}}
            @if($resume->languages->count())
            <section>
                <h2 class="section-header">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
                <div class="space-y-2">
                    @foreach($resume->languages as $lang)
                    <div class="flex justify-between items-center text-sm">
                        <span class="font-bold">{{ $lang->name }}</span>
                        <div class="flex gap-0.5">
                            @for($i=1; $i<=5; $i++)
                                <span class="{{ $i <= ($lang->level ?? 3) ? 'text-[#d84315] star-filled' : 'text-gray-300' }}">★</span>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- Certifications --}}
            @php $certs = collect($extra)->firstWhere('title', 'Certifications'); @endphp
            @if($certs)
            <section>
                <h2 class="section-header">{{ $certs['title'] }}</h2>
                <div class="text-sm space-y-3">
                    {!! nl2br(e($certs['content'] ?? '')) !!}
                </div>
            </section>
            @endif

        </div>

        {{-- Main Content (Right) --}}
        <div class="w-full md:w-[68%] space-y-8">
            
            {{-- Name & Summary Box --}}
            <div class="summary-box">
                <h1 class="text-4xl font-extrabold mb-1">{{ $profile->full_name }}</h1>
                <p class="text-xl font-medium mb-6 opacity-90">{{ $profile->job_title }}</p>
                @if($profile->summary)
                <div class="h-px bg-white/30 mb-6"></div>
                <p class="text-sm leading-relaxed">
                    {{ $profile->summary }}
                </p>
                @endif
            </div>

            {{-- Work Experience --}}
            @if($resume->experiences->count())
            <section>
                <h2 class="section-header">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                <div class="space-y-8">
                    @foreach($resume->experiences as $exp)
                    <div>
                        <div class="flex justify-between items-baseline">
                            <h3 class="font-extrabold text-gray-800 text-lg">{{ $exp->company }}</h3>
                            <span class="text-sm font-bold text-gray-600">
                                ({{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('M Y') : '' }} - {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('M Y') : '') }})
                            </span>
                        </div>
                        <p class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-3">{{ $exp->position }}</p>
                        @if($exp->description)
                            <ul class="list-disc list-outside ml-5 text-[14px] text-gray-700 space-y-2">
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

            {{-- Education --}}
            @if($resume->educations->count())
            <section>
                <h2 class="section-header">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                <div class="space-y-6">
                    @foreach($resume->educations as $edu)
                    <div class="flex justify-between">
                        <div>
                            <h3 class="font-extrabold text-gray-800">{{ $edu->institution }}</h3>
                            <p class="text-sm text-gray-600 font-medium">{{ $edu->degree }} {{ $edu->field_of_study ? 'in ' . $edu->field_of_study : '' }}</p>
                        </div>
                        <span class="text-sm font-bold text-gray-600">({{ $edu->graduation_year }})</span>
                    </div>
                    @endforeach
                </div>
            </section>
            @endif

            {{-- الأقسام الإضافية --}}
            @foreach($extra as $sec)
                @if(!in_array($sec['title'], ['Certifications', 'Projects']))
                <section>
                    <h2 class="section-header">{{ $sec['title'] }}</h2>
                    <div class="text-sm text-gray-700 leading-relaxed">
                        {!! nl2br(e($sec['content'] ?? '')) !!}
                    </div>
                </section>
                @endif
            @endforeach

        </div>
    </div>

    {{-- المودال وزر الترقية --}}
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