@props(['currentLang' => 'ar'])

<div x-show="step === maxStep" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.final_touches', [], $currentLang) ?? 'اللمسات الأخيرة' }}</h2>

        {{-- AI Review Button --}}
        <div class="text-center mb-10">
            <button type="button" 
                    @click="reviewEntireResumeAI()" 
                    :disabled="aiCredits <= 0 || isReviewing" 
                    class="sharp-btn-secondary !w-auto !px-8 flex items-center gap-2 mx-auto">
                <span x-show="!isReviewing">{{ __('messages.ai_improve_text', [], $currentLang) ?? 'تحسين السيرة بالذكاء الاصطناعي' }}</span>
                <span x-show="isReviewing">{{ __('messages.ai_reviewing', [], $currentLang) ?? 'جاري التحسين...' }}</span>
            </button>
            <p x-show="reviewSuccessMessage" class="text-green-600 font-bold mt-4" x-text="reviewSuccessMessage"></p>
        </div>

        {{-- ===== الأقسام الإضافية (Extra Sections) ===== --}}
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-xl font-bold mb-4">{{ __('messages.extra_sections', [], $currentLang) ?? 'الأقسام الإضافية (الشهادات، المشاريع، الجوائز، ...)' }}</h3>
            <template x-for="(section, idx) in extra_sections" :key="idx">
                <div class="mb-6 p-4 border border-gray-200 rounded-lg bg-white">
                    <div class="flex flex-wrap justify-between items-start gap-2 mb-3">
                        <div class="flex gap-2">
                            <button type="button" @click="moveExtraSectionUp(idx)" :disabled="idx === 0" class="text-gray-500 hover:text-gray-700 px-2 text-lg">↑</button>
                            <button type="button" @click="moveExtraSectionDown(idx)" :disabled="idx === extra_sections.length-1" class="text-gray-500 hover:text-gray-700 px-2 text-lg">↓</button>
                        </div>
                        <button type="button" @click="extra_sections.splice(idx,1)" class="text-red-600 hover:text-red-800 text-sm">✕ حذف</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="sharp-label">{{ __('messages.section_title', [], $currentLang) ?? 'عنوان القسم' }}</label>
                            <input type="text" :name="'extra_sections['+idx+'][title]'" x-model="section.title" class="sharp-input" placeholder="مثال: الشهادات المهنية">
                        </div>
                        <div>
                            <label class="sharp-label">{{ __('messages.section_content', [], $currentLang) ?? 'المحتوى' }}</label>
                            <textarea :name="'extra_sections['+idx+'][content]'" x-model="section.content" rows="3" class="sharp-input" placeholder="أدخل محتوى القسم (يمكن استخدام نقاط أو فقرات)"></textarea>
                        </div>
                    </div>
                </div>
            </template>
            <div class="flex justify-center mt-4">
                <button type="button" @click="addExtraSection()" class="sharp-btn-secondary !w-auto !px-6">
                    + {{ __('messages.add_section', [], $currentLang) ?? 'أضف قسماً إضافياً' }}
                </button>
            </div>
        </div>

        {{-- رسالة تأكيد الحفظ --}}
        <div class="text-center mt-8 text-gray-500 text-sm">
            {{ __('messages.final_instruction', [], $currentLang) ?? 'راجع الأقسام الإضافية ثم اضغط حفظ ونشر' }}
        </div>
    </div>
</div>