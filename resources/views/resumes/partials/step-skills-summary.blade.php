<div x-show="step === 4" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">04. المهارات والملخص</h2>
        <div class="space-y-8">
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="sharp-label mb-0">المهارات (افصل بينها بفاصلة)</label>
                    <button type="button" class="sharp-btn-ai" @click="generateSkillsAI()" :disabled="aiCredits <= 0">✨ اقتراح مهارات</button>
                </div>
                <input type="text" name="skills" x-model="skills" class="sharp-input" placeholder="مثال: القيادة، حل المشكلات، البرمجة...">
            </div>
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="sharp-label mb-0">الملخص المهني</label>
                    <button type="button" class="sharp-btn-ai" @click="generateSummaryAI()" :disabled="aiCredits <= 0">✨ صياغة ملخص ذكي</button>
                </div>
                <textarea name="summary" x-model="summary" rows="5" class="sharp-input"></textarea>
            </div>
        </div>
    </div>
</div>