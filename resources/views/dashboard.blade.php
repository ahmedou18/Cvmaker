<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
            {{ __('messages.dashboard_title') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @php
                $user = Auth::user();
                $hasPrioritySupport = $user->plan?->priority_support ?? false;
                $hasCoverLetterAccess = $user->plan?->has_cover_letter ?? false;
                $supportCode = $user->getOrCreateSupportCode();
                $whatsappNumber = '22226121732';
            @endphp

            {{-- Welcome Card --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-2xl font-bold text-gray-800">{{ __('messages.welcome_back', ['name' => $user->name]) }}</h3>
                        <p class="text-gray-500 mt-2">{{ __('messages.manage_resumes') }}</p>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center">
                        @can('create', App\Models\Resume::class)
                            <a href="{{ route('templates.choose') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">
                                {{ __('messages.create_new_resume') }}
                            </a>
                        @else
                            <button type="button" onclick="openPlansModal()" class="bg-amber-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-amber-700 transition">
                                {{ __('messages.upgrade_to_create_more') }}
                            </button>
                        @endcan

                        @if($hasPrioritySupport)
                            @php
                                $planName = $user->plan?->name ?? __('messages.not_defined');
                                $planPrice = $user->plan?->formatted_price ?? __('messages.not_defined');
                                $whatsappMessage = urlencode("مرحباً، أحتاج إلى دعم فني.\nالباقة: {$planName}\nالسعر: {$planPrice}\nالحالة: نشطة\nرمز التحقق: {$supportCode}");
                            @endphp
                            <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-green-700 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                {{ __('messages.contact_whatsapp') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Quick Tools --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                {{-- Cover Letter Card --}}
                @if($hasCoverLetterAccess)
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-indigo-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">{{ __('messages.cover_letter') }}</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ __('messages.cover_letter_desc') }}</p>
                        <a href="{{ route('cover-letters.create') }}" class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-indigo-700 transition text-sm">
                            {{ __('messages.create_cover_letter') }}
                        </a>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow-sm opacity-70">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-gray-200 p-2 rounded-full">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-600">{{ __('messages.cover_letter') }}</h4>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">{{ __('messages.feature_only_paid') }}</p>
                        <button type="button" onclick="openPlansModal()" class="inline-block bg-gray-400 text-white px-5 py-2 rounded-lg font-bold text-sm cursor-pointer">
                            {{ __('messages.upgrade_to_access') }}
                        </button>
                    </div>
                @endif

                {{-- Support Card --}}
                @if($hasPrioritySupport)
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-green-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">{{ __('messages.priority_support') }}</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">{{ __('messages.support_code') }}</p>
                        <div class="bg-white border border-green-300 rounded-lg px-4 py-2 mb-3 inline-block">
                            <span class="text-xl font-mono font-bold text-green-700 tracking-wider">{{ $supportCode }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">{{ __('messages.share_code_with_support') }}</p>
                        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage ?? '' }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-green-700 transition text-sm">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            {{ __('messages.contact_whatsapp') }}
                        </a>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow-sm opacity-70">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-gray-200 p-2 rounded-full">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-600">{{ __('messages.priority_support') }}</h4>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">{{ __('messages.feature_only_paid') }}</p>
                        <button type="button" onclick="openPlansModal()" class="inline-block bg-gray-400 text-white px-5 py-2 rounded-lg font-bold text-sm">
                            {{ __('messages.upgrade_to_access') }}
                        </button>
                    </div>
                @endif
            </div>

            {{-- Subscription Usage Card --}}
            @php
                $cvLimit = $user->plan?->cv_limit ?? 0;
                $resumesUsed = $user->resumes()->count();
                $usagePercentage = $cvLimit > 0 ? min(($resumesUsed / $cvLimit) * 100, 100) : 0;
                $isLimitReached = $resumesUsed >= $cvLimit && $cvLimit > 0;
                $hasNoPlan = $cvLimit === 0;
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-lg font-bold text-gray-800">
                            @if($hasNoPlan) {{ __('messages.no_active_plan') }} @else {{ __('messages.subscription_usage') }} @endif
                        </h4>
                        <span class="text-sm font-medium text-gray-600">
                            @if($hasNoPlan) {{ __('messages.not_subscribed') }} @elseif($isLimitReached) {{ __('messages.limit_reached') }} @else {{ __('messages.remaining_cv', ['count' => $cvLimit - $resumesUsed]) }} @endif
                        </span>
                    </div>

                    @if($hasNoPlan)
                        <p class="text-sm text-gray-500 mb-4">{{ __('messages.subscribe_to_create') }}</p>
                        <div class="mt-4">
                            <button type="button" onclick="openPlansModal()" class="inline-block bg-blue-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-blue-700 transition">
                                {{ __('messages.view_plans') }}
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 mb-3">{!! __('messages.used_of', ['used' => $resumesUsed, 'limit' => $cvLimit]) !!}</p>
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="h-3 rounded-full transition-all duration-500 {{ $isLimitReached ? 'bg-red-500' : ($usagePercentage >= 75 ? 'bg-amber-500' : 'bg-green-500') }}" style="width: {{ $usagePercentage }}%;"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-500">{{ __('messages.percent_used', ['percent' => number_format($usagePercentage, 0)]) }}</span>
                            @if($isLimitReached)
                                <button type="button" onclick="openPlansModal()" class="inline-block bg-amber-600 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-amber-700 transition">
                                    {{ __('messages.upgrade_plan') }}
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-8 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-8 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <h4 class="text-lg font-bold text-gray-700 mb-4">{{ __('messages.previous_resumes') }}</h4>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @can('create', App\Models\Resume::class)
                    <a href="{{ route('resume.create') }}" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center p-8 hover:bg-gray-100 hover:border-blue-400 transition cursor-pointer min-h-[220px]">
                        <div class="bg-blue-100 p-3 rounded-full mb-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </div>
                        <span class="text-gray-600 font-medium">{{ __('messages.start_from_scratch') }}</span>
                    </a>
                @else
                    <div class="bg-gray-50 border-2 border-dashed border-amber-300 rounded-lg flex flex-col items-center justify-center p-8 min-h-[220px] opacity-60">
                        <div class="bg-amber-100 p-3 rounded-full mb-3">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <span class="text-gray-500 font-medium mb-2">{{ __('messages.max_reached') }}</span>
                        <button type="button" onclick="openPlansModal()" class="text-amber-600 font-semibold text-sm hover:underline">{{ __('messages.upgrade_now') }}</button>
                    </div>
                @endcan

                @foreach($resumes as $resume)
                    <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6 flex flex-col justify-between min-h-[220px] hover:shadow-md transition">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-lg font-bold text-gray-800">{{ $resume->title }}</h4>
                                @if($resume->is_published)
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ __('messages.completed') }}</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">{{ __('messages.draft') }}</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mb-1">{{ __('messages.last_modified', ['date' => $resume->updated_at->diffForHumans()]) }}</p>
                            <p class="text-sm text-gray-500">{{ __('messages.default_template') }}</p>
                        </div>
                        <div class="flex space-x-3 space-x-reverse mt-4 border-t pt-4">
                            <a href="{{ route('resume.show', $resume->uuid) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                {{ __('messages.view_edit') }}
                            </a>
                            <a href="#" class="text-gray-600 hover:text-gray-800 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                {{ __('messages.pdf') }}
                            </a>
                        </div>
                    </div>
                @endforeach
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