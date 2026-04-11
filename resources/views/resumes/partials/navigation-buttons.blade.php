<div class="flex justify-between items-center mt-8 pt-4 border-t gap-4">
    <button type="button" x-show="step > 1" @click="step--" class="sharp-btn-primary !bg-gray-400">
        السابق
    </button>
    <div x-show="step === 1" class="flex-grow"></div>
    <button type="button" x-show="step < maxStep" @click="step++" class="sharp-btn-primary">
        التالي
    </button>
    <button type="submit" x-show="step === maxStep" class="sharp-btn-primary !bg-green-600">
        {{ $submitText ?? 'حفظ ونشر السيرة ✅' }}
    </button>
</div>