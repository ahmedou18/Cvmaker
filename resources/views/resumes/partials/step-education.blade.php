@props(['currentLang' => 'ar'])

<div x-show="step === 2" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.step_education', [], $currentLang) }}</h2>
        <template x-for="(edu, index) in educations" :key="edu.id">
            <div class="mb-8 p-6 border border-gray-200 bg-gray-50 relative">
                <button type="button" @click="educations.splice(index, 1)" class="absolute top-0 left-0 bg-red-600 text-white px-3 py-1 text-xs font-bold">{{ __('messages.delete', [], $currentLang) }}</button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                    <div class="col-span-full">
                        <label class="sharp-label">{{ __('messages.institution', [], $currentLang) }}</label>
                        <input type="text" :name="'educations['+index+'][institution]'" x-model="edu.institution" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">{{ __('messages.degree', [], $currentLang) }}</label>
                        <input type="text" :name="'educations['+index+'][degree]'" x-model="edu.degree" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">{{ __('messages.field_of_study', [], $currentLang) }}</label>
                        <input type="text" :name="'educations['+index+'][field_of_study]'" x-model="edu.field_of_study" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">{{ __('messages.graduation_year', [], $currentLang) }}</label>
                        <input type="text" :name="'educations['+index+'][graduation_year]'" x-model="edu.graduation_year" class="sharp-input">
                    </div>
                </div>
            </div>
        </template>
        <button type="button" @click="addEducation()" class="sharp-btn-secondary">{{ __('messages.add_education', [], $currentLang) }}</button>
    </div>
</div>