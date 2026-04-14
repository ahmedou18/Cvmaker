<div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-2xl border border-gray-200">
    <p class="text-sm font-bold text-gray-500 mb-4 uppercase">إثبات التحويل المرفوع:</p>
    @if($getState())
        <a href="{{ asset('storage/' . $getState()) }}" target="_blank" class="block group relative">
            <img src="{{ asset('storage/' . $getState()) }}" 
                 class="max-w-full h-auto rounded-xl shadow-lg border-4 border-white transition-transform group-hover:scale-[1.02]" 
                 style="max-height: 500px;">
            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 bg-black/20 transition-opacity rounded-xl">
                <span class="bg-white text-blue-600 px-4 py-2 rounded-full font-bold shadow-sm">اضغط للتكبير</span>
            </div>
        </a>
    @else
        <div class="p-8 text-gray-400 text-center">
            <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p>لا توجد صورة مرفوعة</p>
        </div>
    @endif
</div>