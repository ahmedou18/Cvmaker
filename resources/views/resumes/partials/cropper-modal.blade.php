@props(['currentLang' => 'ar'])

<div x-show="showCropperModal" class="fixed-modal-overlay" x-transition.opacity>
    <div class="modal-box">
        <h3 class="text-xl font-bold mb-4">{{ __('messages.edit_profile_photo', [], $currentLang) }}</h3>
        <div class="cropper-wrapper">
            <img id="cropper-image" src="">
        </div>
        <div class="flex justify-end gap-3">
            <button type="button" @click="cancelCropping" class="px-6 py-2 border font-bold">{{ __('messages.cancel', [], $currentLang) }}</button>
            <button type="button" @click="saveCroppedImage" class="px-6 py-2 bg-blue-600 text-white font-bold">{{ __('messages.crop_save', [], $currentLang) }}</button>
        </div>
    </div>
</div>