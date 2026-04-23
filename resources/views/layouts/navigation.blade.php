@php
    $unreadCount = auth()->user()->unreadNotifications->count();
    $notifications = auth()->user()->unreadNotifications->take(5)->map(function($n) {
        return [
            'id' => $n->id,
            'message' => $n->data['message'] ?? __('messages.no_message', [], app()->getLocale()),
            'created_at' => $n->created_at->diffForHumans()
        ];
    });
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp

<nav x-data="{ mobileMenuOpen: false, scrolled: false }"
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
     class="bg-white/80 backdrop-blur-md border-b border-slate-200/60 sticky top-0 z-50 transition-all duration-300"
     :class="scrolled ? 'shadow-sm' : ''">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16" :class="scrolled ? 'h-14' : 'h-16'">
            {{-- الشعار والروابط --}}
            <div class="flex items-center gap-8">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group">
                        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white shadow-sm transition-transform group-hover:scale-105">
                            <span class="font-bold text-lg">C</span>
                        </div>
                        <span class="font-extrabold text-xl text-slate-800 tracking-tight hidden sm:block">CV<span class="text-indigo-600">maker</span></span>
                    </a>
                </div>
                <div class="hidden sm:flex space-x-8 sm:-my-px" :dir="'{{ $isRtl ? 'rtl' : 'ltr' }}'">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-slate-600 hover:text-indigo-600 font-medium transition-colors">
                        {{ __('messages.nav_dashboard', [], $locale) }}
                    </x-nav-link>
                </div>
            </div>

            {{-- القسم الأيمن --}}
            <div class="hidden sm:flex sm:items-center gap-4">
                {{-- مبدل اللغة (كبسولة) --}}
                <div class="flex items-center bg-slate-100/50 rounded-full p-1 border border-slate-200/50">
                    <a href="{{ route('lang.switch', 'ar') }}" class="px-3 py-1 text-xs font-bold rounded-full transition-all {{ $locale == 'ar' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">AR</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="px-3 py-1 text-xs font-bold rounded-full transition-all {{ $locale == 'en' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">EN</a>
                    <a href="{{ route('lang.switch', 'fr') }}" class="px-3 py-1 text-xs font-bold rounded-full transition-all {{ $locale == 'fr' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">FR</a>
                </div>

                {{-- الإشعارات --}}
                <div class="relative" x-data="{
                    open: false,
                    unreadCount: {{ $unreadCount }},
                    notifications: {{ Js::from($notifications) }},
                    markAsRead(id) { /* ... */ },
                    markAllAsRead() { /* ... */ }
                }">
                    <button @click="open = !open" class="relative p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-full transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                        <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute top-1 right-1 min-w-[18px] h-[18px] bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center rounded-full ring-2 ring-white"></span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition.opacity.scale.95 class="absolute {{ $isRtl ? 'left-0' : 'right-0' }} mt-3 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden" :dir="'{{ $isRtl ? 'rtl' : 'ltr' }}'" style="display: none;">
                        <!-- نفس محتوى الإشعارات السليم -->
                        <div class="flex justify-between items-center px-5 py-4 border-b border-slate-50 bg-slate-50/50">
                            <h4 class="font-bold text-sm text-slate-800">{{ __('messages.notifications', [], $locale) }}</h4>
                            <button x-show="notifications.length > 0" @click="markAllAsRead()" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">{{ __('messages.mark_all_read', [], $locale) }}</button>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                            <template x-if="notifications.length > 0">
                                <div class="divide-y divide-slate-50">
                                    <template x-for="n in notifications" :key="n.id">
                                        <div class="px-5 py-4 hover:bg-slate-50 flex gap-3 items-start group">
                                            <div class="flex-1"><p class="text-sm text-slate-700" x-text="n.message"></p><span class="text-xs text-slate-400 mt-1 block" x-text="n.created_at"></span></div>
                                            <button @click="markAsRead(n.id)" class="text-slate-300 hover:text-indigo-600 p-1 opacity-0 group-hover:opacity-100"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg></button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <div x-show="notifications.length === 0" class="px-5 py-8 text-center">... {{ __('messages.no_notifications', [], $locale) }}</div>
                        </div>
                        <div class="border-t border-slate-50 p-2"><a href="{{ route('notifications.index') }}" class="block text-center text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-slate-50 py-2 rounded-xl">{{ __('messages.view_all_notifications', [], $locale) }}</a></div>
                    </div>
                </div>

                {{-- قائمة المستخدم --}}
                <x-dropdown align="{{ $isRtl ? 'left' : 'right' }}" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 bg-transparent rounded-full hover:bg-slate-50">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=4f46e5&background=e0e7ff" class="w-7 h-7 rounded-full" alt="Avatar">
                            <span class="hidden lg:block">{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="p-1">
                            <x-dropdown-link :href="route('profile.edit')" class="rounded-lg">{{ __('messages.profile', [], $locale) }}</x-dropdown-link>
                            <div class="h-px bg-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">@csrf<x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="rounded-lg text-rose-600 hover:bg-rose-50">{{ __('messages.logout', [], $locale) }}</x-dropdown-link></form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- زر الهامبرغر --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24"><path :class="{'hidden': mobileMenuOpen, 'inline-flex': !mobileMenuOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/><path :class="{'hidden': !mobileMenuOpen, 'inline-flex': mobileMenuOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- قائمة الجوال --}}
    <div x-show="mobileMenuOpen" x-cloak class="sm:hidden bg-white border-t border-slate-100 shadow-lg">
        <div class="pt-2 pb-3 space-y-1 px-2">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="rounded-lg">{{ __('messages.nav_dashboard', [], $locale) }}</x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-4 border-t border-slate-100">
            <div class="px-4 flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&color=4f46e5&background=e0e7ff" class="w-10 h-10 rounded-full" alt="Avatar">
                <div><div class="font-bold text-base text-slate-800">{{ Auth::user()->name }}</div><div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div></div>
            </div>
            <div class="mt-4 space-y-1 px-2">
                <x-responsive-nav-link :href="route('profile.edit')" class="rounded-lg">{{ __('messages.profile', [], $locale) }}</x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">@csrf<x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="rounded-lg text-rose-600 hover:bg-rose-50">{{ __('messages.logout', [], $locale) }}</x-responsive-nav-link></form>
            </div>
        </div>
    </div>
</nav>