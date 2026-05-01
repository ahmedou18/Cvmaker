<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center" dir="rtl">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">{{ __('messages.dashboard_title') }}</h2>
            @can('create', App\Models\Resume::class)
                <a href="{{ route('templates.choose') }}" class="group flex items-center gap-2 bg-indigo-600 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-md shadow-indigo-200 hover:bg-indigo-700 hover:shadow-lg transition-all duration-300">
                    <svg class="w-4 h-4 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('messages.create_new_resume') }}
                </a>
            @else
                <button onclick="openPlansModal()" class="flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-md shadow-orange-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    {{ __('messages.upgrade_plan') }}
                </button>
            @endcan
        </div>
    </x-slot>

    @php
        // يفضل نقل هذا المنطق إلى الـ Controller لاحقاً
        $user = Auth::user();
        $hasCoverLetterAccess = $user->plan?->has_cover_letter ?? false;
        $cvLimit = $user->plan?->cv_limit ?? 0;
        $resumesUsed = $user->resumes()->count();
        $usage = $cvLimit > 0 ? min(($resumesUsed / $cvLimit) * 100, 100) : 0;
        
        // تحديد لون شريط التقدم بناءً على الاستخدام
        $progressColor = $usage >= 90 ? 'bg-red-500' : ($usage >= 75 ? 'bg-amber-500' : 'bg-indigo-600');
        
        $hasPlan = $cvLimit > 0;
        $canCreate = $user->can('create', App\Models\Resume::class);
    @endphp

    <div class="py-10 bg-slate-50 min-h-screen" dir="rtl">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">

            {{-- تنبيه انتهاء الباقة --}}
            @if(!$canCreate)
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4 shadow-sm">
                    <div class="bg-amber-100 p-2 rounded-full text-amber-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-amber-900">{{ __('messages.no_creation_ability') }}</h4>
                        <p class="text-amber-700 text-sm mt-1 mb-3">
                            {{ $user->plan_expires_at && $user->plan_expires_at->isPast() ? __('messages.plan_expired') : __('messages.no_remaining_creations') }}
                        </p>
                        <button onclick="openPlansModal()" class="bg-amber-600 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-amber-700 transition shadow-sm">
                            {{ __('messages.upgrade_plan') }}
                        </button>
                    </div>
                </div>
            @endif

            {{-- الإحصائيات (Stats) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-indigo-50 p-2 rounded-lg text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">{{ __('messages.cv') }}</p>
                    </div>
                    <h3 class="text-2xl font-black text-slate-800">{{ $resumesUsed }}</h3>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-emerald-50 p-2 rounded-lg text-emerald-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">{{ __('messages.plan') }}</p>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">{{ $user->plan?->name ?? __('messages.none') }}</h3>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-blue-50 p-2 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">{{ __('messages.usage') }}</p>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">{{ $resumesUsed }} <span class="text-slate-400 text-sm">/ {{ $cvLimit }}</span></h3>
                    
                    @if($hasPlan)
                        <div class="mt-3 w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $progressColor }} transition-all duration-500" style="width: {{ $usage }}%"></div>
                        </div>
                    @endif
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-300">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="bg-purple-50 p-2 rounded-lg text-purple-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-sm font-medium text-slate-500">{{ __('messages.remaining_creations') }}</p>
                    </div>
                    <h3 class="text-2xl font-black {{ $user->resume_creations_remaining > 0 ? 'text-slate-800' : 'text-red-500' }}">{{ $user->resume_creations_remaining }}</h3>
                </div>
            </div>

            {{-- الإجراءات السريعة (Quick Actions) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($canCreate)
                    <a href="{{ route('templates.choose') }}" class="group bg-white p-6 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 text-start">
                        <div class="bg-indigo-50 p-4 rounded-full text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-1">{{ __('messages.create_new_resume') }}</h4>
                            <p class="text-sm text-slate-500">{{ __('messages.start_from_scratch') }}</p>
                        </div>
                    </a>
                @else
                    <button onclick="openPlansModal()" class="group bg-gradient-to-br from-white to-orange-50 p-6 rounded-2xl border border-orange-100 hover:border-orange-300 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 text-start w-full relative overflow-hidden">
                        <div class="bg-orange-100 p-4 rounded-full text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-1 flex items-center gap-2">
                                {{ __('messages.create_new_resume') }}
                                <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md">Pro</span>
                            </h4>
                            <p class="text-sm text-slate-500">{{ __('messages.need_subscription_to_create') }}</p>
                        </div>
                    </button>
                @endif

                @if($hasCoverLetterAccess)
                    <a href="{{ route('cover-letters.create') }}" class="group bg-white p-6 rounded-2xl border border-slate-100 hover:border-indigo-200 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 text-start">
                        <div class="bg-indigo-50 p-4 rounded-full text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-1">{{ __('messages.cover_letter') }}</h4>
                            <p class="text-sm text-slate-500">{{ __('messages.create_cover_letter') }}</p>
                        </div>
                    </a>
                @else
                    <button onclick="openPlansModal()" class="group bg-gradient-to-br from-white to-orange-50 p-6 rounded-2xl border border-orange-100 hover:border-orange-300 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex items-center gap-5 text-start w-full relative overflow-hidden">
                        <div class="bg-orange-100 p-4 rounded-full text-orange-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 mb-1 flex items-center gap-2">
                                {{ __('messages.cover_letter') }}
                                <span class="text-xs font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-md">Pro</span>
                            </h4>
                            <p class="text-sm text-slate-500">{{ __('messages.upgrade_to_access') }}</p>
                        </div>
                    </button>
                @endif
            </div>

            {{-- السير الذاتية السابقة --}}
            <div class="mt-8">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-xl font-bold text-slate-800">{{ __('messages.previous_resumes') }}</h4>
                </div>
                
                @if($resumes->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($resumes as $resume)
                            <div class="group bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300 flex flex-col h-full">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="w-12 h-16 bg-slate-100 rounded border border-slate-200 flex items-center justify-center text-slate-400 group-hover:bg-indigo-50 group-hover:text-indigo-400 transition-colors">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $resume->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $resume->is_published ? __('messages.completed') : __('messages.draft') }}
                                    </span>
                                </div>
                                <h5 class="font-bold text-slate-800 text-lg line-clamp-1 mb-1">{{ $resume->title }}</h5>
                                <p class="text-xs text-slate-400 mb-6 flex-1">{{ __('messages.last_updated') }}: {{ $resume->updated_at->diffForHumans() }}</p>
                                
                                {{-- أزرار الإجراءات (عرض/تعديل، تحميل، حذف) --}}
                                <div class="flex justify-between items-center pt-4 border-t border-slate-50">
                                    <a href="{{ route('resume.show', $resume->uuid) }}" class="text-indigo-600 font-semibold text-sm hover:text-indigo-800 flex items-center gap-1">
                                        {{ __('messages.view_edit') }}
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>
                                    <div class="flex items-center gap-1">
                                        <a href="{{ route('resume.download', $resume->uuid) }}" class="text-slate-400 hover:text-slate-800 bg-slate-50 hover:bg-slate-100 p-2 rounded-lg transition-colors" title="{{ __('messages.pdf') }}">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                        {{-- زر الحذف --}}
                                        <form action="{{ route('resume.destroy', $resume->uuid) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirm_delete_resume', [], app()->getLocale()) ?? 'هل أنت متأكد من حذف هذه السيرة؟' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600 bg-slate-50 hover:bg-red-50 p-2 rounded-lg transition-colors" title="{{ __('messages.delete', [], app()->getLocale()) }}">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- حالة عدم وجود سير ذاتية (Empty State) --}}
                    <div class="bg-white p-12 rounded-3xl border-2 border-dashed border-slate-200 text-center flex flex-col items-center justify-center">
                        <div class="bg-slate-50 p-6 rounded-full mb-4 text-slate-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-2">{{ __('messages.no_resumes_yet') }}</h3>
                        <p class="text-slate-500 text-sm max-w-md mb-6">{{ __('messages.no_resumes_description') }}</p>
                        @if($canCreate)
                            <a href="{{ route('templates.choose') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-full text-sm font-bold shadow-md hover:bg-indigo-700 transition-all hover:-translate-y-0.5">
                                + {{ __('messages.create_new_resume') }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- المودال (حاوية بسيطة بدون خلفية مزدوجة) --}}
    <div id="plansModal" class="hidden">
        <x-plans-modal closeAction="closePlansModal()" :resumeUuid="null" :currentLang="app()->getLocale()" />
    </div>

    {{-- سكريبت التحكم بالمودال (مباشر بدون @push) --}}
    <script>
        function openPlansModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        }
        function closePlansModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePlansModal();
        });
    </script>
</x-app-layout>