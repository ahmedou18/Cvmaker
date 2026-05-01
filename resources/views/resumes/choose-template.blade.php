<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.choose_template_title', [], app()->getLocale()) }} - CVmaker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        body { font-family: 'Cairo', sans-serif; background-color: #fafafa; }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(226, 232, 240, 0.6); }
        .swiper-slide { opacity: 0.4; transition: all 0.5s ease; }
        .swiper-slide-active { opacity: 1; transform: scale(1.05); z-index: 3; }
        .template-card { transition: all 0.3s; border-radius: 1.5rem; overflow: hidden; }
        .swiper-slide-active .template-card { border: 2px solid #4f46e5; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); }
        .swiper-slide:not(.swiper-slide-active) { filter: blur(2px); }
        .swiper-pagination-bullet-active { background: #4f46e5 !important; width: 24px !important; border-radius: 5px !important; }
    </style>
</head>
<body class="text-slate-800 antialiased">

    {{-- الهيدر البسيط --}}
    <header class="glass-nav py-4 px-6 flex items-center justify-between">
        <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-indigo-600 font-bold flex items-center gap-2 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ __('messages.back_to_dashboard', [], app()->getLocale()) }}
        </a>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-extrabold">C</div>
            <span class="text-lg font-bold text-slate-800">CV<span class="text-indigo-600">maker</span></span>
        </div>
    </header>

    {{-- المحتوى الرئيسي --}}
    <div class="max-w-6xl mx-auto px-4 py-16">
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4">{{ __('messages.choose_template_heading', [], app()->getLocale()) }}</h1>
            <p class="text-lg text-slate-500">{{ __('messages.choose_template_subheading', [], app()->getLocale()) }}</p>
        </div>

        <form action="{{ route('resumes.start') }}" method="POST">
            @csrf

            {{-- سلايدر القوالب --}}
            <div class="swiper templates-swiper !overflow-visible mt-8">
                <div class="swiper-wrapper">
                    @forelse($templates as $template)
                        <div class="swiper-slide cursor-pointer" data-template-id="{{ $template->id }}">
                            <div class="template-card bg-white shadow-sm border border-gray-100">
                                <div class="aspect-[1/1.3] bg-gray-50 overflow-hidden">
                                    @if($template->thumbnail)
                                        <img src="{{ asset($template->thumbnail) }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">{{ __('messages.template_image_soon', [], app()->getLocale()) }}</div>
                                    @endif
                                </div>
                                <div class="p-5 text-center">
                                    <h3 class="font-bold text-lg text-slate-800">{{ $template->name }}</h3>
                                    <span class="inline-block mt-2 text-xs font-bold px-3 py-1 rounded-full {{ $template->is_premium ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                        {{ $template->is_premium ? __('messages.premium_template', [], app()->getLocale()) : __('messages.free_template', [], app()->getLocale()) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="swiper-slide w-full text-center py-20">
                            <p class="text-slate-500">{{ __('messages.no_templates', [], app()->getLocale()) }}</p>
                        </div>
                    @endforelse
                </div>
                <div class="swiper-pagination mt-6"></div>
            </div>

            {{-- اختيار اللغة --}}
            <div class="mt-10 max-w-xs mx-auto">
                <label class="block text-center text-slate-700 font-bold mb-2">{{ __('messages.choose_resume_language', [], app()->getLocale()) }}</label>
                <select name="resume_language" class="w-full border border-gray-200 rounded-xl p-3 text-slate-700 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="ar">{{ __('messages.arabic', [], app()->getLocale()) }}</option>
                    <option value="en">{{ __('messages.english', [], app()->getLocale()) }}</option>
                    <option value="fr">{{ __('messages.french', [], app()->getLocale()) }}</option>
                </select>
            </div>

            {{-- حقل مخفي لتخزين القالب المحدد --}}
            <input type="hidden" name="template_id" id="selected-template-id" value="{{ $templates->first()->id ?? '' }}">

            {{-- زر المتابعة --}}
            @if($templates->count() > 0)
            <div class="text-center mt-10">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-12 rounded-full shadow-lg shadow-indigo-200 hover:shadow-xl transition-all text-lg">
                    {{ __('messages.next_fill_data', [], app()->getLocale()) }} 🚀
                </button>
            </div>
            @endif
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const swiper = new Swiper('.templates-swiper', {
                effect: 'coverflow',
                centeredSlides: true,
                loop: {{ $templates->count() > 3 ? 'true' : 'false' }},
                grabCursor: true,
                slidesPerView: 1.1,
                coverflowEffect: {
                    rotate: 15,
                    depth: 200,
                    stretch: 0,
                    modifier: 1,
                    slideShadows: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                breakpoints: {
                    640: { slidesPerView: 1.5 },
                    768: { slidesPerView: 2 },
                    1024: { slidesPerView: 2.5 },
                    1280: { slidesPerView: 3 }
                },
                on: {
                    slideChange: function () {
                        const activeSlide = this.slides[this.activeIndex];
                        const templateId = activeSlide ? activeSlide.dataset.templateId : null;
                        if (templateId) {
                            document.getElementById('selected-template-id').value = templateId;
                        }
                    }
                }
            });

            // عند التحميل الأولي: تعيين القيمة من الشريحة النشطة
            const initialSlide = swiper.slides[swiper.activeIndex];
            if (initialSlide && initialSlide.dataset.templateId) {
                document.getElementById('selected-template-id').value = initialSlide.dataset.templateId;
            }

            // النقر على أي شريحة ينقلها إلى المنتصف
            document.querySelectorAll('.swiper-slide').forEach(slide => {
                slide.addEventListener('click', function () {
                    const slideIndex = Array.from(this.parentNode.children).indexOf(this);
                    swiper.slideToLoop(slideIndex);
                });
            });
        });
    </script>
</body>
</html>