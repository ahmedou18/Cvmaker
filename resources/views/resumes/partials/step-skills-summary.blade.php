@props(['currentLang' => 'ar'])

<div x-show="step === 4" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.step_skills_summary', [], $currentLang) }}</h2>
        
        <div class="space-y-8">
            {{-- قسم المهارات مع التقييم --}}
            <div>
                <div class="flex justify-between items-center mb-2 flex-wrap gap-2">
                    <label class="sharp-label mb-0">{{ __('messages.skills_label', [], $currentLang) }}</label>
                    {{-- زر اقتراح المهارات معطل مؤقتاً --}}
                    {{-- <button type="button" class="sharp-btn-ai text-xs" @click="generateSkillsAI()" :disabled="aiCredits <= 0">✨ {{ __('messages.ai_suggest_skills', [], $currentLang) }}</button> --}}
                </div>
                
                <template x-for="(skill, idx) in skillsArray" :key="skill.id">
                    <div class="flex flex-wrap gap-3 mb-4 items-center bg-gray-50 p-3 rounded">
                        <div class="flex-1 min-w-[150px]">
                            <input type="text" :name="'skills_array['+idx+'][name]'" x-model="skill.name" class="sharp-input w-full" placeholder="المهارة (مثال: Laravel)">
                            <span x-show="getFieldError(`skills_array.${idx}.name`)" 
                                  x-text="getFieldError(`skills_array.${idx}.name`)" 
                                  class="text-red-500 text-xs mt-1"></span>
                            <span x-show="getFieldError(`skills_array.${idx}.percentage`)" 
                                  x-text="getFieldError(`skills_array.${idx}.percentage`)" 
                                  class="text-red-500 text-xs mt-1"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="range" :name="'skills_array['+idx+'][percentage]'" x-model="skill.percentage" min="0" max="100" class="w-32">
                            <input type="number" :name="'skills_array['+idx+'][percentage]'" x-model="skill.percentage" class="sharp-input w-20 text-center" min="0" max="100">
                            <span class="text-sm font-bold" x-text="skill.percentage + '%'"></span>
                        </div>
                        <button type="button" @click="skillsArray.splice(idx,1)" class="text-red-600 font-bold px-3 py-2">✕</button>
                    </div>
                </template>
                <button type="button" @click="skillsArray.push({id: Date.now(), name: '', percentage: 80})" class="sharp-btn-secondary mt-2">+ {{ __('messages.add_skill', [], $currentLang) ?? 'أضف مهارة' }}</button>
                
                {{-- حقل مخفي للتوافق مع AI (يتم تعبئته تلقائياً من skillsArray) --}}
                <input type="hidden" name="skills" x-model="skillsTextForAI">
            </div>

            {{-- قسم الملخص الشخصي --}}
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="sharp-label mb-0">{{ __('messages.summary_label', [], $currentLang) }}</label>
                    <button type="button" class="sharp-btn-ai" @click="generateSummaryAI()" :disabled="aiCredits <= 0">{{ __('messages.ai_write_summary', [], $currentLang) }}</button>
                </div>
                <textarea name="summary" x-model="summary" rows="5" class="sharp-input"></textarea>
                <span x-show="getFieldError('summary')" 
                      x-text="getFieldError('summary')" 
                      class="text-red-500 text-xs mt-1"></span>
            </div>
        </div>
    </div>
</div>