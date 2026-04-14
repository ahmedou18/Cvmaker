<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.page_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Cairo', sans-serif; }
        .gradient-text {
            background: linear-gradient(to left, #3b82f6, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased overflow-x-hidden">

    <nav class="bg-white shadow-sm py-4 px-8 flex justify-between items-center fixed w-full top-0 z-50" data-aos="fade-down" data-aos-duration="1000">
        <div class="text-2xl font-bold text-blue-600">CV<span class="text-gray-800">maker</span></div>
        <div class="flex items-center gap-3 mx-4 text-sm font-semibold">
            <a href="{{ route('lang.switch', 'ar') }}" class="{{ app()->getLocale() == 'ar' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-500' }}">عربي</a>
            <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() == 'en' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-500' }}">English</a>
            <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() == 'fr' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-blue-500' }}">Français</a>
        </div>
        
        <div class="flex items-center gap-4">
            @if (Route::has('login'))
                @auth
                    {{-- مكون الإشعارات --}}
                    <div class="relative"
                         x-data="{
                            open: false,
                            unreadCount: {{ auth()->user()->unreadNotifications->count() }},
                            notifications: @json(
                                auth()->user()->unreadNotifications->take(5)->map(function($n) {
                                    return [
                                        'id' => $n->id,
                                        'message' => $n->data['message'] ?? 'بدون رسالة',
                                        'created_at' => $n->created_at->diffForHumans()
                                    ];
                                })
                            ),
                            markAsRead(notificationId) {
                                fetch('/notifications/' + notificationId + '/mark-as-read', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Content-Type': 'application/json'
                                    }
                                }).then(response => {
                                    if (response.ok) {
                                        this.unreadCount--;
                                        this.notifications = this.notifications.filter(n => n.id !== notificationId);
                                    }
                                });
                            },
                            markAllAsRead() {
                                fetch('/notifications/mark-all-as-read', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Content-Type': 'application/json'
                                    }
                                }).then(response => {
                                    if (response.ok) {
                                        this.unreadCount = 0;
                                        this.notifications = [];
                                    }
                                });
                            }
                         }">
                        <button @click="open = !open"
                                class="relative text-gray-600 hover:text-blue-600 transition-colors p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span x-show="unreadCount > 0"
                                  x-text="unreadCount"
                                  class="absolute top-0 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                            </span>
                        </button>

                        <div x-show="open"
                             @click.away="open = false"
                             x-transition
                             class="absolute {{ app()->getLocale() == 'ar' ? 'left-0' : 'right-0' }} mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 py-2 {{ app()->getLocale() == 'ar' ? 'text-right' : 'text-left' }}"
                             dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
                            <div class="flex justify-between items-center px-4 py-2 border-b">
                                <h4 class="font-bold text-sm">{{ __('messages.notifications') }}</h4>
                                <button x-show="notifications.length > 0"
                                        @click="markAllAsRead()"
                                        class="text-xs text-blue-600 hover:underline">
                                    {{ __('messages.mark_all_read') }}
                                </button>
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                <template x-if="notifications.length > 0">
                                    <div>
                                        <template x-for="notification in notifications" :key="notification.id">
                                            <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0 flex justify-between items-start">
                                                <div class="flex-1">
                                                    <p class="text-xs text-gray-800" x-text="notification.message"></p>
                                                    <span class="text-[10px] text-gray-400" x-text="notification.created_at"></span>
                                                </div>
                                                <button @click="markAsRead(notification.id)"
                                                        class="text-blue-500 hover:text-blue-700 text-xs mx-2">
                                                    ✓
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <p x-show="notifications.length === 0" class="px-4 py-6 text-center text-xs text-gray-400">
                                    {{ __('messages.no_notifications') }}
                                </p>
                            </div>
                            <div class="border-t px-4 py-2">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">
                                    {{ __('messages.view_all_notifications') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-blue-600 font-bold transition">{{ __('messages.nav_dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 font-semibold transition">{{ __('messages.nav_login') }}</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-5 py-2 rounded-full shadow hover:bg-blue-700 transition">{{ __('messages.nav_register') }}</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <section class="min-h-screen flex flex-col justify-center items-center text-center px-4 pt-20">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight" data-aos="fade-up" data-aos-duration="1000">
            {{ __('messages.hero_title_1') }} <br> <span class="gradient-text">{{ __('messages.hero_title_2') }}</span>
        </h1>
        <p class="text-xl text-gray-500 mb-10 max-w-2xl" data-aos="fade-up" data-aos-delay="200" data-aos-duration="1000">
            {{ __('messages.hero_subtitle') }}
        </p>
        <div data-aos="zoom-in" data-aos-delay="400" data-aos-duration="1000">
            @auth
                <a href="{{ url('/dashboard') }}" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-xl font-bold px-10 py-4 rounded-full shadow-xl hover:scale-105 transform transition duration-300 inline-block">
                    {{ __('messages.btn_go_dashboard') }}
                </a>
            @else
                <a href="#pricing" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white text-xl font-bold px-10 py-4 rounded-full shadow-xl hover:scale-105 transform transition duration-300 inline-block">
                    {{ __('messages.btn_create_now') }}
                </a>
            @endauth
        </div>
    </section>

    <section id="pricing" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">{{ __('messages.pricing_title') }}</h2>
                <p class="text-lg text-gray-500">{{ __('messages.pricing_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                @foreach ($plans as $index => $plan)
                    <div class="bg-white rounded-2xl border {{ $plan->name == 'المتوسطة' ? 'border-blue-500 shadow-2xl scale-105 relative' : 'border-gray-200 shadow-lg' }} p-8 flex flex-col hover:-translate-y-2 transition-transform duration-300" 
                         data-aos="fade-up" data-aos-delay="{{ $index * 150 }}">
                        
                        @if($plan->name == 'المتوسطة')
                            <div class="absolute top-0 right-1/2 transform translate-x-1/2 -translate-y-1/2 bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-bold shadow-md">
                                {{ __('messages.most_popular') }}
                            </div>
                        @endif

                        <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ $plan->name }}</h3>
                        <div class="text-4xl font-extrabold text-gray-900 mb-6">
                            {{ rtrim(rtrim(number_format($plan->price, 2), '0'), '.') }} <span class="text-lg text-gray-500 font-medium">{{ __('messages.currency') }}</span>
                        </div>

                        <ul class="space-y-4 mb-8 flex-1">
                            <li class="flex items-center text-gray-600">
                                <span class="text-green-500 ml-2">✔️</span>
                                {{ __('messages.cv_count') }} {{ $plan->cv_limit }}
                            </li>
                            <li class="flex items-center text-gray-600">
                                @if($plan->has_watermark)
                                    <span class="text-red-500 ml-2">❌</span> {{ __('messages.with_watermark') }}
                                @else
                                    <span class="text-green-500 ml-2">✔️</span> {{ __('messages.no_watermark') }}
                                @endif
                            </li>
                            <li class="flex items-center text-gray-600">
                                @if($plan->ai_credits > 0)
                                    <span class="text-green-500 ml-2">✔️</span> {{ $plan->ai_credits }} {{ __('messages.ai_credits') }}
                                @else
                                    <span class="text-gray-400 ml-2">🔒</span> {{ __('messages.no_ai') }}
                                @endif
                            </li>
                            <li class="flex items-center text-gray-600">
                                <span class="text-blue-500 ml-2">⬇️</span>
                                {{ __('messages.downloads') }} {{ $plan->download_limit == 999 ? __('messages.unlimited') : $plan->download_limit }}
                            </li>
                            <li class="flex items-center text-gray-600">
                                <span class="text-orange-500 ml-2">✏️</span>
                                {{ __('messages.edits') }} {{ $plan->edit_limit == 999 ? __('messages.unlimited') : $plan->edit_limit }}
                            </li>
                        </ul>

                        @auth
                            <form action="{{ route('payment.checkout', $plan->slug) }}" method="POST" class="w-full">
                                @csrf
                                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                                <button type="submit" class="w-full text-center {{ $plan->name == 'المتوسطة' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} font-bold py-3 rounded-xl transition-colors block">
                                    {{ __('messages.btn_upgrade') }}
                                </button>
                            </form>
                        @else
                            <a href="{{ route('register', ['plan' => $plan->id]) }}" class="w-full text-center {{ $plan->name == 'المتوسطة' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }} font-bold py-3 rounded-xl transition-colors block">
                                {{ __('messages.btn_choose_plan') }}
                            </a>
                        @endauth
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ once: true, offset: 100 });</script>
</body>
</html>