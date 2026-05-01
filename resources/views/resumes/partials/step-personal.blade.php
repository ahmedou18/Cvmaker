@props(['resume' => null, 'isEdit' => false, 'currentLang' => 'ar'])

<div x-show="step === 1" x-transition>
    <div class="sharp-card">
        <h2 class="text-2xl font-black mb-8 border-b pb-4">{{ __('messages.personal_info', [], $currentLang) }}</h2>
        
        @if($isEdit && $resume)
            @if($resume->is_name_locked)
                <div class="bg-yellow-50 border-r-4 border-yellow-500 p-4 mb-6 text-yellow-800 text-sm font-bold">
                    🔒 {{ __('messages.name_locked_message', [], $currentLang) }}
                </div>
            @else
                <div class="bg-blue-50 text-blue-800 p-3 mb-6 text-sm">
                    {{ __('messages.name_changes_left', ['count' => $resume->name_changes_left], $currentLang) }}
                    @if($resume->name_changes_left == 1)
                        <span class="font-bold text-red-600 block mt-1">⚠️ {{ __('messages.name_change_warning', [], $currentLang) }}</span>
                    @endif
                </div>
            @endif
        @endif

        <div class="mb-8 p-6 border border-blue-200 text-center transition-all"
             :class="aiCredits <= 0 ? 'ai-locked-section' : 'bg-blue-50'">
            <template x-if="aiCredits <= 0">
                <p class="text-red-600 font-bold text-xs mb-2">🔒 {{ __('messages.insufficient_credits', [], $currentLang) }}</p>
            </template>
            <h3 class="text-lg font-bold text-blue-800 mb-2">{{ __('messages.save_time', [], $currentLang) }}</h3>
            <button type="button" class="sharp-btn-primary !bg-blue-600" 
                    @click="$refs.cvFileInput.click()" :disabled="isUploading || aiCredits <= 0">
                <span x-show="!isUploading">📄 {{ __('messages.upload_pdf_ai', [], $currentLang) }}</span>
                <span x-show="isUploading">⏳ {{ __('messages.processing', [], $currentLang) }}</span>
            </button>
            <input type="file" x-ref="cvFileInput" accept=".pdf" class="hidden" @change="uploadAndParseCV($event)">
        </div>

        <div class="mb-8">
            <label class="sharp-label">{{ __('messages.profile_photo_label', [], $currentLang) }}</label>
            <div class="flex items-center gap-6">
                <div class="w-32 h-32 bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden" 
                     :style="shape === 'circle' ? 'border-radius: 50% !important' : ''">
                    <template x-if="croppedPhotoData">
                        <img :src="croppedPhotoData" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!croppedPhotoData">
                        <span class="text-4xl text-gray-400">👤</span>
                    </template>
                </div>
                <div class="flex flex-col gap-2">
                    <label class="sharp-btn-primary !px-4 !py-2 !text-sm cursor-pointer">
                        {{ __('messages.upload_photo', [], $currentLang) }} <input type="file" class="hidden" accept="image/*" @change="initFile">
                    </label>
                    <button type="button" @click="shape = shape === 'circle' ? 'square' : 'circle'" class="text-xs font-bold text-blue-600 underline">{{ __('messages.change_frame', [], $currentLang) }}</button>
                </div>
            </div>
            <input type="hidden" name="cropped_photo_base64" :value="croppedPhotoData">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- الاسم الكامل --}}
            <div>
                <label class="sharp-label">{{ __('messages.full_name_label', [], $currentLang) }}</label>
                <input type="text" name="full_name" x-model="full_name" 
                       class="sharp-input" required
                       @if($isEdit && $resume && $resume->is_name_locked) readonly style="background-color:#f3f4f6;" @endif
                       @if($isEdit && $resume && !$resume->is_name_locked && $resume->name_changes_left == 1)
                           @change="showNameWarning()"
                       @endif
                >
                <span x-show="getFieldError('full_name')" 
                      x-text="getFieldError('full_name')" 
                      class="text-red-500 text-xs mt-1"></span>
            </div>

            {{-- رقم الهاتف --}}
            <div>
                <label class="sharp-label">{{ __('messages.phone_label', [], $currentLang) }}</label>
                <input type="tel" name="phone" x-model="phone" class="sharp-input" required>
                <span x-show="getFieldError('phone')" 
                      x-text="getFieldError('phone')" 
                      class="text-red-500 text-xs mt-1"></span>
            </div>

            {{-- المسمى الوظيفي --}}
            <div>
                <label class="sharp-label">{{ __('messages.job_title_label', [], $currentLang) }}</label>
                <input type="text" name="job_title" x-model="job_title" class="sharp-input">
                <span x-show="getFieldError('job_title')" 
                      x-text="getFieldError('job_title')" 
                      class="text-red-500 text-xs mt-1"></span>
            </div>

            {{-- البريد الإلكتروني --}}
            <div>
                <label class="sharp-label">{{ __('messages.email_label', [], $currentLang) }}</label>
                <input type="email" name="email" x-model="email" class="sharp-input">
                <span x-show="getFieldError('email')" 
                      x-text="getFieldError('email')" 
                      class="text-red-500 text-xs mt-1"></span>
            </div>

            {{-- العنوان --}}
            <div class="col-span-full">
                <label class="sharp-label">{{ __('messages.address_label', [], $currentLang) }}</label>
                <input type="text" name="address" x-model="address" class="sharp-input">
                <span x-show="getFieldError('address')" 
                      x-text="getFieldError('address')" 
                      class="text-red-500 text-xs mt-1"></span>
            </div>
        </div>
    </div>
</div>