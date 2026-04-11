<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختر قالب السيرة الذاتية - CVmaker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Cairo', sans-serif; background-color: #f3f4f6; } </style>
</head>
<body class="text-gray-800 p-8 antialiased">

    <div class="max-w-6xl mx-auto mb-6">
        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-blue-600 font-bold flex items-center gap-2 transition-colors">
            <span class="text-xl">&rarr;</span> عودة للوحة التحكم
        </a>
    </div>

    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-4xl font-extrabold text-gray-900 mb-4">الخطوة الأولى: اختر قالب سيرتك الذاتية</h1>
            <p class="text-lg text-gray-600">اختر التصميم الذي يعبر عنك، وسنقوم بتوجيهك لتعبئة بياناتك مباشرة.</p>
        </div>

        <form action="{{ route('resumes.start') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($templates as $template)
                <label class="relative cursor-pointer group">
                    <input type="radio" name="template_id" value="{{ $template->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }} required>
                    
                    <div class="bg-white rounded-2xl shadow-sm border-2 border-transparent peer-checked:border-blue-600 peer-checked:shadow-xl peer-checked:ring-4 peer-checked:ring-blue-100 transition-all duration-300 overflow-hidden group-hover:shadow-md">
                        
                        <div class="h-80 bg-gray-100 w-full relative">
                            @if($template->thumbnail)
                                <img src="{{ asset($template->thumbnail) }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400 font-semibold">صورة القالب قريباً</div>
                            @endif
                            
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-300"></div>
                        </div>

                        <div class="p-5 text-center border-t border-gray-100">
                            <h3 class="text-xl font-bold text-gray-900">{{ $template->name }}</h3>
                            @if($template->is_premium)
                                <span class="inline-block mt-3 text-xs bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-3 py-1 rounded-full font-bold shadow-sm">💎 قالب احترافي</span>
                            @else
                                <span class="inline-block mt-3 text-xs bg-green-100 text-green-800 px-3 py-1 rounded-full font-bold">✅ مجاني</span>
                            @endif
                        </div>
                        
                        <div class="absolute top-4 right-4 bg-blue-600 text-white rounded-full p-2 opacity-0 peer-checked:opacity-100 transition-opacity duration-300 shadow-md transform scale-75 peer-checked:scale-100">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    </div>
                </label>
                @empty
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-20 bg-white rounded-2xl shadow-sm border border-gray-200">
                        <span class="text-6xl mb-4 block">🛠️</span>
                        <h3 class="text-2xl font-bold text-gray-800">لا توجد قوالب مضافة بعد!</h3>
                        <p class="text-gray-500 mt-2">يرجى إضافة قوالب إلى قاعدة البيانات (جدول templates) أولاً.</p>
                    </div>
                @endforelse
            </div>
<div class="mt-8 mb-4 max-w-sm mx-auto bg-white p-4 rounded-xl shadow-sm border border-gray-200">
    <label class="block text-gray-700 font-bold mb-2 text-center">اختر لغة السيرة الذاتية:</label>
    <select name="resume_language" class="w-full border-gray-300 rounded-lg p-3 text-gray-700 focus:ring-blue-500 focus:border-blue-500">
        <option value="ar">العربية (Arabic)</option>
        <option value="en">الإنجليزية (English)</option>
        <option value="fr">الفرنسية (French)</option>
    </select>
</div>
            @if($templates->count() > 0)
            <div class="mt-12 text-center sticky bottom-8 z-10">
                <button type="submit" class="bg-blue-600 text-white font-bold py-4 px-12 rounded-full shadow-xl hover:bg-blue-700 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 text-xl border-4 border-white">
                    التالي: ابدأ بتعبئة بياناتك 🚀
                </button>
            </div>
            @endif
        </form>
    </div>

</body>
</html>