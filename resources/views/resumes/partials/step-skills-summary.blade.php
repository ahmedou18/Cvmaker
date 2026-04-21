@props(['currentLang' => 'ar'])

<div x-show="step === 4" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.step_skills_summary', [], $currentLang) }}</h2>
        <div class="space-y-8">
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="sharp-label mb-0">{{ __('messages.skills_label', [], $currentLang) }}</label>
                    <button type="button" class="sharp-btn-ai" @click="generateSkillsAI()" :disabled="aiCredits <= 0">{{ __('messages.ai_suggest_skills', [], $currentLang) }}</button>
                </div>
                <input type="text" name="skills" x-model="skills" class="sharp-input" placeholder="{{ __('messages.skills_placeholder', [], $currentLang) }}">
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="sharp-label mb-0">{{ __('messages.summary_label', [], $currentLang) }}</label>
                    <button type="button" class="sharp-btn-ai" @click="generateSummaryAI()" :disabled="aiCredits <= 0">{{ __('messages.ai_write_summary', [], $currentLang) }}</button>
                </div>
                <textarea name="summary" x-model="summary" rows="5" class="sharp-input"></textarea>
            </div>
        </div>
    </div>
</div>