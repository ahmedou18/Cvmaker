@props(['currentLang' => 'ar'])

<div x-show="step === 7" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.references', [], $currentLang) ?? 'المراجع' }}</h2>
        <template x-for="(ref, idx) in referencesArray" :key="ref.id">
            <div class="mb-8 p-5 border border-gray-200 bg-gray-50 relative">
                <button type="button" @click="referencesArray.splice(idx,1)" class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">✕</button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    {{-- الاسم الكامل --}}
                    <div>
                        <label class="sharp-label">{{ __('messages.full_name', [], $currentLang) ?? 'الاسم الكامل' }} *</label>
                        <input type="text" :name="'references['+idx+'][full_name]'" x-model="ref.full_name" class="sharp-input" required>
                        <span x-show="getFieldError(`references.${idx}.full_name`)" 
                              x-text="getFieldError(`references.${idx}.full_name`)" 
                              class="text-red-500 text-xs mt-1"></span>
                    </div>
                    {{-- المسمى الوظيفي --}}
                    <div>
                        <label class="sharp-label">{{ __('messages.job_title', [], $currentLang) ?? 'المسمى الوظيفي' }}</label>
                        <input type="text" :name="'references['+idx+'][job_title]'" x-model="ref.job_title" class="sharp-input">
                        <span x-show="getFieldError(`references.${idx}.job_title`)" 
                              x-text="getFieldError(`references.${idx}.job_title`)" 
                              class="text-red-500 text-xs mt-1"></span>
                    </div>
                    {{-- جهة العمل --}}
                    <div>
                        <label class="sharp-label">{{ __('messages.company', [], $currentLang) ?? 'جهة العمل' }}</label>
                        <input type="text" :name="'references['+idx+'][company]'" x-model="ref.company" class="sharp-input">
                        <span x-show="getFieldError(`references.${idx}.company`)" 
                              x-text="getFieldError(`references.${idx}.company`)" 
                              class="text-red-500 text-xs mt-1"></span>
                    </div>
                    {{-- البريد الإلكتروني --}}
                    <div>
                        <label class="sharp-label">{{ __('messages.email', [], $currentLang) ?? 'البريد الإلكتروني' }}</label>
                        <input type="email" :name="'references['+idx+'][email]'" x-model="ref.email" class="sharp-input">
                        <span x-show="getFieldError(`references.${idx}.email`)" 
                              x-text="getFieldError(`references.${idx}.email`)" 
                              class="text-red-500 text-xs mt-1"></span>
                    </div>
                    {{-- رقم الهاتف --}}
                    <div>
                        <label class="sharp-label">{{ __('messages.phone', [], $currentLang) ?? 'رقم الهاتف' }}</label>
                        <input type="tel" :name="'references['+idx+'][phone]'" x-model="ref.phone" class="sharp-input">
                        <span x-show="getFieldError(`references.${idx}.phone`)" 
                              x-text="getFieldError(`references.${idx}.phone`)" 
                              class="text-red-500 text-xs mt-1"></span>
                    </div>
                    {{-- ملاحظات --}}
                    <div class="col-span-full">
                        <label class="sharp-label">{{ __('messages.notes', [], $currentLang) ?? 'ملاحظات إضافية' }}</label>
                        <textarea :name="'references['+idx+'][notes]'" x-model="ref.notes" rows="2" class="sharp-input"></textarea>
                        <span x-show="getFieldError(`references.${idx}.notes`)" 
                              x-text="getFieldError(`references.${idx}.notes`)" 
                              class="text-red-500 text-xs mt-1"></span>
                    </div>
                </div>
            </div>
        </template>
        <button type="button" @click="addReference()" class="sharp-btn-secondary mt-2">+ {{ __('messages.add_reference', [], $currentLang) ?? 'أضف مرجعاً' }}</button>
    </div>
</div>