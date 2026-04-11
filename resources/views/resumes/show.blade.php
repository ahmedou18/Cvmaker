<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resume->title }} - {{ $resume->personalDetail->full_name ?? 'سيرة ذاتية' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* استخدام خط Cairo الفخم والاحترافي للسير الذاتية */
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f3f4f6; /* رمادي فاتح جداً للخلفية */
        }

        /* تنسيقات خاصة بالطباعة A4 */
        @media print {
            body { 
                background-color: white; 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; }
            .print-p-0 { padding: 0 !important; }
            .print-m-0 { margin: 0 !important; }
            /* إخفاء خلفية البطاقات في الطباعة لجعلها ورقة بيضاء نظيفة */
            .bg-gray-50 { background-color: transparent !important; } 
        }
    </style>
</head>
<body class="antialiased text-gray-800">

    <div class="no-print bg-white shadow-sm border-b mb-10">
        <div class="max-w-5xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-blue-600 flex items-center transition">
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                العودة للوحة التحكم
            </a>
            <a href="{{ route('resume.download', $resume->uuid) }}" class="btn btn-primary">
    تحميل كـ PDF
</a>
        </div>
    </div>

    <div class="max-w-5xl mx-auto p-4 md:p-10 mb-10 print-m-0 print-p-0">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden p-8 md:p-12 border border-gray-100 print-shadow-none print-p-0">
            
            @php $profile = $resume->personalDetail; @endphp

            <header class="border-b-2 border-gray-900 pb-8 mb-8 flex flex-col md:flex-row justify-between items-center md:items-start gap-8">
                
                <div class="flex-grow text-center md:text-right">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 tracking-tight">{{ $profile->full_name ?? 'الاسم الكامل' }}</h1>
                    <p class="text-xl md:text-2xl text-gray-600 mt-2 font-semibold">{{ $profile->job_title ?? 'المسمى الوظيفي' }}</p>

                    <div class="flex flex-wrap justify-center md:justify-start gap-x-6 gap-y-2 text-sm text-gray-600 mt-5 font-medium">
                        @if($profile->email) 
                            <span class="flex items-center gap-1.5" dir="ltr">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                {{ $profile->email }}
                            </span> 
                        @endif
                        @if($profile->phone) 
                            <span class="flex items-center gap-1.5" dir="ltr">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                {{ $profile->phone }}
                            </span> 
                        @endif
                        @if($profile->address) 
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                {{ $profile->address }}
                            </span> 
                        @endif
                    </div>
                </div>

                @if($profile->photo_path)
                    <div class="flex-shrink-0">
                        <img src="{{ asset($profile->photo_path) }}" alt="الصورة الشخصية" class="w-32 h-32 md:w-36 md:h-36 rounded-2xl object-cover border-4 border-white shadow-lg">
                    </div>
                @endif
            </header>

            @if($profile && $profile->summary)
                <section class="mb-10">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4 border-r-4 border-gray-900 pr-3">النبذة الشخصية</h2>
                    <p class="text-gray-700 leading-relaxed text-justify font-medium text-lg">{{ $profile->summary }}</p>
                </section>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                
                <div class="md:col-span-2 space-y-12">
                    
                    @if($resume->experiences->count() > 0)
                        <section>
                            <h2 class="text-2xl font-bold text-gray-900 mb-6 border-r-4 border-gray-900 pr-3">الخبرات العملية</h2>
                            <div class="space-y-8">
                                @foreach($resume->experiences as $exp)
                                    <div class="relative pr-6 border-r-2 border-gray-200 pb-2">
                                        <div class="absolute -right-[9px] top-1.5 w-4 h-4 bg-gray-900 rounded-full border-4 border-white"></div>
                                        
                                        <div class="flex flex-col md:flex-row md:justify-between md:items-baseline mb-1 gap-2">
                                            <h3 class="text-xl font-bold text-gray-900">{{ $exp->position }}</h3>
                                            @if($exp->start_date)
    <span class="text-sm font-bold text-gray-600 bg-gray-100 px-3 py-1 rounded-md" dir="ltr">
        {{-- طباعة تاريخ البداية دائماً (بما أننا تأكدنا من وجوده) --}}
        {{ \Carbon\Carbon::parse($exp->start_date)->format('Y/m') }}
        
        {{-- التحقق من تاريخ النهاية وحالة "الآن" --}}
        @if($exp->end_date)
            - {{ \Carbon\Carbon::parse($exp->end_date)->format('Y/m') }}
        @elseif($exp->is_current)
            - الآن
        @endif
        {{-- إذا لم يكن هناك end_date ولم يكن is_current true، فلن يُطبع أي شيء إضافي --}}
    </span>
@endif
                                        </div>
                                        <p class="text-lg font-semibold text-blue-600 mb-3">{{ $exp->company }}</p>
                                        
                                        @if($exp->description)
                                            <div class="text-base text-gray-700 leading-relaxed whitespace-pre-line text-justify">{!! nl2br(e($exp->description)) !!}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($resume->educations->count() > 0)
                        <section>
                            <h2 class="text-2xl font-bold text-gray-900 mb-6 border-r-4 border-gray-900 pr-3">المؤهلات التعليمية</h2>
                            <div class="space-y-6">
                                @foreach($resume->educations as $edu)
                                    <div class="flex justify-between items-start bg-gray-50 p-4 rounded-lg border border-gray-100">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">{{ $edu->degree }} - {{ $edu->field_of_study }}</h3>
                                            <p class="text-md font-semibold text-gray-600 mt-1">{{ $edu->institution }}</p>
                                        </div>
                                        @if($edu->graduation_year)
                                            <span class="text-sm font-bold text-gray-600 bg-white border border-gray-200 px-3 py-1 rounded-md shadow-sm" dir="ltr">
                                                {{ $edu->graduation_year->format('Y') }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <div class="space-y-10">
                    
                    @if($resume->skills->count() > 0)
                        <section>
                            <h2 class="text-xl font-bold text-gray-900 mb-5 pb-2 border-b-2 border-gray-200">المهارات</h2>
                            <div class="flex flex-wrap gap-2.5">
                                @foreach($resume->skills as $skill)
                                    <span class="bg-gray-800 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow-sm">{{ $skill->name }}</span>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($resume->languages->count() > 0)
                        <section>
                            <h2 class="text-xl font-bold text-gray-900 mb-5 pb-2 border-b-2 border-gray-200">اللغات</h2>
                            <div class="space-y-3">
                                @foreach($resume->languages as $lang)
                                    <div class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                                        <span class="font-bold text-gray-900">{{ $lang->name }}</span>
                                        <span class="text-sm text-blue-700 bg-blue-50 px-3 py-1 rounded-md font-bold">{{ $lang->proficiency }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            </div>

            <footer class="mt-16 pt-6 border-t border-gray-200 text-center text-sm text-gray-400 font-medium no-print">

                {{ __('تم إنشاء هذه السيرة عبر CvMakerApp - جميع الحقوق محفوظة') }}
            </footer>

        </div>
    </div>

</body>
</html>