<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center" dir="rtl">
            <h2 class="text-xl font-bold text-slate-800">{{ __('messages.dashboard_title') }}</h2>
            @can('create', App\Models\Resume::class)
                <a href="{{ route('templates.choose') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-full text-sm font-bold shadow hover:bg-indigo-700 transition">+ {{ __('messages.create_new_resume') }}</a>
            @else
                <button onclick="openPlansModal()" class="bg-amber-500 text-white px-5 py-2 rounded-full text-sm font-bold shadow hover:bg-amber-600 transition">{{ __('messages.upgrade_plan') }}</button>
            @endcan
        </div>
    </x-slot>

    @php
        $user = Auth::user();
        $hasCoverLetterAccess = $user->plan?->has_cover_letter ?? false;
        $cvLimit = $user->plan?->cv_limit ?? 0;
        $resumesUsed = $user->resumes()->count();
        $usage = $cvLimit > 0 ? min(($resumesUsed / $cvLimit) * 100, 100) : 0;
        $hasPlan = $cvLimit > 0;
    @endphp

    <div class="py-10 bg-slate-50 min-h-screen" dir="rtl">
        <div class="max-w-6xl mx-auto px-6 space-y-8">

            {{-- Welcome Card --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <h3 class="text-lg font-bold text-slate-800">👋 {{ __('messages.welcome_back', ['name' => $user->name]) }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ __('messages.manage_resumes') }}</p>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500">{{ __('messages.cv') }}</p>
                    <h3 class="text-2xl font-bold text-slate-800">{{ $resumesUsed }}</h3>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500">{{ __('messages.plan') }}</p>
                    <h3 class="text-lg font-bold text-indigo-600">{{ $user->plan?->name ?? __('messages.none') }}</h3>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500">{{ __('messages.usage') }}</p>
                    <h3 class="text-lg font-bold text-slate-800">{{ $resumesUsed }} / {{ $cvLimit }}</h3>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-100">
                    <p class="text-sm text-slate-500">{{ __('messages.remaining_creations') }}</p>
                    <h3 class="text-2xl font-bold text-indigo-600">{{ $user->resume_creations_remaining }}</h3>
                </div>
            </div>

            {{-- Usage Bar --}}
            @if($hasPlan)
            <div class="bg-white p-5 rounded-2xl border border-slate-100">
                <div class="flex justify-between text-sm mb-2"><span>{{ __('messages.subscription_usage') }}</span><span>{{ number_format($usage, 0) }}%</span></div>
                <div class="w-full h-2 bg-slate-200 rounded-full"><div class="h-2 rounded-full bg-indigo-600" style="width: {{ $usage }}%"></div></div>
            </div>
            @endif

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('templates.choose') }}" class="bg-white p-5 rounded-2xl border border-slate-100 hover:shadow transition">
                    <h4 class="font-bold text-slate-800 mb-1">➕ {{ __('messages.create_new_resume') }}</h4>
                    <p class="text-sm text-slate-500">{{ __('messages.start_from_scratch') }}</p>
                </a>
                @if($hasCoverLetterAccess)
                    <a href="{{ route('cover-letters.create') }}" class="bg-white p-5 rounded-2xl border border-slate-100 hover:shadow transition">
                        <h4 class="font-bold text-slate-800 mb-1">✉️ {{ __('messages.cover_letter') }}</h4>
                        <p class="text-sm text-slate-500">{{ __('messages.create_cover_letter') }}</p>
                    </a>
                @else
                    <button onclick="openPlansModal()" class="bg-white p-5 rounded-2xl border border-slate-100 text-right opacity-80">
                        <h4 class="font-bold text-slate-800 mb-1">🔒 {{ __('messages.cover_letter') }}</h4>
                        <p class="text-sm text-slate-500">{{ __('messages.upgrade_to_access') }}</p>
                    </button>
                @endif
            </div>

            {{-- CV List --}}
            <div>
                <h4 class="font-bold text-slate-700 mb-4">{{ __('messages.previous_resumes') }}</h4>
                @if($resumes->count())
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($resumes as $resume)
                    <div class="bg-white p-5 rounded-2xl border border-slate-100 hover:shadow transition">
                        <div class="flex justify-between items-center mb-2">
                            <h5 class="font-bold text-slate-800 text-sm">{{ $resume->title }}</h5>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ $resume->is_published ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $resume->is_published ? __('messages.completed') : __('messages.draft') }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">{{ $resume->updated_at->diffForHumans() }}</p>
                        <div class="flex justify-between text-sm">
                            <a href="{{ route('resume.show', $resume->uuid) }}" class="text-indigo-600 font-medium hover:underline">{{ __('messages.view_edit') }}</a>
                            <a href="{{ route('resume.download', $resume->uuid) }}" class="text-slate-500 hover:text-slate-700">{{ __('messages.pdf') }}</a>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="bg-white p-8 rounded-2xl border border-dashed border-slate-200 text-center">
                    <p class="text-slate-500 text-sm">{{ __('messages.no_resumes_found') }}</p>
                    <a href="{{ route('templates.choose') }}" class="mt-3 inline-block text-indigo-600 text-sm font-medium hover:underline">{{ __('messages.create_new_resume') }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div id="plansModal" style="display: none;">
        <x-plans-modal closeAction="closePlansModal()" :resumeUuid="null" :currentLang="app()->getLocale()" />
    </div>

    <script>
        function openPlansModal() { const modal = document.getElementById('plansModal'); if (modal) { modal.style.display = 'flex'; document.body.style.overflow = 'hidden'; } }
        function closePlansModal() { const modal = document.getElementById('plansModal'); if (modal) { modal.style.display = 'none'; document.body.style.overflow = ''; } }
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closePlansModal(); });
    </script>
</x-app-layout>