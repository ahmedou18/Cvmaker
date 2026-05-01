@php
    $profile = $resume->personalDetail;
    $user = auth()->user();
    $canDownload = $user && $user->plan && $user->plan->price > 0;
    $resumeLanguage = $resume->resume_language;
    $hideActions = $hideActions ?? false;

    $photoUrl = null;
    if (isset($photoAbsoluteUrl) && $photoAbsoluteUrl) {
        $photoUrl = $photoAbsoluteUrl;
    } elseif ($profile && $profile->photo_path) {
        $photoUrl = asset('storage/' . $profile->photo_path);
    }

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
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --header-bg: #0077b6;
            --sidebar-bg: #d9eaf2;
            --accent-blue: #0077b6;
        }

        body {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Inter', sans-serif" }};
            background-color: #e5e7eb;
            color: #1f2937;
        }

        h1, h2, h3, .serif-font {
            font-family: {{ $resumeLanguage == 'ar' ? "'Tajawal', sans-serif" : "'Libre Baskerville', serif" }};
        }

        .section-vertical-line {
            border-{{ $resumeLanguage == 'ar' ? 'right' : 'left' }}-width: 4px;
            border-color: var(--accent-blue);
            padding-{{ $resumeLanguage == 'ar' ? 'right' : 'left' }}: 1rem;
        }

        @media print {
            @page { margin: 0; size: A4; }
            body { background-color: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .main-container { box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: none !important; }
            .header-bg { background-color: #0077b6 !important; }
            .sidebar-bg { background-color: #d9eaf2 !important; }
        }
    </style>
</head>
<body class="py-10 print:py-0">

    {{-- Top Action Bar (متوافق مع كل القوالب) --}}
    @if(!$hideActions)
    <div class="no-print max-w-5xl mx-auto bg-white p-4 mb-6 shadow rounded-lg flex justify-between items-center">
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:underline font-bold">← {{ __('messages.back_to_dashboard', [], $resumeLanguage) }}</a>
        <div class="flex gap-3">
            <a href="{{ route('resume.edit', $resume->uuid) }}" class="bg-gray-100 px-5 py-2 rounded text-sm font-bold">{{ __('messages.edit_data', [], $resumeLanguage) }}</a>
            @if($canDownload)
                <a href="{{ route('resume.download', $resume->uuid) }}" class="bg-[#0077b6] text-white px-5 py-2 rounded text-sm font-bold">{{ __('messages.download_pdf', [], $resumeLanguage) }}</a>
            @else
                <button onclick="openModal()" class="bg-gray-400 text-white px-5 py-2 rounded text-sm font-bold">{{ __('messages.upgrade_to_download', [], $resumeLanguage) }}</button>
            @endif
        </div>
    </div>
    @endif

    <div class="main-container max-w-5xl mx-auto bg-white shadow-2xl flex flex-col min-h-[1122px]">
        
        <!-- Header Section -->
        <div class="header-bg bg-[#0077b6] text-white p-10 flex flex-col md:flex-row gap-8 items-center md:items-end">
            <div class="w-32 h-32 md:w-40 md:h-44 overflow-hidden border-4 border-white/20 bg-gray-200">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" class="w-full h-full object-cover" alt="Profile">
                @else
                    <div class="w-full h-full flex items-center justify-center text-4xl text-gray-400">👤</div>
                @endif
            </div>
            <div class="flex-1 text-center md:text-start">
                <h1 class="text-4xl font-bold mb-2">{{ $profile->full_name }}</h1>
                <p class="text-xl opacity-90 font-medium">{{ $profile->job_title }}</p>
                
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-y-2 text-sm opacity-90">
                    @if($profile->address)
                        <div class="flex items-center gap-2">📍 <span>{{ $profile->address }}</span></div>
                    @endif
                    @if($profile->phone)
                        <div class="flex items-center gap-2" dir="ltr">📞 <span>{{ $profile->phone }}</span></div>
                    @endif
                    @if($profile->email)
                        <div class="flex items-center gap-2">✉️ <span class="break-all">{{ $profile->email }}</span></div>
                    @endif
                    {{-- حقل موقع ويب --}}
                    @if($profile->website)
                        <div class="flex items-center gap-2">🔗 <span>{{ $profile->website }}</span></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row flex-1">
            <!-- Sidebar -->
            <div class="sidebar-bg bg-[#d9eaf2] w-full md:w-[30%] p-8 space-y-10">
                
                <!-- Profiles (LinkedIn, GitHub) - اختيارية ولا تظهر إذا لم تكن موجودة -->
                @if(!empty($profile->linkedin) || !empty($profile->github))
                <section>
                    <h2 class="text-lg font-bold border-b border-gray-400 pb-1 mb-4 uppercase tracking-wider">Profiles</h2>
                    <div class="space-y-3 text-sm font-medium">
                        @if($profile->linkedin)
                            <div class="flex items-center gap-2">
                                <span class="text-blue-700">LinkedIn:</span>
                                <span>{{ $profile->linkedin }}</span>
                            </div>
                        @endif
                        @if($profile->github)
                            <div class="flex items-center gap-2">
                                <span class="text-gray-800">GitHub:</span>
                                <span>{{ $profile->github }}</span>
                            </div>
                        @endif
                    </div>
                </section>
                @endif

                <!-- Skills -->
                @if($resume->skills->count())
                <section>
                    <h2 class="text-lg font-bold border-b border-gray-400 pb-1 mb-4 uppercase tracking-wider">{{ __('messages.skills', [], $resumeLanguage) }}</h2>
                    <div class="space-y-6">
                        @foreach($resume->skills as $skill)
                        <div>
                            <p class="font-bold text-gray-800">{{ $skill->name }}</p>
                            @if($skill->level)
                                <p class="text-xs text-gray-600 mb-1 italic">{{ $skill->level }}</p>
                            @endif
                            @if($skill->description)
                                <p class="text-xs leading-relaxed text-gray-700">{{ $skill->description }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Certifications (من extra_sections) -->
                @php $certs = collect($extra)->firstWhere('title', 'Certifications'); @endphp
                @if($certs)
                <section>
                    <h2 class="text-lg font-bold border-b border-gray-400 pb-1 mb-4 uppercase tracking-wider">{{ $certs['title'] }}</h2>
                    <div class="text-sm space-y-4">
                        {!! nl2br(e($certs['content'] ?? '')) !!}
                    </div>
                </section>
                @endif
            </div>

            <!-- Main Content Area -->
            <div class="w-full md:w-[70%] p-10 space-y-10">
                
                <!-- Summary -->
                @if($profile->summary)
                <section>
                    <p class="text-gray-700 leading-relaxed text-[15px] italic">
                        {{ $profile->summary }}
                    </p>
                </section>
                @endif

                <!-- Experience -->
                @if($resume->experiences->count())
                <section>
                    <h2 class="text-xl font-bold mb-6 serif-font">{{ __('messages.experience', [], $resumeLanguage) }}</h2>
                    <div class="space-y-8">
                        @foreach($resume->experiences as $exp)
                        <div class="section-vertical-line">
                            <div class="flex flex-wrap justify-between items-baseline mb-1">
                                <h3 class="font-extrabold text-[#0077b6] text-lg">{{ $exp->company }}</h3>
                                <span class="text-sm font-bold text-gray-700">
                                    {{ $exp->start_date ? \Carbon\Carbon::parse($exp->start_date)->format('F Y') : '' }} - 
                                    {{ $exp->is_current ? __('messages.present', [], $resumeLanguage) : ($exp->end_date ? \Carbon\Carbon::parse($exp->end_date)->format('F Y') : '') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline text-sm mb-2">
                                <span class="font-bold text-gray-800">{{ $exp->position }}</span>
                                <span class="text-gray-500 italic">{{ $exp->city ?? '' }}</span>
                            </div>
                            @if($exp->description)
                            <ul class="list-disc list-outside ml-5 mt-2 text-sm text-gray-700 space-y-1">
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

                <!-- Education -->
                @if($resume->educations->count())
                <section>
                    <h2 class="text-xl font-bold mb-6 serif-font">{{ __('messages.education', [], $resumeLanguage) }}</h2>
                    <div class="space-y-6">
                        @foreach($resume->educations as $edu)
                        <div class="section-vertical-line">
                            <div class="flex flex-wrap justify-between items-baseline">
                                <h3 class="font-extrabold text-[#0077b6]">{{ $edu->institution }}</h3>
                                <span class="text-sm font-bold text-gray-700">{{ $edu->graduation_year }}</span>
                            </div>
                            <p class="text-sm text-gray-800 font-medium">{{ $edu->degree }} {{ $edu->field_of_study ? 'in ' . $edu->field_of_study : '' }}</p>
                        </div>
                        @endforeach
                    </div>
                </section>
                @endif

                <!-- Projects (من extra_sections) -->
                @php $projects = collect($extra)->firstWhere('title', 'Projects'); @endphp
                @if($projects)
                <section>
                    <h2 class="text-xl font-bold mb-6 serif-font">{{ $projects['title'] }}</h2>
                    <div class="space-y-6">
                        <div class="section-vertical-line text-sm leading-relaxed">
                            {!! nl2br(e($projects['content'] ?? '')) !!}
                        </div>
                    </div>
                </section>
                @endif

                {{-- References (اختياري، إن أردت الاحتفاظ به) --}}
                @if($resume->references && $resume->references->count())
                <section>
                    <h2 class="text-xl font-bold mb-6 serif-font">{{ __('messages.references', [], $resumeLanguage) ?? 'References' }}</h2>
                    <div class="space-y-4">
                        @foreach($resume->references as $ref)
                        <div class="section-vertical-line">
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
    </div>

    {{-- المودال وزر الترقية (متوافق مع القوالب الأخرى) --}}
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