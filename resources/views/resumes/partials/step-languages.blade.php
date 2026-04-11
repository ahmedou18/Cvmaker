<div x-show="step === 5" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">05. اللغات</h2>
        <template x-for="(lang, index) in languages" :key="lang.id">
            <div class="mb-6 p-4 border border-gray-100 bg-gray-50 flex gap-4 items-start">
                <div class="flex-grow">
                    <label class="sharp-label">اللغة</label>
                    <input type="text" :name="'languages['+index+'][name]'" x-model="lang.name" class="sharp-input" placeholder="مثال: العربية، الإنجليزية">
                </div>
                <div class="w-1/3">
                    <label class="sharp-label">المستوى</label>
                    <select :name="'languages['+index+'][proficiency]'" x-model="lang.proficiency" class="sharp-input">
                        <option value="مبتدئ">مبتدئ</option>
                        <option value="متوسط">متوسط</option>
                        <option value="ممتاز">ممتاز</option>
                        <option value="لغة أم">لغة أم</option>
                    </select>
                </div>
                <button type="button" @click="languages.splice(index, 1)" class="bg-red-500 text-white p-3 h-[50px] mt-8 flex items-center justify-center rounded">🗑️</button>
            </div>
        </template>
        <button type="button" @click="addLanguage()" class="sharp-btn-secondary">+ إضافة لغة</button>
    </div>
</div>