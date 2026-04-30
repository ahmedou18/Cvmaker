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

    // معالجة extra_sections
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
    <title>{{ $profile->full_name ?? 'CV' }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --accent-dark: #1e293b;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Inter', sans-serif" }};
            background-color: #e2e8f0;
            color: #334155;
        }

        .main-card {
            background: white;
            min-height: 297mm;
            width: 100%;
            max-width: 900px;
            margin: auto;
        }

        .header-top {
            background-color: var(--accent-dark);
            color: white;
            padding: 3rem 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            justify-content: center;
            text-align: center;
        }

        @media (min-width: 768px) {
            .header-top {
                flex-wrap: nowrap;
                text-align: left;
                justify-content: flex-start;
            }
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--accent-dark);
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin-bottom: 1.5rem;
        }

        .section-heading::before {
            content: "";
            width: 8px;
            height: 8px;
            background: var(--accent-dark);
            border-radius: 50%;
        }

        .skill-dot {
            height: 8px;
            width: 8px;
            border-radius: 50%;
            display: inline-block;
            margin: 0 2px;
        }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none !important; }
            .main-card { box-shadow: none; max-width: 100%; margin: 0; }
            .header-top { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="py-10 print:py-0">

    {{-- Top Action Bar --}}
    @if(!$hideActions)
    <div class="no-print max-w-[900px] mx-auto bg-white/80 backdrop-blur-md p-4 mb-6 shadow-sm rounded-xl flex justify-between items-center border border-slate-200">
        <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900 font-medium flex items-center gap-2">
            <span>{{ $resumeLanguage == 'ar' ? '← العودة' : '← Back' }}</span>
        </a>
        <div class="flex gap-2">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition">
                {{ __('messages.edit_data', [], $resumeLanguage) }}
            </a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="px-4 py-2 text-sm font-semibold text-white bg-slate-800 rounded-lg hover:bg-slate-700 transition shadow-sm">
                    {{ __('messages.download_pdf', [], $resumeLanguage) }}
                </a>
            @else
                <button onclick="openModal()" class="px-4 py-2 text-sm font-semibold text-white bg-gray-400 rounded-lg hover:bg-gray-500 transition">
                    {{ __('messages.upgrade_to_download', [], $resumeLanguage) }}
                </button>
            @endif
        </div>
    </div>
    @endif

    <div class="main-card shadow-2xl overflow-hidden print:shadow-none">
        
        {{-- Header --}}
        <div class="header-top flex flex-col md:flex-row">
            @if($photoUrl)
                <img src="{{ $photoUrl }}" class="w-32 h-32 md:w-40 md:h-40 object-cover rounded-full border-4 border-slate-700 shadow-xl mx-auto md:mx-0">
            @endif
            
            <div class="flex-1">
                <h1 class="text-3xl md:text-5xl font-bold tracking-tight mb-2">{{ $profile->full_name }}</h1>
                <p class="text-slate-400 text-xl font-light uppercase tracking-widest">{{ $profile->job_title }}</p>
                
                <div class="mt-6 flex flex-wrap justify-center md:justify-start gap-y-2 gap-x-6 text-sm text-slate-300">
                    @if($profile->email)
                        <span class="flex items-center gap-2 italic">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ $profile->email }}
                        </span>
                    @endif
                    @if($profile->phone)
                        <span class="flex items-center gap-2" dir="ltr">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            {{ $profile->phone }}
                        </span>
                    @endif
                    @if($profile->address)
                        <span class="flex items-center gap-2 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $profile->address }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row">
            {{-- Left Column: Main Info --}}
            <div class="w-full md:w-2/3 p-10 border-r border-slate-100">
                
                @if($profile->summary)
                <section class="mb-10">
                    <h2 class="section-heading">{{ __('messages.summary', [], $resumeLanguage) }}</h2>
                    <p class="text-slate-600 leading-relaxed text-justify">{{ $profile->summary }}</p>
                </section>
                @endif

                @if($resume->experiences->count())
                <section class="mb-10">
                    <h2 class="section-heading">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                    <div class="space-y-8">
                        @foreach($resume->experiences as $exp)
                        <div class="group">
                            <div class="flex justify-between items-start mb-1">
                                <h3 class="font-bold text-slate-800 text-lg">{{ $exp->position }}</h3>
                                <span class="text-xs font-bold text-slate-400 uppercase bg-slate-50 px-2 py-1 rounded">
                                    {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('Y') : '' }} - 
                                    {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : (\Carbon\Carbon::parse($exp->end_date)->format('Y')) }}
                                </span>
                            </div>
                            <p class="text-slate-500 font-medium mb-2">{{ $exp->company }}</p>
                            <p class="text-slate-600 text-sm leading-relaxed">{!! nl2br(e($exp->description)) !!}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                @if($resume->educations->count())
                <section class="mb-10">
                    <h2 class="section-heading">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                    <div class="grid grid-cols-1 gap-6">
                        @foreach($resume->educations as $edu)
                        <div>
                            <h3 class="font-bold text-slate-800">{{ $edu->degree }} - {{ $edu->field_of_study }}</h3>
                            <p class="text-slate-500 text-sm">{{ $edu->institution }} | {{ $edu->graduation_year }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- References Section (مُضافة) --}}
                @if($resume->references->count())
                <section>
                    <h2 class="section-heading">{{ __('messages.references', [], $resumeLanguage) ?? 'References' }}</h2>
                    <div class="space-y-4">
                        @foreach($resume->references as $ref)
                        <div>
                            <p class="font-bold text-slate-800">{{ $ref->full_name }}</p>
                            <p class="text-sm text-slate-600">{{ $ref->job_title }} @if($ref->company) - {{ $ref->company }} @endif</p>
                            @if($ref->email || $ref->phone)
                                <p class="text-xs text-slate-500 mt-1">
                                    @if($ref->email) {{ $ref->email }} @endif
                                    @if($ref->email && $ref->phone) | @endif
                                    @if($ref->phone) {{ $ref->phone }} @endif
                                </p>
                            @endif
                            @if($ref->notes)
                                <p class="text-sm text-slate-700 italic mt-1">{{ $ref->notes }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            {{-- Right Column: Skills & Details --}}
            <div class="w-full md:w-1/3 p-10 bg-slate-50/50">
                
                @if($resume->skills->count())
                <section class="mb-10">
                    <h2 class="section-heading text-sm">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                    <div class="space-y-4">
                        @foreach($resume->skills as $skill)
                        <div>
                            <div class="flex justify-between text-xs font-bold text-slate-700 mb-1 uppercase">
                                <span>{{ $skill->name }}</span>
                            </div>
                            <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-slate-800 h-full rounded-full" style="width: {{ $skill->percentage ?? 80 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                @if($resume->languages->count())
                <section class="mb-10">
                    <h2 class="section-heading text-sm">{{ __('messages.languages', [], $resumeLanguage) }}</h2>
                    <div class="space-y-3">
                        @foreach($resume->languages as $lang)
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-slate-700">{{ $lang->name }}</span>
                            <div class="flex">
                                @for($i=1; $i<=5; $i++)
                                    <span class="skill-dot {{ $i <= ($lang->level ?? 3) ? 'bg-slate-800' : 'bg-slate-200' }}"></span>
                                @endfor
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                @if($resume->hobbies->count())
                <section class="mb-10">
                    <h2 class="section-heading text-sm">{{ __('messages.hobbies', [], $resumeLanguage) }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($resume->hobbies as $hobby)
                            <span class="text-xs bg-white border border-slate-200 text-slate-600 px-2 py-1 rounded shadow-sm">
                                @if($hobby->icon){{ $hobby->icon }} @endif{{ $hobby->name }}
                            </span>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- Extra Sidebar --}}
                @if(!empty($extra))
                    @foreach($extra as $section)
                        <section class="mb-8">
                            <h2 class="section-heading text-sm">{{ $section['title'] }}</h2>
                            <div class="text-xs text-slate-600 leading-relaxed italic">
                                {!! nl2br(e($section['content'] ?? '')) !!}
                            </div>
                        </section>
                    @endforeach
                @endif

            </div>
        </div>
    </div>

    @if(!$hideActions)
    <x-plans-modal id="plansModal" class="hidden" closeAction="closeModal()" :resume-uuid="$resume->uuid" :currentLang="$resumeLanguage" />
    <script>
        function openModal() { document.getElementById('plansModal')?.classList.remove('hidden'); }
        function closeModal() { document.getElementById('plansModal')?.classList.add('hidden'); }
        document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeModal(); });
    </script>
    @endif

</body>
</html>