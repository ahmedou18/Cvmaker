@php
    $unreadCount = auth()->user()->unreadNotifications->count();
    $notifications = auth()->user()->unreadNotifications->take(5)->map(function($n) {
        return [
            'id' => $n->id,
            'message' => $n->data['message'] ?? __('messages.no_message', [], app()->getLocale()),
            'created_at' => $n->created_at->diffForHumans()
        ];
    });
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex" dir="ltr">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('messages.nav_dashboard', [], app()->getLocale()) }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                {{-- Language Switcher --}}
                <div class="flex items-center gap-1 text-sm font-semibold ml-4">
                    <a href="{{ route('lang.switch', 'ar') }}" class="px-2 py-1 {{ app()->getLocale() == 'ar' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">AR</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="px-2 py-1 {{ app()->getLocale() == 'en' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">EN</a>
                    <span class="text-gray-300">|</span>
                    <a href="{{ route('lang.switch', 'fr') }}" class="px-2 py-1 {{ app()->getLocale() == 'fr' ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-indigo-600' }}">FR</a>
                </div>

                {{-- Notifications Component --}}
                <div class="relative mr-4"
                     x-data="{
                        open: false,
                        unreadCount: {{ $unreadCount }},
                        notifications: {{ Js::from($notifications) }},
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
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 py-2"
                         dir="rtl">
                        <div class="flex justify-between items-center px-4 py-2 border-b">
                            <h4 class="font-bold text-sm">{{ __('messages.notifications', [], app()->getLocale()) }}</h4>
                            <button x-show="notifications.length > 0"
                                    @click="markAllAsRead()"
                                    class="text-xs text-blue-600 hover:underline">
                                {{ __('messages.mark_all_read', [], app()->getLocale()) }}
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
                                                    class="text-blue-500 hover:text-blue-700 text-xs mr-2">
                                                ✓
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <p x-show="notifications.length === 0" class="px-4 py-6 text-center text-xs text-gray-400">
                                {{ __('messages.no_notifications', [], app()->getLocale()) }}
                            </p>
                        </div>
                        <div class="border-t px-4 py-2">
                            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">
                                {{ __('messages.view_all_notifications', [], app()->getLocale()) }}
                            </a>
                        </div>
                    </div>
                </div>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('messages.profile', [], app()->getLocale()) }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('messages.logout', [], app()->getLocale()) }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('messages.nav_dashboard', [], app()->getLocale()) }}
            </x-responsive-nav-link>
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('messages.profile', [], app()->getLocale()) }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('messages.logout', [], app()->getLocale()) }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>