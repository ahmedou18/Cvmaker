@props(['currentLang' => 'ar'])

<div class="flex justify-between items-center mt-8 pt-4 border-t gap-4">
    <button type="button" x-show="step > 1" @click="step--" class="sharp-btn-primary !bg-gray-400">
        {{ __('messages.prev', [], $currentLang) }}
    </button>
    <div x-show="step === 1" class="flex-grow"></div>
    <button type="button" x-show="step < maxStep" @click="step++" class="sharp-btn-primary">
        {{ __('messages.next', [], $currentLang) }}
    </button>
    <button type="submit" x-show="step === maxStep" class="sharp-btn-primary !bg-green-600">
        {{ $submitText ?? __('messages.save_publish', [], $currentLang) }}
    </button>
</div>