<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.page_title') ?? 'CVmaker - منصة السيرة الذاتية الذكية' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #fafafa; }
        .bg-grid-pattern { background-image: radial-gradient(#e2e8f0 1px, transparent 1px); background-size: 32px 32px; }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.6); }
        [x-cloak] { display: none !important; }

        /* تأثير المنظور ثلاثي الأبعاد للمعاينة */
        .perspective-container { perspective: 2000px; }
        .cv-mockup-wrapper {
            transform: rotateX(15deg) rotateY(-15deg) rotateZ(5deg);
            transition: all 0.5s ease-in-out;
        }
        .cv-mockup-wrapper:hover {
            transform: rotateX(5deg) rotateY(-5deg) rotateZ(2deg);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 4s ease-in-out infinite; }

        @keyframes bounce-slow {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        .animate-bounce-slow { animation: bounce-slow 3s ease-in-out infinite; }

        .typing-text {
            overflow: hidden;
            border-right: 2px solid #4f46e5;
            white-space: nowrap;
            animation: typing 3.5s steps(30, end) infinite, blink-caret .75s step-end infinite;
        }
        @keyframes typing { from { width: 0 } to { width: 100% } }
        @keyframes blink-caret { from, to { border-color: transparent } 50% { border-color: #4f46e5; } }

        /* تحسينات السلايدر الاحترافي */
        .templates-swiper .swiper-slide {
            opacity: 0.5;
            transform: scale(0.9);
            transition: all 0.5s ease;
        }
        .templates-swiper .swiper-slide-active {
            opacity: 1;
            transform: scale(1);
        }
        .swiper-pagination-bullet-active {
            background: #4f46e5 !important;
            width: 24px !important;
            border-radius: 5px !important;
        }
        .template-card:hover {
            border-color: rgba(79, 70, 229, 0.2);
        }
    </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden">

    {{-- ========== شريط التنقل ========== --}}
    <nav class="glass-nav fixed top-0 w-full z-50 transition-all" 
         x-data="{ mobileMenuOpen: false, scrolled: false }" 
         x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })" 
         :class="scrolled ? 'shadow-sm' : ''">
        <div class="max-w-7xl mx-auto px-6" :class="scrolled ? 'h-14' : 'h-20'" style="transition: height 0.3s ease;">
            <div class="flex justify-between items-center h-full">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-md"><span class="font-extrabold text-xl">C</span></div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-800">CV<span class="text-indigo-600">maker</span></span>
                </div>

                <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <a href="#how-it-works" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_how_it_works') }}</a>
                    <a href="#features" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_features') }}</a>
                    <a href="#templates" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_templates') }}</a>
                    <a href="#pricing" class="hover:text-indigo-600 transition-colors">{{ __('messages.nav_pricing') }}</a>
                </div>

                <div class="hidden md:flex items-center gap-4">
                    <div class="flex items-center bg-slate-100/50 rounded-full p-1 border border-slate-200/50">
                        <a href="{{ route('lang.switch', 'ar') }}" class="px-3 py-1 text-xs font-bold rounded-full transition-all {{ app()->getLocale() == 'ar' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">AR</a>
                        <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 text-xs font-bold rounded-full transition-all {{ app()->getLocale() == 'en' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">EN</a>
                        <a href="{{ route('lang.switch', 'fr') }}" class="px-3 py-1 text-xs font-bold rounded-full transition-all {{ app()->getLocale() == 'fr' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">FR</a>
                    </div>

                    @auth
                    <a href="{{ route('dashboard') }}" class="bg-slate-900 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-slate-800 transition shadow-sm">{{ __('messages.nav_dashboard') }}</a>
                    @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-indigo-600 transition-colors">{{ __('messages.nav_login') }}</a>
                    <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-5 py-2.5 rounded-full text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">{{ __('messages.nav_register') }}</a>
                    @endauth
                </div>

                <div class="md:hidden flex items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-white border-t border-slate-100 shadow-xl absolute w-full">
            <div class="flex flex-col p-6 gap-4 text-base font-semibold text-slate-700">
                <a href="#how-it-works" @click="mobileMenuOpen = false" class="p-2 rounded-lg hover:bg-slate-50">{{ __('messages.nav_how_it_works') }}</a>
                <a href="#features" @click="mobileMenuOpen = false" class="p-2 rounded-lg hover:bg-slate-50">{{ __('messages.nav_features') }}</a>
                <a href="#templates" @click="mobileMenuOpen = false" class="p-2 rounded-lg hover:bg-slate-50">{{ __('messages.nav_templates') }}</a>
                <a href="#pricing" @click="mobileMenuOpen = false" class="p-2 rounded-lg hover:bg-slate-50">{{ __('messages.nav_pricing') }}</a>
                <div class="flex items-center justify-center bg-slate-100/50 rounded-full p-1 border border-slate-200/50 w-fit mx-auto my-2">
                    <a href="{{ route('lang.switch', 'ar') }}" class="px-4 py-1.5 text-sm font-bold rounded-full transition-all {{ app()->getLocale() == 'ar' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">AR</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="px-4 py-1.5 text-sm font-bold rounded-full transition-all {{ app()->getLocale() == 'en' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">EN</a>
                    <a href="{{ route('lang.switch', 'fr') }}" class="px-4 py-1.5 text-sm font-bold rounded-full transition-all {{ app()->getLocale() == 'fr' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">FR</a>
                </div>
                <hr class="border-slate-100 my-2">
                @auth
                <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white text-center px-6 py-3 rounded-xl">{{ __('messages.nav_dashboard') }}</a>
                @else
                <a href="{{ route('login') }}" class="text-center p-2">{{ __('messages.nav_login') }}</a>
                <a href="{{ route('register') }}" class="bg-indigo-600 text-white text-center px-6 py-3 rounded-xl">{{ __('messages.nav_register') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="relative pt-32 md:pt-48 pb-20 md:pb-32 px-6 overflow-hidden">
        <div class="absolute inset-0 bg-grid-pattern opacity-[0.3] -z-10"></div>
        <div class="absolute top-0 right-0 -translate-y-12 translate-x-1/3 w-[600px] h-[600px] bg-indigo-100 rounded-full blur-[100px] opacity-50 -z-10"></div>
        <div class="max-w-5xl mx-auto text-center z-10 relative">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50/80 text-indigo-700 text-xs font-bold mb-8 border border-indigo-100/50 backdrop-blur-sm" data-aos="fade-down">
                <span class="relative flex h-2 w-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span><span class="relative inline-flex rounded-full h-2 w-2 bg-indigo-500"></span></span>
                <span>تحديث 2026 - مدعوم بالذكاء الاصطناعي</span>
            </div>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-slate-900 mb-6 leading-[1.1] tracking-tight" data-aos="fade-up">
                {{ __('messages.hero_title_1') ?? 'أنشئ سيرة ذاتية احترافية' }} <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-violet-500">{{ __('messages.hero_title_2') ?? 'في دقائق معدودة' }}</span>
            </h1>
            <p class="text-lg md:text-xl text-slate-500 mb-10 max-w-2xl mx-auto leading-relaxed" data-aos="fade-up" data-aos-delay="100">
                {{ __('messages.hero_description') ?? 'منصة ذكية تساعدك على تصميم سيرة ذاتية تبرز مهاراتك وتزيد من فرص قبولك في وظيفة أحلامك.' }}
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-20" data-aos="fade-up" data-aos-delay="200">
                @auth
                <a href="{{ route('templates.choose') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-bold px-8 py-4 rounded-full shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-1">{{ __('messages.btn_create_now') ?? 'اصنع سيرتك الآن' }}</a>
                @else
                <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-lg font-bold px-8 py-4 rounded-full shadow-lg shadow-indigo-500/30 transition-all hover:-translate-y-1">{{ __('messages.btn_create_now') ?? 'ابدأ مجاناً' }}</a>
                @endauth
                <a href="#templates" class="bg-white border border-slate-200 text-slate-700 text-lg font-bold px-8 py-4 rounded-full hover:bg-slate-50 hover:border-slate-300 transition-all">{{ __('messages.btn_browse_templates') }}</a>
            </div>

            <!-- واجهة بناء السيرة الذاتية المائلة -->
            <div class="relative w-full max-w-4xl mx-auto perspective-container" data-aos="zoom-in-up" data-aos-delay="300">
                <div class="cv-mockup-wrapper bg-white rounded-2xl shadow-2xl border border-slate-200/50 p-4 md:p-8 overflow-hidden">
                    <div class="h-8 bg-slate-50 rounded-t-xl flex items-center px-4 gap-2 mb-4 -mx-4 md:-mx-8 -mt-4 md:-mt-8 border-b border-slate-100">
                        <div class="w-3 h-3 rounded-full bg-rose-400"></div>
                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                        <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                    </div>
                    <div class="flex flex-col md:flex-row gap-6 text-left" dir="rtl">
                        <div class="w-full md:w-1/3 border-r border-indigo-100 pr-4">
                            <div class="w-20 h-20 bg-indigo-50 rounded-lg mb-4"></div>
                            <div class="h-4 bg-indigo-100 rounded w-3/4 mb-2"></div>
                            <div class="h-4 bg-indigo-100 rounded w-1/2 mb-6"></div>
                            <div class="space-y-3">
                                <div class="h-2 bg-indigo-50 rounded w-full"></div>
                                <div class="h-2 bg-indigo-50 rounded w-full"></div>
                                <div class="h-2 bg-indigo-50 rounded w-2/3"></div>
                            </div>
                        </div>
                        <div class="w-full md:w-2/3 relative">
                            <div class="absolute -top-2 -right-2 bg-indigo-600 text-white p-2 rounded-lg shadow-lg animate-bounce-slow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            </div>
                            <div class="mb-6">
                                <div class="h-6 bg-indigo-200 rounded w-1/3 mb-4"></div>
                                <div class="space-y-3">
                                    <p class="text-sm text-indigo-600 font-mono typing-text">{{ __('messages.ai_generating') ?? 'جاري التوليد...' }}</p>
                                    <div class="h-3 bg-gray-100 rounded w-full animate-pulse"></div>
                                    <div class="h-3 bg-gray-100 rounded w-full animate-pulse delay-75"></div>
                                    <div class="h-3 bg-gray-100 rounded w-4/5 animate-pulse delay-150"></div>
                                </div>
                            </div>
                            <div class="border-t border-gray-50 pt-4">
                                <div class="flex items-center gap-2 mb-3"><span class="w-2 h-2 bg-green-500 rounded-full"></span><div class="h-4 bg-gray-200 rounded w-1/4"></div></div>
                                <div class="h-3 bg-gray-100 rounded w-full mb-2"></div>
                                <div class="h-3 bg-gray-100 rounded w-5/6"></div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -left-6 bg-white p-3 rounded-lg shadow-xl border border-indigo-100 flex items-center gap-3 animate-float">
                        <div class="bg-indigo-100 p-2 rounded-full"><span class="text-indigo-600 text-xs font-bold">✨ AI</span></div>
                        <div class="text-xs text-slate-700 font-semibold">ATS Score Optimized: 98%</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Problem & Solution --}}
    <section class="py-24 bg-white relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <span class="text-slate-400 font-bold text-sm tracking-widest uppercase mb-2 block">{{ __('messages.problem_title') ?? 'الطريقة القديمة' }}</span>
                    <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-8">{{ __('messages.problem_heading') ?? 'تنسيق السيرة الذاتية متعب ومعقد' }}</h2>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4"><div class="w-6 h-6 rounded-full bg-rose-50 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><span class="text-slate-600 leading-relaxed">{{ __('messages.problem_1') ?? 'قضاء ساعات في ضبط الهوامش والخطوط.' }}</span></li>
                        <li class="flex items-start gap-4"><div class="w-6 h-6 rounded-full bg-rose-50 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></div><span class="text-slate-600 leading-relaxed">{{ __('messages.problem_2') ?? 'صعوبة صياغة الجمل الاحترافية.' }}</span></li>
                    </ul>
                </div>
                <div class="bg-indigo-50/50 p-8 md:p-12 rounded-[2rem] border border-indigo-100" data-aos="fade-left">
                    <span class="text-indigo-600 font-bold text-sm tracking-widest uppercase mb-2 block">{{ __('messages.solution_title') ?? 'الحل الذكي' }}</span>
                    <h2 class="text-3xl font-extrabold text-slate-800 mb-8">{{ __('messages.solution_heading') ?? 'دع الذكاء الاصطناعي يقوم بالعمل' }}</h2>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4"><div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div><span class="text-slate-700 font-medium leading-relaxed">{{ __('messages.solution_1') ?? 'قوالب جاهزة ومهنية بضغطة زر.' }}</span></li>
                        <li class="flex items-start gap-4"><div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5"><svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg></div><span class="text-slate-700 font-medium leading-relaxed">{{ __('messages.solution_2') ?? 'محتوى مقترح آلياً يناسب تخصصك.' }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- Features --}}
    <section id="features" class="py-24 bg-slate-50/50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-4">{{ __('messages.features_title') }}</h2>
                <p class="text-slate-500 text-lg max-w-2xl mx-auto">{{ __('messages.features_subtitle') }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @php
                    $featureKeys = [
                        ['icon' => '📄', 'title_key' => 'feature_1_title', 'desc_key' => 'feature_1_desc'],
                        ['icon' => '📥', 'title_key' => 'feature_2_title', 'desc_key' => 'feature_2_desc'],
                        ['icon' => '🌐', 'title_key' => 'feature_3_title', 'desc_key' => 'feature_3_desc'],
                        ['icon' => '🤖', 'title_key' => 'feature_4_title', 'desc_key' => 'feature_4_desc'],
                    ];
                @endphp
                @foreach($featureKeys as $index => $feature)
                    <div class="bg-white p-6 rounded-2xl border border-slate-100 hover:shadow-xl transition-all duration-300 group" data-aos="fade-up" data-aos-delay="{{ $index * 50 }}">
                        <div class="text-3xl mb-4">{{ $feature['icon'] }}</div>
                        <h3 class="text-lg font-bold mb-2">{{ __("messages.{$feature['title_key']}") }}</h3>
                        <p class="text-sm text-slate-500">{{ __("messages.{$feature['desc_key']}") }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Templates Slider - النسخة الاحترافية المحدثة --}}
    <section id="templates" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6" data-aos="fade-up">
                <div class="max-w-xl">
                    <span class="text-indigo-600 font-bold text-sm tracking-widest uppercase mb-2 block">المظهر المهني</span>
                    <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-4">{{ __('messages.templates_title') }}</h2>
                    <p class="text-slate-500 text-lg leading-relaxed">{{ __('messages.templates_subtitle') }}</p>
                </div>
                <div class="flex gap-3 mb-2">
                    <button class="swiper-nav-prev w-12 h-12 rounded-full border border-slate-200 flex items-center justify-center hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all duration-300 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button class="swiper-nav-next w-12 h-12 rounded-full border border-slate-200 flex items-center justify-center hover:bg-indigo-600 hover:text-white hover:border-indigo-600 transition-all duration-300 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <div class="swiper templates-swiper !overflow-visible">
                <div class="swiper-wrapper pb-12">
                    @forelse($templates ?? [] as $template)
                    <div class="swiper-slide w-[300px] sm:w-[380px] transition-all duration-500">
                        <div class="template-card group relative rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-500 overflow-hidden">
                            <div class="aspect-[1/1.4] bg-slate-50 relative overflow-hidden">
                                @if($template->thumbnail)
                                    <img src="{{ asset($template->thumbnail) }}" alt="{{ $template->name }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-100 to-indigo-50 flex flex-col items-center justify-center p-8 text-center">
                                        <svg class="w-16 h-16 text-indigo-200 mb-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                        <span class="text-slate-400 font-bold text-sm">{{ __('messages.template_preview') }}</span>
                                    </div>
                                @endif

                                <div class="absolute inset-0 bg-indigo-900/60 opacity-0 group-hover:opacity-100 transition-all duration-300 backdrop-blur-[3px] flex flex-col items-center justify-center gap-4 p-6 text-center">
                                    <p class="text-white text-sm font-medium transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">تصميم معتمد من قبل خبراء التوظيف (ATS Friendly)</p>
                                    <a href="{{ route('templates.choose') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-bold text-sm hover:bg-indigo-50 transition-all transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300 delay-75 shadow-xl">
                                        {{ __('messages.use_template') ?? 'استخدام القالب' }}
                                    </a>
                                </div>
                            </div>

                            <div class="p-5 flex justify-between items-center bg-white border-t border-slate-50">
                                <div>
                                    <h3 class="font-extrabold text-slate-800 group-hover:text-indigo-600 transition-colors">{{ $template->name }}</h3>
                                    <p class="text-xs text-slate-400 mt-1">تستخدمه +{{ rand(100, 500) }} شخص</p>
                                </div>
                                <span class="text-[10px] font-bold px-3 py-1.5 rounded-lg {{ isset($template->is_premium) && $template->is_premium ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                    {{ isset($template->is_premium) && $template->is_premium ? 'PREMIUM' : 'FREE' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="swiper-slide w-full text-center py-20">
                        <p class="text-slate-500">{{ __('messages.no_templates_message') }}</p>
                    </div>
                    @endforelse
                </div>
                <div class="swiper-pagination !-bottom-2"></div>
            </div>
        </div>
    </section>

    {{-- Pricing Section --}}
    <section id="pricing" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-800 mb-4">{{ __('messages.pricing_title') ?? 'باقات بسيطة وواضحة' }}</h2>
                <p class="text-slate-500 text-lg">{{ __('messages.pricing_subtitle') ?? 'اختر الباقة التي تناسب احتياجاتك.' }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
                @foreach($plans as $plan)
                    @php $isPopular = $plan->is_popular; @endphp
                    <div class="relative bg-white p-8 md:p-10 rounded-[2rem] border transition-all duration-500 flex flex-col {{ $isPopular ? 'border-indigo-200 ring-1 ring-indigo-500 scale-105 z-10' : 'border-gray-100 shadow-sm hover:shadow-xl' }}" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        @if($isPopular)<span class="absolute -top-4 left-1/2 -translate-x-1/2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider shadow-md">{{ __('messages.most_popular') }}</span>@endif
                        <h3 class="text-2xl font-bold mb-2">{{ $plan->name }}</h3>
                        <div class="flex items-baseline gap-1 mb-2"><span class="text-4xl font-extrabold text-slate-900">{{ rtrim(rtrim(number_format($plan->price, 2), '0'), '.') }}</span><span class="text-slate-400 font-medium">{{ __('messages.currency') ?? 'MRU' }}</span></div>
                        <p class="text-sm text-slate-400 mb-6">{{ $plan->duration_in_days }} {{ __('messages.day') }}</p>
                        <ul class="space-y-4 mb-8 flex-grow text-slate-600 text-sm">
                            <li class="flex items-center gap-3"><span class="text-green-500 text-lg">✔️</span><span>{{ $plan->cv_limit }} {{ __('messages.cv') }}</span></li>
                            <li class="flex items-center gap-3">@if($plan->ai_credits > 0)<span class="text-green-500 text-lg">✔️</span><span>{{ $plan->ai_credits }} {{ __('messages.ai_credits_label') }}</span>@else<span class="text-gray-400 text-lg">🔒</span><span class="text-gray-400">{{ __('messages.no_ai_label') }}</span>@endif</li>
                            <li class="flex items-center gap-3">@if($plan->remove_watermark)<span class="text-green-500 text-lg">✔️</span><span>{{ __('messages.no_watermark_label') }}</span>@else<span class="text-red-500 text-lg">❌</span><span>{{ __('messages.with_watermark_label') }}</span>@endif</li>
                            <li class="flex items-center gap-3">@if($plan->has_cover_letter)<span class="text-green-500 text-lg">✔️</span><span>{{ __('messages.cover_letter_label') }}</span>@else<span class="text-gray-400 text-lg">🔒</span><span class="text-gray-400">{{ __('messages.cover_letter_label') }}</span>@endif</li>
                            <li class="flex items-center gap-3">@if($plan->priority_support)<span class="text-green-500 text-lg">✔️</span><span>{{ __('messages.priority_support_label') }}</span>@else<span class="text-gray-400 text-lg">🔒</span><span class="text-gray-400">{{ __('messages.normal_support_label') }}</span>@endif</li>
                        </ul>
                        @auth
                        <a href="{{ route('payment.checkout', $plan->slug) }}" class="w-full text-center py-4 rounded-full font-bold transition-all {{ $isPopular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-200' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ __('messages.btn_upgrade') }}</a>
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
            <div data-aos="fade-up" data-aos-delay="100"><a href="{{ auth()->check() ? route('templates.choose') : route('register') }}" class="inline-block bg-white text-indigo-600 font-bold px-10 py-4 rounded-full shadow-xl hover:bg-indigo-50 transition transform hover:scale-105 text-lg">{{ __('messages.final_cta_btn') }}</a></div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-8 border-t border-slate-100 bg-white">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4 text-sm text-slate-500">
            <div class="flex items-center gap-2"><div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white"><span class="font-bold text-lg">C</span></div><span class="font-bold text-slate-700">CV<span class="text-indigo-600">maker</span></span></div>
            <p>© {{ date('Y') }} {{ __('messages.footer_copyright') }}</p>
            <div class="flex gap-6"><a href="#" class="hover:text-indigo-600">{{ __('messages.footer_terms') }}</a><a href="#" class="hover:text-indigo-600">{{ __('messages.footer_privacy') }}</a><a href="#" class="hover:text-indigo-600">{{ __('messages.footer_contact') }}</a></div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init({ once: true, duration: 800 });

            new Swiper('.templates-swiper', {
                slidesPerView: 'auto',
                spaceBetween: 30,
                centeredSlides: true,
                loop: true,
                grabCursor: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: '.swiper-nav-next',
                    prevEl: '.swiper-nav-prev',
                },
                breakpoints: {
                    320: { spaceBetween: 20 },
                    768: { spaceBetween: 30 },
                    1024: { spaceBetween: 40 }
                }
            });
        });
    </script>
</body>
</html>