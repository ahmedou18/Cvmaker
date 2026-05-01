@props(['currentLang' => 'ar'])

<div x-show="step === 6" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.step_hobbies', [], $currentLang) ?? 'الهوايات والاهتمامات' }}</h2>
        <template x-for="(hobby, idx) in hobbiesArray" :key="hobby.id">
            <div class="flex flex-wrap gap-3 mb-4 items-center bg-gray-50 p-3 rounded">
                <div class="w-20">
                    <input type="text" :name="'hobbies['+idx+'][icon]'" x-model="hobby.icon" class="sharp-input text-center" placeholder="📚">
                </div>
                <div class="flex-1">
                    <input type="text" :name="'hobbies['+idx+'][name]'" x-model="hobby.name" class="sharp-input w-full" placeholder="الهواية (مثال: القراءة)">
                    <span x-show="getFieldError(`hobbies.${idx}.name`)" 
                          x-text="getFieldError(`hobbies.${idx}.name`)" 
                          class="text-red-500 text-xs mt-1"></span>
                </div>
                <div class="flex-1">
                    <input type="text" :name="'hobbies['+idx+'][description]'" x-model="hobby.description" class="sharp-input w-full" placeholder="وصف قصير (اختياري)">
                    <span x-show="getFieldError(`hobbies.${idx}.description`)" 
                          x-text="getFieldError(`hobbies.${idx}.description`)" 
                          class="text-red-500 text-xs mt-1"></span>
                </div>
                <button type="button" @click="hobbiesArray.splice(idx,1)" class="text-red-600 px-3 py-2 text-xl">✕</button>
            </div>
        </template>
        <button type="button" @click="addHobby()" class="sharp-btn-secondary mt-2">+ {{ __('messages.add_hobby', [], $currentLang) ?? 'أضف هواية' }}</button>
    </div>
</div>