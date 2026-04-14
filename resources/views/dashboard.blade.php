<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
                {{ __('لوحة التحكم') }}
            </h2>

            {{-- قائمة الإشعارات --}}
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
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                        class="relative text-gray-600 hover:text-blue-600 transition-colors pt-2">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span x-show="unreadCount > 0"
                          x-text="unreadCount"
                          class="absolute top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">
                    </span>
                </button>

                <div x-show="open"
                     @click.away="open = false"
                     x-transition
                     class="absolute left-0 mt-2 w-80 bg-white rounded-xl shadow-xl border border-gray-100 z-50 py-2 text-right"
                     dir="rtl">
                    <div class="flex justify-between items-center px-4 py-2 border-b">
                        <h4 class="font-bold text-sm">الإشعارات</h4>
                        <button x-show="notifications.length > 0"
                                @click="markAllAsRead()"
                                class="text-xs text-blue-600 hover:underline">
                            تحديد الكل كمقروء
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
                            لا توجد إشعارات جديدة
                        </p>
                    </div>
                    <div class="border-t px-4 py-2">
                        <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:underline">
                            عرض كل الإشعارات
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- رسالة الترحيب --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-2xl font-bold text-gray-800">أهلاً بك، {{ Auth::user()->name }}! 👋</h3>
                        <p class="text-gray-500 mt-2">هنا يمكنك إدارة سيرك الذاتية، تعديلها، أو تحميلها في أي وقت.</p>
                    </div>
                    <div>
                        <a href="{{ route('templates.choose') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">
                            + إنشاء سيرة ذاتية جديدة
                        </a>
                    </div>
                </div>
            </div>

            {{-- رسالة نجاح --}}
            @if(session('success'))
                <div class="mb-8 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <h4 class="text-lg font-bold text-gray-700 mb-4">سيرك الذاتية السابقة</h4>

            {{-- بطاقات السير الذاتية --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- بطاقة "ابدأ من الصفر" --}}
                <a href="{{ route('resume.create') }}" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center p-8 hover:bg-gray-100 hover:border-blue-400 transition cursor-pointer min-h-[220px]">
                    <div class="bg-blue-100 p-3 rounded-full mb-3">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <span class="text-gray-600 font-medium">ابدأ من الصفر</span>
                </a>

                {{-- السير الذاتية المخزنة --}}
                @foreach($resumes as $resume)
                    <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6 flex flex-col justify-between min-h-[220px] hover:shadow-md transition">
                        <div>
                            <div class="flex justify-between items-start mb-4">
                                <h4 class="text-lg font-bold text-gray-800">{{ $resume->title }}</h4>

                                @if($resume->is_published)
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">مكتملة</span>
                                @else
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded">مسودة</span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mb-1">آخر تعديل: {{ $resume->updated_at->diffForHumans() }}</p>
                            <p class="text-sm text-gray-500">القالب: الافتراضي</p>
                        </div>

                        <div class="flex space-x-3 space-x-reverse mt-4 border-t pt-4">
                            <a href="{{ route('resume.show', $resume->uuid) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                عرض / تعديل
                            </a>
                            <a href="#" class="text-gray-600 hover:text-gray-800 text-sm font-medium flex items-center">
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                PDF
                            </a>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</x-app-layout>