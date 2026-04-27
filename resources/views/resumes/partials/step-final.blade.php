@props(['currentLang' => 'ar'])

<div x-show="step === maxStep" x-transition>
    <div class="sharp-card text-center">
        <div class="py-10">
            <div class="text-6xl mb-6">🚀</div>
            <h2 class="text-3xl font-black mb-4">{{ __('messages.ready_to_save', [], $currentLang) }}</h2>
            <p class="text-gray-600 mb-8">{{ __('messages.final_instruction', [], $currentLang) }}</p>
            
            <button type="button" 
                    @click="reviewEntireResumeAI()" 
                    :disabled="aiCredits <= 0 || isReviewing" 
                    class="sharp-btn-secondary !w-auto !px-8 flex items-center gap-2 mx-auto">
                <span x-show="!isReviewing">{{ __('messages.ai_improve_text', [], $currentLang) }}</span>
                <span x-show="isReviewing">{{ __('messages.ai_reviewing', [], $currentLang) }}</span>
            </button>
            
            <p x-show="reviewSuccessMessage" class="text-green-600 font-bold mt-4" x-text="reviewSuccessMessage"></p>
        </div>
    </div>
</div>