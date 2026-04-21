@props(['currentLang' => 'ar'])

<div x-show="step === 5" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.step_languages', [], $currentLang) }}</h2>
        <template x-for="(lang, index) in languages" :key="lang.id">
            <div class="mb-6 p-4 border border-gray-100 bg-gray-50 flex gap-4 items-start">
                <div class="flex-grow">
                    <label class="sharp-label">{{ __('messages.language_name', [], $currentLang) }}</label>
                    <input type="text" :name="'languages['+index+'][name]'" x-model="lang.name" class="sharp-input" placeholder="{{ __('messages.language_placeholder', [], $currentLang) }}">
                </div>
                <div class="w-1/3">
                    <label class="sharp-label">{{ __('messages.proficiency', [], $currentLang) }}</label>
                    <select :name="'languages['+index+'][proficiency]'" x-model="lang.proficiency" class="sharp-input">
                        <option value="{{ __('messages.beginner', [], $currentLang) }}">{{ __('messages.beginner', [], $currentLang) }}</option>
                        <option value="{{ __('messages.intermediate', [], $currentLang) }}">{{ __('messages.intermediate', [], $currentLang) }}</option>
                        <option value="{{ __('messages.advanced', [], $currentLang) }}">{{ __('messages.advanced', [], $currentLang) }}</option>
                        <option value="{{ __('messages.native', [], $currentLang) }}">{{ __('messages.native', [], $currentLang) }}</option>
                    </select>
                </div>
                <button type="button" @click="languages.splice(index, 1)" class="bg-red-500 text-white p-3 h-[50px] mt-8 flex items-center justify-center rounded">{{ __('messages.delete', [], $currentLang) }}</button>
            </div>
        </template>
        <button type="button" @click="addLanguage()" class="sharp-btn-secondary">{{ __('messages.add_language', [], $currentLang) }}</button>
    </div>
</div>