<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
            {{ __('لوحة التحكم') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- رسالة الترحيب --}}
            @php
                $user = Auth::user();
                $hasPrioritySupport = $user->plan?->priority_support ?? false;
                $hasCoverLetterAccess = $user->plan?->has_cover_letter ?? false;
                $supportCode = $user->getOrCreateSupportCode();
                $whatsappNumber = '22226121732'; // ضع رقم واتساب الخاص بك هنا
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900 flex flex-col md:flex-row justify-between items-center">
                    <div class="mb-4 md:mb-0">
                        <h3 class="text-2xl font-bold text-gray-800">أهلاً بك، {{ $user->name }}! 👋</h3>
                        <p class="text-gray-500 mt-2">هنا يمكنك إدارة سيرك الذاتية، تعديلها، أو تحميلها في أي وقت.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 items-center">
                        {{-- زر إنشاء سيرة ذاتية --}}
                        @can('create', App\Models\Resume::class)
                            <a href="{{ route('templates.choose') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">
                                + إنشاء سيرة ذاتية جديدة
                            </a>
                        @else
                            <button type="button" onclick="openPlansModal()" class="bg-amber-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-amber-700 transition">
                                ترقية الباقة لإنشاء المزيد
                            </button>
                        @endcan

                        {{-- زر الدعم ذو الأولوية (واتساب) --}}
                        @if($hasPrioritySupport)
                            @php
                                $planName = $user->plan?->name ?? 'غير محدد';
                                $planPrice = $user->plan?->formatted_price ?? 'غير محدد';
                                $whatsappMessage = urlencode("مرحباً، أحتاج إلى دعم فني.\nالباقة: {$planName}\nالسعر: {$planPrice}\nالحالة: نشطة\nرمز التحقق: {$supportCode}");
                            @endphp
                            <a
                                href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-green-700 transition"
                            >
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                تواصل مع الدعم
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- قسم الأدوات السريعة (خطاب تحفيزي + دعم) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                {{-- بطاقة خطاب التغطية --}}
                @if($hasCoverLetterAccess)
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-indigo-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">خطاب تحفيزي</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">أنشئ خطاب تغطية احترافي بالذكاء الاصطناعي مخصص للوظيفة التي تتقدم لها.</p>
                        <a href="{{ route('cover-letters.create') }}" class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-indigo-700 transition text-sm">
                            إنشاء خطاب جديد
                        </a>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow-sm opacity-70">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-gray-200 p-2 rounded-full">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-600">خطاب تحفيزي</h4>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">هذه الميزة متاحة فقط في الباقات المدفوعة.</p>
                        <button type="button" onclick="openPlansModal()" class="inline-block bg-gray-400 text-white px-5 py-2 rounded-lg font-bold text-sm cursor-pointer">
                            رقي باقتك للوصول
                        </button>
                    </div>
                @endif

                {{-- بطاقة الدعم الفني --}}
                @if($hasPrioritySupport)
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-lg p-6 shadow-sm">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-green-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">الدعم ذو الأولوية</h4>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">رمز التحقق الخاص بك:</p>
                        <div class="bg-white border border-green-300 rounded-lg px-4 py-2 mb-3 inline-block">
                            <span class="text-xl font-mono font-bold text-green-700 tracking-wider">{{ $supportCode }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-3">شارك هذا الرمز مع فريق الدعم للتحقق من هويتك</p>
                        <a
                            href="https://wa.me/{{ $whatsappNumber }}?text={{ $whatsappMessage ?? '' }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-green-700 transition text-sm"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            تواصل عبر واتساب
                        </a>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 shadow-sm opacity-70">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="bg-gray-200 p-2 rounded-full">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-gray-600">الدعم ذو الأولوية</h4>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">احصل على دعم فني سريع عبر واتساب مع رمز تحقق خاص.</p>
                        <button type="button" onclick="openPlansModal()" class="inline-block bg-gray-400 text-white px-5 py-2 rounded-lg font-bold text-sm">
                            رقي باقتك للوصول
                        </button>
                    </div>
                @endif
            </div>

            {{-- بطاقة استخدام الاشتراك --}}
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
                            @if($hasNoPlan)
                                لا توجد باقة نشطة
                            @else
                                استخدام الاشتراك
                            @endif
                        </h4>
                        <span class="text-sm font-medium text-gray-600">
                            @if($hasNoPlan)
                                أنت غير مشترك في أي باقة حالياً
                            @elseif($isLimitReached)
                                لقد وصلت الحد الأقصى
                            @else
                                متبقي {{ $cvLimit - $resumesUsed }} سيرة ذاتية
                            @endif
                        </span>
                    </div>

                    @if($hasNoPlan)
                        <p class="text-sm text-gray-500 mb-4">
                            قم بالاشتراك في باقة لإنشاء سير ذاتية.
                        </p>
                        <div class="mt-4">
                            <button type="button" onclick="openPlansModal()" class="inline-block bg-blue-600 text-white px-5 py-2 rounded-lg font-bold hover:bg-blue-700 transition">
                                عرض الباقات
                            </button>
                        </div>
                    @else
                        <p class="text-sm text-gray-600 mb-3">
                            لقد استخدمت <span class="font-semibold text-gray-800">{{ $resumesUsed }}</span> من أصل <span class="font-semibold text-gray-800">{{ $cvLimit }}</span> سيرة ذاتية.
                        </p>

                        {{-- شريط التقدم --}}
                        <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div
                                class="h-3 rounded-full transition-all duration-500
                                {{ $isLimitReached ? 'bg-red-500' : ($usagePercentage >= 75 ? 'bg-amber-500' : 'bg-green-500') }}"
                                style="width: {{ $usagePercentage }}%;"
                            ></div>
                        </div>

                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-500">{{ number_format($usagePercentage, 0) }}% مستخدم</span>

                            @if($isLimitReached)
                                <button type="button" onclick="openPlansModal()" class="inline-block bg-amber-600 text-white px-4 py-1.5 rounded-lg text-sm font-bold hover:bg-amber-700 transition">
                                    ترقية الباقة
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- رسالة نجاح --}}
            @if(session('success'))
                <div class="mb-8 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- رسالة خطأ --}}
            @if(session('error'))
                <div class="mb-8 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <h4 class="text-lg font-bold text-gray-700 mb-4">سيرك الذاتية السابقة</h4>

            {{-- بطاقات السير الذاتية --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- بطاقة "ابدأ من الصفر" --}}
                @can('create', App\Models\Resume::class)
                    <a href="{{ route('resume.create') }}" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center p-8 hover:bg-gray-100 hover:border-blue-400 transition cursor-pointer min-h-[220px]">
                        <div class="bg-blue-100 p-3 rounded-full mb-3">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <span class="text-gray-600 font-medium">ابدأ من الصفر</span>
                    </a>
                @else
                    <div class="bg-gray-50 border-2 border-dashed border-amber-300 rounded-lg flex flex-col items-center justify-center p-8 min-h-[220px] opacity-60">
                        <div class="bg-amber-100 p-3 rounded-full mb-3">
                            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="text-gray-500 font-medium mb-2">تم الوصول للحد الأقصى</span>
                        <button type="button" onclick="openPlansModal()" class="text-amber-600 font-semibold text-sm hover:underline">
                            رقي باقتك الآن
                        </button>
                    </div>
                @endcan

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

    {{-- مودال الباقات (مخفي في البداية) --}}
    <div id="plansModal" style="display: none;">
        <x-plans-modal 
            closeAction="closePlansModal()" 
            :resumeUuid="null" 
        />
    </div>

    {{-- JavaScript لفتح وإغلاق المودال --}}
    <script>
        function openPlansModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        }
        function closePlansModal() {
            const modal = document.getElementById('plansModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        }
        // إغلاق المودال عند الضغط على Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePlansModal();
            }
        });
    </script>
</x-app-layout>