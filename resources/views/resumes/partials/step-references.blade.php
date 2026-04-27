@props(['currentLang' => 'ar'])

<div x-show="step === 7" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.references', [], $currentLang) ?? 'المراجع' }}</h2>
        <template x-for="(ref, idx) in referencesArray" :key="ref.id">
            <div class="mb-8 p-4 border border-gray-200 bg-gray-50 relative">
                <button type="button" @click="referencesArray.splice(idx,1)" class="absolute top-2 left-2 bg-red-600 text-white text-xs px-2 py-1 rounded">✕</button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div>
                        <label class="sharp-label">الاسم الكامل</label>
                        <input type="text" :name="'references['+idx+'][full_name]'" x-model="ref.full_name" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">المسمى الوظيفي</label>
                        <input type="text" :name="'references['+idx+'][job_title]'" x-model="ref.job_title" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">جهة العمل</label>
                        <input type="text" :name="'references['+idx+'][company]'" x-model="ref.company" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">البريد الإلكتروني</label>
                        <input type="email" :name="'references['+idx+'][email]'" x-model="ref.email" class="sharp-input">
                    </div>
                    <div>
                        <label class="sharp-label">رقم الهاتف</label>
                        <input type="tel" :name="'references['+idx+'][phone]'" x-model="ref.phone" class="sharp-input">
                    </div>
                    <div class="col-span-full">
                        <label class="sharp-label">ملاحظات إضافية</label>
                        <textarea :name="'references['+idx+'][notes]'" x-model="ref.notes" rows="2" class="sharp-input"></textarea>
                    </div>
                </div>
            </div>
        </template>
        <button type="button" @click="addReference()" class="sharp-btn-secondary mt-2">+ {{ __('messages.add_reference', [], $currentLang) ?? 'أضف مرجعاً' }}</button>
    </div>
</div>