<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
            {{ __('إنشاء خطاب تغطية') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- رسائل الخطأ --}}
            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-6">إنشاء خطاب تحفيزي بالذكاء الاصطناعي</h3>

                    <form action="{{ route('cover-letters.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- اختيار مصدر البيانات --}}
                        <div class="mb-8 p-4 bg-blue-50 rounded-lg border border-blue-200">
                            <h4 class="font-bold text-blue-800 mb-3">مصدر بيانات السيرة الذاتية</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {{-- اختيار سيرة ذاتية موجودة --}}
                                <div>
                                    <label for="resume_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        اختر سيرتك الذاتية الموجودة
                                    </label>
                                    <select
                                        id="resume_id"
                                        name="resume_id"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    >
                                        <option value="">-- اختر سيرة ذاتية --</option>
                                        @foreach($resumes as $resume)
                                            <option value="{{ $resume->id }}" {{ old('resume_id') == $resume->id ? 'selected' : '' }}>
                                                {{ $resume->title }} ({{ $resume->updated_at->diffForHumans() }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('resume_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- أو رفع ملف --}}
                                <div>
                                    <label for="uploaded_file" class="block text-sm font-medium text-gray-700 mb-1">
                                        أو ارفع ملف (PDF / Word)
                                    </label>
                                    <input
                                        type="file"
                                        id="uploaded_file"
                                        name="uploaded_file"
                                        accept=".pdf,.doc,.docx"
                                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">الحد الأقصى: 5 ميجابايت</p>
                                    @error('uploaded_file')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- المسمى الوظيفي المستهدف --}}
                        <div class="mb-6">
                            <label for="target_job_title" class="block text-sm font-medium text-gray-700 mb-1">
                                المسمى الوظيفي المستهدف <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="target_job_title"
                                name="target_job_title"
                                value="{{ old('target_job_title') }}"
                                required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="مثال: مهندس برمجيات، مصمم جرافيك..."
                            >
                            @error('target_job_title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- اسم الشركة --}}
                        <div class="mb-6">
                            <label for="company_name" class="block text-sm font-medium text-gray-700 mb-1">
                                اسم الشركة (اختياري)
                            </label>
                            <input
                                type="text"
                                id="company_name"
                                name="company_name"
                                value="{{ old('company_name') }}"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="مثال: شركة أرامكو"
                            >
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- وصف الوظيفة أو الرابط --}}
                        <div class="mb-6">
                            <label for="job_description" class="block text-sm font-medium text-gray-700 mb-1">
                                وصف الوظيفة أو رابط الإعلان
                            </label>
                            <textarea
                                id="job_description"
                                name="job_description"
                                rows="4"
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="الصق وصف الوظيفة هنا لتخصيص الخطاب بشكل أفضل..."
                            >{{ old('job_description') }}</textarea>
                            <div class="mt-2">
                                <label for="job_description_url" class="block text-sm text-gray-500 mb-1">
                                    أو أدخل رابط إعلان الوظيفة:
                                </label>
                                <input
                                    type="url"
                                    id="job_description_url"
                                    name="job_description_url"
                                    value="{{ old('job_description_url') }}"
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="https://..."
                                >
                            </div>
                            @error('job_description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('job_description_url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- اللغة --}}
                        <div class="mb-6">
                            <label for="language" class="block text-sm font-medium text-gray-700 mb-1">
                                لغة الخطاب <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="language"
                                name="language"
                                required
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="ar" {{ old('language') == 'ar' ? 'selected' : '' }}>العربية</option>
                                <option value="en" {{ old('language') == 'en' ? 'selected' : '' }}>English</option>
                                <option value="fr" {{ old('language') == 'fr' ? 'selected' : '' }}>Français</option>
                            </select>
                            @error('language')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- زر الإرسال --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t">
                            <a href="{{ url()->previous() }}" class="px-5 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                إلغاء
                            </a>
                            <button
                                type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition"
                            >
                                إنشاء خطاب التغطية
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
