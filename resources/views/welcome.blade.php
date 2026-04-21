<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.page_title') ?? 'CVmaker - منصة السيرة الذاتية الذكية' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* نفس الـ styles السابقة، لا تغيير */
        body { font-family: 'Cairo', sans-serif; }
        .bg-soft-gradient { background: radial-gradient(circle at top right, #f0f7ff 0%, #ffffff 70%); }
        .glass-nav { background: rgba(255, 255, 255, 0.75); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.03); }
        .template-slide { transition: all 0.3s ease; }
        .template-slide:hover { transform: translateY(-8px); box-shadow: 0 20px 30px -10px rgba(0,0,0,0.1); }
        .swiper-button-next, .swiper-button-prev { color: #4f46e5 !important; background: white; width: 40px !important; height: 40px !important; border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .swiper-button-next:after, .swiper-button-prev:after { font-size: 18px !important; font-weight: bold; }
        .pricing-card-highlight { box-shadow: 0 30px 40px -15px rgba(79, 70, 229, 0.25); }
        .step-number { background: linear-gradient(135deg, #4f46e5, #7c3aed); box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.3); }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased overflow-x-hidden bg-soft-gradient">

    {{-- شريط التنقل --}}
    <nav class="glass-nav fixed top-0 w-full z-50" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-violet-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
                    <span class="font-extrabold text-2xl">C</span>
                </div>
                <span class="text-2xl font-extrabold tracking-tight text-slate-800">CV<span class="text-indigo-600">maker</span></span>
            </div>

            <div class="hidden md:flex items-center gap-6 text-sm font-semibold text-slate-700">
                <a href="#how-it-works" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_how_it_works') }}</a>
                <a href="#features" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_features') }}</a>
                <a href="#templates" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_templates') }}</a>
                <a href="#pricing" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_pricing') }}</a>
            </div>

            <div class="flex items-center gap-3">
                <div class="flex items-center gap-1 text-sm font-semibold">
                    <a href="{{ route('lang.switch', 'ar') }}" class="px-2 py-1 {{ app()->getLocale() == 'ar' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">AR</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="px-2 py-1 {{ app()->getLocale() == 'en' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">EN</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('lang.switch', 'fr') }}" class="px-2 py-1 {{ app()->getLocale() == 'fr' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">FR</a>
                </div>

                @auth
                <div class="relative" x-data="notificationComponent()">
                    <button @click="open = !open" class="relative text-gray-600 hover:text-indigo-600 transition-colors p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute top-0 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"></span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 py-2 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}">
                        <div class="flex justify-between items-center px-4 py-2 border-b">
                            <h4 class="font-bold text-sm">{{ __('messages.notifications') }}</h4>
                            <button x-show="notifications.length > 0" @click="markAllAsRead()" class="text-xs text-indigo-600 hover:underline">{{ __('messages.mark_all_read') }}</button>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <template x-if="notifications.length > 0">
                                <div><template x-for="notification in notifications" :key="notification.id"><div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 flex justify-between items-start"><div class="flex-1"><p class="text-xs text-gray-800" x-text="notification.message"></p><span class="text-[10px] text-gray-400" x-text="notification.created_at"></span></div><button @click="markAsRead(notification.id)" class="text-indigo-500 hover:text-indigo-700 text-xs mx-2">✓</button></div></template></div>
                            </template>
                            <p x-show="notifications.length === 0" class="px-4 py-6 text-center text-xs text-gray-400">{{ __('messages.no_notifications') }}</p>
                        </div>
                        <div class="border-t px-4 py-2"><a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:underline">{{ __('messages.view_all_notifications') }}</a></div>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="bg-slate-900 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-indigo-700 transition shadow-md">{{ __('messages.nav_dashboard') }}</a>
                @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-indigo-600">{{ __('messages.nav_login') }}</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-indigo-700 transition shadow-md">{{ __('messages.nav_register') }}</a>
                @endauth
            </div>

            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-transition.opacity.duration.300ms class="md:hidden glass-nav absolute top-20 inset-x-0 py-6 px-6 border-t border-white/20 shadow-lg">
            <div class="flex flex-col gap-5 text-base font-semibold text-slate-700">
                <a href="#how-it-works" @click="mobileMenuOpen = false">{{ __('messages.nav_how_it_works') }}</a>
                <a href="#features" @click="mobileMenuOpen = false">{{ __('messages.nav_features') }}</a>
                <a href="#templates" @click="mobileMenuOpen = false">{{ __('messages.nav_templates') }}</a>
                <a href="#pricing" @click="mobileMenuOpen = false">{{ __('messages.nav_pricing') }}</a>
                @auth
                <a href="{{ route('dashboard') }}" class="bg-slate-900 text-white text-center px-6 py-3 rounded-full">{{ __('messages.nav_dashboard') }}</a>
                @else
                <a href="{{ route('login') }}" class="hover:text-indigo-600">{{ __('messages.nav_login') }}</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white text-center px-6 py-3 rounded-full">{{ __('messages.nav_register') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative pt-36 md:pt-44 pb-16 md:pb-24 px-6 text-center">
        <div class="max-w-6xl mx-auto">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-700 text-sm font-bold mb-8 border border-indigo-100" data-aos="fade-down">
                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span></span>
                <span>✨ مدعوم بالذكاء الاصطناعي - تحديث 2026</span>
            </div>

            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-slate-900 mb-6 leading-[1.2]" data-aos="fade-up">
                {{ __('messages.hero_title_1') ?? 'أنشئ سيرة ذاتية احترافية في دقيقتين' }} <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600">{{ __('messages.hero_title_2') ?? 'باستخدام الذكاء الاصطناعي' }}</span>
            </h1>

            <p class="text-lg md:text-xl text-slate-500 mb-6 max-w-3xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="50">{{ __('messages.hero_description') }}</p>

            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-16" data-aos="fade-up" data-aos-delay="100">
                @auth
                <a href="{{ route('templates.choose') }}" class="bg-[#10a37f] hover:bg-[#0d8a6a] text-white text-lg font-bold px-10 py-4 rounded-full shadow-xl shadow-teal-200 transition transform hover:scale-105">{{ __('messages.btn_create_now') ?? 'اصنع سيرتك الآن' }}</a>
                @else
                <a href="{{ route('register') }}" class="bg-[#10a37f] hover:bg-[#0d8a6a] text-white text-lg font-bold px-10 py-4 rounded-full shadow-xl shadow-teal-200 transition transform hover:scale-105">{{ __('messages.btn_create_now') ?? 'ابدأ مجاناً' }}</a>
                @endauth
                <a href="#templates" class="bg-white border border-slate-200 text-slate-700 text-lg font-bold px-10 py-4 rounded-full hover:bg-slate-50 transition shadow-sm">{{ __('messages.btn_browse_templates') }}</a>
            </div>

            <div class="relative max-w-5xl mx-auto rounded-3xl border border-white/50 shadow-2xl overflow-hidden bg-white/40 backdrop-blur-sm" data-aos="zoom-in-up">
                <img src="https://placehold.co/1200x700/e2e8f0/475569?text=CV+Builder+Interface" alt="CV Builder Interface" class="w-full h-auto">
                <div class="absolute top-5 right-5 md:top-8 md:right-8 bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-5 py-2.5 rounded-full text-sm font-bold flex items-center gap-2 shadow-lg"><span>⚡</span> اقتراحات ذكية فورية</div>
            </div>
        </div>
    </section>

    {{-- Problem & Solution --}}
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div data-aos="fade-right">
                    <div class="inline-block px-4 py-1 bg-red-50 text-red-600 rounded-full text-sm font-bold mb-4">{{ __('messages.problem_title') }}</div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">{{ __('messages.problem_heading') }}</h2>
                    <ul class="space-y-4 text-lg text-slate-600">
                        <li class="flex items-start gap-3"><span class="text-red-500 text-xl mt-1">❌</span><span><strong class="text-slate-800">{{ __('messages.problem_1') }}</strong></span></li>
                        <li class="flex items-start gap-3"><span class="text-red-500 text-xl mt-1">❌</span><span><strong class="text-slate-800">{{ __('messages.problem_2') }}</strong></span></li>
                        <li class="flex items-start gap-3"><span class="text-red-500 text-xl mt-1">❌</span><span><strong class="text-slate-800">{{ __('messages.problem_3') }}</strong></span></li>
                    </ul>
                </div>
                <div data-aos="fade-left">
                    <div class="inline-block px-4 py-1 bg-green-50 text-green-600 rounded-full text-sm font-bold mb-4">{{ __('messages.solution_title') }}</div>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6">{{ __('messages.solution_heading') }}</h2>
                    <ul class="space-y-4 text-lg text-slate-600">
                        <li class="flex items-start gap-3"><span class="text-green-500 text-xl mt-1">✔️</span><span><strong class="text-slate-800">{{ __('messages.solution_1') }}</strong></span></li>
                        <li class="flex items-start gap-3"><span class="text-green-500 text-xl mt-1">✔️</span><span><strong class="text-slate-800">{{ __('messages.solution_2') }}</strong></span></li>
                        <li class="flex items-start gap-3"><span class="text-green-500 text-xl mt-1">✔️</span><span><strong class="text-slate-800">{{ __('messages.solution_3') }}</strong></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="py-20 bg-slate-50/80">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-14" data-aos="fade-up">
                <span class="text-indigo-600 font-bold text-sm uppercase tracking-wider">{{ __('messages.how_it_works_badge') }}</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mt-2 mb-4">{{ __('messages.how_it_works_title') }}</h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto">{{ __('messages.how_it_works_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="relative text-center" data-aos="fade-up" data-aos-delay="0">
                    <div class="step-number w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-6">1</div>
                    <h3 class="text-xl font-bold mb-3">{{ __('messages.step_1_title') }}</h3>
                    <p class="text-slate-500">{{ __('messages.step_1_desc') }}</p>
                </div>
                <div class="relative text-center" data-aos="fade-up" data-aos-delay="100">
                    <div class="step-number w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-6">2</div>
                    <h3 class="text-xl font-bold mb-3">{{ __('messages.step_2_title') }}</h3>
                    <p class="text-slate-500">{{ __('messages.step_2_desc') }}</p>
                </div>
                <div class="relative text-center" data-aos="fade-up" data-aos-delay="200">
                    <div class="step-number w-16 h-16 mx-auto rounded-2xl flex items-center justify-center text-white text-2xl font-bold mb-6">3</div>
                    <h3 class="text-xl font-bold mb-3">{{ __('messages.step_3_title') }}</h3>
                    <p class="text-slate-500">{{ __('messages.step_3_desc') }}</p>
                </div>
            </div>

            <div class="mt-14 text-center">
                <a href="{{ auth()->check() ? route('templates.choose') : route('register') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-full shadow-lg shadow-indigo-200 transition transform hover:scale-105">
                    <span>{{ __('messages.try_now_free') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-14" data-aos="fade-up">
                <span class="text-indigo-600 font-bold text-sm uppercase tracking-wider">{{ __('messages.features_badge') }}</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mt-2 mb-4">{{ __('messages.features_title') }}</h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto">{{ __('messages.features_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $features = [
                        ['icon' => '📄', 'key' => 'feature_1'],
                        ['icon' => '📥', 'key' => 'feature_2'],
                        ['icon' => '🌐', 'key' => 'feature_3'],
                        ['icon' => '✏️', 'key' => 'feature_4'],
                        ['icon' => '🤖', 'key' => 'feature_5'],
                        ['icon' => '🔒', 'key' => 'feature_6'],
                        ['icon' => '📊', 'key' => 'feature_7'],
                        ['icon' => '⚡', 'key' => 'feature_8'],
                    ];
                @endphp
                @foreach($features as $index => $feature)
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 hover:shadow-md transition" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                    <div class="text-3xl mb-4">{{ $feature['icon'] }}</div>
                    <h3 class="text-lg font-bold mb-2">{{ __("messages.{$feature['key']}_title") }}</h3>
                    <p class="text-sm text-slate-500">{{ __("messages.{$feature['key']}_desc") }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Social Proof --}}
    <section class="py-16 bg-indigo-50/50">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <div data-aos="fade-up">
                <span class="text-indigo-600 font-bold text-sm uppercase tracking-wider">{{ __('messages.trust_badge') }}</span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-2 mb-6">{{ __('messages.trust_title') }}</h2>
                <p class="text-slate-600 text-lg max-w-2xl mx-auto mb-10">{{ __('messages.trust_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl shadow-sm" data-aos="fade-up" data-aos-delay="0">
                    <div class="text-4xl mb-2">⭐️⭐️⭐️⭐️⭐️</div>
                    <p class="text-slate-700 italic mb-4">{{ __('messages.testimonial_1_text') }}</p>
                    <p class="font-bold">{{ __('messages.testimonial_1_name') }}</p>
                    <p class="text-sm text-slate-400">{{ __('messages.testimonial_1_title') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm" data-aos="fade-up" data-aos-delay="100">
                    <div class="text-4xl mb-2">⭐️⭐️⭐️⭐️⭐️</div>
                    <p class="text-slate-700 italic mb-4">{{ __('messages.testimonial_2_text') }}</p>
                    <p class="font-bold">{{ __('messages.testimonial_2_name') }}</p>
                    <p class="text-sm text-slate-400">{{ __('messages.testimonial_2_title') }}</p>
                </div>
                <div class="bg-white p-6 rounded-2xl shadow-sm" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-4xl mb-2">⭐️⭐️⭐️⭐️⭐️</div>
                    <p class="text-slate-700 italic mb-4">{{ __('messages.testimonial_3_text') }}</p>
                    <p class="font-bold">{{ __('messages.testimonial_3_name') }}</p>
                    <p class="text-sm text-slate-400">{{ __('messages.testimonial_3_title') }}</p>
                </div>
            </div>

            <div class="mt-12 flex flex-wrap justify-center items-center gap-8 opacity-50">
                <span class="text-xl font-bold text-slate-600">MICROSOFT</span>
                <span class="text-xl font-bold text-slate-600">ORANGE</span>
                <span class="text-xl font-bold text-slate-600">DELL</span>
                <span class="text-xl font-bold text-slate-600">ACCENTURE</span>
            </div>
        </div>
    </section>

    {{-- Templates Slider --}}
    <section id="templates" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-12" data-aos="fade-up">
                <span class="text-indigo-600 font-bold text-sm uppercase tracking-wider">{{ __('messages.templates_badge') }}</span>
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mt-2 mb-4">{{ __('messages.templates_title') }}</h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto">{{ __('messages.templates_subtitle') }}</p>
            </div>

            <div class="relative" data-aos="fade-up" data-aos-delay="100">
                <div class="swiper templates-swiper">
                    <div class="swiper-wrapper pb-8">
                        @forelse($templates ?? [] as $template)
                        <div class="swiper-slide w-72">
                            <div class="template-slide bg-white rounded-2xl p-4 border border-slate-100 shadow-md">
                                <div class="aspect-[3/4] bg-slate-100 rounded-xl overflow-hidden mb-4 relative">
                                    @if($template->thumbnail)
                                        <img src="{{ asset($template->thumbnail) }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-slate-200 to-slate-300 flex items-center justify-center text-slate-500 font-bold">{{ __('messages.template_preview') }}</div>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-slate-800">{{ $template->name }}</h3>
                                @if(isset($template->is_premium) && $template->is_premium)
                                    <span class="inline-block mt-2 text-xs bg-amber-100 text-amber-700 px-3 py-1 rounded-full font-semibold">{{ __('messages.template_premium') }}</span>
                                @else
                                    <span class="inline-block mt-2 text-xs bg-emerald-100 text-emerald-700 px-3 py-1 rounded-full font-semibold">{{ __('messages.template_free') }}</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="swiper-slide w-full text-center py-20">
                            <p class="text-slate-500">{{ __('messages.no_templates_message') }}</p>
                        </div>
                        @endforelse
                    </div>
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-pagination !bottom-0"></div>
                </div>
            </div>

            <div class="mt-12 text-center">
                <a href="{{ route('templates.choose') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-8 py-4 rounded-full shadow-lg shadow-indigo-200 transition transform hover:scale-105">
                    <span>{{ __('messages.btn_start_template') }}</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-24 bg-slate-50/80">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-4">{{ __('messages.pricing_title') ?? 'اختر باقتك المناسبة' }}</h2>
                <p class="text-slate-500 text-lg">{{ __('messages.pricing_subtitle') ?? 'جميع الباقات تشمل فترة تجريبية مجانية، لا حاجة لبطاقة ائتمان.' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                @foreach($plans as $plan)
                    @php $isPopular = $plan->is_popular; @endphp
                    <div class="relative bg-white p-8 md:p-10 rounded-[2.5rem] border transition-all duration-500 flex flex-col {{ $isPopular ? 'border-indigo-200 ring-1 ring-indigo-500 scale-105 z-10 pricing-card-highlight' : 'border-gray-100 shadow-sm hover:shadow-xl' }}" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        @if($isPopular)
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-md">{{ __('messages.most_popular') }}</span>
                        @endif

                        <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                        <div class="flex items-baseline gap-1 mb-2">
                            <span class="text-4xl font-extrabold text-slate-900">{{ rtrim(rtrim(number_format($plan->price, 2), '0'), '.') }}</span>
                            <span class="text-slate-400 font-medium">{{ __('messages.currency') ?? 'ريال' }}</span>
                        </div>
                        <p class="text-sm text-slate-400 mb-6">{{ $plan->duration_in_days }} {{ __('messages.day') }}</p>

                        <ul class="space-y-4 mb-8 flex-grow text-slate-600 text-sm">
                            <li class="flex items-center gap-3"><span class="text-green-500 text-lg">✔️</span><span>{{ $plan->cv_limit }} {{ __('messages.cv') }}</span></li>
                            <li class="flex items-center gap-3">@if($plan->ai_credits > 0)<span class="text-green-500 text-lg">✔️</span><span>{{ $plan->ai_credits }} {{ __('messages.ai_credits_label') }}</span>@else<span class="text-gray-400 text-lg">🔒</span><span class="text-gray-400">{{ __('messages.no_ai_label') }}</span>@endif</li>
                            <li class="flex items-center gap-3">@if($plan->remove_watermark)<span class="text-green-500 text-lg">✔️</span><span>{{ __('messages.no_watermark_label') }}</span>@else<span class="text-red-500 text-lg">❌</span><span>{{ __('messages.with_watermark_label') }}</span>@endif</li>
                            <li class="flex items-center gap-3">@if($plan->has_cover_letter)<span class="text-green-500 text-lg">✔️</span><span>{{ __('messages.cover_letter_label') }}</span>@else<span class="text-gray-400 text-lg">🔒</span><span class="text-gray-400">{{ __('messages.cover_letter_label') }}</span>@endif</li>
                            <li class="flex items-center gap-3">@if($plan->priority_support)<span class="text-green-500 text-lg">✔️</span><span>{{ __('messages.priority_support_label') }}</span>@else<span class="text-gray-400 text-lg">🔒</span><span class="text-gray-400">{{ __('messages.normal_support_label') }}</span>@endif</li>
                        </ul>

                        @auth
                            <form action="{{ route('payment.checkout', $plan->slug) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-4 rounded-full font-bold transition-all {{ $isPopular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('messages.upgrade_btn') }}</button>
                            </form>
                        @else
                            <a href="{{ route('register', ['plan' => $plan->id]) }}" class="w-full text-center py-4 rounded-full font-bold transition-all {{ $isPopular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $plan->price == 0 ? __('messages.free_plan_btn') : __('messages.choose_plan_btn') }}</a>
                        @endauth
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="py-16 bg-gradient-to-r from-indigo-600 to-violet-600 text-white">
        <div class="max-w-4xl mx-auto text-center px-6">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-4" data-aos="fade-up">{{ __('messages.final_cta_title') }}</h2>
            <p class="text-lg text-indigo-100 mb-8" data-aos="fade-up" data-aos-delay="50">{{ __('messages.final_cta_subtitle') }}</p>
            <div data-aos="fade-up" data-aos-delay="100">
                <a href="{{ auth()->check() ? route('templates.choose') : route('register') }}" class="inline-block bg-white text-indigo-600 font-bold px-10 py-4 rounded-full shadow-xl hover:bg-indigo-50 transition transform hover:scale-105 text-lg">{{ __('messages.final_cta_btn') }}</a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-8 border-t border-slate-100 bg-white">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-500">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white"><span class="font-bold text-lg">C</span></div>
                <span class="font-bold text-slate-700">CV<span class="text-indigo-600">maker</span></span>
            </div>
            <p>© {{ date('Y') }} {{ __('messages.footer_copyright') }}</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-indigo-600">{{ __('messages.footer_terms') }}</a>
                <a href="#" class="hover:text-indigo-600">{{ __('messages.footer_privacy') }}</a>
                <a href="#" class="hover:text-indigo-600">{{ __('messages.footer_contact') }}</a>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        function notificationComponent() {
            return {
                open: false,
                unreadCount: {{ auth()->check() ? auth()->user()->unreadNotifications->count() : 0 }},
                notifications: @json(auth()->check() ? auth()->user()->unreadNotifications->take(5)->map(fn($n) => ['id' => $n->id, 'message' => $n->data['message'] ?? __('messages.no_message'), 'created_at' => $n->created_at->diffForHumans()]) : []),
                markAsRead(id) {
                    fetch('/notifications/' + id + '/mark-as-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' } }).then(r => { if(r.ok) { this.unreadCount--; this.notifications = this.notifications.filter(n => n.id !== id); } });
                },
                markAllAsRead() {
                    fetch('/notifications/mark-all-as-read', { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Content-Type': 'application/json' } }).then(r => { if(r.ok) { this.unreadCount = 0; this.notifications = []; } });
                }
            }
        }
        AOS.init({ once: true, duration: 800, offset: 80 });
        new Swiper('.templates-swiper', { slidesPerView: 1, spaceBetween: 16, loop: true, autoplay: { delay: 3000, disableOnInteraction: false }, pagination: { el: '.swiper-pagination', clickable: true }, navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' }, breakpoints: { 640: { slidesPerView: 2, spaceBetween: 20 }, 1024: { slidesPerView: 3, spaceBetween: 24 }, 1280: { slidesPerView: 4, spaceBetween: 30 } } });
    </script>
</body>
</html>