<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight text-right" dir="rtl">
            {{ __('خطاب التغطية') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-sm" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            {{-- رأس الصفحة مع أزرار الإجراءات --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-t-lg border-b">
                <div class="p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">
                            خطاب: {{ $coverLetter->target_job_title }}
                        </h3>
                        @if($coverLetter->company_name)
                            <p class="text-gray-500 mt-1">شركة: {{ $coverLetter->company_name }}</p>
                        @endif
                        <p class="text-sm text-gray-400 mt-1">
                            تم الإنشاء: {{ $coverLetter->created_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <a
                            href="{{ route('cover-letters.download', $coverLetter->id) }}"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition"
                        >
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            تحميل PDF
                        </a>
                        <a
                            href="{{ route('cover-letters.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 transition"
                        >
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            إنشاء خطاب جديد
                        </a>
                    </div>
                </div>
            </div>

            {{-- محتوى الخطاب --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-b-lg">
                <div class="p-8">
                    <div class="prose prose-lg max-w-none text-gray-800 whitespace-pre-line" dir="auto">
                        {!! nl2br(e($coverLetter->content)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
