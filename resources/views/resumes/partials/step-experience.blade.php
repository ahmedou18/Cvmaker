@props(['currentLang' => 'ar'])

<div x-show="step === 3" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.step_experience', [], $currentLang) }}</h2>
        <template x-for="(exp, index) in experiences" :key="exp.id">
            <div class="mb-10 p-6 border border-gray-200 bg-gray-50 relative">
                <button type="button" @click="experiences.splice(index, 1)" class="absolute top-0 left-0 bg-red-600 text-white px-3 py-1 text-xs font-bold">{{ __('messages.delete', [], $currentLang) }}</button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    <div>
                        <label class="sharp-label">{{ __('messages.company', [], $currentLang) }}</label>
                        <input type="text" :name="'experiences['+index+'][company]'" x-model="exp.company" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">{{ __('messages.position', [], $currentLang) }}</label>
                        <input type="text" :name="'experiences['+index+'][position]'" x-model="exp.position" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">{{ __('messages.start_date', [], $currentLang) }}</label>
                        <input type="date" :name="'experiences['+index+'][start_date]'" x-model="exp.start_date" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">{{ __('messages.end_date', [], $currentLang) }}</label>
                        <input type="date" :name="'experiences['+index+'][end_date]'" x-model="exp.end_date" class="sharp-input" :disabled="exp.is_current" :class="{'opacity-50 bg-gray-100': exp.is_current}">
                        <div class="mt-3 flex items-center">
                            <input type="checkbox" x-model="exp.is_current" @change="if(exp.is_current) exp.end_date = ''" class="ml-2">
                            <label class="text-sm font-bold text-gray-700">{{ __('messages.currently_working', [], $currentLang) }}</label>
                        </div>
                    </div>
                    <div class="col-span-full">
                        <div class="flex justify-between items-center mb-2">
                            <label class="sharp-label mb-0">{{ __('messages.description', [], $currentLang) }}</label>
                            <button type="button" class="sharp-btn-ai" @click="generateExperienceAI(index)" :disabled="aiCredits <= 0">{{ __('messages.ai_generate', [], $currentLang) }}</button>
                        </div>
                        <textarea :name="'experiences['+index+'][description]'" x-model="exp.description" rows="4" class="sharp-input"></textarea>
                    </div>
                </div>
            </div>
        </template>
        <button type="button" @click="addExperience()" class="sharp-btn-secondary">{{ __('messages.add_experience', [], $currentLang) }}</button>
    </div>
</div>