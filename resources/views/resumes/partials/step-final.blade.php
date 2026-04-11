<div x-show="step === 6" x-transition>
    <div class="sharp-card text-center">
        <div class="py-10">
            <div class="text-6xl mb-6">🚀</div>
            <h2 class="text-3xl font-black mb-4">جاهز للحفظ!</h2>
            <p class="text-gray-600 mb-8">يمكنك الآن تدقيق سيرتك بالكامل أو حفظها مباشرة.</p>
            
            {{-- زر تحسين السيرة بالذكاء الاصطناعي --}}
            <button type="button" 
                    @click="reviewEntireResumeAI()" 
                    :disabled="aiCredits <= 0 || isReviewing" 
                    class="sharp-btn-secondary !w-auto !px-8 flex items-center gap-2 mx-auto">
                <span x-show="!isReviewing">✨ تحسين السيرة لغوياً بالذكاء الاصطناعي</span>
                <span x-show="isReviewing">⏳ جاري التدقيق...</span>
            </button>
            
            {{-- رسالة نجاح التحسين --}}
            <p x-show="reviewSuccessMessage" class="text-green-600 font-bold mt-4" x-text="reviewSuccessMessage"></p>
        </div>
    </div>
</div>